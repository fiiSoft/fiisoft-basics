<?php

namespace FiiSoft\Test\Tools\DateTime;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use FiiSoft\Tools\DateTime\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_formatted_time_from_date_time_object()
    {
        $format = 'Y-m-d H:i:s';
        $dt = new DateTimeImmutable();
        
        self::assertSame($dt->format($format), Date::format($dt, $format));
    }
    
    public function test_immutable_get_formatted_time_from_string_format()
    {
        $format = 'Y-m-d';
        $dt = new DateTimeImmutable();
        
        self::assertSame($dt->format($format), Date::format('now', $format));
    }
    
    public function test_immutable_can_be_created_from_immutable_datetime()
    {
        $dt = new DateTimeImmutable();
        $actual = Date::immutable($dt);
        
        self::assertSame($dt, $actual);
    }
    
    public function test_immutable_can_be_created_from_datetime()
    {
        $dt = new DateTime();
        $actual = Date::immutable($dt);
        
        self::assertSame($dt->getTimestamp(), $actual->getTimestamp());
        self::assertSame($dt->getOffset(), $actual->getOffset());
        self::assertSame($dt->getTimezone()->getName(), $actual->getTimezone()->getName());
    }
    
    public function test_immutable_can_be_created_from_string()
    {
        $dt = new DateTimeImmutable();
        $actual = Date::immutable('now');
        
        $format = 'Y-m-d H:i:s';
        self::assertSame($dt->format($format), $actual->format($format));
    }
    
    public function test_immutable_can_be_created_from_timestamp()
    {
        $timestamp = 123456789;
        $actual = Date::immutable($timestamp);
        
        self::assertSame($timestamp, $actual->getTimestamp());
    }
    
    public function test_can_tell_if_some_date_is_in_future_or_not()
    {
        $now = new DateTimeImmutable();
        
        $tomorrow = $now->add(new DateInterval('P1D'));
        self::assertTrue(Date::isInFuture($tomorrow));
        
        $yesterday = $now->sub(new DateInterval('P1D'));
        self::assertFalse(Date::isInFuture($yesterday));
    }
    
    public function test_can_tell_if_some_date_is_older_then_other()
    {
        $now = new DateTimeImmutable();
        $tomorrow = $now->add(new DateInterval('P1D'));
        $yesterday = $now->sub(new DateInterval('P1D'));
        
        self::assertTrue(Date::isFirstOlderThenSecond($yesterday, $tomorrow));
        self::assertFalse(Date::isFirstOlderThenSecond($tomorrow, $yesterday));
    }
}
