<?php

namespace App\Auth\Middleware;

use App\Auth\Profiles;

class SchoolMiddleware extends EnsureProfileMiddleware
{
    protected function profile(): Profiles
    {
        return Profiles::School;
    }
}
