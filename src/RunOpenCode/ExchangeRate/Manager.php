<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate;

use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Enum\RateType;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Class Manager
 *
 * Default implementation of manager.
 *
 * @package RunOpenCode\ExchangeRate
 */
class Manager implements ManagerInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $baseCurrency;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var SourcesRegistryInterface
     */
    protected $sources;

    /**
     * @var ProcessorsRegistryInterface
     */
    protected $processors;

    /**
     * @var RatesConfigurationRegistryInterface
     */
    protected $configurations;

    /**
     * Manager constructor.
     *
     * @param string $baseCurrency
     * @param RepositoryInterface $repository
     * @param SourcesRegistryInterface $sources
     * @param ProcessorsRegistryInterface $processors
     * @param RatesConfigurationRegistryInterface $configurations
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function __construct($baseCurrency, RepositoryInterface $repository, SourcesRegistryInterface $sources, ProcessorsRegistryInterface $processors, RatesConfigurationRegistryInterface $configurations)
    {
        $this->baseCurrency = CurrencyCodeUtil::clean($baseCurrency);
        $this->repository = $repository;
        $this->configurations = $configurations;
        $this->sources = $sources;
        $this->processors = $processors;
    }

    /**
     * Get base currency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function has($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        return $this->repository->has($sourceName, CurrencyCodeUtil::clean($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function get($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        return $this->repository->get($sourceName, CurrencyCodeUtil::clean($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
        return $this->repository->latest($sourceName, CurrencyCodeUtil::clean($currencyCode), $rateType);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function today($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);
        $today = new \DateTime('now');

        if ($this->has($sourceName, $currencyCode, $today, $rateType)) {
            return $this->get($sourceName, $currencyCode, $today, $rateType);
        }

        if ((int) $today->format('N') >= 6 && $this->has($sourceName, $currencyCode, $lastFriday = new \DateTime('last Friday'), $rateType)) {
            return $this->get($sourceName, $currencyCode, $lastFriday, $rateType);
        }

        $message = sprintf('Rate for currency code "%s" of type "%s" from source "%s" is not available for today "%s".', $currencyCode, $rateType, $sourceName, date('Y-m-d'));
        $this->getLogger()->critical($message);
        throw new ExchangeRateException($message);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function historical($sourceName, $currencyCode, \DateTime $date, $rateType = RateType::MEDIAN)
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);

        if ($this->has($sourceName, $currencyCode, $date, $rateType)) {
            return $this->get($sourceName, $currencyCode, $date, $rateType);
        }

        if ((int) $date->format('N') === 6 && $this->has($sourceName, $currencyCode, $lastFriday = $date->sub(new \DateInterval('P1D')), $rateType)) {
            return $this->get($sourceName, $currencyCode, $lastFriday, $rateType);
        }

        if ((int) $date->format('N') === 7 && $this->has($sourceName, $currencyCode, $lastFriday = $date->sub(new \DateInterval('P2D')), $rateType)) {
            return $this->get($sourceName, $currencyCode, $lastFriday, $rateType);
        }

        $message = sprintf('Rate for currency code "%s" of type "%s" from source "%s" is not available for historical date "%s".', $currencyCode, $rateType, $sourceName, $date->format('Y-m-d'));
        $this->getLogger()->critical($message);
        throw new ExchangeRateException($message);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($sourceName = null, \DateTime $date = null)
    {
        $rates = array();

        $filteredSourceNames = ($sourceName === null) ? array_map(function (SourceInterface $source) {
            return $source->getName();
        }, $this->sources->all()) : (array) $sourceName;

        foreach ($filteredSourceNames as $name) {

            $source = $this->sources->get($name);

            $filteredConfigurations = $this->configurations->all(array(
                'sourceName' => $name,
            ));

            /**
             * @var Configuration $configuration
             */
            foreach ($filteredConfigurations as $configuration) {
                $rates[] = $source->fetch($configuration->getCurrencyCode(), $configuration->getRateType(), $date);
            }
        }

        /**
         * @var ProcessorInterface $processor
         */
        foreach ($this->processors->all() as $processor) {
            $rates = $processor->process($this->baseCurrency, $this->configurations, $rates);
        }

        $this->repository->save($rates);

        return $rates;
    }
}
