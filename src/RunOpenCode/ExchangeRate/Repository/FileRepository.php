<?php

namespace RunOpenCode\ExchangeRate\Repository;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Utils\RateFilter;
use RunOpenCode\ExchangeRateBundle\Model\Rate;

class FileRepository implements RepositoryInterface
{
    const RATE_KEY_FORMAT = '%currency_code%_%date%_%rate_type%';

    /**
     * File where all rates are persisted.
     *
     * @var string
     */
    protected $pathToFile;

    /**
     * Collection of loaded rates.
     *
     * @var array
     */
    protected $rates;

    /**
     * Collection of latest rates (to speed up search process).
     *
     * @var array
     */
    protected $latest;

    public function __construct($pathToFile)
    {
        $this->pathToFile = $pathToFile;

        if (!file_exists($this->pathToFile)) {
            touch($this->pathToFile);
        }

        if (!is_readable($this->pathToFile)) {
            throw new \RuntimeException(sprintf('File on path "%s" for storing rates must be readable.', $this->pathToFile));
        }

        if (!is_writable($this->pathToFile)) {
            throw new \RuntimeException(sprintf('File on path "%s" for storing rates must be writeable.', $this->pathToFile));
        }

        $this->load();
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $rates)
    {
        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {
            $this->rates[sprintf('%s_%s_%s', $rate->getCurrencyCode(), $rate->getDate()->format('Y-m-d'), $rate->getRateType())] = $rate;
        }

        usort($this->rates, function(RateInterface $rate1, RateInterface $rate2) {
            return ($rate1->getDate() > $rate2->getDate()) ? -1 : 1;
        });

        $data = '';

        /**
         * @var RateInterface $rate
         */
        foreach ($this->rates as $rate) {
            $data .= json_encode(array(
                    'sourceName' => $rate->getSourceName(),
                    'value' => $rate->getValue(),
                    'currencyCode' => $rate->getCurrencyCode(),
                    'rateType' => $rate->getRateType(),
                    'date' => $rate->getDate()->format('Y-m-d H:i:s'),
                    'baseCurrencyCode' => $rate->getBaseCurrencyCode()
                )) . "\n";
        }

        file_put_contents($this->pathToFile, $data, LOCK_EX);

        $this->load();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $rates)
    {
        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {
            unset($this->rates[$this->getRateKey($rate)]);
        }

        $this->save(array());
    }

    /**
     * {@inheritdoc}
     */
    public function has($currencyCode, \DateTime $date = null, $rateType = 'default')
    {
        if (is_null($date)) {
            $date = new \DateTime('now');
        }

        return array_key_exists(str_replace(array(
            $currencyCode,
            $date->format('Y-m-d'),
            $rateType
        ), array(
            '%currency_code%',
            '%date%',
            '%rate_type%'
        ), self::RATE_KEY_FORMAT), $this->rates);
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, \DateTime $date = null, $rateType = 'default')
    {
        if (is_null($date)) {
            $date = new \DateTime('now');
        }

        return $this->rates[str_replace(array(
            $currencyCode,
            $date->format('Y-m-d'),
            $rateType
        ), array(
            '%currency_code%',
            '%date%',
            '%rate_type%'
        ), self::RATE_KEY_FORMAT)];
    }

    /**
     * {@inheritdoc}
     */
    public function latest($currencyCode, $rateType = 'default')
    {
        /**
         * @var RateInterface $rate
         */
        foreach ($this->rates as $rate) {

            if ($rate->getCurrencyCode() == $currencyCode && $rate->getRateType() == $rateType) {
                return $rate;
            }
        }

        throw new ExchangeRateException(sprintf('Could not fetch latest rate for rate currency code "%s" and rate type "%s".', $currencyCode, $rateType));
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $criteria = array())
    {
        if (count($criteria) == 0) {
            return $this->rates;
        } else {
            $result = array();

            /**
             * @var RateInterface $rate
             */
            foreach ($this->rates as $rate) {

                if (RateFilter::matches($rate, $criteria)) {
                    $result[] = $rate;
                }
            }

            return $result;
        }
    }

    /**
     * Load rates from file.
     *
     * @return RateInterface[]
     */
    protected function load()
    {
        $this->rates = array();
        $this->latest = array();

        $handle = fopen($this->pathToFile, 'r');

        if ($handle) {

            while (($line = fgets($handle)) !== false) {
                $data = json_decode($line, true);

                $rate = new Rate(
                    $data['sourceName'],
                    $data['value'],
                    $data['currencyCode'],
                    $data['rateType'],
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['date']),
                    $data['baseCurrencyCode']
                );

                $this->rates[$this->getRateKey($rate)] = $rate;

                $latestKey = sprintf('%s_%s', $rate->getCurrencyCode(), $rate->getRateType());

                if (!isset($this->latest[$latestKey]) || ($this->latest[$latestKey]->getDate() < $rate->getDate())) {
                    $this->latest[$latestKey] = $rate;
                }
            }

            fclose($handle);

        } else {
            throw new \RuntimeException(sprintf('Error opening file on path "%s".', $this->pathToFile));
        }

        return $this->rates;
    }

    protected function getRateKey(RateInterface $rate)
    {
        return str_replace(array(
            $rate->getCurrencyCode(),
            $rate->getDate()->format('Y-m-d'),
            $rate->getRateType()
        ), array(
            '%currency_code%',
            '%date%',
            '%rate_type%'
        ), self::RATE_KEY_FORMAT);
    }
}
