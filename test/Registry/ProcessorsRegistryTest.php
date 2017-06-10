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
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Registry\ProcessorsRegistry;

/**
 * Class ProcessorsRegistryTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Registry
 */
class ProcessorsRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function cover()
    {
        $registry = new ProcessorsRegistry(array(
            $this->getMockBuilder(ProcessorInterface::class)->getMock()
        ));
        $registry->add($this->getMockBuilder(ProcessorInterface::class)->getMock());

        $this->assertSame(2, count($registry->all()));
        $this->assertInstanceOf(\Iterator::class, $registry->getIterator());
    }
}
