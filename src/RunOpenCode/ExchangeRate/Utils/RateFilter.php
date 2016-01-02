<?php

namespace RunOpenCode\ExchangeRate\Utils;

use RunOpenCode\ExchangeRate\Contract\RateInterface;

final class RateFilter
{
    private function __construct() { }

    public static function matches(RateInterface $rate, array $criteria)
    {
        return
            self::matchesCurrencyCodes($rate, self::extractCurrencyCodes($criteria))
            &&
            self::matchesSources($rate, self::extractSources($criteria))
            &&
            self::matchesRateTypes($rate, self::extractRateTypes($criteria))
            &&
            self::matchesOnDate($rate, self::extractOnDate($criteria))
            &&
            self::matchesDateFrom($rate, self::extractDateFrom($criteria))
            &&
            self::matchesDateTo($rate, self::extractDateTo($criteria))
            ;
    }

    public static function matchesCurrencyCodes(RateInterface $rate, $currencyCodes)
    {
        if (!is_array($currencyCodes)) {
            $currencyCodes = array($currencyCodes);
        }

        if (empty($currencyCodes)) {
            return true;
        }

        return in_array($rate->getCurrencyCode(), $currencyCodes);
    }

    public static function matchesSources(RateInterface $rate, $sources)
    {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        if (empty($sources)) {
            return true;
        }

        return in_array($rate->getSourceName(), $sources);
    }

    public static function matchesRateTypes(RateInterface $rate, $rateTypes)
    {
        if (!is_array($rateTypes)) {
            $rateTypes = array($rateTypes);
        }

        if (empty($rateTypes)) {
            return true;
        }

        return in_array($rate->getRateType(), $rateTypes);
    }

    public static function matchesDateFrom(RateInterface $rate, \DateTime $dateFrom = null)
    {
        if (is_null($dateFrom)) {
            return true;
        }

        return $dateFrom <= $rate->getDate();
    }

    public static function matchesDateTo(RateInterface $rate, \DateTime $dateTo = null)
    {
        if (is_null($dateTo)) {
            return true;
        }

        return $dateTo >= $rate->getDate();
    }

    public static function matchesOnDate(RateInterface $rate, \DateTime $date = null)
    {
        if (is_null($date)) {
            return true;
        }

        return $date->format('Y-m-d') == $rate->getDate()->format('Y-m-d');
    }

    private static function extractSources(array $criteria)
    {
        return !empty($criteria['currencyCode']) ? array($criteria['currencyCode']) : !empty($criteria['currencyCodes']) ? $criteria['currencyCodes'] : array();
    }

    private static function extractCurrencyCodes(array $criteria)
    {
        return  !empty($criteria['source']) ? array($criteria['source']) : !empty($criteria['sources']) ? $criteria['sources'] : array();
    }

    private static function extractRateTypes(array $criteria)
    {
        return  !empty($criteria['rateType']) ? array($criteria['rateType']) : !empty($criteria['rateTypes']) ? $criteria['rateTypes'] : array();
    }

    private static function extractOnDate(array $criteria)
    {
        return (!empty($criteria['onDate'])) ? $criteria['onDate'] : null;
    }

    private static function extractDateFrom(array $criteria)
    {
        return (!empty($criteria['dateFrom'])) ? $criteria['dateFrom'] : null;
    }

    private static function extractDateTo(array $criteria)
    {
        return (!empty($criteria['dateTo'])) ? $criteria['dateTo'] : null;
    }

}
