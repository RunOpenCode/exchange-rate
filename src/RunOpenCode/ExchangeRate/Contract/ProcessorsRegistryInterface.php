<?php

namespace RunOpenCode\ExchangeRate\Contract;

use RunOpenCode\Backup\Contract\ProcessorInterface;

interface ProcessorsRegistryInterface extends \IteratorAggregate
{
    public function add(ProcessorInterface $processor);

    public function all();
}