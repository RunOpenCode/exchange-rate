<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\Source\Api;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Source\NationalBankOfSerbiaDomCrawlerSource;

/**
 * Class NationalBankOfSerbiaXmlSaxParser
 *
 * Parses XML files from National bank of Serbia.
 *
 * @package RunOpenCode\ExchangeRate\Source\Api
 * @internal
 */
class NationalBankOfSerbiaXmlSaxParser
{
    /**
     * @var RateInterface[]
     */
    private $rates;

    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var array
     */
    private $currentRate;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $rateType;

    /**
     * Parse XML from National bank of Serbia.
     *
     * @param string $xml
     * @return RateInterface[]
     */
    public function parse($xml)
    {
        $this->rates = array();
        $this->stack = new \SplStack();
        $this->currentRate = array();
        $this->date = new \DateTime('now');
        $this->rateType = 'default';

        $parser = xml_parser_create();

        xml_set_element_handler(
            $parser,
            \Closure::bind(function($parser, $name, $attributes) {
                $this->onStart($parser, $name, $attributes);
            }, $this),
            \Closure::bind(function($parser, $name) {
                $this->onEnd($parser, $name);
            }, $this)
        );

        xml_set_character_data_handler(
            $parser,
            \Closure::bind(function($parser, $data) {
                $this->onData($parser, $data);
            }, $this));

        xml_parse($parser, $xml);
        xml_parser_free($parser);

        return $this->rates;
    }

    /**
     * SAX on tag start callback.
     *
     * @param $parser
     * @param $name
     * @param $attributes
     */
    protected function onStart($parser, $name, $attributes)
    {
        $this->stack->push($name);

        if ($name === 'ITEM') {
            $this->currentRate = array();
        }
    }

    /**
     * SAX on tag end callback.
     *
     * @param $parser
     * @param $name
     */
    protected function onEnd($parser, $name)
    {
        $this->stack->pop();

        $buildRate = function($value, $currencyCode, $rateType, $date) {

            return new Rate(
                NationalBankOfSerbiaDomCrawlerSource::NAME,
                $value,
                $currencyCode,
                $rateType,
                $date,
                'RSD',
                new \DateTime('now'),
                new \DateTime('now')
            );
        };

        if ($name === 'ITEM') {

            if (array_key_exists('buyingRate', $this->currentRate)) {

                $this->rates[] = $buildRate(
                    $this->currentRate['buyingRate'] / $this->currentRate['unit'],
                    $this->currentRate['currencyCode'],
                    $this->rateType . '_buying',
                    $this->date
                );
            }

            if (array_key_exists('sellingRate', $this->currentRate)) {

                $this->rates[] = $buildRate(
                    $this->currentRate['sellingRate'] / $this->currentRate['unit'],
                    $this->currentRate['currencyCode'],
                    $this->rateType . '_selling',
                    $this->date
                );
            }

            if (array_key_exists('middleRate', $this->currentRate)) {

                $this->rates[] = $buildRate(
                    $this->currentRate['middleRate'] / $this->currentRate['unit'],
                    $this->currentRate['currencyCode'],
                    'default',
                    $this->date
                );
            }

            $this->currentRate = array();
        }
    }

    /**
     * SAX on data callback.
     *
     * @param $parser
     * @param $data
     */
    protected function onData($parser, $data)
    {
        if (!empty($data)) {

            switch ($this->stack->top()) {
                case 'DATE':
                    $this->date = \DateTime::createFromFormat('d.m.Y', $data);
                    break;
                case 'TYPE':
                    $data = trim($data);
                    if ($data === 'FOREIGN EXCHANGE') {
                        $this->rateType = 'foreign_exchange';
                    } elseif ($data === 'FOREIGN CASH') {
                        $this->rateType = 'foreign_cache';
                    }
                    break;
                case 'CURRENCY':
                    $this->currentRate['currencyCode'] = trim($data);
                    break;
                case 'UNIT':
                    $this->currentRate['unit'] = (int) trim($data);
                    break;
                case 'BUYING_RATE':
                    $this->currentRate['buyingRate'] = (float) trim($data);
                    break;
                case 'SELLING_RATE':
                    $this->currentRate['sellingRate'] = (float) trim($data);
                    break;
                case 'MIDDLE_RATE':
                    $this->currentRate['middleRate'] = (float) trim($data);
                    break;
            }
        }
    }

    /**
     * Parse XML from National bank of Serbia.
     *
     * @param string $xml
     * @return RateInterface[]
     */
    public static function parseXml($xml)
    {
        $parser = new static();
        return $parser->parse($xml);
    }

}
