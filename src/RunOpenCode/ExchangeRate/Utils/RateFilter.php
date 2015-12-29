<?php

namespace RunOpenCode\ExchangeRate\Utils;

use RunOpenCode\ExchangeRate\Contract\RateInterface;

final class RateFilter
{
    private function __construct() { }

    public static function matches(RateInterface $rate, array $criteria)
    {
        return
            self::matchesCurrencyCode($rate, $criteria)
            &&
            self::matchesCurrencyCodes($rate, $criteria)
            &&
            self::matchesDateFrom($rate, $criteria)
            &&
            self::matchesDateTo($rate, $criteria)
            &&
            self::matchesOnDate($rate, $criteria)
            &&
            self::matchesRateType($rate, $criteria)
            &&
            self::matchesRateTypes($rate, $criteria)
            &&
            self::matchesSource($rate, $criteria)
            &&
            self::matchesSources($rate, $criteria)
            ;
    }

    public static function matchesCurrencyCode(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['currencyCode']) && $criteria['currencyCode'] != $rate->getCurrencyCode()) {
            return false;
        }

        return true;
    }

    public static function matchesCurrencyCodes(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['currencyCodes']) && !in_array($rate->getCurrencyCode(), $criteria['currencyCodes'])) {
            return false;
        }

        return true;
    }

    public static function matchesDateFrom(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['dateFrom']) && $criteria['dateFrom'] <= $rate->getDate()) {
            return false;
        }

        return true;
    }

    public static function matchesDateTo(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['dateTo']) && $criteria['dateTo'] >= $rate->getDate()) {
            return false;
        }

        return true;
    }

    public static function matchesOnDate(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['onDate']) && $criteria['onDate']->format('Y-m-d') != $rate->getDate()->format('Y-m-d')) {
            return false;
        }

        return true;
    }

    public static function matchesRateType(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['rateType']) && $criteria['rateType'] != $rate->getRateType()) {
            return false;
        }

        return true;
    }

    public static function matchesRateTypes(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['rateTypes']) && !in_array($rate->getRateType(), $criteria['rateTypes'])) {
            return false;
        }

        return true;
    }

    public static function matchesSource(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['source']) && $criteria['source'] != $rate->getSourceName()) {
            return false;
        }

        return true;
    }

    public static function matchesSources(RateInterface $rate, array $criteria)
    {
        if (isset($criteria['sources']) && !in_array($rate->getSourceName(), $criteria['sources'])) {
            return false;
        }

        return true;
    }
}
