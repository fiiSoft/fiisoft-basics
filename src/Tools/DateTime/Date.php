<?php

namespace FiiSoft\Tools\DateTime;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class Date
{
    /**
     * @param DateTimeInterface|string|integer $date as object, string or timestamp
     * @param string $format (default Y-m-d H:i:s)
     * @throws InvalidArgumentException
     * @return string date as formatted string
     */
    public static function format($date, $format = 'Y-m-d H:i:s')
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format($format);
        }
        
        return self::immutable($date)->format($format);
    }
    
    /**
     * @param DateTimeInterface|string|integer $date as object, string or timestamp
     * @throws InvalidArgumentException
     * @return DateTimeImmutable
     */
    public static function immutable($date)
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }
    
        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }
    
        if ($date instanceof DateTimeInterface) {
            return new DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
        }
    
        if (is_string($date)) {
            return new DateTimeImmutable($date);
        }
    
        if (is_int($date)) {
            return new DateTimeImmutable('@'.$date);
        }
        
        throw new InvalidArgumentException('Invalid param date - cannot be converted to DateTimeImmutable');
    }
    
    /**
     * Tell if given date is in future.
     *
     * @param DateTimeInterface|string|integer $date as object, string or timestamp
     * @throws InvalidArgumentException
     * @return bool
     */
    public static function isInFuture($date)
    {
        return self::immutable($date) > self::immutable('now');
    }
    
    /**
     * Tell if first date is older then second.
     *
     * @param DateTimeInterface|string|integer $first as object, string or timestamp
     * @param DateTimeInterface|string|integer $second as object, string or timestamp
     * @throws InvalidArgumentException
     * @return bool
     */
    public static function isFirstOlderThenSecond($first, $second)
    {
        return self::immutable($first) < self::immutable($second);
    }
}