<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Log;

use Psr\Log\LoggerAwareTrait as BaseLoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class LoggerAwareTrait
 *
 * Provides base logging capability. If logger is not provided, null logger will be initialized.
 *
 * @package RunOpenCode\ExchangeRate\Log
 */
trait LoggerAwareTrait
{
    use BaseLoggerAwareTrait;

    /**
     * Get logger. If logger does not exists, NullLogger will be provided.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }
}
