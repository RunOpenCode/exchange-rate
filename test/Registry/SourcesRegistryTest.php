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
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Registry\SourcesRegistry;

class SourcesRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function cover()
    {
        $source1 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source1->method('getName')->willReturn('source_one');

        $source2 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source2->method('getName')->willReturn('source_two');

        $registry = new SourcesRegistry(array($source1));
        $registry->add($source2);

        $this->assertSame(2, count($registry->all()));
        $this->assertInstanceOf(\Iterator::class, $registry->getIterator());
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function noDuplicates()
    {
        $source1 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source1->method('getName')->willReturn('source_one');

        $source2 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source2->method('getName')->willReturn('source_one');

        $registry = new SourcesRegistry(array($source1));
        $registry->add($source2);
    }

    /**
     * @test
     */
    public function filter()
    {
        $source1 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source1->method('getName')->willReturn('source_one');

        $source2 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source2->method('getName')->willReturn('source_two');

        $registry = new SourcesRegistry(array($source1, $source2));

        $this->assertSame(1, count($registry->all(array(
            'name' => 'source_one'
        ))));
    }

    /**
     * @test
     */
    public function hasAndGet()
    {
        $source1 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source1->method('getName')->willReturn('my_source');

        $registry = new SourcesRegistry(array($source1));

        $this->assertTrue($registry->has('my_source'), 'My source should exists.');
        $this->assertSame($source1, $registry->get('my_source'), 'Should get source.');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException
     */
    public function doesNotExists()
    {
        $source1 = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source1->method('getName')->willReturn('my_source');

        $registry = new SourcesRegistry(array($source1));

        $registry->get('does_not_exists');
    }
}
