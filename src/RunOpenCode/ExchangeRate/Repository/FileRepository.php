<?php

namespace RunOpenCode\ExchangeRate\Repository;

use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;

class FileRepository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function save(array $rates)
    {

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
    public function all(array $criteria = array())
    {
        // TODO: Implement all() method.
    }
}
