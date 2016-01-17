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

use RunOpenCode\ExchangeRate\Contract\ProcessorsRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Contract\SourcesRegistryInterface;
use RunOpenCode\ExchangeRate\Manager;

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
        $repository->method('has')->willReturn(false);
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

        $today = new \DateTime('last Friday');
        $this->assertSame($today->format('Y-m-d'), $invocations->parameters[2]->format('Y-m-d'));
    }



}
