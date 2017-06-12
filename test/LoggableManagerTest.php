<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\LoggableManager;
use RunOpenCode\ExchangeRate\Manager;
use RunOpenCode\ExchangeRate\Model\Rate;

/**
 * Class LoggableManagerTest
 *
 * @package RunOpenCode\ExchangeRate\Tests
 */
class LoggableManagerTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function has()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('has')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->has('source', 'EUR');
    }

    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function get()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('get')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->get('source', 'EUR', new \DateTime());
    }

    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function latest()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('latest')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->latest('source', 'EUR');
    }

    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function today()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('today')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->today('source', 'EUR');
    }

    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function historical()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('historical')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->historical('source', 'EUR', new \DateTime());
    }

    /**
     * @test
     *
     * @expectedException \Exception
     */
    public function fetchError()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('fetch')
            ->willThrowException(new \Exception());


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(1))
            ->method('error');

        $manager = new LoggableManager($decoratedManager, $logger);

        $manager->fetch('source', new \DateTime());
    }


    /**
     * @test
     */
    public function fetchSuccess()
    {
        $decoratedManager = $this->getMockBuilder(Manager::class)->disableOriginalConstructor()->getMock();
        $decoratedManager
            ->method('fetch')
            ->willReturn($rates = [
                new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2017-10-08'), 'RSD', new \DateTime(), new \DateTime()),
                new Rate('some_source', 10, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2017-10-08'), 'RSD', new \DateTime(), new \DateTime()),
            ]);


        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->exactly(2))
            ->method('debug');

        $manager = new LoggableManager($decoratedManager, $logger);

        $this->assertEquals($rates, $manager->fetch('source', new \DateTime()));
    }
}
