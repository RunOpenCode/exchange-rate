<?php
///*
// * This file is part of the Exchange Rate package, an RunOpenCode project.
// *
// * (c) 2016 RunOpenCode
// *
// * For the full copyright and license information, please view the LICENSE
// * file that was distributed with this source code.
// */
//namespace RunOpenCode\ExchangeRate\Tests {
//
//    use PHPUnit\Framework\TestCase;
//    use RunOpenCode\ExchangeRate\Manager;
//    use RunOpenCode\ExchangeRate\Model\Rate;
//    use RunOpenCode\ExchangeRate\Registry\ProcessorsRegistry;
//    use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;
//    use RunOpenCode\ExchangeRate\Registry\SourcesRegistry;
//    use RunOpenCode\ExchangeRate\Repository\MemoryRepository;
//
//    /**
//     * Class ManagerWorkingDayTest
//     *
//     * @package RunOpenCode\ExchangeRate\Tests
//     */
//    class ManagerWorkingDayTest extends TestCase
//    {
//        /**
//         * @test
//         */
//        public function today()
//        {
//            $manager = new Manager(
//                'RSD',
//                $repository = new MemoryRepository(),
//                new SourcesRegistry(),
//                new ProcessorsRegistry(),
//                new RatesConfigurationRegistry()
//            );
//
//            $repository->save([
//                new Rate('some_source', 10, 'BAM', 'median', \DateTime::createFromFormat('Y-m-d', '2015-10-08'), 'RSD', new \DateTime(), new \DateTime()),
//            ]);
//
//            $this->assertEquals(10.0, $manager->today('some_source', 'BAM')->getValue());
//        }
//    }
//}
//
//namespace RunOpenCode\ExchangeRate {
//
//    function time() {
//        return \DateTime::createFromFormat('Y-m-d', '2015-10-08')->getTimestamp();
//    }
//}