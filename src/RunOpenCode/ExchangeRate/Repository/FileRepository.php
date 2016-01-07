<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Repository;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;
use RunOpenCode\ExchangeRate\Utils\RateFilterUtil;
use RunOpenCode\ExchangeRate\Model\Rate;

/**
 * Class FileRepository
 *
 * File repository is simple file based repository for storing rates.
 * Rates are serialized into JSON and stored in plain text file, row by row.
 *
 * File repository can be used as repository for small number of rates.
 *
 * @package RunOpenCode\ExchangeRate\Repository
 */
class FileRepository implements RepositoryInterface
{
    const RATE_KEY_FORMAT = '%currency_code%_%date%_%rate_type%_%source_name%';

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
        $this->initStorage();
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
            $this->rates[$this->getRateKey($rate)] = $rate;
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
                    'baseCurrencyCode' => $rate->getBaseCurrencyCode(),
                    'createdAt' => $rate->getCreatedAt()->format('Y-m-d H:i:s'),
                    'modifiedAt' => $rate->getModifiedAt()->format('Y-m-d H:i:s')
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
    public function has($sourceName, $currencyCode, \DateTime $date = null, $rateType = 'default')
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        return array_key_exists(
            str_replace(
                array('%currency_code%', '%date%', '%rate_type%', '%source_name%'),
                array(CurrencyCodeUtil::clean($currencyCode), $date->format('Y-m-d'), $rateType, $sourceName),
                self::RATE_KEY_FORMAT
            ),
            $this->rates
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($sourceName, $currencyCode, \DateTime $date = null, $rateType = 'default')
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        if ($this->has($currencyCode, $date, $rateType)) {
            return $this->rates[
                str_replace(
                    array('%currency_code%', '%date%', '%rate_type%', '%source_name%'),
                    array(CurrencyCodeUtil::clean($currencyCode), $date->format('Y-m-d'), $rateType, $sourceName),
                    self::RATE_KEY_FORMAT
                )
            ];
        }

        throw new ExchangeRateException(sprintf('Could not fetch rate for rate currency code "%s" and rate type "%s" on date "%s".', $currencyCode, $rateType, $date->format('Y-m-d')));
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = 'default')
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);
        /**
         * @var RateInterface $rate
         */
        foreach ($this->rates as $rate) {

            if (
                $rate->getSourceName() === $sourceName
                &&
                $rate->getCurrencyCode() === $currencyCode
                &&
                $rate->getRateType() === $rateType
            ) {
                return $rate;
            }
        }

        throw new ExchangeRateException(sprintf('Could not fetch latest rate for rate currency code "%s" and rate type "%s" from source "%s".', $currencyCode, $rateType, $sourceName));
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

                if (RateFilterUtil::matches($rate, $criteria)) {
                    $result[] = $rate;
                }
            }

            return $result;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->rates);
    }

    /**
     * Load all rates from file.
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
                    $data['baseCurrencyCode'],
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['createdAt']),
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['modifiedAt'])
                );

                $this->rates[$this->getRateKey($rate)] = $rate;

                $latestKey = sprintf('%s_%s_%s', $rate->getCurrencyCode(), $rate->getRateType(), $rate->getSourceName());

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

    /**
     * Builds rate key to speed up search.
     *
     * @param RateInterface $rate
     * @return string
     */
    protected function getRateKey(RateInterface $rate)
    {
        return str_replace(
            array('%currency_code%', '%date%', '%rate_type%', '%source_name%'),
            array($rate->getCurrencyCode(), $rate->getDate()->format('Y-m-d'), $rate->getRateType(), $rate->getSourceName()),
            self::RATE_KEY_FORMAT
        );
    }

    /**
     * Initializes file storage.
     */
    protected function initStorage()
    {
        /** @noinspection MkdirRaceConditionInspection */
        if (!file_exists(dirname($this->pathToFile)) && !mkdir(dirname($this->pathToFile), 0777, true)) {
            throw new \RuntimeException(sprintf('Could not create storage file on path "%s".', $this->pathToFile));
        }

        if (!file_exists($this->pathToFile) && !(touch($this->pathToFile) && chmod($this->pathToFile, 0777))) {
            throw new \RuntimeException(sprintf('Could not create storage file on path "%s".', $this->pathToFile));
        }

        if (!is_readable($this->pathToFile)) {
            throw new \RuntimeException(sprintf('File on path "%s" for storing rates must be readable.', $this->pathToFile));
        }

        if (!is_writable($this->pathToFile)) {
            throw new \RuntimeException(sprintf('File on path "%s" for storing rates must be writeable.', $this->pathToFile));
        }
    }
}
