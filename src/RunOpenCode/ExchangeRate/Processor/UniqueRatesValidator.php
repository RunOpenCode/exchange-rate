<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Processor;

use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;

/**
 * Class UniqueRatesValidator
 *
 * Checks if all rates are unique in collection (e.g. there should not be rates from same source with same currency code,
 * same rate type which is valid on same date).
 *
 * This processor should be added to processing queue as one of the last ones.
 *
 * @package RunOpenCode\ExchangeRate\Processor
 */
class UniqueRatesValidator implements ProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @throws ConfigurationException
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates)
    {
        $registry = array();

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {

            $key = sprintf('%s_%s_%s_%s', $rate->getCurrencyCode(), $rate->getRateType(), $rate->getSourceName(), $rate->getDate()->format('Y-m-d'));

            if (array_key_exists($key, $registry)) {
                $message = sprintf('Currency code "%s" of rate type "%s" from source "%s" valid on date "%s" is duplicated.', $rate->getCurrencyCode(), $rate->getRateType(), $rate->getSourceName(), $rate->getDate()->format('Y-m-d'));

                $this->getLogger()->critical($message);
                throw new ConfigurationException($message);
            }

            $registry[$key] = $rate->getSourceName();
        }

        return $rates;
    }
}
