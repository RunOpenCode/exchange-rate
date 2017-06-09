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

class FilterUtilHelperTest extends TestCase
{
    /**
     * @test
     */
    public function itExtractsArrayCriteria()
    {
        $mock = $this->getMockForTrait(FilterUtilHelper::class);

        $data = [
            'currencyCode' => 'EUR'
        ];

        $reflectionMethod = new \ReflectionMethod(get_class($mock), 'extractArrayCriteria');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals(['EUR'], $reflectionMethod->invoke($mock, 'currencyCode', $data));

        $data = [
            'currencyCodes' => ['EUR', 'CHF']
        ];

        $this->assertEquals(['EUR', 'CHF'], $reflectionMethod->invoke($mock, 'currencyCode', $data));
    }
}
