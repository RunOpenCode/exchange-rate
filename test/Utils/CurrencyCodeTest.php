<?php

namespace RunOpenCode\ExchangeRate\Tests\Utils;


use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

class CurrencyCodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function exists()
    {
        $this->assertTrue(CurrencyCode::exists('EUR'));
    }

    /**
     * @test
     */
    public function notExists()
    {
        $this->assertFalse(CurrencyCode::exists('YSA'));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function invalid()
    {
        CurrencyCode::validate('YSA');
    }
}