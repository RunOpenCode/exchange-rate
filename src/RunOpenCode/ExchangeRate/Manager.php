<?php

namespace RunOpenCode\ExchangeRate;

use Psr\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

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
        $this->baseCurrency = CurrencyCode::validate($baseCurrency);
        $this->repository = $repository;
        $this->configurations = $configurations;
        $this->sources = $sources;
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function has($currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->has(CurrencyCode::validate($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->get(CurrencyCode::validate($currencyCode), $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function latest($currencyCode, $rateType = 'default')
    {
        return $this->repository->latest(CurrencyCode::validate($currencyCode), $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function today($currencyCode, $rateType = 'default')
    {
        $currencyCode = CurrencyCode::validate($currencyCode);
        $today = new \DateTime('now');

        if ($this->has($currencyCode, $rateType, $today)) {
            return $this->get($currencyCode, $rateType, $today);
        }

        if ((int)$today->format('N') >= 6) {
            $today = new \DateTime('last Friday');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($sourceName = null, $date = null)
    {
        $sources = $this->sources->all();

        if (!is_null($sourceName)) {
            $sources = array();
            $sourceNames = is_array($sourceName) ? $sourceName : array($sourceName);

            foreach ($sourceNames as $sourceName) {
                $sources[] = $this->sources->get($sourceName);
            }
        }

        $rates = array();

        /**
         * @var SourceInterface $source
         */
        foreach ($sources as $source) {
            $configurations = $this->configurations->find($source->getName());

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
