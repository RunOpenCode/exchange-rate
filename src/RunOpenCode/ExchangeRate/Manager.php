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
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
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
        $this->baseCurrency = $baseCurrency;
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
        CurrencyCode::validate($currencyCode);

        return $this->repository->has($currencyCode, $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, $date = null, $rateType = 'default')
    {
        CurrencyCode::validate($currencyCode);

        return $this->repository->get($currencyCode, $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function latest($currencyCode, $rateType = 'default')
    {
        CurrencyCode::validate($currencyCode);

        return $this->repository->latest($currencyCode, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($sourceName = null, $date = null)
    {
        $sources = $this->sources;

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
