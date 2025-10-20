<?php

namespace App\Auth\Middleware;

use App\Auth\Profiles;

class AlunoMiddleware extends EnsureProfileMiddleware
{
    protected function profile(): Profiles
    {
        return Profiles::Student;
    }
}
