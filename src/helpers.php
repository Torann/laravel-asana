<?php

if (!function_exists('asana')) {
    /**
     * Get the asana instance.
     *
     * @return \Torann\LaravelAsana\Asana
     */
    function asana()
    {
        return app('torann.asana');
    }
}