<?php

namespace RunOpenCode\ExchangeRate\Contract;

use Psr\Log\LoggerAwareInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\RateConfiguration;

interface ProcessorInterface extends LoggerAwareInterface
{
    /**
     * Process all given rates to base currency code.
     *
     * @param string $baseCurrencyCode Desired base currency code.
     * @param RateInterface[] $rates All available rates.
     * @param RateConfiguration[] $rateConfigurations Rates configurations.
     * @return RateInterface[] Processed rates.
     *
     * @throws ExchangeRateException
     */
    public function process($baseCurrencyCode, array $rateConfigurations, array $rates);
}