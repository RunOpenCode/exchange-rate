<?php

namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;

final class SourcesRegistry implements SourcesRegistryInterface
{
    private $sources;

    public function __construct(array $sources = array())
    {
        $this->sources = array();

        foreach ($sources as $source) {
            $this->add($source);
        }
    }

    public function add(SourceInterface $source)
    {
        if ($this->has($source->getName())) {
            throw new \RuntimeException(sprintf('Source "%s" is already registered.', $source->getName()));
        }

        $this->sources[$source->getName()] = $source;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->sources);
    }

    public function get($name)
    {
        return $this->sources[$name];
    }

    public function all()
    {
        return $this->sources;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->sources);
    }
}
