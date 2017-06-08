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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Repository\DoctrineDbalRepository;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * Class DoctrineDbalRepositoryTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Repository
 */
class DoctrineDbalRepositoryTest extends AbstractRepositoryTest
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenUnableToSave()
    {
        $schemaManager = $this->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $schemaManager->method('tablesExist')->willReturn(true);

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        $connection->method('beginTransaction');
        $connection->method('fetchAll')->willReturn([]);
        $connection->method('executeQuery')->willThrowException(new \Exception());
        $connection->expects($this->exactly(0))->method('commit');
        $connection->expects($this->exactly(1))->method('rollBack');

        $repository = new DoctrineDbalRepository($connection);

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
        ));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenUnableToDelete()
    {
        $schemaManager = $this->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $schemaManager->method('tablesExist')->willReturn(true);

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        $connection->method('beginTransaction');
        $connection->method('executeQuery')->willThrowException(new \Exception());
        $connection->expects($this->exactly(0))->method('commit');
        $connection->expects($this->exactly(1))->method('rollBack');

        $repository = new DoctrineDbalRepository($connection);

        $repository->delete(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
        ));
    }


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
