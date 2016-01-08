<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Registry;

use RunOpenCode\ExchangeRate\Configuration;
use RunOpenCode\ExchangeRate\Contract\AliasRegistryInterface;
use RunOpenCode\ExchangeRate\Contract\ManagerInterface;
use RunOpenCode\ExchangeRate\Contract\RatesConfigurationRegistryInterface;

/**
 * Class AliasRegistry
 *
 * Default implementation of alias registry.
 *
 * @package RunOpenCode\ExchangeRate\Registry
 */
class AliasRegistry implements AliasRegistryInterface
{
    /**
     * @var array
     */
    protected $registry;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    public function __construct(ManagerInterface $manager, RatesConfigurationRegistryInterface $configurations)
    {
        $this->manager = $manager;
        $this->registry = array();

        /**
         * @var Configuration $configuration
         */
        foreach ($configurations as $configuration) {

            if ($configuration->getAlias() !== null) {
                $this->registry[$configuration->getAlias()] = $configuration;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($alias, $date = null)
    {
        if (array_key_exists($alias, $this->registry)) {
            /**
             * @var Configuration $configuration
             */
            $configuration = $this->registry[$alias];

            return $this->manager->has(
                $configuration->getSource(),
                $configuration->getCurrencyCode(),
                $date,
                $configuration->getRateType()
            );
        }

        throw new \RuntimeException(sprintf('Unknown alias "%s".', $alias));
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias, $date = null)
    {
        if (array_key_exists($alias, $this->registry)) {
            /**
             * @var Configuration $configuration
             */
            $configuration = $this->registry[$alias];

            return $this->manager->get(
                $configuration->getSource(),
                $configuration->getCurrencyCode(),
                $date,
                $configuration->getRateType()
            );
        }

        throw new \RuntimeException(sprintf('Unknown alias "%s".', $alias));
    }

    /**
     * {@inheritdoc}
     */
    public function latest($alias, $date = null)
    {
        if (array_key_exists($alias, $this->registry)) {
            /**
             * @var Configuration $configuration
             */
            $configuration = $this->registry[$alias];

            return $this->manager->latest(
                $configuration->getSource(),
                $configuration->getCurrencyCode(),
                $configuration->getRateType()
            );
        }

        throw new \RuntimeException(sprintf('Unknown alias "%s".', $alias));
    }

    /**
     * {@inheritdoc}
     */
    public function today($alias)
    {
        if (array_key_exists($alias, $this->registry)) {
            /**
             * @var Configuration $configuration
             */
            $configuration = $this->registry[$alias];

            return $this->manager->today(
                $configuration->getSource(),
                $configuration->getCurrencyCode(),
                $configuration->getRateType()
            );
        }

        throw new \RuntimeException(sprintf('Unknown alias "%s".', $alias));
    }
}
