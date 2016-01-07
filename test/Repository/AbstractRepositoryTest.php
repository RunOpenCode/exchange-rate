<?php

namespace RunOpenCode\ExchangeRate\Tests\Repository;

use RunOpenCode\ExchangeRate\Contract\RepositoryInterface;
use RunOpenCode\ExchangeRate\Model\Rate;

abstract class AbstractRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test§
     */
    public function have()
    {
        $repository = $this->getRepository();

        $repository->save(array(
            new Rate('moj', 10, 'EUR', 'default', new \DateTime(), 'RSD', new \DateTime(), new \DateTime())
        ));

        $this->assertTrue($repository->has('moj', 'EUR'));

    }

    /**
     * @return RepositoryInterface
     */
    protected abstract function getRepository();
}