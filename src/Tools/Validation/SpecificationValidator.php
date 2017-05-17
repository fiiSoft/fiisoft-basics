<?php

namespace FiiSoft\Tools\Validation;

use DateTimeImmutable;
use LogicException;
use UnexpectedValueException;

final class SpecificationValidator
{
    /** @var array  */
    private $defaultAttrSpec = ['required' => true, 'type' => 'string'];
    
    /** @var array  */
    private $defaultChildSpec = ['required' => true, 'mayBeChildless' => false];
    
    /** @var string description of last error */
    private $lastError;
    
    /** @var array */
    private $specification;
    
    /** @var array specification normalized */
    private $normalized = [];
    
    /**
     * @param array $specification
     * @param array|null $defaultAttrSpec
     * @param array|null $defaultChildSpec
     */
    public function __construct(array $specification, array $defaultAttrSpec = null, array $defaultChildSpec = null)
    {
        if (isset($specification['attributes']['_default_'])) {
            $this->defaultAttrSpec = array_merge($this->defaultAttrSpec, $specification['attributes']['_default_']);
            unset($specification['attributes']['_default_']);
        }
        
        if ($defaultAttrSpec !== null) {
            $this->defaultAttrSpec = array_merge($this->defaultAttrSpec, $defaultAttrSpec);
        }
    
        if ($defaultChildSpec !== null) {
            $this->defaultChildSpec = array_merge($this->defaultChildSpec, $defaultChildSpec);
        }
    
        $this->specification = $specification;
    }
    
    /**
     * @param array $item
     * @throws LogicException
     * @throws UnexpectedValueException
     * @return bool
     */
    public function isValid(array $item)
    {
        if (empty($this->normalized)) {
            $this->normalized = $this->normalizeSpecification($this->specification);
        }
        
        $this->lastError = null;
        
        return $this->validate($item, $this->normalized);
    }
    
    /**
     * @param array $item
     * @param array $specification
     * @throws UnexpectedValueException
     * @return bool
     */
    private function validate(array $item, array $specification)
    {
        $diff = array_diff(array_keys($item), array_keys($specification), ['name', 'value']);
        if (!empty($diff)) {
            $this->lastError = 'Item has unspecified fields: '.implode(',', $diff);
            return false;
        }
        
        foreach ($specification as $key => $spec) {
            if ($key === 'name') {
                if (!isset($item[$key]) || $item[$key] !== $spec) {
                    $this->lastError = 'Item does not have name equal '.$spec;
                    return false;
                }
                
            } elseif ($key === 'attributes') {
                if (!isset($item[$key]) || !is_array($item[$key])) {
                    $this->lastError = 'Item does not have attributes';
                    return false;
                }
                
                $diff = array_diff(array_keys($item[$key]), array_keys($spec));
                if (!empty($diff)) {
                    $this->lastError = 'Item has unspecified attributes: '.implode(',', $diff);
                    return false;
                }
                
                foreach ($spec as $attr => $attrSpec) {
                    if (!isset($item[$key][$attr])) {
                        if ($attrSpec['required']) {
                            $this->lastError = 'Item does not have required attribute '.$attr;
                            return false;
                        }
                        continue;
                    }
                    
                    $attrValue = $item[$key][$attr];
    
                    if (!empty($attrSpec['enum'])) {
                        foreach ($attrSpec['enum'] as $value) {
                            if ($attrValue === $value) {
                                continue 2; //continue outer loop
                            }
                        }
                        $this->lastError = 'Attribute '.$attr.' value ('. $attrValue
                                            .') does not satisfy enum constraint';
                        return false;
                    }
                    
                    if ($attrSpec['type'] === 'string'
                        && !empty($attrSpec['maxLength'])
                        && $this->strLength($attrValue) > $attrSpec['maxLength']
                    ) {
                        $this->lastError = 'Attribute '.$attr.' length exceeded max '.$attrSpec['maxLength']
                                            .' and is '.$this->strLength($attrValue);
                        return false;
                    }
                    
                    if ($attrSpec['type'] === 'date'
                        && !empty($attrSpec['format'])
                        && !DateTimeImmutable::createFromFormat($attrSpec['format'], $attrValue)
                    ) {
                        $this->lastError = 'Attribute '.$attr.' value ('.$attrValue.') is not valid date in format '
                                            .$attrSpec['format'];
                        return false;
                    }
    
                    if ($attrSpec['type'] === 'integer'
                        && !(
                            is_int($attrValue)
                            || (is_string($attrValue) && ctype_digit($attrValue))
                        )
                    ) {
                        $this->lastError = 'Attribute '.$attr.' value ('.$attrValue.') is not an integer';
                        return false;
                    }
                }
                
            } elseif ($key === 'children') {
                if (!isset($item[$key])) {
                    if ($specification['mayBeChildless']) {
                        continue;
                    }
                    
                    $this->lastError = 'Item '.$item['name'].' cannot be empty but has no children';
                    return false;
                }
                
                if (!is_array($item[$key])) {
                    $this->lastError = 'Item has children but it is not an array';
                    return false;
                }
                
                $foundItemChildren = [];
                
                foreach ($item[$key] as $child) {
                    $name = $child['name'];
                    if (!isset($spec[$name])) {
                        $this->lastError = 'Item has unspecified child named '.$name;
                        return false;
                    }
                    
                    if (!$this->validate($child, $spec[$name])) {
                        return false;
                    }
                    
                    $foundItemChildren[$name] = isset($child['children']) ? $child['children'] : [];
                }
    
                foreach ($spec as $childName => $childSpec) {
                    if ($childSpec['required'] && !isset($foundItemChildren[$childName])) {
                        $this->lastError = 'Child '.$childName.' is required but not found';
                        return false;
                    }
                    
                    if (!$childSpec['mayBeChildless']
                        && empty($foundItemChildren[$childName])
                        && isset($childSpec['children'])
                    ) {
                        $this->lastError = 'Child '.$childName.' cannot be childless but has no children';
                        return false;
                    }
                }
                
            } elseif ($key === 'mayBeChildless') {
                if ($spec) {
                    continue;
                }
                
                if (empty($item['children']) && isset($specification['children'])) {
                    $this->lastError = 'Item '.$item['name'].' has no children but cannot be childless';
                    return false;
                }
                
                if (!isset($item['value']) && empty($item['attributes']) && empty($item['children'])
                ) {
                    $this->lastError = 'Item '.$item['name'].' is empty but cannot be';
                    return false;
                }
                
            } elseif ($key === 'required') {
                continue;
                
            } else {
                throw new UnexpectedValueException('Unsupported check for key '.$key.' in specification');
            }
        }
        
        return true;
    }
    
    /**
     * Convert simplified specification to full form used in validation.
     *
     * @param array $input specification
     * @throws LogicException
     * @return array normalized specification
     */
    private function normalizeSpecification(array $input)
    {
        $output = [];
        $mayBeChildless = null;
        
        foreach ($input as $key => $spec) {
            if ($key === 'attributes') {
                if (!is_array($spec)) {
                    throw new LogicException('Value under key '.$key.' have to be an array');
                }
                $output[$key] = [];
                foreach ($spec as $attrName => $attrSpec) {
                    if (is_string($attrName)) {
                        if (!is_array($attrSpec)) {
                            throw new LogicException('Attribute specification have to be an array');
                        }
                        
                        if (!empty($attrSpec['maxLength'])
                            && (!is_int($attrSpec['maxLength']) || $attrSpec['maxLength'] < 1)
                        ) {
                            throw new LogicException('Attribute constraint "maxLength" has to be an integer >= 1');
                        }
                        
                        if (!empty($attrSpec['enum']) && !is_array($attrSpec['enum'])) {
                            throw new LogicException('Attribute constraint "enum" has to be an array');
                        }
    
                        $output[$key][$attrName] = array_merge($this->defaultAttrSpec, $attrSpec);
                    } else {
                        $output[$key][$attrSpec] = $this->defaultAttrSpec;
                    }
                }
                
            } elseif ($key === 'children') {
                $output[$key] = [];
                foreach ($input[$key] as $childKey => $child) {
                    if (is_string($childKey)) {
                        if (!is_array($child)) {
                            throw new LogicException('Invalid child specification for key '.$childKey);
                        }
                        $output[$key][$childKey] = array_merge(
                            $this->defaultChildSpec,
                            $this->normalizeSpecification($child)
                        );
                        if ($mayBeChildless !== false) {
                            $mayBeChildless = $output[$key][$childKey]['mayBeChildless'];
                        }
                    } else {
                        $output[$key][$child] = $this->defaultChildSpec;
                        if ($mayBeChildless !== false) {
                            $mayBeChildless = $output[$key][$child]['mayBeChildless'];
                        }
                    }
                }
    
            } else {
                $output[$key] = $spec;
            }
        }
    
        if (!isset($output['mayBeChildless'])) {
            if ($mayBeChildless !== null) {
                $output['mayBeChildless'] = $mayBeChildless;
            } elseif (isset($this->defaultChildSpec['mayBeChildless'])) {
                $output['mayBeChildless'] = $this->defaultChildSpec['mayBeChildless'];
            }
        }
        
        return $output;
    }
    
    /**
     * @param string $str
     * @return int
     */
    private function strLength($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str);
        }
        
        return strlen($str);
    }
    
    /**
     * @return string|null
     */
    public function getLastError()
    {
        return $this->lastError;
    }
}