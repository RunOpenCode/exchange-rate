<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
