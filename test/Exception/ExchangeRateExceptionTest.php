<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Exception;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;

class ExchangeRateExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function typeOf()
    {
        $cases = [
            ['value' => null, 'expected' => 'NULL'],
            ['value' => $this, 'expected' => get_class($this)],
            ['value' => 1, 'expected' => gettype(1)],
        ];

        foreach ($cases as $case) {
            /**
             * @var $value
             * @var $expected
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertEquals($expected, ExchangeRateException::typeOf($value));
        }
    }
}
