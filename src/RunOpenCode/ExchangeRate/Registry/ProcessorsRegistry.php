<?php

namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\Backup\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;

final class ProcessorsRegistry implements ProcessorsRegistryInterface
{
    private $processors;

    public function __construct(array $processors = array())
    {
        $this->processors = array();

        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    public function add(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    public function all()
    {
        return $this->processors;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->processors);
    }
}
