<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Model;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Class Rate
 *
 * Default implementation of exchange rate value.
 *
 * @package RunOpenCode\ExchangeRate\Model
 */
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

    /**
     * Rate constructor.
     *
     * @param string $sourceName
     * @param float $value
     * @param string $currencyCode
     * @param string $rateType
     * @param \DateTime|int $date
     * @param string $baseCurrencyCode
     * @param null|\DateTime|int $createdAt
     * @param null|\DateTime|int $modifiedAt
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function __construct($sourceName, $value, $currencyCode, $rateType, $date, $baseCurrencyCode, $createdAt = null, $modifiedAt = null)
    {
        $this->sourceName = $sourceName;
        $this->value = $value;
        $this->currencyCode = CurrencyCodeUtil::clean($currencyCode);
        $this->rateType = $rateType;
        $this->baseCurrencyCode = CurrencyCodeUtil::clean($baseCurrencyCode);

        $processDate = function ($arg) {
            $arg = (null === $arg) ? new \DateTime('now') : $arg;

            return is_numeric($arg) ? date_timestamp_set(new \DateTime(), $arg) : clone $arg;
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
