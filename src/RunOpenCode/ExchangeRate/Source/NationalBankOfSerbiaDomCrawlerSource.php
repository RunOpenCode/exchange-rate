<?php

namespace RunOpenCode\ExchangeRate\Source;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;
use RunOpenCode\ExchangeRate\Exception\UnknownRateTypeException;
use RunOpenCode\ExchangeRate\Utils\CurrencyCode;

class NationalBankOfSerbiaDomCrawlerSource implements SourceInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'National Bank of Serbia';
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($currencyCode, $rateType = 'default', $date = null)
    {
        $this
            ->validateRateType($rateType)
            ->validateCurrencyCode($currencyCode);


    }

    protected function validateRateType($rateType)
    {
        $knownTypes = array(
            'default', // It is actually a middle exchange rate
            'top',
            'bottom'
        );

        if (!in_array($rateType, $knownTypes)) {
            throw new UnknownRateTypeException(sprintf('Unknown rate type "%s" for source "%s", known types are: %s.', $rateType, $this->getName(), implode(', ', $knownTypes)));
        }

        return $this;
    }

    public function validateCurrencyCode($currencyCode)
    {
        if (!CurrencyCode::exists($currencyCode)) {
            throw new UnknownCurrencyCodeException(sprintf('Unknown currency code "%s".', $currencyCode));
        }
        return $this;
    }
}
