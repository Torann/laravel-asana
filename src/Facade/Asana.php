<?php

namespace Torann\LaravelAsana\Facade;

class Asana extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'torann.asana';
    }
}
