<?php

namespace Chadhurin\LaravelMasquerade\Traits;

use Illuminate\Database\Eloquent\Model;
use Chadhurin\LaravelMasquerade\LaravelMasquerade;

trait Masquerade
{
    /**
     * Return true or false if the user can masquerade another user.
     *
     * @return  bool
     */
    public function canMasquerade(): bool
    {
        return true;
    }

    /**
     * Return true or false if the user can be masqueraded.
     *
     * @return  bool
     */
    public function canBeMasqueraded(): bool
    {
        return true;
    }

    /**
     * Masquerade the given user.
     *
     * @param Model $user
     * @param string|null $guardName
     *
     * @return  bool
     */
    public function masquerade(Model $user, string $guardName = null): bool
    {
        return app(LaravelMasquerade::class)->take($this, $user, $guardName);
    }

    /**
     * Check if the current user is masqueraded.
     *
     * @return  bool
     */
    public function isMasqueraded(): bool
    {
        return app(LaravelMasquerade::class)->isMasquerading();
    }

    /**
     * Leave the current masquerade.
     *
     * @return bool
     */
    public function leaveMasquerade(): bool
    {
        if ($this->isMasqueraded()) {
            return app(LaravelMasquerade::class)->leave();
        }

        return false;
    }
}