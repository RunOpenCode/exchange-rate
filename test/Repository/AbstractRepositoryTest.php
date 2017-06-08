<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Tests\Repository;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Model\Rate;

abstract class AbstractRepositoryTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function getThrowsExceptionWhenDoesNotExists()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
        ));


        $repository->get('some_source', 'CHF');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\ExchangeRate\Exception\ExchangeRateException
     */
    public function latestThrowsExceptionWhenEmpty()
    {
        $repository = $this->getRepository();
        $repository->latest('some_source', 'EUR');
    }

    /**
     * @test
     */
    public function all()
    {
        $repository = $this->getRepository();


        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 10, 'CHF', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 10, 'BAM', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
        ));

        $rates = $repository->all();

        $this->assertEquals(3, count($rates));
    }

    /**
     * @test
     */
    public function save()
    {
        $repository = $this->getRepository();

        $this->assertSame(0, $repository->count(), 'Repository should be empty.');

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
        ));

        $this->assertTrue($repository->has('some_source', 'EUR'));
        $this->assertFalse($repository->has('some_source', 'CHF'));
        $this->assertEquals(10, $repository->get('some_source', 'EUR')->getValue());
        $this->assertSame(1, $repository->count(), 'Repository should have one record.');

        $repository->save(array(
            new Rate('some_source', 20, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 15, 'CHF', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime()),
        ));

        $this->assertTrue($repository->has('some_source', 'EUR'));
        $this->assertTrue($repository->has('some_source', 'CHF'));
        $this->assertSame(2, $repository->count(), 'Repository should have two records.');
        $this->assertEquals(20, $repository->get('some_source', 'EUR')->getValue());
    }

    /**
     * @test
     */
    public function delete()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
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
            new Rate('some_source', 10, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-11'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 13, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rate = $repository->latest('some_source', 'EUR');

        $this->assertSame(12.0, $rate->getValue());
    }

    /**
     * @test
     */
    public function pagination()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-08'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-07'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 13, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rates = $repository->all(array(
            'offset' => 2,
            'limit' => 2
        ));

        $this->assertSame(2, count($rates));

        $this->assertSame(12.0, $rates[0]->getValue());
        $this->assertSame(13.0, $rates[1]->getValue());
    }

    /**
     * @test
     */
    public function filter()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('some_source', 10, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-10'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 11, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-09'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_source', 12, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-11'), 'RSD', new \DateTime(), new \DateTime()),
            new Rate('some_other_source', 13, 'EUR', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-06'), 'RSD', new \DateTime(), new \DateTime())
        ));

        $rates = $repository->all(array(
            'sourceName' => 'some_other_source'
        ));

        $this->assertSame(1, count($rates));

        $this->assertSame(13.0, $rates[0]->getValue());
    }

    /**
     * @return RepositoryInterface
     */
    protected abstract function getRepository();
}
