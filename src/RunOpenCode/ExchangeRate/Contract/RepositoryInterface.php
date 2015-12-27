<?php

namespace RunOpenCode\ExchangeRate\Contract;

interface RepositoryInterface
{
    /**
     * Persist rates. If rates exist
     *
     * @param RateInterface[] $rates
     */
    public function save(array $rates);

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
     * Get all rates. If filtering criteria is provided, return matches only.
     *
     * Available filter params:
     * * currencyCode, string
     * * currencyCodes, array
     * * dateFrom, \DateTime
     * * dateTo, \DateTime
     * * onDate, \DateTime
     * * rateType, string
     * * rateTypes, string
     * * source, string
     * * sources, array
     *
     * @param array $criteria Filtering criteria to apply.
     * @return RateInterface[]
     */
    public function all(array $criteria = array());
}
