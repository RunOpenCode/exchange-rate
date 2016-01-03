<?php

namespace RunOpenCode\ExchangeRate\Tests\Source;

use Goutte\Client;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Source\NationalBankOfSerbiaDomCrawlerSource;
use Symfony\Component\CssSelector\CssSelectorConverter;
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

    /**
     * @test
     */
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function getNbsData()
    {

        $post_data = array(
            'index:brKursneListe:' => '',
            'index:year' => '2016',
            'index:inputCalendar1' => '02/01/2016',
            'index:vrsta' => 1,
            'index:prikaz' => 0,
            'index:buttonShow' => 'Show',
            'index' => 'index',
            'com.sun.faces.VIEW' => null
        );

        $guzzleClient = new \GuzzleHttp\Client(array('cookies' => true));
        $jar = new \GuzzleHttp\Cookie\CookieJar;
        $client = new Client();
        $client->setClient($guzzleClient);

        $url = 'http://www.nbs.rs/kursnaListaModul/naZeljeniDan.faces';

        try {

            $response = $guzzleClient->request('GET', $url, array('cookies' => $jar));

            $crawler = new Crawler($response->getBody()->getContents());

           // $form = $crawler->selectButton('Show')->form();

            $converter = new CssSelectorConverter();

            $hiddens = $crawler->filter('input[type="hidden"]');

            $csrfToken = null;

            /**
             * @var \DOMElement $hidden
             */
            foreach ($hiddens as $hidden) {

                if ($hidden->getAttribute('id') === 'com.sun.faces.VIEW') {
                    $csrfToken = $hidden->getAttribute('value');
                    break;
                }
            }

            $post_data['com.sun.faces.VIEW'] = $csrfToken;

            //$crawler = $client->submit($form, $post_data);

            $response = $guzzleClient->request('POST', $url, array(
                'form_params' => $post_data,
                'cookies' => $jar
            ));

             $crawler =  new Crawler($response->getBody()->getContents());

            echo $crawler->html();

        } catch (\Exception $e) {

            var_dump($e->getTraceAsString());
        }
    }
}