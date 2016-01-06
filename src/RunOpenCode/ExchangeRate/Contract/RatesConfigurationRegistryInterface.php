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

use RunOpenCode\ExchangeRate\Configuration;

/**
 * Interface RatesConfigurationRegistryInterface
 *
 * Rates configuration registry is collection of all rates configurations.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface RatesConfigurationRegistryInterface extends \IteratorAggregate
{
    /**
     * Add rate configuration to collection.
     *
     * @param Configuration $configuration Configuration to add.
     */
    public function add(Configuration $configuration);

    /**
     * Get rate configurations by source name.
     *
     * @param string $sourceName Source name.
     * @return Configuration[]
     */
    public function find($sourceName);

    /**
     * Get all rates configurations.
     *
     * @return Configuration[]
     */
    public function all();
}
