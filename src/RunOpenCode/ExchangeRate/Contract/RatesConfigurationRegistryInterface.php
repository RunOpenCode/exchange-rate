<?php

namespace RunOpenCode\ExchangeRate\Contract;

use RunOpenCode\ExchangeRate\Configuration;

interface RatesConfigurationRegistryInterface extends \IteratorAggregate
{
    public function add(Configuration $configuration);

    public function find($sourceName);

    public function all();
}
