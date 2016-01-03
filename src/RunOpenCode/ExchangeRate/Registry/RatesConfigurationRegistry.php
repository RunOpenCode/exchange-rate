<?php

namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;

class RatesConfigurationRegistry implements RatesConfigurationRegistryInterface
{
    protected $configurations;

    public function __construct()
    {
        $this->configurations = array();
    }

    public function add(Configuration $configuration)
    {
        $this->configurations[] = $configuration;
    }

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

    public function all()
    {
        return $this->configurations;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->configurations);
    }
}
