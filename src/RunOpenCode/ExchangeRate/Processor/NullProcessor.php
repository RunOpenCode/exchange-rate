<?php

namespace RunOpenCode\ExchangeRate\Processor;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;

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
