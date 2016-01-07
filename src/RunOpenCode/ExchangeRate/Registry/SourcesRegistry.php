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

use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;

/**
 * Class SourcesRegistry
 *
 * Default implementation of sources registry.
 *
 * @package RunOpenCode\ExchangeRate\Registry
 */
final class SourcesRegistry implements SourcesRegistryInterface
{
    /**
     * @var SourceInterface[]
     */
    private $sources;

    public function __construct(array $sources = array())
    {
        $this->sources = array();

        foreach ($sources as $source) {
            $this->add($source);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(SourceInterface $source)
    {
        if ($this->has($source->getName())) {
            throw new \RuntimeException(sprintf('Source "%s" is already registered.', $source->getName()));
        }

        $this->sources[$source->getName()] = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $this->sources);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SourceNotAvailableException
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->sources[$name];
        }

        throw new SourceNotAvailableException(sprintf('Unknown source requested: "%s".', $name));
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Filter sources.
     *
     * @param SourcesRegistryInterface $registry Sources collection.
     * @param string|array|null $names Source names to keep.
     *
     * @return SourceInterface[]
     *
     * @throws \InvalidArgumentException
     */
    public static function filter(SourcesRegistryInterface $registry, $names = null)
    {
        if ($names === null) {
            return $registry->all();
        }

        if (!is_array($names)) {
            $names = array($names);
        }

        if (count($names) === 0) {
            throw new \InvalidArgumentException('You have to provide either name of source to keep, of array of names of sources to keep.');
        }

        $result = array();

        foreach ($names as $name) {
            $result[] = $registry->get($name);
        }

        return $result;
    }
}
