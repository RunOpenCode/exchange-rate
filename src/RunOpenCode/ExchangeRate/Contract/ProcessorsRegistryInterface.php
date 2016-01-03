<?php

namespace RunOpenCode\ExchangeRate\Contract;

interface ProcessorsRegistryInterface extends \IteratorAggregate
{
    public function add(ProcessorInterface $processor);

    public function all();
}
