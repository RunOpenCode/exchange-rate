<?php

namespace RunOpenCode\ExchangeRateBundle\Model;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

class Rate implements RateInterface
{
    /**
     * @var string
     */
    protected $sourceName;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var string
     */
    protected $rateType;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $baseCurrencyCode;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $modifiedAt;

    public function __construct($sourceName, $value, $currencyCode, $rateType, $date, $baseCurrencyCode, $createdAt = null, $modifiedAt = null)
    {
        $this->sourceName = $sourceName;
        $this->value = $value;
        $this->currencyCode = CurrencyCode::validate($currencyCode);
        $this->rateType = $rateType;
        $this->baseCurrencyCode = CurrencyCode::validate($baseCurrencyCode);

        $processDate = function($arg) {
            $arg = (is_null($arg)) ? new \DateTime('now') : $arg;
            return (is_numeric($arg)) ? date_timestamp_set(new \DateTime(), $arg) : clone $arg;
        };

        $this->date = $processDate($date);
        $this->createdAt = $processDate($createdAt);
        $this->modifiedAt = $processDate($modifiedAt);
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

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return clone $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedAt()
    {
        return clone $this->modifiedAt;
    }
}
