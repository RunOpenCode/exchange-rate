<?php

namespace RunOpenCode\ExchangeRateBundle\Model;

use RunOpenCode\ExchangeRate\Contract\RateInterface;

class Rate implements RateInterface
{
    protected $sourceName;

    protected $value;

    protected $currencyCode;

    protected $rateType;

    protected $date;

    protected $baseCurrencyCode;

    public function __construct($sourceName, $value, $currencyCode, $rateType, $date, $baseCurrencyCode)
    {
        $this->sourceName = $sourceName;
        $this->value = $value;
        $this->currencyCode = $currencyCode;
        $this->rateType = $rateType;
        $this->baseCurrencyCode = $baseCurrencyCode;
        $this->date =  (is_numeric($date)) ? date_timestamp_set(new \DateTime(), $date) : clone $date;;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getRateType()
    {
        return $this->rateType;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return ($this->date) ? clone $this->date : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->baseCurrencyCode;
    }
}
