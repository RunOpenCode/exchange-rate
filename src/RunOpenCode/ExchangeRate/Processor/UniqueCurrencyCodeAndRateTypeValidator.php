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

use RunOpenCode\AssetsInjection\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;

/**
 * Class UniqueCurrencyCodeAndRateTypeValidator
 *
 * Checks if all rates are unique by compound key: "currency code", "rate type".
 *
 * This processor should be added to processing queue as one of the last ones.
 *
 * @package RunOpenCode\ExchangeRate\Processor
 */
class UniqueCurrencyCodeAndRateTypeValidator implements ProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates)
    {
        $registry = array();

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {

            $key = sprintf('%s_%s', $rate->getCurrencyCode(), $rate->getRateType());

            if (array_key_exists($key, $registry)) {
                $message = sprintf('Currency code "%s" of rate type "%s" is being fetched from at least two sources: "%s" and "%s".', $rate->getCurrencyCode(), $rate->getRateType(), $rate->getSourceName(), $registry[$key]);

                $this->getLogger()->critical($message);
                throw new ConfigurationException($message);
            } else {
                $registry[$key] = $rate->getSourceName();
            }

        }

        return $rates;
    }
}
