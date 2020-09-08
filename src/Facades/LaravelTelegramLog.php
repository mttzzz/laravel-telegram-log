<?php

namespace mttzzz\LaravelTelegramLog\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelTelegramLog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laraveltelegramlog';
    }
}
