<?php

namespace RunOpenCode\ExchangeRate\Tests\Repository;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Model\Rate;

abstract class AbstractRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function save()
    {
        $repository = $this->getRepository();

        $this->assertSame(0, $repository->count(), 'Repository should be empty.');

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'default', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
        ));

        $this->assertTrue($repository->has('some_source', 'EUR'));
        $this->assertFalse($repository->has('some_source', 'CHF'));
    }

    /**
     * @test
     */
    public function delete()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'default', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
        ));

        $this->assertTrue($repository->has('some_source', 'EUR'));
        $rate = $repository->get('some_source', 'EUR');

        $repository->delete(array($rate));

        $this->assertFalse($repository->has('some_source', 'EUR'));
    }

    /**
     * @test
     */
    public function latest()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-11'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 13, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rate = $repository->latest('some_source', 'EUR');

        $this->assertSame(12, $rate->getValue());
    }

    /**
     * @test
     */
    public function pagination()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-11'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 13, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rates = $repository->all(array(
            'offset' => 2,
            'limit' => 3
        ));

        $this->assertSame(2, count($rates));

        $this->assertSame(11, $rates[0]->getValue());
        $this->assertSame(13, $rates[1]->getValue());
    }

    /**
     * @test
     */
    public function filter()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-11'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_other_source', 13, 'EUR', 'default', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rates = $repository->all(array(
            'sourceName' => 'some_other_source'
        ));

        $this->assertSame(1, count($rates));

        $this->assertSame(13, $rates[0]->getValue());
    }

    /**
     * @return RepositoryInterface
     */
    protected abstract function getRepository();
}
