<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Source;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Source\NationalBankOfSerbiaDomCrawlerSource;

class NationalBankOfSerbiaDomCrawlerSourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException
     */
    public function sourceIsNotAvailable()
    {
        require_once  __DIR__ . '/../Fixtures/Fake/FakeGuzzleClient.php';
        Client::expect(new \Exception());
        $source = new NationalBankOfSerbiaDomCrawlerSource();
        $source->fetch('EUR');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException
     */
    public function unknownCurrencyCode()
    {
        $source = new NationalBankOfSerbiaDomCrawlerSource();
        $source->fetch('not existing');
    }

    /**
     * @test
     */
    public function successFetchMedian()
    {
        require_once  __DIR__ . '/../Fixtures/Fake/FakeGuzzleClient.php';

        $streamStubToken = $this->getMockBuilder(Stream::class)->disableOriginalConstructor()->getMock();
        $streamStubToken->method('getContents')->willReturn(file_get_contents(__DIR__ . '/../Fixtures/xml/NationalBankOfSerbia/faketoken.html'));

        $responseStubToken = $this->getMockBuilder(Response::class)->getMock();
        $responseStubToken
            ->method('getBody')
            ->willReturn($streamStubToken);

        Client::expect($responseStubToken);

        $streamStubXml = $this->getMockBuilder(Stream::class)->disableOriginalConstructor()->getMock();
        $streamStubXml->method('getContents')->willReturn(file_get_contents(__DIR__ . '/../Fixtures/xml/NationalBankOfSerbia/median.xml'));

        $responseStubXml = $this->getMockBuilder(Response::class)->getMock();
        $responseStubXml->method('getBody')->willReturn($streamStubXml);

        Client::expect($responseStubXml);

        $source = new NationalBankOfSerbiaDomCrawlerSource();

        /**
         * @var RateInterface $rate
         */
        $rate = $source->fetch('EUR', 'default', \DateTime::createFromFormat('d.m.Y', '31.12.2015'));
        $this->assertSame(121.6261, $rate->getValue());

        $rate = $source->fetch('CHF', 'default', \DateTime::createFromFormat('d.m.Y', '31.12.2015'));
        $this->assertSame(112.5230, $rate->getValue());

    }
}
