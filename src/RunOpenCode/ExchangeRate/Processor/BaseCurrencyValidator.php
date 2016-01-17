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

use RunOpenCode\ExchangeRate\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Class BaseCurrencyValidator
 *
 * Processor which checks weather all rates have same base currency code.
 *
 * This processor should be added to processing queue as one of the last ones.
 *
 * @package RunOpenCode\ExchangeRate\Processor
 */
class BaseCurrencyValidator implements ProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates)
    {
        $baseCurrencyCode = CurrencyCodeUtil::clean($baseCurrencyCode);

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {

            if ($baseCurrencyCode !== $rate->getBaseCurrencyCode()) {
                $message = sprintf('Invalid base currency code "%s" of rate "%s" from source "%s" is not calculated.', $rate->getBaseCurrencyCode(), $rate->getCurrencyCode(), $rate->getSourceName());

                $this->getLogger()->critical($message);
                throw new ConfigurationException($message);
            }
        }

        return $rates;
    }
}
