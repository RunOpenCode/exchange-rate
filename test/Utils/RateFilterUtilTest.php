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
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Utils\RateFilterUtil;

class RateFilterUtilTest extends TestCase
{
    /**
     * @test
     */
    public function currencyCodeMatching()
    {
        $rate = new Rate('my_source', 100, 'EUR', 'my_rate_type', new \DateTime('now'), 'EUR');

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'currencyCode' => 'EUR'
        )));

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'currencyCodes' => array('EUR', 'BAM', 'USD')
        )));

        $this->assertFalse(RateFilterUtil::matches($rate, array(
            'currencyCodes' => array('EUR', 'BAM', 'USD'),
            'currencyCode' => 'BAM'
        )));
    }

    /**
     * @test
     */
    public function sourceMatching()
    {
        $rate = new Rate('my_source', 100, 'EUR', 'my_rate_type', new \DateTime('now'), 'EUR');

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'sourceSource' => 'my_source'
        )));

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'sourceNames' => array('my_source', 'your_source', 'their_source')
        )));

        $this->assertFalse(RateFilterUtil::matches($rate, array(
            'sourceNames' => array('my_source', 'your_source', 'their_source'),
            'sourceName' => 'non_existing'
        )));
    }

    /**
     * @test
     */
    public function rateTypeMatching()
    {
        $rate = new Rate('my_source', 100, 'EUR', 'my_rate_type', new \DateTime('now'), 'EUR');

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'rateType' => 'my_rate_type'
        )));

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'rateTypes' => array('my_rate_type', 'your_rate_type', 'their_rate_type')
        )));

        $this->assertFalse(RateFilterUtil::matches($rate, array(
            'rateTypes' => array('my_rate_type', 'your_rate_type', 'their_rate_type'),
            'rateType' => 'non_existing'
        )));
    }

    /**
     * @test
     */
    public function dateMatching()
    {
        $rate = new Rate('my_source', 100, 'EUR', 'my_rate_type', new \DateTime('now'), 'EUR');

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'onDate' => new \DateTime('now')
        )));

        $this->assertFalse(RateFilterUtil::matches($rate, array(
            'onDate' => new \DateTime('tomorrow')
        )));

        $this->assertTrue(RateFilterUtil::matches($rate, array(
            'dateTo' => new \DateTime('tomorrow')
        )));

        $this->assertFalse(RateFilterUtil::matches($rate, array(
            'dateFrom' => new \DateTime('tomorrow')
        )));
    }
}
