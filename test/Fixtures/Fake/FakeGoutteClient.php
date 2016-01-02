<?php

namespace Goutte;

class Client
{
    private static $expect;

    public static function expect($arg)
    {
        self::$expect = $arg;
    }

    public function __call($name, $arguments)
    {
        return self::___fakerResult___();
    }

    private static function ___fakerResult___()
    {
        if (self::$expect instanceof \Exception) {
            throw self::$expect;
        } else {
            return self::$expect;
        }
    }

}
