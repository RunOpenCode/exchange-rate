<?php

namespace RunOpenCode\ExchangeRate\Contract;

interface ManagerInterface
{
    /**
     * Check if exchange rate for currency is available on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return bool
     */
    public function has($currencyCode, $date = null, $rateType = 'default');

    /**
     * Get rate for currency on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return RateInterface
     */
    public function get($currencyCode, $date = null, $rateType = 'default');

    /**
     * Get latest available rate.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param string $rateType Type of rate.
     * @return RateInterface
     */
    public function latest($currencyCode, $rateType = 'default');

    /**
     * Fetch rates from sources.
     *
     * @param string|array|null $source Name of source from where rates should be fetched from. If none is provided, rates will be fetched from all sources.
     * @param null|\DateTime $date Date for which rates should be fetched. If not provieded, current date will be used.
     */
    public function fetch($source = null, $date = null);
}