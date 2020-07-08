<?php


namespace TS\DoctrineExtensions\DBAL\FixedDbTimezone;


use PHPUnit\Framework\TestCase;

class TsConversionTest extends TestCase
{


    // make sure that defining constants for each test works
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;


    function testTimezoneDbUsesConstant()
    {
        date_default_timezone_set('America/New_York');
        define('DATABASE_TIMEZONE', 'Europe/London');
        $this->assertEquals('Europe/London', TzConversion::timezoneDB()->getName());
    }

    function testTimezoneDbFallsBackToTimezonePhp()
    {
        date_default_timezone_set('America/New_York');
        $this->assertEquals('America/New_York', TzConversion::timezoneDB()->getName());
    }

    function testTimezonePhp()
    {
        date_default_timezone_set('America/New_York');
        $this->assertEquals('America/New_York', TzConversion::timezonePHP()->getName());
    }

    function testConvertToDbUpdatesTimezone()
    {
        define('DATABASE_TIMEZONE', 'Europe/London');
        date_default_timezone_set('America/New_York');
        $date = date_create_from_format('Y-m-d H:i:s', '2020-01-01 12:00:00');
        $converted = TzConversion::convertToDB($date);
        $this->assertEquals($date->getTimestamp(), $converted->getTimestamp());
        $this->assertEquals('Europe/London', $converted->getTimezone()->getName());
    }

    function testConvertToDbUpdatesTimezoneForImmutableDatesToo()
    {
        define('DATABASE_TIMEZONE', 'Europe/London');
        date_default_timezone_set('America/New_York');
        $date = date_create_immutable_from_format('Y-m-d H:i:s', '2020-01-01 12:00:00');
        $converted = TzConversion::convertToDB($date);
        $this->assertEquals($date->getTimestamp(), $converted->getTimestamp());
        $this->assertEquals('Europe/London', $converted->getTimezone()->getName());
    }


}
