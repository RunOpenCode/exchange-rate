<?php

namespace RunOpenCode\ExchangeRate;

use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

class Configuration
{
    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $rateType;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $extraConfiguration;

    public function __construct($currencyCode, $rateType, $source, array $extraConfiguration = array())
    {
        if (!CurrencyCode::exists($currencyCode)) {
            throw new UnknownCurrencyCodeException(sprintf('Unknown currency code "%s".', $currencyCode));
        }

        $this->currencyCode = $currencyCode;
        $this->rateType = $rateType;
        $this->source = $source;
        $this->extraConfiguration = $extraConfiguration;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getRateType()
    {
        return $this->rateType;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getExtraConfiguration()
    {
        return $this->extraConfiguration;
    }
}
