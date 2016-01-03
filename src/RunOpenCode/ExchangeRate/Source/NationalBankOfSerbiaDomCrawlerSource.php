<?php

namespace RunOpenCode\ExchangeRate\Source;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;
use Symfony\Component\DomCrawler\Crawler;

class NationalBankOfSerbiaDomCrawlerSource implements SourceInterface
{
    const SOURCE = 'http://www.nbs.rs/kursnaListaModul/naZeljeniDan.faces';

    use LoggerAwareTrait;

    private $cache;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'national_bank_of_serbia';
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($currencyCode, $rateType = 'default', $date = null)
    {
        $this
            ->validateRateType($rateType)
            ->validateCurrencyCode($currencyCode, $rateType);

        if (is_null($date)) {
            $date = new \DateTime('now');
        }

        if ($this->cache === null) {
            $this->cache = $this->load($date);
        }

        if (array_key_exists($key = sprintf('%s_%s', $currencyCode, $rateType), $this->cache)) {
            return $this->cache[$key];
        } else {
            // Baciti exception.
        }

    }

    protected function validateRateType($rateType)
    {
        $knownTypes = array(
            'default', // It is actually a middle exchange rate
            'foreign_cache_buying',
            'foreign_cache_selling',
            'foreign_exchange_buying',
            'foreign_exchange_selling'
        );

        if (!in_array($rateType, $knownTypes)) {
            throw new UnknownRateTypeException(sprintf('Unknown rate type "%s" for source "%s", known types are: %s.', $rateType, $this->getName(), implode(', ', $knownTypes)));
        }

        return $this;
    }

    protected function validateCurrencyCode($currencyCode, $rateType)
    {
        $supports = array(
            'default' => array(
                'EUR'
            ),
            'foreign_cache_buying',
            'foreign_cache_selling',
            'foreign_exchange_buying',
            'foreign_exchange_selling'
        );

        if (!in_array($currencyCode, $supports[$rateType])) {
            throw new UnknownCurrencyCodeException(sprintf('Unknown currency code "%s".', $currencyCode));
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return RateInterface[]
     * @throws SourceNotAvailableException
     */
    protected function load(\DateTime $date)
    {
        $guzzleClient = new GuzzleClient(array('cookies' => true));
        $jar = new CookieJar;
        $client = new GoutteClient();
        $client->setClient($guzzleClient);

        $crawler = $this->getCrawler($date);

        // parsiranje
    }

    /**
     * @param \DateTime $date
     * @return Crawler
     * @throws SourceNotAvailableException
     */
    protected function getCrawler(\DateTime $date)
    {
        $url = sprintf('http', $date->format('Y-m-d'));
        $goutte = new Client();
        try {
            return $goutte->request('GET', $url);
        } catch (\Exception $e) {
            throw new SourceNotAvailableException(sprintf('Source not available on "%s".', $url), 0, $e);
        }
    }

    protected function getPostParams(\DateTime $date, $rateType)
    {
        return  array(
            'index:brKursneListe:' => '',
            'index:year' => $date->format('Y'),
            'index:inputCalendar1' => $date->format('d/m/Y'),
            'index:vrsta' => call_user_func(function($rateType) {
                switch ($rateType) {
                    case 'foreign_cache_buying':        // FALL TROUGH
                    case 'foreign_cache_selling':
                        return 1;
                        break;
                    case 'foreign_exchange_buying':     // FALL TROUGH
                    case 'foreign_exchange_selling':
                        return 2;
                        break;
                    default:
                        return 3;
                        break;
                }
            }, $rateType),
            'index:prikaz' => 0,
            'index:buttonShow' => 'Show',
            'index' => 'index',
            'com.sun.faces.VIEW' => null
        );
    }

    protected function extractCsrfToken(GuzzleClient $guzzleClient, CookieJar $jar)
    {
        $response = $guzzleClient->request('GET', self::SOURCE, array('cookies' => $jar));
        $crawler = new Crawler($response->getBody()->getContents());

        $hiddens = $crawler->filter('input[type="hidden"]');

        /**
         * @var \DOMElement $hidden
         */
        foreach ($hiddens as $hidden) {

            if ($hidden->getAttribute('id') === 'com.sun.faces.VIEW') {
                return $hidden->getAttribute('value');
            }
        }

        $exception = new \RuntimeException('FATAL ERROR: National Bank of Serbia changed it\'s API, unable to extract token.');

        if ($this->logger) {
            $this->logger->emergency($exception->getMessage());
        }

        throw $exception;
    }
}
