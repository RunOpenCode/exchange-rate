<?php

namespace RunOpenCode\ExchangeRate\Contract;

use Psr\Log\LoggerAwareInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;

interface ProcessorInterface extends LoggerAwareInterface
{
    /**
     * Process all given rates to base currency code.
     *
     * @param string $baseCurrencyCode Desired base currency code.
     * @param RatesConfigurationRegistryInterface $configurations Rates configurations.
     * @param RateInterface[] $rates All available rates.
     * @return RateInterface[] Processed rates.
     *
     * @throws ExchangeRateException
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates);
}