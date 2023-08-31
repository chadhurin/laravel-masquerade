<?php

namespace Chadhurin\LaravelMasquerade;

use Chadhurin\LaravelMasquerade\Guards\SessionGuard;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;


class LaravelMasqueradeServiceProvider extends ServiceProvider
{
    /** @var string $configName */
    protected string $configName = 'laravel-masquerade';


    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfig();

        $this->app->bind(LaravelMasquerade::class, LaravelMasquerade::class);


        $this->app->singleton(LaravelMasquerade::class, function ($app) {
            return new LaravelMasquerade($app);
        });

        $this->app->alias(LaravelMasquerade::class, 'masquerade');

        $this->registerRoutesMacro();
//        $this->registerBladeDirectives();
//        $this->registerMiddleware();
        $this->registerAuthDriver();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishConfig();

        Event::listen(Login::class, function ($event) {
            app('masquerade')->clear();
        });
        Event::listen(Logout::class, function ($event) {
            app('masquerade')->clear();
        });
    }

    /**
     * Register routes macro.
     *
     * @return  void
     */
    protected function registerRoutesMacro(): void
    {
        $router = $this->app['router'];

        $router->macro('masquerade', function () use ($router) {
            $router->get('/masquerade/take/{id}/{guardName?}',
                '\Chadhurin\LaravelMasquerade\Controllers\LaravelMasqueradeController@take')
                ->name('masquerade');
            $router->get('/masquerade/leave',
                '\Chadhurin\LaravelMasquerade\Controllers\LaravelMasqueradeController@leave')->name('masquerade.leave');
        });
    }

    /**
     * @return  void
     */
    protected function registerAuthDriver()
    {
        /** @var AuthManager $auth */
        $auth = $this->app['auth'];


        $auth->extend('session', function (Application $app, $name, array $config) use ($auth) {
            $provider = $auth->createUserProvider($config['provider']);

            $guard = new SessionGuard($name, $provider, $app['session.store']);

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }

    /**
     * Merge config file.
     *
     * @return  void
     */
    protected function mergeConfig(): void
    {
        $configPath = __DIR__ . '/../config/laravel-masquerade.php';

        $this->mergeConfigFrom($configPath, $this->configName);
    }

    /**
     * Publish config file.
     *
     * @return  void
     */
    protected function publishConfig(): void
    {
        $configPath = __DIR__ . '/../config/laravel-masquerade.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'masquerade');
    }
}
