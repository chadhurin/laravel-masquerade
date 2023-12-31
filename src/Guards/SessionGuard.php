<?php

namespace Chadhurin\LaravelMasquerade\Guards;

use Illuminate\Auth\SessionGuard as BaseSessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;

class SessionGuard extends BaseSessionGuard
{
    /**
     * Log a user into the application without firing the Login event.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return void
     */
    public function quietLogin(Authenticatable $user)
    {
        $this->updateSession($user->getAuthIdentifier());

        $this->setUser($user);
    }

    /**
     * Logout the user without updating remember_token
     * and without firing the Logout event.
     *
     * @return  void
     */
    public function quietLogout()
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            $this->session->remove('password_hash_' . $guard);
        }

        $this->clearUserDataFromStorage();

        $this->user = null;

        $this->loggedOut = true;
    }
}
