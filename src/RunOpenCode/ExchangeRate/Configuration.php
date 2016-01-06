<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate;

use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Class Configuration
 *
 * Configuration defines which rates should be acquired from which source, and holds additional custom cofiguration
 * parameters, if required.
 *
 * @package RunOpenCode\ExchangeRate
 */
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
        $this->currencyCode = CurrencyCodeUtil::clean($currencyCode);
        $this->rateType = $rateType;
        $this->source = $source;
        $this->extraConfiguration = $extraConfiguration;
    }

    /**
     * Get configured currency code.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Get configured rate type.
     *
     * @return string
     */
    public function getRateType()
    {
        return $this->rateType;
    }

    /**
     * Get configured source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get additional configuration paramaters, if applicable.
     *
     * @return array
     */
    public function getExtraConfiguration()
    {
        return $this->extraConfiguration;
    }
}
