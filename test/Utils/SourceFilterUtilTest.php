<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Utils;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Utils\SourceFilterUtil;

/**
 * Class SourceFilterUtilTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Utils
 */
class SourceFilterUtilTest extends TestCase
{
    /**
     * @test
     */
    public function sourceMatching()
    {
        $source = $this->getMockBuilder(SourceInterface::class)->getMock();
        $source->method('getName')->willReturn('my_source');

        $this->assertTrue(SourceFilterUtil::matches($source, array(
            'name' => 'my_source'
        )));

        $this->assertTrue(SourceFilterUtil::matches($source, array(
            'names' => array('my_source', 'your_source', 'their_source')
        )));

        $this->assertFalse(SourceFilterUtil::matches($source, array(
            'names' => array('my_source', 'your_source', 'their_source'),
            'name' => 'non_existing'
        )));
    }
}
