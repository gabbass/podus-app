<?php

namespace App\Auth\Middleware;

use App\Auth\Profiles;

class ProfessorMiddleware extends EnsureProfileMiddleware
{
    protected function profile(): Profiles
    {
        return Profiles::Teacher;
    }
}
