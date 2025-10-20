<?php

namespace App\Auth\Middleware;

use App\Auth\Profiles;

class AdminMiddleware extends EnsureProfileMiddleware
{
    protected function profile(): Profiles
    {
        return Profiles::Administrator;
    }
}
