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

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function repositoryProxy()
    {
        $repository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $repository->method('has')->willReturn(true);
        $repository->method('get')->willReturn($this->getMockBuilder(RateInterface::class)->getMock());
        $repository->method('latest')->willReturn($this->getMockBuilder(RateInterface::class)->getMock());

        $manager = new Manager(
            'RSD',
            $repository,
            $this->getMockBuilder(SourcesRegistryInterface::class)->getMock(),
            $this->getMockBuilder(ProcessorsRegistryInterface::class)->getMock(),
            $this->getMockBuilder(RatesConfigurationRegistryInterface::class)->getMock()
        );

        $this->assertTrue($manager->has('test_source', 'EUR'));
        $this->assertInstanceOf(RateInterface::class, $manager->get('test_source', 'EUR'));
        $this->assertInstanceOf(RateInterface::class, $manager->latest('test_source', 'EUR'));
    }

    /**
     * @test
     */
    public function today()
    {
        $expectedMock = $this->getMockBuilder(RateInterface::class)->getMock();
        $repository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $repository->method('has')->willReturnOnConsecutiveCalls((int)date('N') < 6, true);
        $repository->expects($spy = $this->any())->method('get')->willReturn($expectedMock);

        $manager = new Manager(
            'RSD',
            $repository,
            $this->getMockBuilder(SourcesRegistryInterface::class)->getMock(),
            $this->getMockBuilder(ProcessorsRegistryInterface::class)->getMock(),
            $this->getMockBuilder(RatesConfigurationRegistryInterface::class)->getMock()
        );

        $this->assertSame($expectedMock, $manager->today('test_source', 'EUR'));

        $invocations = $spy->getInvocations();
        $invocations = end($invocations);

        $this->assertSame('test_source', $invocations->parameters[0]);
        $this->assertSame('EUR', $invocations->parameters[1]);

        $today = new \DateTime(((int)date('N') < 6 ? 'now' : 'last Friday'));
        $this->assertSame($today->format('Y-m-d'), $invocations->parameters[2]->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function historical()
    {
        $expectedMock = $this->getMockBuilder(RateInterface::class)->getMock();
        $repository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $repository->method('has')->willReturnOnConsecutiveCalls((int)date('N') < 6, true);
        $repository->expects($spy = $this->any())->method('get')->willReturn($expectedMock);

        $manager = new Manager(
            'RSD',
            $repository,
            $this->getMockBuilder(SourcesRegistryInterface::class)->getMock(),
            $this->getMockBuilder(ProcessorsRegistryInterface::class)->getMock(),
            $this->getMockBuilder(RatesConfigurationRegistryInterface::class)->getMock()
        );

        $this->assertSame($expectedMock, $manager->historical('test_source', 'EUR', new \DateTime('now')));

        $invocations = $spy->getInvocations();
        $invocations = end($invocations);

        $this->assertSame('test_source', $invocations->parameters[0]);
        $this->assertSame('EUR', $invocations->parameters[1]);

        $today = new \DateTime(((int)date('N') < 6 ? 'now' : 'last Friday'));
        $this->assertSame($today->format('Y-m-d'), $invocations->parameters[2]->format('Y-m-d'));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function noRateForToday()
    {
        $repository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $repository->method('has')->willReturn(false);
        $repository->method('get')->willThrowException(new \Exception());

        $manager = new Manager(
            'RSD',
            $repository,
            $this->getMockBuilder(SourcesRegistryInterface::class)->getMock(),
            $this->getMockBuilder(ProcessorsRegistryInterface::class)->getMock(),
            $this->getMockBuilder(RatesConfigurationRegistryInterface::class)->getMock()
        );

        $manager->today('test_source', 'EUR');
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

        $this->assertSame(3, $invocations->parameters[0][0]->getValue(), 'Fetch process should result with processed rate value.');
        $this->assertSame(3, $fetchedRates[0]->getValue(), 'Fetched rate should have given value.');
    }
}
