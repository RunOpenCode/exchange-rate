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

/**
 * Class BaseFilterUtil
 *
 * Common shared functions for filter utilities.
 *
 * @package RunOpenCode\ExchangeRate\Utils
 */
trait FilterUtilHelper
{
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

    /**
     * Check if array|string criteria is matched.
     *
     * @param string $key Array|string criteria key.
     * @param mixed $object Object to check for match.
     * @param array $criteria Filter criterias.
     * @return bool TRUE if there is a match.
     */
    private static function matchesArrayCriteria($key, $object, array $criteria)
    {
        $criteria = self::extractArrayCriteria($key, $criteria);

        if (count($criteria) === 0) {
            return true;
        }

        $getter = sprintf('get%s', ucfirst($key));

        if (!method_exists($object, $getter)) {
            throw new \RuntimeException(sprintf('Object instance of "%s" does not have required getter "%s" to be used for filtering.', get_class($object), $getter));
        }

        return in_array($object->{$getter}(), $criteria, true);
    }
}

