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
     * @param DateTimeInterface|string|integer $date as object, string or timestamp
     * @throws InvalidArgumentException
     * @return DateTime
     */
    public static function mutable($date)
    {
        if ($date instanceof DateTimeInterface) {
            return new DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
        }
    
        if (is_string($date)) {
            return new DateTime($date);
        }
    
        if (is_int($date)) {
            return new DateTime('@'.$date);
        }
    
        throw new InvalidArgumentException('Invalid param date - cannot be converted to DateTime');
    }
    
    /**
     * @param DateTimeInterface|string|integer $date as object, string or timestamp
     * @throws InvalidArgumentException
     * @return DateTimeInterface
     */
    public static function object($date)
    {
        if ($date instanceof DateTimeInterface) {
            return $date;
        }
    
        if (is_string($date)) {
            return new DateTimeImmutable($date);
        }
    
        if (is_int($date)) {
            return new DateTimeImmutable('@'.$date);
        }
    
        throw new InvalidArgumentException('Invalid param date - cannot be converted to DateTimeInterface');
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
        return self::object($date) > self::object('now');
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
        return self::object($first) < self::object($second);
    }
    
    /**
     * @param DateTimeInterface|string|integer $first as object, string or timestamp
     * @param DateTimeInterface|string|integer $second as object, string or timestamp
     * @throws InvalidArgumentException
     * @return bool
     */
    public static function isFirstNotOlderThenSecond($first, $second)
    {
        return self::object($first) >= self::object($second);
    }
}