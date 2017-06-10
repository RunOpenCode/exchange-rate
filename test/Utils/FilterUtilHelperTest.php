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
use RunOpenCode\ExchangeRate\Utils\FilterUtilHelper;

/**
 * Class FilterUtilHelperTest
 *
 * @package RunOpenCode\ExchangeRate\Tests\Utils
 */
class FilterUtilHelperTest extends TestCase
{
    /**
     * @test
     */
    public function itExtractsArrayCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'extractArrayCriteria');
        $reflectionMethod->setAccessible(true);

        $data = [
            'currencyCode' => 'EUR'
        ];

        $this->assertEquals(['EUR'], $reflectionMethod->invoke($mock, 'currencyCode', $data));

        $data = [
            'currencyCodes' => ['EUR', 'CHF']
        ];

        $this->assertEquals(['EUR', 'CHF'], $reflectionMethod->invoke($mock, 'currencyCode', $data));
    }

    /**
     * @test
     */
    public function itExtractsDateCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'extractDateCriteria');
        $reflectionMethod->setAccessible(true);

        $data = [
            'dateFrom' => new \DateTime('2017-01-01')
        ];

        $this->assertEquals((new \DateTime('2017-01-01'))->format('Y-m-d'), $reflectionMethod->invoke($mock, 'dateFrom', $data)->format('Y-m-d'));

        $data = [
            'dateFrom' => '2017-01-01'
        ];

        $this->assertEquals((new \DateTime('2017-01-01'))->format('Y-m-d'), $reflectionMethod->invoke($mock, 'dateFrom', $data)->format('Y-m-d'));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\InvalidArgumentException
     */
    public function itThrowsExceptionWhenItCanNotExtractDateCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'extractDateCriteria');
        $reflectionMethod->setAccessible(true);

        $data = [
            'dateFrom' => 'not a valid date format'
        ];

        $reflectionMethod->invoke($mock, 'dateFrom', $data);
    }

    /**
     * @test
     */
    public function itMatchesArrayCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'matchesArrayCriteria');
        $reflectionMethod->setAccessible(true);

        $object = new class {

            public function getCurrencyCode() {
                return 'EUR';
            }
        };

        $this->assertTrue($reflectionMethod->invoke($mock, 'currencyCode', $object, [ 'currencyCodes' => ['EUR', 'CHF'] ]));
        $this->assertFalse($reflectionMethod->invoke($mock, 'currencyCode', $object, [ 'currencyCodes' => ['BAM', 'RSD'] ]));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\RuntimeException
     */
    public function itThrowsExceptionWhenThereIsNoGetterToMatchArrayCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'matchesArrayCriteria');
        $reflectionMethod->setAccessible(true);

        $object = new class { };

        $reflectionMethod->invoke($mock, 'currencyCode', $object, [ 'currencyCodes' => ['EUR', 'CHF'] ]);
    }
}
