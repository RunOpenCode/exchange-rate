<?php

namespace RunOpenCode\ExchangeRate\Processor;

use Psr\Log\LoggerAwareTrait;
use RunOpenCode\AssetsInjection\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

class BaseCurrencyValidator implements ProcessorInterface
{
    use  LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates)
    {
        if (!CurrencyCode::exists($baseCurrencyCode)) {
            throw new \RuntimeException(sprintf('Unknown base currency code "%s".', $baseCurrencyCode));
        }

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {

            if ($baseCurrencyCode !== $rate->getBaseCurrencyCode()) {
                throw new ConfigurationException(sprintf('Invalid base currency code "%s" of rate "%s" from source "%s" is not calculated.', $rate->getBaseCurrencyCode(), $rate->getCurrencyCode(), $rate->getSourceName()));
            }
        }

        return $rates;
    }
}
