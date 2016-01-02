<?php

namespace RunOpenCode\ExchangeRate\Source;

use Goutte\Client;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;
use RunOpenCode\ExchangeRateBundle\Model\Rate;
use Symfony\Component\DomCrawler\Crawler;

class NationalBankOfSerbiaDomCrawlerSource implements SourceInterface
{
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
            //
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
            'default' => array(),
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
}
