<?php

namespace RunOpenCode\ExchangeRate\Processor;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;

class NullProcessor implements ProcessorInterface
{
    use  LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process($baseCurrencyCode, array $rateConfigurations, array $rates)
    {
        return $rates;
    }
}