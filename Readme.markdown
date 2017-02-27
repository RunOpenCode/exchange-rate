Exchange rate
====
*Fetch, store and use currency exchange rates in your application*

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/exchange-rate.svg)](https://packagist.org/packages/runopencode/exchange-rate)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/exchange-rate/build-status/master)
[![Build Status](https://travis-ci.org/RunOpenCode/exchange-rate.svg?branch=master)](https://travis-ci.org/RunOpenCode/exchange-rate)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2ea424ca-2cfc-4b14-b1ae-3dafb7f93685/big.png)](https://insight.sensiolabs.com/projects/2ea424ca-2cfc-4b14-b1ae-3dafb7f93685)

# About domain problem when working with exchange rates on your web shop/accounting application, or similar.

If you are building any kind of accounting application or some kind of web store that deals with various currency exchange rates,
you are probably have to deal with exchange rates. 

Exchange rate calculator is easy job, however, acquiring proper exchange rate to work with can be hassle, especially since
all exchange rates are provided by external third party, accessible via some API. That means that your application depends on
availability of third party API.

Now, considering that (at least in business theory) exchange rates are established every working day at 2PM by national banks
for next day (Friday for Saturday and Sunday) and those rates are unchanged during working day (at least not on goods and service)
market, you should be able to fetch rates in advance only once, and use them from your own local source, without fetching
rates from external source for that day. Commercial banks follows similar practice, national bank will determine lower and upper 
buying/selling rates, and commercial banks can establish their prices within that range (after all, money trading is trading 
business in its basis).

In general, your job is to fetch rates with which your application works with in advance, store them on some local repository,
and use those rates from that point from your local source.
  
This library solves that problem providing necessary classes and tools that will help you with dealing with exchange rates.

Note that this library is not exchange rate calculator.

# Architecture of the system

System defines 6 core components:

- **Source**, implementation of `RunOpenCode\ExchangeRate\Contract\SourceInterface` which only purpose is to provide you
with exchange rate from your third API provider (per example, your bank or your state's national bank). Each source implementation
MUST have its own unique name. You can construct the name of your source with your bank name and country name, per example. 
Name SHOULD contain lowercase characters, separated with underscore. Note that this library DOES NOT provide you with any 
source, sources are available in separate libraries which you can use in your project per your needs, or you can develop
your own.
- **Rate**, implementation of `RunOpenCode\ExchangeRate\Contract\RateInterface` models one single exchange rate value, which
is provided to your system from Source. Rate is going to be persisted in your local storage system per your choice. You are
provided with default implementation of this interface, of course.
- **Repository**, implementation of `RunOpenCode\ExchangeRate\Contract\RepositoryInterface` is in charge for persisting
Rates on local storage system. This library provides you with implementation of `RunOpenCode\ExchangeRate\Repository\FileRepository`
which saves Rates into local plain text file. Considering that there is only 365 days per year, this is good enough storage system, 
especially if you wrap him with some kind of caching mechanism. You can, of course, use `RunOpenCode\ExchangeRate\Repository\DoctrineDbalRepository`
as well, if you would like to store rates in relational database (like MySql) via Doctrine Dbal library.
- **Processor**, implementation of `RunOpenCode\ExchangeRate\Contract\ProcessorInterface` which process and modifies rates
after they are fetched from sources. Library delivers several processor implementations, mainly in charge of validating 
fetched rates. However, you can modify rates however you like prior to their persistence. Per example, your client could
ask from you to work with their own exchange rates that have different prices in comparison to some base rates. Processor
gives you the possibility to add your own rates as well, after base rates are fetched.
- **Configuration** is entity in which you configure a system for which rate you are interested in. Source will provide you
with rates that are available with third party API, however, not all rates are going to be stored into local repository.
With configuration you can determine which rates you would like to have available in your system.
- **Manager** is central component which expose exchange rate system to developer. 

# Anatomy of Rate

Let's consider rate:

- A rate is provided from Source (per example, a "National Bank of Serbia")
- A rate have its type (per example, "median rate"). Rate type is not something that is finite set, each bank can work 
with different rate types. In general, there are 3 commonly used rate types: "median", "buying" and "selling". However, 
bank mentioned above, National Bank of Serbia, provides 5 different rates. 
- A rate have its base currency (that is usually a currency of the country in which bank operates)
- Currency code and value, of course, for exchange, and date when that rate is valid.

# Manager

Manager follows rate anatomy, and exposes API which allows you to query for rate which you need:
  
- `Manager::get($sourceName, $currencyCode, \DateTime $date, $rateType)` will provide you with exchange rate from 
specific source, on provided date of provided rate type.
- `Manager::latest($sourceName, $currencyCode, $rateType)` will provide you with latest available rate.
- `Manager::today($sourceName, $currencyCode, $rateType)` will provide you with rate that should be used on current system
date. That means that if it is a Saturday or Sunday, and if rates are not available, a rate from last Friday will be used.
- `Manager::historical($sourceName, $currencyCode, \DateTime $date, $rateType)` will provide you with rate that should be 
used on given date. That means that if it is a Saturday or Sunday, and if rates are not available, 
a rate from Friday before given date will be used.  

System defines 'median' rate, considering that every Source will provide you with at least one rate type, which is usually
a median. Every implementation should declare one of available rate types as default on. Recommendation is to consider
'median' rate type as default.

# Bootstrapping manager

In example bellow you can find example of manager initialization, which you can use in your application.

        // We are using Composer for autoloading
        include 'vendor/autoload.php';
        
        use RunOpenCode\ExchangeRate\Configuration;
        use RunOpenCode\ExchangeRate\Manager;
        use RunOpenCode\ExchangeRate\Processor\BaseCurrencyValidator;
        use RunOpenCode\ExchangeRate\Processor\UniqueRatesValidator;
        use RunOpenCode\ExchangeRate\Registry\ProcessorsRegistry;
        use RunOpenCode\ExchangeRate\Registry\RatesConfigurationRegistry;
        use RunOpenCode\ExchangeRate\Registry\SourcesRegistry;
        use RunOpenCode\ExchangeRate\Repository\FileRepository;
        
        // Create sources registry
        $sourcesRegistry = new SourcesRegistry();
        
        // And register your sources. You can make your own sources, or find them on packagist, this is just example.
        $sourcesRegistry->add(new MySource());
        $sourcesRegistry->add(new MyOtherSource());
        
        // Create your configuration, register which rates you would like to fetch
        $configurationsRegistry = new RatesConfigurationRegistry(array(
            new Configuration('EUR', 'median', 'my_source'),
            new Configuration('CHF', 'median', 'my_source'),
            new Configuration('USD', 'median', 'my_other_source'),
        ));
        
        // Register your processors
        $processorsRegistry = new ProcessorsRegistry(array(
            new BaseCurrencyValidator(),
            new UniqueRatesValidator()
        ));
        
        // Provide repository
        $repository = new FileRepository('path/to/file/rates.db');
        
        // ... and initialize manager.
        $manager = new Manager('RSD', $repository, $sourcesRegistry, $processorsRegistry, $configurationsRegistry);
        
        
# Crontab and fetching rates
        
As in example above, you can make a similar script that will be executed by crontab every day to fetch fresh rates.         

        $manager = new Manager('RSD', $repository, $sourcesRegistry, $processorsRegistry, $configurationsRegistry);
        $manager->fetch();
        
# Some design notes and guidelines

When you design a system that uses rates from this library, and even if you use DB as storage for rates persistence - DO NOT
establish relation with rates. That is very bad practice! If you have, per example, an invoice in foreign currency, do copy
all data from Rate to your invoice model, do not reference Rate via foreign key, or by any other method.

The reason for that is to have data consistency and flexibility:

1. Your client can agree with buyer/seller specific exchange rate. This is quite possible and legit, if relation is used
than this request can not be supported.
2. If error is spotted in exchange rate value and if relation exists, it could not be corrected, because all issued and paid
invoices referencing that rate would be affected. That would mean that those invoices would have to be stored and re-issued,
and user of system would be forced to do additional explaining to his clients. 
        
However - if there is a data redundancy, that is, you copy all values from Rate to your model, all possibilities for 
occurrence of issues stated above would be avoided.

# Known implementations of sources

Below is the list of known implementations of exchange rates sources for this library which you can use in your projects:
 
- [National Bank of Serbia](http://www.nbs.rs), available via packagist: `runopencode/exchange-rate-nbs`, 
source via [Github](https://github.com/RunOpenCode/exchange-rate-nbs), courtesy of RunOpenCode.
- [Banca Intesa Serbia](http://www.bancaintesa.rs), available via packagist: `runopencode/exchange-rate-intesa-rs`, 
source via [Github](https://github.com/RunOpenCode/exchange-rate-intesa-rs), courtesy of RunOpenCode
 
# Known bridges 

Below is the list of known bridges for PHP frameworks:

- [Symfony Bundle](http://symfony.com), available via packagist: `runopencode/exchange-rate-bundle`, source via 
[Github](https://github.com/RunOpenCode/exchange-rate-bundle), courtesy of RunOpenCode.

