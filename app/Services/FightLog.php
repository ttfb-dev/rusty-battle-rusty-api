<?php


namespace App\Services;


class FightLog
{
    /** @var string[] */
    private static $log;

    public static function write(string $message) {
        self::$log []= $message;
    }

    /** @return string[] */
    public static function read(): array {
        return self::$log;
    }

    public static function clear() {
        self::$log = [];
    }
}
