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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Enum\RateType;
use RunOpenCode\ExchangeRate\Exception\ExchangeRateException;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Utils\FilterUtilHelper;

/**
 * Class DoctrineDbalRepository
 *
 * Dbal repository uses http://www.doctrine-project.org/projects/dbal.html for rates persistance.
 *
 * @package RunOpenCode\ExchangeRate\Repository
 */
class DoctrineDbalRepository implements RepositoryInterface
{
    use FilterUtilHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var RateInterface[]
     */
    private $identityMap = [];

    /**
     * DoctrineDbalRepository constructor.
     *
     * @param Connection $connection Dbal connection.
     * @param string|null $tableName Table name in which rates will be stored, or NULL for 'runopencode_exchange_rate'.
     */
    public function __construct(Connection $connection, $tableName = null)
    {
        $this->connection = $connection;
        $this->tableName = ($tableName = trim($tableName)) ? $tableName : 'runopencode_exchange_rate';

        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $rates)
    {
        $this->connection->beginTransaction();

        try {

            /**
             * @var RateInterface $rate
             */
            foreach ($rates as $rate) {

                if ($this->has($rate->getSourceName(), $rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType())) {

                    $this->connection->executeQuery(sprintf('UPDATE %s SET rate_value = :rate_value, modified_at = :modified_at WHERE source_name = :source_name AND currency_code = :currency_code AND rate_date = :rate_date AND rate_type = :rate_type;', $this->tableName), [
                        'rate_value' => (float) $rate->getValue(),
                        'source_name' => $rate->getSourceName(),
                        'currency_code' => $rate->getCurrencyCode(),
                        'rate_date' => $rate->getDate()->format('Y-m-d'),
                        'rate_type' => $rate->getRateType(),
                        'modified_at' => date('Y-m-d H:i:s'),
                    ]);

                    continue;
                }

                $this->connection->executeQuery(sprintf('INSERT INTO %s (source_name, rate_value, currency_code, rate_type, rate_date, base_currency_code, created_at, modified_at) VALUES (:source_name, :rate_value, :currency_code, :rate_type, :rate_date, :base_currency_code, :created_at, :modified_at);', $this->tableName), [
                    'source_name' => $rate->getSourceName(),
                    'rate_value' => (float) $rate->getValue(),
                    'currency_code' => $rate->getCurrencyCode(),
                    'rate_type' => $rate->getRateType(),
                    'rate_date' => $rate->getDate()->format('Y-m-d'),
                    'base_currency_code' => $rate->getBaseCurrencyCode(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->connection->commit();
            $this->identityMap = [];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new ExchangeRateException('Unable to save rates.', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $rates)
    {
        $this->connection->beginTransaction();

        try {

            /**
             * @var RateInterface $rate
             */
            foreach ($rates as $rate) {
                $key = $this->getRateKey($rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType(), $rate->getSourceName());

                if (isset($this->identityMap[$key])) {
                    unset($this->identityMap[$key]);
                }

                $this->connection->executeQuery(sprintf('DELETE FROM %s WHERE source_name = :source_name AND currency_code = :currency_code AND rate_date = :rate_date AND rate_type = :rate_type;', $this->tableName), [
                    'source_name' => $rate->getSourceName(),
                    'currency_code' => $rate->getCurrencyCode(),
                    'rate_date' => $rate->getDate()->format('Y-m-d'),
                    'rate_type' => $rate->getRateType(),
                ]);
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new ExchangeRateException('Unable to delete rates.', 0, $e);
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

        try {
            return (bool) $this->get($sourceName, $currencyCode, $date, $rateType);
        } catch (ExchangeRateException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($sourceName, $currencyCode, \DateTime $date = null, $rateType = RateType::MEDIAN)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        $key = $this->getRateKey($currencyCode, $date, $rateType, $sourceName);

        if (!isset($this->identityMap[$key])) {

            /**
             * @var array $result
             */
            $result = $this->connection->fetchAll(
                sprintf('SELECT R.* FROM %s R WHERE R.source_name = :source_name AND R.currency_code = :currency_code AND R.rate_date = :rate_date AND R.rate_type = :rate_type;', $this->tableName),
                [
                    'source_name' => $sourceName,
                    'currency_code' => $currencyCode,
                    'rate_date' => $date->format('Y-m-d'),
                    'rate_type' => $rateType,
                ]
            );

            if (0 === count($result)) {
                throw new ExchangeRateException(sprintf('Could not fetch rate for rate currency code "%s" and rate type "%s" on date "%s".', $currencyCode, $rateType, $date->format('Y-m-d')));
            }

            $this->identityMap[$key] = $this->buildRateFromTableRowData($result[0]);
        }

        return $this->identityMap[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function latest($sourceName, $currencyCode, $rateType = RateType::MEDIAN)
    {
        /**
         * @var array $result
         */
        $result = $this->connection->fetchAll(
            sprintf('SELECT R.* FROM %s R WHERE R.source_name = :source_name AND R.currency_code = :currency_code AND R.rate_type = :rate_type ORDER BY R.rate_date DESC;', $this->tableName),
            [
                'source_name' => $sourceName,
                'currency_code' => $currencyCode,
                'rate_type' => $rateType,
            ]
        );

        if (0 === count($result)) {
            throw new ExchangeRateException(sprintf('Could not fetch latest rate for rate currency code "%s" and rate type "%s".', $currencyCode, $rateType));
        }

        $rate = $this->buildRateFromTableRowData($result[0]);
        $key = $this->getRateKey($rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType(), $rate->getSourceName());

        return ($this->identityMap[$key] = $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $criteria = array())
    {
        /**
         * @var QueryBuilder $queryBuilder
         */
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('R.*')
            ->from($this->tableName, 'R')
            ->addOrderBy('R.rate_date', 'DESC')
            ->addOrderBy('R.source_name', 'ASC')
            ->addOrderBy('R.currency_code', 'ASC')
            ->addOrderBy('R.rate_type', 'ASC')
        ;

        if (0 !== count($currencyCodes = self::extractArrayCriteria('currencyCode', $criteria))) {
            $queryBuilder
                ->andWhere('R.currency_code IN (:currency_codes)')
                ->setParameter(':currency_codes', $currencyCodes, Connection::PARAM_STR_ARRAY);
        }

        if (0 !== count($rateTypes = self::extractArrayCriteria('rateType', $criteria))) {
            $queryBuilder
                ->andWhere('R.rate_type IN (:rate_types)')
                ->setParameter(':rate_types', $rateTypes, Connection::PARAM_STR_ARRAY);
        }

        if (0 !== count($sourceNames = self::extractArrayCriteria('sourceName', $criteria))) {
            $queryBuilder
                ->andWhere('R.source_name IN (:source_names)')
                ->setParameter(':source_names', $sourceNames, Connection::PARAM_STR_ARRAY);
        }

        if (isset($criteria['dateFrom'])) {
            $queryBuilder
                ->andWhere('R.rate_date >= :date_from')
                ->setParameter('date_from', $criteria['dateFrom']->format('Y-m-d'));
        }

        if (isset($criteria['dateTo'])) {
            $queryBuilder
                ->andWhere('R.rate_date <= :date_to')
                ->setParameter('date_to', $criteria['dateTo']->format('Y-m-d'));
        }

        if (isset($criteria['onDate'])) {
            $queryBuilder
                ->andWhere('R.rate_date = :on_date')
                ->setParameter('on_date', $criteria['onDate']->format('Y-m-d'));
        }

        if (isset($criteria['limit'])) {
            $queryBuilder->setMaxResults($criteria['limit']);
        }

        if (isset($criteria['offset'])) {
            $queryBuilder->setFirstResult($criteria['offset']);
        }

        /**
         * @var Statement $statement
         */
        $statement = $queryBuilder->execute();
        while (($row = $statement->fetch()) !== false) {
            $rate = $this->buildRateFromTableRowData($row);
            $key = $this->getRateKey($rate->getCurrencyCode(), $rate->getDate(), $rate->getRateType(), $rate->getSourceName());
            $this->identityMap[$key] = $rate;
        }

        return array_values($this->identityMap);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        /**
         * @var Statement $statement
         */
        $statement = $this->connection->query(sprintf('SELECT count(*) as cnt FROM %s;', $this->tableName), \PDO::FETCH_ASSOC);
        return (int) $statement->fetchAll()[0]['cnt'];
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
     * Initialize table schema where rates would be stored.
     */
    protected function initialize()
    {
        if ($this->connection->getSchemaManager()->tablesExist([$this->tableName])) {
            return; // @codeCoverageIgnore
        }

        $schema = new Schema();

        $table = $schema->createTable($this->tableName);
        $table->addColumn('source_name', 'string', ['length' => 255]);
        $table->addColumn('rate_value', 'float', ['precision' => 10, 'scale' => 4]);
        $table->addColumn('currency_code', 'string', ['length' => 3]);
        $table->addColumn('rate_type', 'string', ['length' => 255]);
        $table->addColumn('rate_date', 'date', []);
        $table->addColumn('base_currency_code', 'string', ['length' => 3]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('modified_at', 'datetime', []);

        $table->setPrimaryKey(['currency_code', 'rate_date', 'rate_type', 'source_name']);

        $this->connection->exec($schema->toSql($this->connection->getDatabasePlatform())[0]);
    }

    /**
     * Build rate from table row data.
     *
     * @param array $row Row data.
     * @return Rate
     */
    private function buildRateFromTableRowData(array $row)
    {
        return new Rate(
            $row['source_name'],
            (float) $row['rate_value'],
            $row['currency_code'],
            $row['rate_type'],
            \DateTime::createFromFormat('Y-m-d', $row['rate_date']),
            $row['base_currency_code'],
            \DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']),
            \DateTime::createFromFormat('Y-m-d H:i:s', $row['modified_at'])
        );
    }
}
