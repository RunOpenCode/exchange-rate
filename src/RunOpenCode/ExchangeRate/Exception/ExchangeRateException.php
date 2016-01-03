<?php

namespace RunOpenCode\ExchangeRate\Exception;

class ExchangeRateException extends \Exception
{
    /**
     * Get type of argument for exception messages.
     *
     * @param $arg
     * @return string
     */
    public static function typeOf($arg) {
        if (is_null($arg)) {
            return 'NULL';
        } elseif (is_object($arg)) {
            return get_class($arg);
        } else {
            return gettype($arg);
        }
    }
}
