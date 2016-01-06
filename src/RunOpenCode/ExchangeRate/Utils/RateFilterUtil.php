<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Utils;

use RunOpenCode\ExchangeRate\Contract\RateInterface;

/**
 * Class RateFilterUtil
 *
 * Utility to check if rate matches filters.
 *
 * @package RunOpenCode\ExchangeRate\Utils
 */
final class RateFilterUtil
{
    private function __construct() { }

    /**
     * Check if rate matches filters.
     *
     * @param RateInterface $rate Rate to filter.
     * @param array $criteria Filter criteria.
     * @return bool TRUE if filter criteria is matched.
     */
    public static function matches(RateInterface $rate, array $criteria)
    {
        return
            self::matchesArrayCriteria('currencyCode', $rate, $criteria)
            &&
            self::matchesArrayCriteria('sourceName', $rate, $criteria)
            &&
            self::matchesArrayCriteria('rateType', $rate, $criteria)
            &&
            self::matchesDateCriteria('onDate', $rate, $criteria)
            &&
            self::matchesDateCriteria('dateFrom', $rate, $criteria)
            &&
            self::matchesDateCriteria('dateTo', $rate, $criteria)
            ;
    }

    /**
     * Check if date criteria is matched.
     *
     * @param string $key Date criteria key.
     * @param RateInterface $rate Rate to check for match.
     * @param array $criteria Filter criterias.
     * @return bool TRUE if there is a match.
     */
    private static function matchesDateCriteria($key, RateInterface $rate, array $criteria)
    {
        $date = self::extractDateCriteria($key, $criteria);

        if ($date === null) {
            return true;
        }

        if ($key === 'dateFrom') {
            return $date <= $rate->getDate();
        } elseif ($key === 'dateTo') {
            return $date >= $rate->getDate();
        }

        return $date->format('Y-m-d') === $rate->getDate()->format('Y-m-d');
    }

    /**
     * Check if array|string criteria is matched.
     *
     * @param string $key Array|string criteria key.
     * @param RateInterface $rate Rate to check for match.
     * @param array $criteria Filter criterias.
     * @return bool TRUE if there is a match.
     */
    private static function matchesArrayCriteria($key, RateInterface $rate, array $criteria)
    {
        $criteria = self::extractArrayCriteria($key, $criteria);

        if (count($criteria) === 0) {
            return true;
        }

        return in_array($rate->{sprintf('get%s', ucfirst($key))}(), $criteria, true);
    }

    /**
     * Extract array criteria from criterias.
     *
     * @param string $key Criteria name.
     * @param array $criteria Filter criterias.
     * @return array Extracted array criterias.
     */
    private static function extractArrayCriteria($key, array $criteria)
    {
        if (!empty($criteria[$key])) {
            return array($criteria[$key]);
        } elseif (!empty($criteria[$key . 's'])) {
            return $criteria[$key . 's'];
        }

        return array();
    }

    /**
     * Extract date from filter criterias.
     *
     * @param string $key Criteria name.
     * @param array $criteria Filter criterias.
     * @return \DateTime|null Extracted date criteria.
     */
    private static function extractDateCriteria($key, array $criteria)
    {
        return (!empty($criteria[$key])) ? $criteria[$key] : null;
    }
}
