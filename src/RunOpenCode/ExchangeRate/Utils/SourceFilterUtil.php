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

use RunOpenCode\ExchangeRate\Contract\SourceInterface;

/**
 * Class SourceFilterUtil
 *
 * Utility to check if source matches filters.
 *
 * @package RunOpenCode\ExchangeRate\Utils
 */
final class SourceFilterUtil
{
    use FilterUtilHelper;

    private function __construct() { }

    /**
     * Check if rate matches filters.
     *
     * @param SourceInterface $source Rate to filter.
     * @param array $criteria Filter criteria.
     * @return bool TRUE if filter criteria is matched.
     */
    public static function matches(SourceInterface $source, array $criteria)
    {
        return
            self::matchesArrayCriteria('name', $source, $criteria)
            ;
    }
}
