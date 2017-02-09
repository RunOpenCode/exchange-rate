<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Exception\RuntimeException;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Utils\SourceFilterUtil;

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
            throw new RuntimeException(sprintf('Source "%s" is already registered.', $source->getName()));
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
    public function all(array $filter = array())
    {
        if (count($filter) === 0) {
            return $this->sources;
        }

        return $this->filter($this->sources, $filter);
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
     * Available filter criteria:
     * * name: string
     * * names: string[]
     *
     * @param SourceInterface[] $sources Sources to filter.
     * @param array $filters Filter criteria.
     *
     * @return SourceInterface[]
     */
    private function filter($sources, array $filters = array())
    {
        $result = array();

        foreach ($sources as $source) {

            if (SourceFilterUtil::matches($source, $filters)) {
                $result[] = $source;
            }
        }

        return $result;
    }
}
