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

use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Registry\SourcesRegistry;
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

    public function __construct($baseCurrency, RepositoryInterface $repository, SourcesRegistryInterface $sources, ProcessorsRegistryInterface $processors, RatesConfigurationRegistryInterface $configurations)
    {
        $this->baseCurrency = CurrencyCodeUtil::clean($baseCurrency);
        $this->repository = $repository;
        $this->configurations = $configurations;
        $this->sources = $sources;
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function has($sourceName, $currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->has($sourceName, CurrencyCodeUtil::clean($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function get($sourceName, $currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->get($sourceName, CurrencyCodeUtil::clean($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = 'default')
    {
        return $this->repository->latest(CurrencyCodeUtil::clean($currencyCode), $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function today($sourceName, $currencyCode, $rateType = 'default')
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);
        $today = new \DateTime('now');

        if ($this->has($currencyCode, $rateType, $today)) {
            return $this->get($currencyCode, $today, $rateType);
        }

        if ((int)$today->format('N') >= 6) {
            $today = new \DateTime('last Friday');
            return $this->get($currencyCode, $today, $rateType);
        }

        $message = sprintf('Rate for currency code "%s" of type "%s" is not available for today "%s".', $currencyCode, $rateType, date('Y-m-d'));
        $this->getLogger()->critical($message);
        throw new ExchangeRateException($message);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($sourceName = null, $date = null)
    {
        $rates = array();

        /**
         * @var SourceInterface $source
         */
        foreach ($this->sources->all((is_string($sourceName) ? array($sourceName) : $sourceName)) as $source) {

            $configurations = $this->configurations->all(array(
                'sourceName' => $source->getName()
            ));

            /**
             * @var Configuration $configuration
             */
            foreach ($configurations as $configuration) {
                $rates[] = $source->fetch($configuration->getCurrencyCode(), $configuration->getRateType(), $date);
            }
        }

        /**
         * @var ProcessorInterface $processor
         */
        foreach ($this->processors as $processor) {
            $rates = $processor->process($this->baseCurrency, $this->configurations, $rates);
        }

        $this->repository->save($rates);
    }
}
