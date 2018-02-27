<?php

namespace FiiSoft\Tools\Configuration;

abstract class AbstractConfiguration
{
    /**
     * @param array $settings
     * @param bool $allowNulls
     */
    public function __construct(array $settings = [], $allowNulls = false)
    {
        foreach ($settings as $key => $value) {
            if (($allowNulls || $value !== null) && property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * @param bool $notNull (default false) if true then filter out null values from array
     * @return array
     */
    final public function toArray($notNull = false)
    {
        if ($notNull) {
            return array_filter(get_object_vars($this), function ($value) {
                return $value !== null;
            });
        }
        
        return get_object_vars($this);
    }
    
    /**
     * Tell if other configuration is the same, what is true only if other is configuration object
     * of the same class or an array and contains (all and only) identical attributes.
     *
     * @param AbstractConfiguration|array $other
     * @return bool
     */
    final public function equals($other)
    {
        if ($other instanceof $this) {
            if ($other === $this) {
                return true;
            }
            $other = $other->toArray();
        } elseif (!is_array($other)) {
            return false;
        }
        
        $me = $this->toArray();
    
        if (count($me) !== count($other)) {
            return false;
        }
    
        foreach ($me as $key => $value) {
            if (!array_key_exists($key, $other) || $value !== $other[$key]) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create copy of this configuration with merged other data.
     *
     * @param AbstractConfiguration|\Traversable|\ArrayAccess|array $other
     * @param bool $notNull
     * @throws \InvalidArgumentException if cannot merge $other to $this
     * @return AbstractConfiguration copy of $this configuration with merged $other data
     */
    public function mergeCopyWith($other, $notNull = true)
    {
        $copy = clone $this;
    
        if ($other === $this) {
            return $copy;
        }
        
        if ($other instanceof self) {
            foreach ($other->toArray($notNull) as $key => $value) {
                if (property_exists($copy, $key)) {
                    $copy->$key = $value;
                }
            }
        } elseif (is_array($other) || $other instanceof \Traversable) {
            foreach ($other as $key => $value) {
                if (($notNull === false || $value !== null) && property_exists($copy, $key)) {
                    $copy->$key = $value;
                }
            }
        } else if ($other instanceof \ArrayAccess) {
            foreach (array_keys(get_object_vars($copy)) as $key) {
                if (isset($other[$key])) {
                    $value = $other[$key];
                    if ($notNull === false || $value !== null) {
                        $copy->$key = $value;
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException('Invalid type of param other');
        }
        
        return $copy;
    }
}