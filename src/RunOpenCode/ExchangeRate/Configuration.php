<?php

namespace RunOpenCode\ExchangeRate;

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
        $this->currencyCode = CurrencyCode::validate($currencyCode);
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
