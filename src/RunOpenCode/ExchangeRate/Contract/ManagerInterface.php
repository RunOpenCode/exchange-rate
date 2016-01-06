<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Contract;

use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;

/**
 * Interface ManagerInterface
 *
 * Manager manages exchange rates and it is entry point to access exchange rate system.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface ManagerInterface
{
    /**
     * Check if exchange rate for currency is available on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return bool
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     */
    public function has($currencyCode, $date = null, $rateType = 'default');

    /**
     * Get rate for currency on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return RateInterface
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     */
    public function get($currencyCode, $date = null, $rateType = 'default');

    /**
     * Get latest available rate.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param string $rateType Type of rate.
     * @return RateInterface
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     */
    public function latest($currencyCode, $rateType = 'default');

    /**
     * Get rate which ought to be used today.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param string $rateType Type of rate.
     * @return RateInterface
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     */
    public function today($currencyCode, $rateType = 'default');

    /**
     * Fetch rates from sources.
     *
     * @param string|array|null $sourceName Name of source from where rates should be fetched from. If none is provided, rates will be fetched from all sources.
     * @param null|\DateTime $date Date for which rates should be fetched. If not provieded, current date will be used.
     */
    public function fetch($sourceName = null, $date = null);
}
