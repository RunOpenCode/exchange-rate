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

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Contract\ProcessorInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Processor\BaseCurrencyValidator;
use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;

/**
 * Class BaseCurrencyValidatorTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Processor
 */
class BaseCurrencyValidatorTest extends TestCase
{
    /**
     * @var ProcessorInterface
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new BaseCurrencyValidator();
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

        $this->assertSame(3, count($this->processor->process('RSD', new RatesConfigurationRegistry(), $rates)), 'Should have same base currency code.');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ConfigurationException
     */
    public function error()
    {
        $this->processor->process('RSD', new RatesConfigurationRegistry(), array(
            new Rate('test', 10, 'EUR', 'test', new \DateTime('now'), 'CHF')
        ));
    }
}
