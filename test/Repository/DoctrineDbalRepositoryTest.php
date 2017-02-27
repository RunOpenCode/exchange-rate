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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use RunOpenCode\ExchangeRate\Repository\DoctrineDbalRepository;

class DoctrineDbalRepositoryTest extends AbstractRepositoryTest
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * {@inheritdoc}
     */
    protected function getRepository()
    {
        if ($this->connection) {
            $this->connection->close();
        }

        $this->connection = DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ], new Configuration());

        return new DoctrineDbalRepository($this->connection);
    }
}
