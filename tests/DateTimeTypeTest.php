<?php

namespace TS\DoctrineExtensions\DBAL\FixedDbTimezone;

use DateTime;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{


    public function testConvertToDatabaseValue()
    {
        date_default_timezone_set('Europe/London');
        define('DATABASE_TIMEZONE', 'UTC');

        $phpDate = DateTime::createFromFormat('Y-m-d H:i:s', '2019-05-08 00:30:00');
        $dbDate = $this->type->convertToDatabaseValue($phpDate, $this->platform);
        $this->assertSame('2019-05-07 23:30:00', $dbDate);
    }


    public function testConvertToPHPValue()
    {
        date_default_timezone_set('Europe/London');
        define('DATABASE_TIMEZONE', 'UTC');

        $phpDate = $this->type->convertToPHPValue('2019-05-07 23:30:00', $this->platform);
        $this->assertSame('UTC', $phpDate->getTimezone()->getName());
    }


    public function testRoundTripIsSameTimestamp()
    {
        date_default_timezone_set('Europe/London');
        define('DATABASE_TIMEZONE', 'UTC');
        $date = date_create_from_format('Y-m-d H:i:s', '2019-05-08 00:30:00');

        $db = $this->type->convertToDatabaseValue($date, $this->platform);
        $back = $this->type->convertToPHPValue($db, $this->platform);

        $this->assertSame($date->getTimestamp(), $back->getTimestamp());
    }


    // make sure that defining constants for each test works
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;


    /** @var DateTimeType */
    private $type;

    /** @var SqlitePlatform */
    private $platform;

    protected function setUp()
    {
        Type::overrideType('datetime', DateTimeType::class);
        $this->type = Type::getType('datetime');
        $this->platform = new SqlitePlatform();
    }


}
