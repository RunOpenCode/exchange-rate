<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Repository;

use RunOpenCode\ExchangeRate\Repository\MemoryRepository;

/**
 * Class MemoryRepositoryTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Repository
 */
class MemoryRepositoryTest extends AbstractRepositoryTest
{
    /**
     * {@inheritdoc}
     */
    protected function getRepository()
    {
        return new MemoryRepository();
    }
}
