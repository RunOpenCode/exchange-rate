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
 * Interface RateInterface
 *
 * Abstraction of single exchange rate value.
 *
 * @package RunOpenCode\ExchangeRateBundle\Contract
 */
interface RateInterface
{
    /**
     * Name of the source from which rate is gained.
     *
     * @return string
     */
    public function getSourceName();

    /**
     * Get exchange rate value.
     *
     * @return float
     */
    public function getValue();

    /**
     * Get ISO currency code of exchange rate value.
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Get exchange rate type.
     *
     * @return string
     */
    public function getRateType();

    /**
     * Get date when rate was applied.
     *
     * @return \DateTime
     */
    public function getDate();

    /**
     * Get ISO currency code of base exchange rate value.
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Get date when rate was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Get date when rate was modified.
     *
     * @return \DateTime
     */
    public function getModifiedAt();
}
