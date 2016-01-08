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
     */
    public function latest($sourceName, $currencyCode, $rateType = 'default');

    /**
     * Get rate which ought to be used today.
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
     */
    public function today($sourceName, $currencyCode, $rateType = 'default');

    /**
     * Get alias registry interface.
     *
     * @return AliasRegistryInterface
     */
    public function alias();

    /**
     * Fetch rates from sources.
     *
     * @param string|array|null $sourceName Name of source from where rates should be fetched from. If none is provided, rates will be fetched from all sources.
     * @param null|\DateTime $date Date for which rates should be fetched. If not provieded, current date will be used.
     *
     * @throws SourceNotAvailableException
     */
    public function fetch($sourceName = null, $date = null);
}
