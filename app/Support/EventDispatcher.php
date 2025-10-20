<?php

namespace App\Support;

class EventDispatcher
{
    /**
     * @var array<string, array<int, callable>>
     */
    private static array $listeners = [];

    public static function listen(string $eventClass, callable $listener): void
    {
        self::$listeners[$eventClass][] = $listener;
    }

    public static function dispatch(object $event): void
    {
        $eventClass = get_class($event);
        foreach (self::$listeners[$eventClass] ?? [] as $listener) {
            $listener($event);
        }
    }
}

