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

/**
 * Class RatesConfigurationRegistry
 *
 * Default implementation of rates configuration registry.
 *
 * @package RunOpenCode\ExchangeRate\Registry
 */
class RatesConfigurationRegistry implements RatesConfigurationRegistryInterface
{
    /**
     * @var Configuration[]
     */
    protected $configurations;

    public function __construct()
    {
        $this->configurations = array();
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
    public function find($sourceName)
    {
        $result = array();

        /**
         * @var Configuration $configuration
         */
        foreach ($this->configurations as $configuration) {

            if ($configuration->getSource() == $sourceName) {
                $result[] = $configuration;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->configurations;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configurations);
    }
}
