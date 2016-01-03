<?php

namespace RunOpenCode\ExchangeRate\Contract;

use Psr\Log\LoggerAwareInterface;
use RunOpenCode\ExchangeRate\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;

/**
 * Interface SourceInterface
 *
 * Defines source from which currency rate is extracted.
 *
 * @package RunOpenCode\ExchangeRateBundle\Contract
 */
interface SourceInterface extends LoggerAwareInterface
{
    /**
     * Get unique source name.
     *
     * @return string
     */
    public function getName();

    /**
     * @param string $currencyCode ISO currency code for which rate is being fetched.
     * @param string $rateType Type of the rate which is being fetched.
     * @param null|\DateTime $date Date on which rate is being fetched.
     *
     * @return RateInterface Fetched rate.
     *
     * @throws UnknownCurrencyCodeException
     * @throws UnknownRateTypeException
     * @throws SourceNotAvailableException
     * @throws ConfigurationException
     */
    public function fetch($currencyCode, $rateType = 'default', $date = null);
}
