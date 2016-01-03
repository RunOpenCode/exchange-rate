<?php

namespace GuzzleHttp;

class Client
{
    private static $expect = null;

    public static function expect($arg)
    {
        if (self::$expect === null) {
            self::$expect = new \SplQueue();
        }

        self::$expect->enqueue($arg);
    }

    public function __call($name, $arguments)
    {
        if (self::$expect === null) {
            throw new \RuntimeException('There is nothing to expect as method call.');
        }

        $value = self::$expect->dequeue();

        if ($value instanceof \Exception) {
            throw $value;
        } else {
            return $value;
        }
    }
}