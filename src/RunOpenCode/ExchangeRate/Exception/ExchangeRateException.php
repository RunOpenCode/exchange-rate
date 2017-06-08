<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Exception;

use RunOpenCode\ExchangeRate\Contract\ExceptionInterface;

/**
 * Class ExchangeRateException
 *
 * @package RunOpenCode\ExchangeRate\Exception
 */
class ExchangeRateException extends \Exception implements ExceptionInterface
{
    /**
     * Get type of argument for exception messages.
     *
     * @param $arg
     * @return string
     */
    public static function typeOf($arg) {

        if (null === $arg) {
            return 'NULL';
        }

        if (is_object($arg)) {
            return get_class($arg);
        }

        return gettype($arg);
    }
}
