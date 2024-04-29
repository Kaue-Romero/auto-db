<?php

namespace Leivingson\AutoDB;

use Illuminate\Support\Facades\Facade;
use Illuminate\View\View;

class AutoDB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'autodb';
    }
}
