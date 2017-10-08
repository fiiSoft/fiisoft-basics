<?php

namespace FiiSoft\Tools\Other;

use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

trait StaticConstDict
{
    /** @var array */
    private static $constants = [];
    
    /**
     * @throws RuntimeException
     * @return array
     */
    public static function toArray()
    {
        self::init();
        
        return self::$constants;
    }
    
    /**
     * @param string|int $needle
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return bool
     */
    public static function isValid($needle)
    {
        if (!is_string($needle) && !is_int($needle)) {
            throw new InvalidArgumentException('Invalid param needle');
        }
        
        self::init();
        
        return isset(self::$constants[$needle]) || in_array($needle, self::$constants, true);
    }
    
    /**
     * @param string|int $needle
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws LogicException
     * @return mixed
     */
    public static function get($needle)
    {
        if (!is_string($needle) && !is_int($needle)) {
            throw new InvalidArgumentException('Invalid param needle');
        }
        
        self::init();
        
        if (isset(self::$constants[$needle])) {
            return self::$constants[$needle];
        }
        
        $pos = array_search($needle, self::$constants, true);
        if ($pos !== false) {
            return self::$constants[$pos];
        }
        
        throw new LogicException('There is no constant that matches to "'.$needle.'" in '.static::class);
    }
    
    /**
     * @throws RuntimeException
     * @return void
     */
    private static function init()
    {
        if (empty(self::$constants)) {
            try {
                $refl = new ReflectionClass(__CLASS__);
                self::$constants = $refl->getConstants();
            } catch (ReflectionException $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}