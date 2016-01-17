<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Contract;

/**
 * Interface SourcesRegistryInterface
 *
 * Source registry is collection of sources.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface SourcesRegistryInterface extends \IteratorAggregate
{
    /**
     * Add source to collection.
     *
     * @param SourceInterface $source Source to add.
     */
    public function add(SourceInterface $source);

    /**
     * Check if collection has source with given name.
     *
     * @param string $name Source name.
     * @return bool TRUE if source exists in collection.
     */
    public function has($name);

    /**
     * Get source by its name.
     *
     * @param string $name Source name.
     * @return SourceInterface
     */
    public function get($name);

    /**
     * Get all sources.
     *
     * Available filter criterias:
     * * name: string
     * * names: string[]
     *
     * @return SourceInterface[]
     */
    public function all(array $filter = array());
}
