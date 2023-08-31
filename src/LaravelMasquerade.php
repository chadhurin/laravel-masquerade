<?php

namespace Chadhurin\LaravelMasquerade;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LaravelMasquerade
{
    const REMEMBER_PREFIX = 'remember_web';

    /** @var Application $app */
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * @param int $id
     *
     * @return Authenticatable
     * @throws \Exception
     */
    public function findUserById($id, $guardName = null)
    {
        if (empty($guardName)) {
            $guardName = $this->app['config']->get('auth.default.guard', 'web');
        }

        $providerName = $this->app['config']->get("auth.guards.$guardName.provider");

        if (empty($providerName)) {
            throw new \Exception("MissingUserProvider");
        }


        try {
            /** @var UserProvider $userProvider */
            $userProvider = $this->app['auth']->createUserProvider($providerName);
        } catch (\InvalidArgumentException $e) {
            dd("here2", $e);
            throw new \Exception("InvalidUserProvider");
        }


        if (!($modelInstance = $userProvider->retrieveById($id))) {
            $model = $this->app['config']->get("auth.providers.$providerName.model");

            throw (new ModelNotFoundException())->setModel(
                $model,
                $id
            );
        }

        return $modelInstance;
    }


    public function isMasquerading(): bool
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @return  int|null
     */
    public function getMasqueraderId()
    {
        return session($this->getSessionKey(), null);
    }

    /**
     * @return Authenticatable
     */
    public function getMasquerader()
    {
        $id = session($this->getSessionKey(), null);

        return is_null($id) ? null : $this->findUserById($id, $this->getMasqueraderGuardName());
    }

    /**
     * @return string|null
     */
    public function getMasqueraderGuardName()
    {
        return session($this->getSessionGuard(), null);
    }

    /**
     * @return string|null
     */
    public function getMasqueraderGuardUsingName()
    {
        return session($this->getSessionGuardUsing(), null);
    }


    /**
     * @param Authenticatable $from
     * @param Authenticatable $to
     * @param string|null $guardName
     *
     * @return bool
     */
    public function take(Authenticatable $from, Authenticatable $to, string $guardName = null)
    {
        $this->saveAuthCookieInSession();

        try {
            $currentGuard = $this->getCurrentAuthGuardName();

            $guardName = $guardName ?? $this->getDefaultSessionGuard();

            if ($this->currentAuthGuardIsSanctum()) {
                $this->app['auth']->guard("web")->quietLogout();
                $this->app['auth']->guard("web")->quietLogin($to);

                $this->app['auth']->guard($guardName)->login($to);
                $this->app['auth']->guard('sanctum')->setUser($to);
            } else {
                $this->app['auth']->guard($currentGuard)->quietLogout();
                $this->app['auth']->guard($guardName)->quietLogin($to);

                $this->app['auth']->guard('web')->login($to);
                $this->app['auth']->guard('sanctum')->setUser($to);
            }


            session()->put($this->getSessionKey(), $from->getAuthIdentifier());
            session()->put($this->getSessionGuard(), $currentGuard);
            session()->put($this->getSessionGuardUsing(), $guardName);


        } catch (\Exception $e) {
            dd("here", $e);
            unset($e);
            return false;
        }

        return true;
    }


    public function leave(): bool
    {
        try {
            $masqueraded = $this->app['auth']->guard($this->getMasqueraderGuardUsingName())->user();
            $masquerader = $this->findUserById($this->getMasqueraderId(), $this->getMasqueraderGuardName());

            $this->app['auth']->guard($this->getCurrentAuthGuardName())->quietLogout();
            $this->app['auth']->guard($this->getMasqueraderGuardName())->quietLogin($masquerader);

            $this->app['auth']->guard('web')->login($masquerader);
            $this->app['auth']->guard('sanctum')->setUser($masquerader);

            $this->extractAuthCookieFromSession();

            $this->clear();

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        return true;
    }

    public function clear()
    {
        session()->forget($this->getSessionKey());
        session()->forget($this->getSessionGuard());
        session()->forget($this->getSessionGuardUsing());
    }


    public function getSessionKey(): string
    {
        return config('laravel-masquerade.session_key');
    }

    public function getSessionGuard(): string
    {
        return config('laravel-masquerade.session_guard');
    }

    public function getSessionGuardUsing(): string
    {
        return config('laravel-masquerade.session_guard_using');
    }


    public function getDefaultSessionGuard(): string
    {
        return config('laravel-masquerade.default_masquerade_guard');
    }

    public function getTakeRedirectTo(): string
    {
        try {
            $uri = route(config('laravel-masquerade.take_redirect_to'));
        } catch (\InvalidArgumentException $e) {
            $uri = config('laravel-masquerade.take_redirect_to');
        }

        return $uri;
    }

    public function getLeaveRedirectTo(): string
    {
        try {
            $uri = route(config('laravel-masquerade.leave_redirect_to'));
        } catch (\InvalidArgumentException $e) {
            $uri = config('laravel-masquerade.leave_redirect_to');
        }

        return $uri;
    }

    /**
     * @return bool
     */
    public function currentAuthGuardIsSanctum(): bool
    {
        if (in_array('sanctum', array_keys(config('auth.guards')))) {
            return $this->app['auth']->guard('sanctum')->check();
        }

        return false;
    }


    /**
     * @return int|string|null
     */
    public function getCurrentAuthGuardName()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if ($this->app['auth']->guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    protected function saveAuthCookieInSession(): void
    {
        $cookie = $this->findByKeyInArray($this->app['request']->cookies->all(), static::REMEMBER_PREFIX);
        $key = $cookie->keys()->first();
        $val = $cookie->values()->first();

        if (!$key || !$val) {
            return;
        }

        session()->put(static::REMEMBER_PREFIX, [
            $key,
            $val,
        ]);
    }

    protected function extractAuthCookieFromSession(): void
    {
        if (!$session = $this->findByKeyInArray(session()->all(), static::REMEMBER_PREFIX)->first()) {
            return;
        }

        $this->app['cookie']->queue($session[0], $session[1]);
        session()->forget($session);
    }

    /**
     * @param array $values
     * @param string $search
     *
     * @return Collection
     */
    protected function findByKeyInArray(array $values, string $search)
    {
        return collect($values ?? session()->all())
            ->filter(function ($val, $key) use ($search) {
                return str_contains($key, $search);
            });
    }
}
