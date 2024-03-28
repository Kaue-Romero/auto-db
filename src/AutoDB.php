<?php

namespace Leivingson\AutoDB;

use Illuminate\Support\Facades\Facade;

class AutoDB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'autodb';
    }
}
