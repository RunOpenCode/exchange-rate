<?php

namespace RunOpenCode\ExchangeRate;

use Psr\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;

class Manager implements ManagerInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var SourceInterface[]
     */
    protected $sources;

    /**
     * @var ProcessorInterface[]
     */
    protected $processors;

    /**
     * @var RateConfiguration[]
     */
    protected $rateConfigurations;

    public function __construct($baseCurrency, RepositoryInterface $repository, array $sources, array $processors, array $rateConfigurations)
    {
        $this->repository = $repository;
        $this->rateConfigurations = $rateConfigurations;
        $this->sources = $sources;
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function has($currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->has($currencyCode, $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, $date = null, $rateType = 'default')
    {
        return $this->repository->get($currencyCode, $date, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function latest($currencyCode, $rateType = 'default')
    {
      return $this->repository->latest($currencyCode, $rateType);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($source = null, $date = null)
    {
        if (is_null($source)) {
            $sources = $this->sources;
        } else {
            $source = is_array($source) ? $source : array($source);

            foreach ($source as $name) {

            }
        }
    }
}
