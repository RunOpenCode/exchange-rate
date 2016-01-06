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

use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

class CurrencyCodeUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function exists()
    {
        $this->assertTrue(CurrencyCodeUtil::exists('EUR'));
    }

    /**
     * @test
     */
    public function notExists()
    {
        $this->assertFalse(CurrencyCodeUtil::exists('non-existing'));
    }

    /**
     * @test
     */
    public function clean()
    {
        $this->assertSame('EUR', CurrencyCodeUtil::clean(' eur '));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function invalid()
    {
        CurrencyCodeUtil::clean('non-existing');
    }
}
