<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Contract;

/**
 * Interface ProcessorsRegistryInterface
 *
 * Processor registry is collection of rate processors.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface ProcessorsRegistryInterface extends \IteratorAggregate
{
    /**
     * Add rate processor to collection.
     *
     * @param ProcessorInterface $processor Processor to add.
     */
    public function add(ProcessorInterface $processor);

    /**
     * Get all rate processors.
     *
     * @return ProcessorInterface[]
     */
    public function all();
}
