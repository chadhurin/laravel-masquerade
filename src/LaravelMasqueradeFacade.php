<?php

namespace Chadhurin\LaravelMasquerade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Chadhurin\LaravelMasquerade\Skeleton\SkeletonClass
 */
class LaravelMasqueradeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-masquerade';
    }
}
