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

use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;

/**
 * Class ProcessorsRegistry
 *
 * Default implementation of processor registry.
 *
 * @package RunOpenCode\ExchangeRate\Registry
 */
final class ProcessorsRegistry implements ProcessorsRegistryInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * ProcessorsRegistry constructor.
     *
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = array())
    {
        $this->processors = array();

        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * {@inheritdoc}
     */
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
