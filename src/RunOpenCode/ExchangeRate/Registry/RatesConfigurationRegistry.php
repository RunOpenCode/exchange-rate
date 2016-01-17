<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Utils\ConfigurationFilterUtil;

/**
 * Class RatesConfigurationRegistry
 *
 * Default implementation of rates configuration registry.
 *
 * @package RunOpenCode\ExchangeRate\Registry
 */
final class RatesConfigurationRegistry implements RatesConfigurationRegistryInterface
{
    /**
     * @var Configuration[]
     */
    private $configurations;

    public function __construct(array $configurations = array())
    {
        $this->configurations = array();

        foreach ($configurations as $configuration) {
            $this->add($configuration);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(Configuration $configuration)
    {
        $this->configurations[] = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $filter = array())
    {
        if (count($filter) === 0) {
            return $this->configurations;
        }

        return $this->filter($this->configurations, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configurations);
    }

    /**
     * Get configurations that matches given filter criteria.
     *
     * @param Configuration[] $configurations Configurations to filter.
     * @param array $criteria Filter criteria.
     * @return Configuration[] Matched configurations.
     */
    private function filter($configurations, array $criteria)
    {
        $result = array();

        /**
         * @var Configuration $configuration
         */
        foreach ($configurations as $configuration) {

            if (ConfigurationFilterUtil::matches($configuration, $criteria)) {
                $result[] = $configuration;
            }
        }

        return $result;
    }
}
