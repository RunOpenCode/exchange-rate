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
use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Manager;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Registry\ProcessorsRegistry;
use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;
use RunOpenCode\ExchangeRate\Registry\SourcesRegistry;
use RunOpenCode\ExchangeRate\Repository\MemoryRepository;

/**
 * Class ManagerTest
 *
 * @package RunOpenCode\ExchangeRate\Tests
 */
class ManagerTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsBaseCurrency()
    {
        $manager = new Manager(
            'RSD',
            new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $this->assertEquals('RSD', $manager->getBaseCurrency());
    }

    /**
     * @test
     */
    public function has()
    {
        $manager = new Manager(
            'RSD',
            $repository = new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $repository->save([
            new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
        ]);

        $this->assertTrue($manager->has('some_source', 'BAM', new \DateTime('2015-10-10')));
        $this->assertFalse($manager->has('some_other_source', 'BAM', new \DateTime('2015-10-10')));
    }

    /**
     * @test
     */
    public function get()
    {
        $manager = new Manager(
            'RSD',
            $repository = new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $repository->save([
            new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
        ]);

        $this->assertInstanceOf(RateInterface::class, $manager->get('some_source', 'BAM', new \DateTime('2015-10-10')));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenGettinNonExisting()
    {
        $manager = new Manager(
            'RSD',
            new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $manager->get('some_source', 'BAM', new \DateTime('2015-10-10'));
    }

    /**
     * @test
     */
    public function latest()
    {
        $manager = new Manager(
            'RSD',
            $repository = new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $repository->save([
            new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
        ]);

        $this->assertEquals(10.0, $manager->latest('some_source', 'BAM')->getValue());
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenFetchingLatestOnEmptyRepository()
    {
        $manager = new Manager(
            'RSD',
            new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $manager->latest('some_source', 'BAM');
    }

    /**
     * @test
     */
    public function historicalExactDate()
    {
        $manager = new Manager(
            'RSD',
            $repository = new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $repository->save([
            new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2017-10-08'), 'RSD', new \DateTime(), new \DateTime()),
        ]);

        $this->assertEquals(10.0, $manager->historical('some_source', 'BAM', \DateTime::createFromFormat('Y-m-d', '2017-10-08'))->getValue());
    }

    /**
     * @test
     */
    public function historicalWeekend()
    {
        $manager = new Manager(
            'RSD',
            $repository = new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $repository->save([
            new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2017-06-09'), 'RSD', new \DateTime(), new \DateTime()),
        ]);

        $this->assertEquals(10.0, $manager->historical('some_source', 'BAM', \DateTime::createFromFormat('Y-m-d', '2017-06-10'))->getValue());
        $this->assertEquals(10.0, $manager->historical('some_source', 'BAM', \DateTime::createFromFormat('Y-m-d', '2017-06-11'))->getValue());
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenHistoricalRateDoesNotExists()
    {
        $manager = new Manager(
            'RSD',
            new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $manager->historical('some_source', 'BAM', \DateTime::createFromFormat('Y-m-d', '2017-06-10'));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function itThrowsExceptionWhenTodayRateDoesNotExists()
    {
        $manager = new Manager(
            'RSD',
            new MemoryRepository(),
            new SourcesRegistry(),
            new ProcessorsRegistry(),
            new RatesConfigurationRegistry()
        );

        $manager->today('some_source', 'BAM');
    }

    /**
     * @test
     */
    public function fetch()
    {
        $repository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $repository->expects($spy = $this->any())->method('save');

        $source = $this->getMockBuilder(SourceInterface::class)->getMock();

        $rate1 = new Rate('test1', 1, 'EUR', 'test1', new \DateTime(), 'RSD');
        $rate2 = new Rate('test2', 2, 'EUR', 'test2', new \DateTime(), 'RSD');

        $source->method('fetch')->willReturnOnConsecutiveCalls($rate1, $rate2);

        $sourceRegistry = $this->getMockBuilder(SourcesRegistryInterface::class)->getMock();
        $sourceRegistry->method('get')->willReturn($source);

        $configurationRegistry = $this->getMockBuilder(RatesConfigurationRegistryInterface::class)->getMock();
        $configurationRegistry->method('all')->willReturn(array(
            new Configuration('EUR', 'test', 'test'),
            new Configuration('EUR', 'test', 'test')
        ));

        $processor = $this->getMockBuilder(ProcessorInterface::class)->getMock();
        $processor->method('process')->will($this->returnCallback(\Closure::bind(function($baseCurrencyCode, $configurations, $rates) use ($rate1, $rate2) {
            $this->assertSame($rate1->getValue(), $rates[0]->getValue());
            $this->assertSame($rate2->getValue(), $rates[1]->getValue());

            return array(new Rate('test3', 3, 'EUR', 'test3', new \DateTime(), 'RSD'));
        }, $this)));

        $processorRegistry = $this->getMockBuilder(ProcessorsRegistryInterface::class)->getMock();
        $processorRegistry->method('all')->willReturn(array(
            $processor
        ));

        $manager = new Manager(
            'RSD',
            $repository,
            $sourceRegistry,
            $processorRegistry,
            $configurationRegistry
        );

        /**
         * @var RateInterface[] $fetchedRates
         */
        $fetchedRates = $manager->fetch('any');

        $invocations = $spy->getInvocations();
        $invocations = end($invocations);

        $this->assertSame(3.0, $invocations->parameters[0][0]->getValue(), 'Fetch process should result with processed rate value.');
        $this->assertSame(3.0, $fetchedRates[0]->getValue(), 'Fetched rate should have given value.');
    }
}
