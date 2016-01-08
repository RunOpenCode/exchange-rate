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
 * Interface AliasRegistry
 *
 * Alias registry allows access to rates via its alias.
 *
 * @package RunOpenCode\ExchangeRate\Contract
 */
interface AliasRegistryInterface
{
    /**
     * Check if rate with given alias exists.
     *
     * @param string $alias Alias of rate.
     * @param null|\DateTime $date Date of rate.
     * @return boolean TRUE if there is a rate with that alias on given date.
     */
    public function has($alias, $date = null);

    /**
     * Get rate with given alias.
     *
     * @param string $alias Alias of rate.
     * @param null|\DateTime $date Date of rate.
     * @return RateInterface
     */
    public function get($alias, $date = null);

    /**
     * Get latest rate with given alias.
     *
     * @param string $alias Alias of rate.
     * @return RateInterface
     */
    public function latest($alias);

    /**
     * Get today's working rate with given alias.
     *
     * @param string $alias Alias of rate.
     * @return RateInterface
     */
    public function today($alias);
}
