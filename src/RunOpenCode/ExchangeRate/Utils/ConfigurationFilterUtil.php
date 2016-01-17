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

use RunOpenCode\ExchangeRate\Configuration;

/**
 * Class ConfigurationFilterUtil
 *
 * Utility to check if configuration matches filter.
 *
 * @package RunOpenCode\ExchangeRate\Utils
 */
final class ConfigurationFilterUtil
{
    use FilterUtilHelper;

    private function __construct() { }

    /**
     * Check if rate matches filters.
     *
     * @param Configuration $configuration Configuration to filter.
     * @param array $criteria Filter criteria.
     * @return bool TRUE if filter criteria is matched.
     */
    public static function matches(Configuration $configuration, array $criteria)
    {
        return
            self::matchesArrayCriteria('currencyCode', $configuration, $criteria)
            &&
            self::matchesArrayCriteria('sourceName', $configuration, $criteria)
            &&
            self::matchesArrayCriteria('rateType', $configuration, $criteria)
            ;
    }
}
