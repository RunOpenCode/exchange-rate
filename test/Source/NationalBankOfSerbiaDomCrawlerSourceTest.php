<?php

namespace RunOpenCode\ExchangeRate\Tests\Source;

use Goutte\Client;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Source\NationalBankOfSerbiaDomCrawlerSource;
use Symfony\Component\DomCrawler\Crawler;

class NationalBankOfSerbiaDomCrawlerSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException
     */
    public function sourceIsNotAvailable()
    {
        require_once  __DIR__ . '/../Fixtures/Fake/FakeGoutteClient.php';
        Client::expect(new \RuntimeException('Radi'));
        $source = new NationalBankOfSerbiaDomCrawlerSource();
        $result = $source->fetch('EUR');
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

    public function success()
    {
        require_once  __DIR__ . '/../Fixtures/Fake/FakeGoutteClient.php';
        Client::expect(new Crawler(file_get_contents(__DIR__ . '/../Fixtures/html/middle_exchange_rate.252.html')));

        $source = new NationalBankOfSerbiaDomCrawlerSource();

        /**
         * @var RateInterface $rate
         */
        $rate = $source->fetch('EUR', 'default');
        $this->assertSame(122.21, $rate->getValue());
    }
}