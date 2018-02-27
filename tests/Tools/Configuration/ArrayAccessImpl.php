<?php

namespace FiiSoft\Test\Tools\Configuration;

final class ArrayAccessImpl implements \ArrayAccess
{
    public $fieldOne;
    
    public $fieldTwo;
    
    public function __construct($prop1 = null, $prop2 = null)
    {
        $this->fieldOne = $prop1;
        $this->fieldTwo = $prop2;
    }
    
    public function offsetExists($offset)
    {
        return $offset === 'fieldOne' || $offset === 'fieldTwo';
    }
    
    public function offsetGet($offset)
    {
        if ($offset === 'fieldOne') {
            return $this->fieldOne;
        }
        
        if ($offset === 'fieldTwo') {
            return $this->fieldTwo;
        }
        
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        if ($offset === 'fieldOne') {
            $this->fieldOne = $value;
        }
    
        if ($offset === 'fieldTwo') {
            $this->fieldTwo = $value;
        }
    }
    
    public function offsetUnset($offset)
    {
        if ($offset === 'fieldOne') {
            $this->fieldOne = null;
        }
    
        if ($offset === 'fieldTwo') {
            $this->fieldTwo = null;
        }
    }
}