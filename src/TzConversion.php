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


use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use LogicException;


abstract class TzConversion
{

    /**
     * Returns the timezone configured for the database.
     *
     * @return DateTimeZone
     */
    public static function timezoneDB(): DateTimeZone
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
                    throw new LogicException('Environment variable "DATABASE_TIMEZONE" was expected to contain a string, got array.');
                }
            }
            if ($name) {
                static::$tz_db = new DateTimeZone($name);
            } else {
                static::$tz_db = static::timezonePHP();
            }
        }
        return static::$tz_db;
    }


    /**
     * Returns the timezone configured for PHP.
     *
     * @return DateTimeZone
     */
    public static function timezonePHP(): DateTimeZone
    {
        if (!static::$tz_php) {
            static::$tz_php = new DateTimeZone(date_default_timezone_get());
        }
        return static::$tz_php;
    }


    /**
     * Converts a date time object to the database timezone.
     *
     * The new date time object will still point to the same
     * point in time, it will just have a different timezone
     * attached.
     *
     * When you string format the date however, the converted
     * date will have a different representation, because the
     * point in time looks different from a different
     * timezone.
     *
     * @param DateTimeInterface $dateTime
     * @return DateTimeInterface
     */
    public static function convertToDB(DateTimeInterface $dateTime): DateTimeInterface
    {
        if ($dateTime instanceof DateTimeImmutable) {
            return $dateTime->setTimezone(static::timezoneDB());
        }

        if ($dateTime instanceof DateTime) {
            $asUtc = clone $dateTime;
            $asUtc->setTimezone(static::timezoneDB());
            return $asUtc;
        }

        throw new LogicException('Unknown DateTimeInterface implementation: ' . get_class($dateTime));
    }


    private static $tz_db;
    private static $tz_php;


}
