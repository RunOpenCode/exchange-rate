<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Registry;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;

/**
 * Class RatesConfigurationRegistryTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Registry
 */
class RatesConfigurationRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function cover()
    {
        $registry = new RatesConfigurationRegistry(array(
            $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock()
        ));
        $registry->add($this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock());

        $this->assertSame(2, count($registry->all()));
        $this->assertInstanceOf(\Iterator::class, $registry->getIterator());
    }

    /**
     * @test
     */
    public function filter()
    {
        $source1 = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock();
        $source1->method('getSourceName')->willReturn('source_one');

        $source2 = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock();
        $source2->method('getSourceName')->willReturn('source_two');

        $registry = new RatesConfigurationRegistry(array($source1, $source2));

        $this->assertSame(1, count($registry->all(array(
            'sourceName' => 'source_one'
        ))));
    }
}
