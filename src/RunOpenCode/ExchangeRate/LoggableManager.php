<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate;

use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Enum\RateType;

/**
 * Class LoggableManager
 *
 * @package RunOpenCode\ExchangeRate
 */
class LoggableManager implements ManagerInterface
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LoggableManager constructor.
     *
     * @param ManagerInterface $manager
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerInterface $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function has($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        try {
            return $this->manager->has($sourceName, $currencyCode, $date, $rateType);
        } catch (\Exception $e) {
            $this->logger->error('Unable to determine if rate for {currency_code} of type {rate_type} from source {source} on {date} exists.', [
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
                'source' => $sourceName,
                'date' => (null === $date) ? date('Y-m-d') : $date->format('Y-m-d'),
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($sourceName, $currencyCode, \DateTime $date, $rateType = RateType::MEDIAN)
    {
        try {
            return $this->manager->get($sourceName, $currencyCode, $date, $rateType);
        } catch (\Exception $e) {
            $this->logger->error('Unable to fetch rate for {currency_code} of type {rate_type} from source {source} on {date}.', [
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
                'source' => $sourceName,
                'date' => (null === $date) ? date('Y-m-d') : $date->format('Y-m-d'),
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
        try {
            return $this->manager->latest($sourceName, $currencyCode, $rateType);
        } catch (\Exception $e) {
            $this->logger->error('Unable to fetch latest rate for {currency_code} of type {rate_type} from source {source}.', [
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
                'source' => $sourceName,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function today($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
        try {
            return $this->manager->today($sourceName, $currencyCode, $rateType);
        } catch (\Exception $e) {
            $this->logger->error('Unable to fetch today\'s rate for {currency_code} of type {rate_type} from source {source}.', [
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
                'source' => $sourceName,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function historical($sourceName, $currencyCode, \DateTime $date, $rateType = RateType::MEDIAN)
    {
        try {
            return $this->manager->historical($sourceName, $currencyCode, $date, $rateType);
        } catch (\Exception $e) {
            $this->logger->error('Unable to fetch historical rate for {currency_code} of type {rate_type} from source {source} on {date}.', [
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
                'source' => $sourceName,
                'date' => (null === $date) ? date('Y-m-d') : $date->format('Y-m-d'),
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($sourceName = null, \DateTime $date = null)
    {
        try {
            $rates = $this->manager->fetch($sourceName, $date);
        } catch (\Exception $e) {
            $this->logger->error('Unable to fetch rates from source {source} on {date}.', [
                'source' => $sourceName,
                'date' => (null === $date) ? date('Y-m-d') : $date->format('Y-m-d'),
                'exception' => $e,
            ]);
            throw $e;
        }

        /**
         * @var RateInterface $rate
         */
        foreach ($rates as $rate) {
            $this->logger->debug('Fetched rate for {currency_code} of type {rate_type} from source {source} on {date}.', [
                'currency_code' => $rate->getCurrencyCode(),
                'rate_type' => $rate->getRateType(),
                'source' => $rate->getSourceName(),
                'date' => $rate->getDate()->format('Y-m-d'),
            ]);
        }

        return $rates;
    }
}
