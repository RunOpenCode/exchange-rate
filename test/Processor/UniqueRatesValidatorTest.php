<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Processor;

use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Processor\UniqueRatesValidator;
use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;

class UniqueRatesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new UniqueRatesValidator();
    }

    /**
     * @test
     */
    public function success()
    {
        $rates = array(
            new Rate('test', 10, 'EUR', 'test', new \DateTime('now'), 'RSD'),
            new Rate('test', 10, 'CHF', 'test', new \DateTime('now'), 'RSD'),
            new Rate('test', 10, 'USD', 'test', new \DateTime('now'), 'RSD')
        );

        $this->assertSame(3, count($this->processor->process('RSD', new RatesConfigurationRegistry(), $rates)), 'Should contain unique rates.');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ConfigurationException
     */
    public function error()
    {
        $this->processor->process('EUR', new RatesConfigurationRegistry(), array(
            new Rate('test', 10, 'EUR', 'test', new \DateTime('now'), 'RSD'),
            new Rate('test', 20, 'EUR', 'test', new \DateTime('now'), 'CHF')
        ));
    }
}