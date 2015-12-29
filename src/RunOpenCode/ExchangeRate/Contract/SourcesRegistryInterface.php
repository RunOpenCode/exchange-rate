<?php

namespace RunOpenCode\ExchangeRate\Contract;

interface SourcesRegistryInterface extends \IteratorAggregate
{
    public function add(SourceInterface $source);

    public function has($name);

    public function get($name);

    public function all();
}
