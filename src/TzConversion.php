<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace TS\DoctrineExtensions\DBAL\FixedDbTimezone;


use Doctrine\DBAL\Types\ConversionException;


abstract class TzConversion
{

    public static function timezoneDB(): \DateTimeZone
    {
        if (!static::$tz_db) {
            $name = null;
            if (defined('DATABASE_TIMEZONE')) {
                $name = DATABASE_TIMEZONE;
            } else {
                $env = getenv('DATABASE_TIMEZONE');
                if (is_string($env)) {
                    $name = $env;
                } else if (is_array($env)) {
                    throw new \LogicException('Environment variable "DATABASE_TIMEZONE" was expected to contain a string, got array.');
                }
            }
            if ($name) {
                static::$tz_db = new \DateTimeZone($name);
            } else {
                static::$tz_db = static::timezonePHP();
            }
        }
        return static::$tz_db;
    }


    public static function timezonePHP(): \DateTimeZone
    {
        if (!static::$tz_php) {
            static::$tz_php = new \DateTimeZone(date_default_timezone_get());
        }
        return static::$tz_php;
    }


    public static function enforceDB(\DateTimeInterface $value, string $toType): void
    {
        $actualTz = $value->getTimezone();
        $expectedTz = static::timezoneDB();
        if ($actualTz === $expectedTz) {
            return;
        }
        if ($actualTz->getName() === $expectedTz->getName()) {
            return;
        }
        $msg = 'Could not convert PHP DateTime "' . $value->format(DATE_RFC3339) . '" to Doctrine Type ' . $toType . '. '
            . 'The time zone "' . $actualTz->getName() . '" of the PHP DateTime must match the database timezone "' . $expectedTz->getName() . '"".';
        throw new ConversionException($msg);
    }


    public static function convertToDB(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        if ($dateTime instanceof \DateTimeImmutable) {
            return $dateTime->setTimezone(static::timezoneDB());
        }

        if ($dateTime instanceof \DateTime) {
            $asUtc = clone $dateTime;
            $asUtc->setTimezone(static::timezoneDB());
            return $asUtc;
        }

        throw new \LogicException('Unknown DateTimeInterface implementation: ' . get_class($dateTime));
    }


    public static function castToDB(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        $tz_db = static::timezoneDB();
        if ($dateTime->getTimezone() === $tz_db) {
            return $dateTime;
        }
        if ($dateTime->getTimezone()->getName() === $tz_db->getName()) {
            return $dateTime;
        }
        $cast = new \DateTime('now', $tz_db);

        $cast->setDate($dateTime->format('Y'), $dateTime->format('n'), $dateTime->format('j'));

        $cast->setTimestamp($dateTime->getTimestamp());
        return $dateTime instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromMutable($cast) : $cast;
    }


    private static $tz_db;
    private static $tz_php;


}
