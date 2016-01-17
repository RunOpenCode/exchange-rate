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

use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
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
     * Check if manager contains required exchange rate.
     *
     * @param string $sourceName Source from which rate is fetched.
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     *
     * @return bool
     *
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     * @throws SourceNotAvailableException
     */
    public function has($sourceName, $currencyCode, $date = null, $rateType = 'default');

    /**
     * Get rate for currency on given date.
     *
     * @param string $sourceName Source from which rate is fetched.
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     *
     * @return RateInterface
     *
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     * @throws SourceNotAvailableException
     * @throws ExchangeRateException
     */
    public function get($sourceName, $currencyCode, $date = null, $rateType = 'default');

    /**
     * Get latest available rate.
     *
     * @param string $sourceName Source from which rate is fetched.
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param string $rateType Type of rate.
     *
     * @return RateInterface
     *
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     * @throws SourceNotAvailableException
     * @throws ExchangeRateException
     */
    public function latest($sourceName, $currencyCode, $rateType = 'default');

    /**
     * Get rate which ought to be used today.
     *
     * According to common business practice, exchange rate is determined until 2 PM of current day (or before) which
     * will be used for next business day. Next business day starts at 00:00 AM.
     *
     * For Saturday and Sunday, rate from Friday is used.
     *
     * @param string $sourceName Source from which rate is fetched.
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param string $rateType Type of rate.
     *
     * @return RateInterface
     *
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     * @throws SourceNotAvailableException
     * @throws ExchangeRateException
     */
    public function today($sourceName, $currencyCode, $rateType = 'default');

    /**
     * Fetch rates from sources.
     *
     * Execute this method once every day, after 2 PM.
     *
     * @param string|array|null $sourceName Name of source from where rates should be fetched from. If none is provided, rates will be fetched from all sources.
     * @param null|\DateTime $date Date for which rates should be fetched. If not provieded, current date will be used.
     *
     * @throws SourceNotAvailableException
     * @throws ExchangeRateException
     */
    public function fetch($sourceName = null, $date = null);
}
