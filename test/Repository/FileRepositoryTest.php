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

use RunOpenCode\ExchangeRate\Repository\FileRepository;

class FileRepositoryTest extends AbstractRepositoryTest
{
    protected function getRepository()
    {
        return new FileRepository(tempnam(sys_get_temp_dir(), 'roc_exchange_rate_file_repository'));
    }
}
