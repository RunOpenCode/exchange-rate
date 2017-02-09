<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Contract;

use Psr\Log\LoggerAwareInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;

/**
 * Interface ProcessorInterface
 *
 * Processor process fetched rates.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface ProcessorInterface extends LoggerAwareInterface
{
    /**
     * Process all given rates (after fetch process).
     *
     * @param string $baseCurrencyCode Base currency code.
     * @param RatesConfigurationRegistryInterface $configurations Rates configurations.
     * @param RateInterface[] $rates All available rates.
     *
     * @return RateInterface[] Processed rates.
     *
     * @throws ExchangeRateException
     */
    public function process($baseCurrencyCode, RatesConfigurationRegistryInterface $configurations, array $rates);
}
