<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Repository;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Enum\RateType;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Utils\RateFilterUtil;

/**
 * Class MemoryRepository
 *
 * Memory repository is useful for unit testing only.
 *
 * @package RunOpenCode\ExchangeRate\Repository
 */
class MemoryRepository implements RepositoryInterface
{
    /**
     * @var RateInterface[]
     */
    private $rates;

    public function __construct()
    {
        $this->rates = [];
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
            $this->rates[$this->getRateKey($rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType(), $rate->getSourceName())] = $rate;
        }

        uasort($this->rates, function (RateInterface $rate1, RateInterface $rate2) {
            return ($rate1->getDate() > $rate2->getDate()) ? -1 : 1;
        });
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
            unset($this->rates[$this->getRateKey($rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType(), $rate->getSourceName())]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        return array_key_exists($this->getRateKey($currencyCode, $date, $rateType, $sourceName), $this->rates);
    }

    /**
     * {@inheritdoc}
     */
    public function get($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        if ($this->has($sourceName, $currencyCode, $date, $rateType)) {
            return $this->rates[$this->getRateKey($currencyCode, $date, $rateType, $sourceName)];
        }

        throw new ExchangeRateException(sprintf('Could not fetch rate for rate currency code "%s" and rate type "%s" on date "%s".', $currencyCode, $rateType, $date->format('Y-m-d')));
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
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
        }

        $result = array();

        /**
         * @var RateInterface $rate
         */
        foreach ($this->rates as $rate) {

            if (RateFilterUtil::matches($rate, $criteria)) {
                $result[] = $rate;
            }
        }

        return $this->paginate($result, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->rates);
    }

    /**
     * Builds rate key to speed up search.
     *
     * @param string $currencyCode
     * @param \DateTime $date
     * @param string $rateType
     * @param string $sourceName
     * @return string
     */
    protected function getRateKey($currencyCode, $date, $rateType, $sourceName)
    {
        return str_replace(
            array('%currency_code%', '%date%', '%rate_type%', '%source_name%'),
            array($currencyCode, $date->format('Y-m-d'), $rateType, $sourceName),
            '%currency_code%_%date%_%rate_type%_%source_name%'
        );
    }

    /**
     * Extract requested page from filter criteria.
     *
     * @param array $rates Rates to filter for pagination.
     * @param array $criteria Filter criteria.
     * @return RateInterface[] Paginated rates.
     */
    protected function paginate(array $rates, $criteria)
    {
        if (!array_key_exists('offset', $criteria) && !array_key_exists('limit', $criteria)) {
            return $rates;
        }

        $range = array();
        $offset = array_key_exists('offset', $criteria) ? $criteria['offset'] : 0;
        $limit = min((array_key_exists('limit', $criteria) ? $criteria['limit'] : count($rates)) + $offset, count($rates));

        for ($i = $offset; $i < $limit; $i++) {
            $range[] = $rates[$i];
        }

        return $range;
    }
}
