<?php

namespace App\Support\Session;

class SessionManager
{
    private static ?SessionStore $store = null;

    public static function instance(): SessionStore
    {
        if (self::$store === null) {
            self::$store = new NativeSessionStore();
        }

        return self::$store;
    }

    public static function swap(SessionStore $store): ?SessionStore
    {
        $previous = self::$store;
        self::$store = $store;

        return $previous;
    }

    public static function reset(): void
    {
        self::$store = null;
    }
}
