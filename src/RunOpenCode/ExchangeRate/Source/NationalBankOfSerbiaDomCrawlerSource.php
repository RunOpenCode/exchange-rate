<?php

namespace RunOpenCode\ExchangeRate\Source;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;
use RunOpenCode\ExchangeRate\Model\Rate;
use Symfony\Component\DomCrawler\Crawler;

class NationalBankOfSerbiaDomCrawlerSource implements SourceInterface
{
    const SOURCE = 'http://www.nbs.rs/kursnaListaModul/naZeljeniDan.faces';

    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $cache;

    public function __construct()
    {
        $this->cache = array();
    }

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

        if (!array_key_exists($rateType, $this->cache)) {

            try {
                $this->load($date, $rateType);
            } catch (\Exception $e) {
                $exception = new SourceNotAvailableException(sprintf('Unable to load data from "%s" for "%s" of rate type "%s".', $this->getName(), $currencyCode, $rateType), 0, $e);

                if ($this->logger) {
                    $this->logger->emergency($exception->getMessage());
                }

                throw $exception;
            }
        }

        if (array_key_exists($currencyCode, $this->cache[$rateType])) {
            return $this->cache[$rateType][$currencyCode];
        }

        throw new ConfigurationException(sprintf('Source "%s" does not provide currency code "%s" for rate type "%s".', $this->getName(), $currencyCode, $rateType));
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

        if (!in_array($rateType, $knownTypes, true)) {
            throw new UnknownRateTypeException(sprintf('Unknown rate type "%s" for source "%s", known types are: %s.', $rateType, $this->getName(), implode(', ', $knownTypes)));
        }

        return $this;
    }

    protected function validateCurrencyCode($currencyCode, $rateType)
    {
        $supports = array(
            'default' => array(
                'EUR', 'CHF'
            ),
            'foreign_cache_buying',
            'foreign_cache_selling',
            'foreign_exchange_buying',
            'foreign_exchange_selling'
        );

        if (!in_array($currencyCode, $supports[$rateType], true)) {
            throw new UnknownCurrencyCodeException(sprintf('Unknown currency code "%s" for source "%s" and rate type "%s".', $currencyCode, $this->getName(), $rateType));
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return RateInterface[]
     * @throws SourceNotAvailableException
     */
    protected function load(\DateTime $date, $rateType)
    {
        $guzzleClient = new GuzzleClient(array('cookies' => true));
        $jar = new CookieJar;

        $postParams = $this->getPostParams($date, $rateType, $this->extractCsrfToken($guzzleClient, $jar));

        $response = $guzzleClient->request('POST', self::SOURCE, array(
            'form_params' => $postParams,
            'cookies' => $jar
        ));

        $this->cache[$rateType] = array();

        /**
         * @var RateInterface $rate
         */
        foreach ($this->parseXml($response->getBody()->getContents(), $rateType) as $rate) {
            $this->cache[$rate->getRateType()][$rate->getCurrencyCode()] = $rate;
        }
    }

    protected function parseXml($xml, $rateType)
    {
        $rates = array();
        $stack = new \SplStack();
        $currentRate = array();
        $date = new \DateTime('now');

        $parser = xml_parser_create();

        xml_set_element_handler($parser, function($parser, $name, $attributes) use (&$rates, &$stack, &$currentRate) { // Element tag start

            if (!empty($name)) {

                $stack->push($name);

                if ($name === 'ITEM') {
                    $currentRate = array();
                }
            }

        }, function($parser, $name) use (&$rates, &$stack, &$currentRate, &$rateType, &$date) { // Element tag end

            if (!empty($name)) {

                $stack->pop();

                if ($name === 'ITEM') {

                    if (strpos($rateType, 'buying') !== false) {
                        $value = $currentRate['buyingRate'];
                    } elseif (strpos($rateType, 'selling') !== false) {
                        $value = $currentRate['sellingRate'];
                    } else {
                        $value = $currentRate['middleRate'];
                    }

                    $rates[] = new Rate(
                        $this->getName(),
                        ($value / $currentRate['unit']),
                        $currentRate['currencyCode'],
                        $rateType,
                        $date,
                        'RSD',
                        new \DateTime('now'),
                        new \DateTime('now')
                    );
                    $currentRate = array();
                }
            }
        });

        xml_set_character_data_handler($parser, function($parser, $data) use (&$rates, &$stack, &$currentRate, &$date) { // Element tag data

            if (!empty($data)) {


                switch ($stack->top()) {
                    case 'DATE':
                        $date = \DateTime::createFromFormat('d.m.Y', $data);
                        break;
                    case 'CURRENCY':
                        $currentRate['currencyCode'] = trim($data);
                        break;
                    case 'UNIT':
                        $currentRate['unit'] = (int) trim($data);
                        break;
                    case 'BUYING_RATE':
                        $currentRate['buyingRate'] = (float) trim($data);
                        break;
                    case 'SELLING_RATE':
                        $currentRate['sellingRate'] = (float) trim($data);
                        break;
                    case 'MIDDLE_RATE':
                        $currentRate['middleRate'] = (float) trim($data);
                        break;
                }

            }
        });

        xml_parse($parser, $xml);
        xml_parser_free($parser);

        return $rates;
    }

    protected function getPostParams(\DateTime $date, $rateType, $csrfToken)
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
            'index:prikaz' => 3, // XML
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
