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
 * Class InvalidArgumentException
 *
 * @package RunOpenCode\ExchangeRate\Exception
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{

}
