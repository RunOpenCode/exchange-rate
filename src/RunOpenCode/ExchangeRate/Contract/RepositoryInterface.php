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

/**
 * Interface RepositoryInterface
 *
 * Repository is storage abstraction for exchange rate values.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface RepositoryInterface extends \Countable
{
    /**
     * Persist rates. If rates already exists, they gets updated.
     *
     * @param RateInterface[] $rates Rates to save.
     */
    public function save(array $rates);

    /**
     * Delete rates.
     *
     * @param RateInterface[] $rates Rates to delete.
     */
    public function delete(array $rates);

    /**
     * Check if exchange rate for currency is available on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return bool
     */
    public function has($currencyCode, \DateTime $date = null, $rateType = 'default');

    /**
     * Get rate for currency on given date.
     *
     * @param string $currencyCode Currency code for which exchange rate is required.
     * @param \DateTime|null $date Date of rate, or current date is going to be used.
     * @param string $rateType Type of rate.
     * @return RateInterface
     */
    public function get($currencyCode, \DateTime $date = null, $rateType = 'default');

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
     * * sourceName, string
     * * sourceNames, array
     *
     * @param array $criteria Filtering criteria to apply.
     * @return RateInterface[]
     */
    public function all(array $criteria = array());
}
