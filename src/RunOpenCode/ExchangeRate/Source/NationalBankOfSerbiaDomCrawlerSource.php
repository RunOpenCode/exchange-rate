<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Source;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Exception\ConfigurationException;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\Source\Api\NationalBankOfSerbiaXmlSaxParser;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class NationalBankOfSerbiaDomCrawlerSource
 *
 * This crawler crawls from National bank of Serbia public form for rates.
 *
 * @package RunOpenCode\ExchangeRate\Source
 */
class NationalBankOfSerbiaDomCrawlerSource implements SourceInterface
{
    /**
     * API - Source of rates from National bank of Serbia.
     */
    const SOURCE = 'http://www.nbs.rs/kursnaListaModul/naZeljeniDan.faces';

    /**
     * API - Name of source.
     */
    const NAME = 'national_bank_of_serbia';

    use LoggerAwareTrait;

    /**
     * List of supported exchange rate types and currencies.
     *
     * @var array
     */
    private static $supports = array(
        'default' => array('EUR', 'AUD', 'CAD', 'CNY', 'HRK', 'CZK', 'DKK', 'HUF', 'JPY', 'KWD', 'NOK', 'RUB', 'SEK', 'CHF',
                           'GBP', 'USD', 'BAM', 'PLN', 'ATS', 'BEF', 'FIM', 'FRF', 'DEM', 'GRD', 'IEP', 'ITL', 'LUF', 'PTE',
                           'ESP'),
        'foreign_cache_buying' => array('EUR', 'CHF', 'USD'),
        'foreign_cache_selling' => array('EUR', 'CHF', 'USD'),
        'foreign_exchange_buying' => array('EUR', 'AUD', 'CAD', 'CNY', 'DKK', 'JPY', 'NOK', 'RUB', 'SEK', 'CHF', 'GBP', 'USD'),
        'foreign_exchange_selling' => array('EUR', 'AUD', 'CAD', 'CNY', 'DKK', 'JPY', 'NOK', 'RUB', 'SEK', 'CHF', 'GBP', 'USD')
    );

    /**
     * @var array
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $guzzleClient;

    /**
     * @var CookieJar
     */
    protected $guzzleCookieJar;

    public function __construct()
    {
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($currencyCode, $rateType = 'default', \DateTime $date = null)
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);

        $this->validate($currencyCode, $rateType);

        if ($date === null) {
            $date = new \DateTime('now');
        }

        if (!array_key_exists($rateType, $this->cache)) {

            try {

                $this->load($date, $rateType);

            } catch (\Exception $e) {
                $message = sprintf('Unable to load data from "%s" for "%s" of rate type "%s".', $this->getName(), $currencyCode, $rateType);

                $this->getLogger()->emergency($message);;
                throw new SourceNotAvailableException($message, 0, $e);
            }
        }

        if (array_key_exists($currencyCode, $this->cache[$rateType])) {
            return $this->cache[$rateType][$currencyCode];
        }

        $message = sprintf('API Changed: source "%s" does not provide currency code "%s" for rate type "%s".', $this->getName(), $currencyCode, $rateType);
        $this->getLogger()->critical($message);
        throw new \RuntimeException($message);
    }

    /**
     * Check if currency code and rate type are supported by this source.
     *
     * @param string $currencyCode Currency code.
     * @param string $rateType Rate type.
     * @throws ConfigurationException If currency code is not supported by source and rate type.
     * @throws UnknownRateTypeException If rate type is unknown.
     */
    protected function validate($currencyCode, $rateType)
    {
        if (!array_key_exists($rateType, self::$supports)) {
            throw new UnknownRateTypeException(sprintf('Unknown rate type "%s" for source "%s".', $rateType, $this->getName()));
        }

        if (!in_array($currencyCode, self::$supports[$rateType], true)) {
            throw new ConfigurationException(sprintf('Unsupported currency code "%s" for source "%s" and rate type "%s".', $currencyCode, $this->getName(), $rateType));
        }
    }

    /**
     * @param \DateTime $date
     * @return RateInterface[]
     * @throws SourceNotAvailableException
     */
    protected function load(\DateTime $date, $rateType)
    {
        $this->cache[$rateType] = array();

        $xml = $this->executeHttpRequest(self::SOURCE, 'POST', array(), array(
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
            'com.sun.faces.VIEW' => $this->getFormCsrfToken()
        ));

        $rates = NationalBankOfSerbiaXmlSaxParser::parseXml($xml);

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {
            $this->cache[$rate->getRateType()][$rate->getCurrencyCode()] = $rate;
        }
    }

    /**
     * Get NBS's form CSRF token.
     *
     * @return string CSRF token.
     */
    protected function getFormCsrfToken()
    {
        $crawler = new Crawler($this->executeHttpRequest(self::SOURCE, 'GET'));

        $hiddens = $crawler->filter('input[type="hidden"]');

        /**
         * @var \DOMElement $hidden
         */
        foreach ($hiddens as $hidden) {

            if ($hidden->getAttribute('id') === 'com.sun.faces.VIEW') {
                return $hidden->getAttribute('value');
            }
        }

        $message = 'FATAL ERROR: National Bank of Serbia changed it\'s API, unable to extract token.';
        $this->getLogger()->emergency($message);
        throw new \RuntimeException($message);
    }

    /**
     * Execute HTTP request and get raw body response.
     *
     * @param string $url URL to fetch.
     * @param string $method HTTP Method.
     * @param array $params Params to send with request.
     * @return string
     */
    protected function executeHttpRequest($url, $method, array $query = array(), array $params = array())
    {
        $client = $this->getGuzzleClient();

        $response = $client->request($method, $url, array(
            'cookies' => $this->getGuzzleCookieJar(),
            'form_params' => $params,
            'query' => $query
        ));

        return $response->getBody()->getContents();
    }

    /**
     * Get Guzzle Client.
     *
     * @return Client
     */
    protected function getGuzzleClient()
    {
        if ($this->guzzleClient === null) {
            $this->guzzleClient = new Client(array('cookies' => true));
        }

        return $this->guzzleClient;
    }

    /**
     * Get Guzzle CookieJar.
     *
     * @return CookieJar
     */
    protected function getGuzzleCookieJar()
    {
        if ($this->guzzleCookieJar === null) {
            $this->guzzleCookieJar = new CookieJar();
        }

        return $this->guzzleCookieJar;
    }
}
