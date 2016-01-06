<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Processor;

use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;

/**
 * Class NullProcessor
 *
 * Null processor does not process.
 *
 * @package RunOpenCode\ExchangeRate\Processor
 */
class NullProcessor implements ProcessorInterface
{
    use  LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates)
    {
        return $rates;
    }
}
