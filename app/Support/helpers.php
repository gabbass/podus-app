<?php

use App\Support\ResponseFactory;
use App\Support\Session\SessionManager;
use App\Support\Session\SessionStore;

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

if (!function_exists('session')) {
    /**
     * @return SessionStore|mixed
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $store = SessionManager::instance();

        if ($key === null) {
            return $store;
        }

        return $store->get($key, $default);
    }
}
