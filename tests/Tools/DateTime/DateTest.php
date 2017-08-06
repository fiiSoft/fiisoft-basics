<?php

namespace FiiSoft\Test\Tools\DateTime;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
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
    
        $this->assertDateTimeInterfaceObjectsAreTheSame($dt, $actual);
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
    
    public function test_mutable_can_be_created_from_other_mutable()
    {
        $now = new DateTime();
        $actual = Date::mutable($now);
        
        self::assertNotSame($now, $actual);
        self::assertInstanceOf(DateTime::class, $actual);
        
        $this->assertDateTimeInterfaceObjectsAreTheSame($now, $actual);
    }
    
    public function test_mutable_can_be_created_from_immutable()
    {
        $now = new DateTimeImmutable();
        $actual = Date::mutable($now);
    
        self::assertInstanceOf(DateTime::class, $actual);
        $this->assertDateTimeInterfaceObjectsAreTheSame($now, $actual);
    }
    
    public function test_mutable_can_be_created_from_string()
    {
        $date = '2017-02-15 17:38:46';
        $actual = Date::mutable($date);
    
        self::assertInstanceOf(DateTime::class, $actual);
        self::assertSame($date, $actual->format('Y-m-d H:i:s'));
    }
    
    public function test_mutable_can_be_created_from_timestamp()
    {
        $timestamp = time();
        $date = Date::mutable($timestamp);
     
        self::assertSame($timestamp, $date->getTimestamp());
    }
    
    public function test_it_fetch_any_DateTimeInterface_object()
    {
        $mutable = Date::mutable('now');
        $immutable = Date::immutable('now');
        
        self::assertSame($mutable, Date::object($mutable));
        self::assertSame($immutable, Date::object($immutable));
    
        $date = $mutable->format('Y-m-d');
        self::assertSame($date, Date::object($date)->format('Y-m-d'));
    
        $timestamp = $mutable->getTimestamp();
        self::assertSame($timestamp, Date::object($timestamp)->getTimestamp());
    }
    
    public static function test_can_tell_if_some_date_is_not_older_then_other()
    {
        $now = new DateTimeImmutable();
        $tomorrow = $now->add(new DateInterval('P1D'));
        $yesterday = $now->sub(new DateInterval('P1D'));
    
        self::assertTrue(Date::isFirstNotOlderThenSecond($tomorrow, $yesterday));
        self::assertTrue(Date::isFirstNotOlderThenSecond($tomorrow, $now));
        self::assertTrue(Date::isFirstNotOlderThenSecond($now, $yesterday));
        self::assertTrue(Date::isFirstNotOlderThenSecond($now, $now));
    }
    
    public function test_two_dates_are_equal_in_given_scope()
    {
        $d1 = Date::object('2017-08-06 15:43:07');
        $d2 = Date::object('2017-08-07 13:43:09');
        
        self::assertFalse(Date::areEqual($d1, $d2));
        self::assertFalse(Date::areEqual($d1, $d2, 'Ymd'));
        
        self::assertTrue(Date::areEqual($d1, $d2, 'i'));
        self::assertTrue(Date::areEqual($d1, $d2, 'Ym'));
    }
    
    public function test_get_current_time_as_string()
    {
        $now = Date::time();
        self::assertSame(19, strlen($now));
        self::assertTrue(Date::areEqual(new DateTimeImmutable(), $now, 'YmdHi'));
    }
    
    public function test_get_current_date_as_string()
    {
        $today = Date::date();
        self::assertSame(10, strlen($today));
        self::assertTrue(Date::areEqual(new DateTimeImmutable(), $today, 'Ymd'));
    }
    
    /**
     * @param DateTimeInterface $expected
     * @param DateTimeInterface $actual
     */
    private function assertDateTimeInterfaceObjectsAreTheSame(DateTimeInterface $expected, DateTimeInterface $actual)
    {
        self::assertSame($actual->getTimestamp(), $actual->getTimestamp());
        self::assertSame($actual->getOffset(), $actual->getOffset());
        self::assertSame($actual->getTimezone()->getName(), $actual->getTimezone()->getName());
    }
}
