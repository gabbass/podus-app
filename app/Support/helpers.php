<?php

use App\Support\ResponseFactory;

if (!function_exists('response')) {
    function response(): ResponseFactory
    {
        static $factory = null;
        if ($factory === null) {
            $factory = new ResponseFactory();
        }

        return $factory;
    }
}
