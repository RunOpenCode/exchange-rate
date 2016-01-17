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

use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Utils\ConfigurationFilterUtil;

class ConfigurationFilterUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function currencyCodeMatching()
    {
        $configuration = new Configuration('EUR', 'my_rate_type', 'my_source');

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'currencyCode' => 'EUR'
        )));

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'currencyCodes' => array('EUR', 'BAM', 'USD')
        )));

        $this->assertFalse(ConfigurationFilterUtil::matches($configuration, array(
            'currencyCodes' => array('EUR', 'BAM', 'USD'),
            'currencyCode' => 'BAM'
        )));
    }

    /**
     * @test
     */
    public function sourceMatching()
    {
        $configuration = new Configuration('EUR', 'my_rate_type', 'my_source');

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'sourceSource' => 'my_source'
        )));

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'sourceNames' => array('my_source', 'your_source', 'their_source')
        )));

        $this->assertFalse(ConfigurationFilterUtil::matches($configuration, array(
            'sourceNames' => array('my_source', 'your_source', 'their_source'),
            'sourceName' => 'non_existing'
        )));
    }

    /**
     * @test
     */
    public function rateTypeMatching()
    {
        $configuration = new Configuration('EUR', 'my_rate_type', 'my_source');

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'rateType' => 'my_rate_type'
        )));

        $this->assertTrue(ConfigurationFilterUtil::matches($configuration, array(
            'rateTypes' => array('my_rate_type', 'your_rate_type', 'their_rate_type')
        )));

        $this->assertFalse(ConfigurationFilterUtil::matches($configuration, array(
            'rateTypes' => array('my_rate_type', 'your_rate_type', 'their_rate_type'),
            'rateType' => 'non_existing'
        )));
    }
}
