<?php

namespace RunOpenCode\ExchangeRate;

use Psr\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;

class Manager implements ManagerInterface
{
    use LoggerAwareTrait;

    protected $repository;

    protected $sources;

    protected $settings;

    public function __construct(RepositoryInterface $repository, array $sources, array $settings)
    {
        $this->repository = $repository;
        $this->sources = $sources;
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function has($currencyCode, $date = null, $rateType = 'default')
    {
        // TODO: Implement has() method.
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, $date = null, $rateType = 'default')
    {
        // TODO: Implement get() method.
    }

    /**
     * {@inheritdoc}
     */
    public function latest($currencyCode, $rateType = 'default')
    {
        // TODO: Implement latest() method.
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($source = null, $date = null)
    {
        // TODO: Implement fetch() method.
    }
}
