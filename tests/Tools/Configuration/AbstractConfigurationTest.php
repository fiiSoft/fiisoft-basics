<?php

namespace FiiSoft\Test\Tools\Configuration;

class AbstractConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_does_not_assign_non_existing_properties()
    {
        $config = new SampleTestConfiguration(['non_existing_field' => 'aaa']);
    
        self::assertFalse(property_exists($config, 'non_existing_field'));
        self::assertFalse(isset($config->non_existing_field));
    }
    
    public function test_it_can_set_value_to_property()
    {
        $config = new SampleTestConfiguration(['fieldOne' => 'aaa']);
    
        self::assertSame('aaa', $config->fieldOne);
    }
    
    public function test_it_does_not_change_property_not_set()
    {
        $config = new SampleTestConfiguration(['fieldOne' => 'aaa']);
    
        self::assertSame(3, $config->fieldTwo);
    }
    
    public function test_set_configuration_without_null_values_does_not_assign_them()
    {
        $config = new SampleTestConfiguration(['fieldThree' => null]);
    
        self::assertSame('default', $config->fieldThree);
    }
    
    public function test_set_configuration_with_null_values_assigns_them()
    {
        $config = new SampleTestConfiguration(['fieldThree' => null], true);
        
        self::assertNull($config->fieldThree);
    }
    
    public function test_it_allows_convert_to_array_with_null_values_by_default()
    {
        $data = [
            'fieldTwo' => 'bbbb',
            'fieldThree' => 'cccc',
        ];
        
        $config = new SampleTestConfiguration($data);
        $expected = array_merge(['fieldOne' => null], $data);
        
        self::assertSame($expected, $config->toArray());
    }
    
    public function test_it_allows_convert_to_array_without_null_values_too()
    {
        $data = [
            'fieldTwo' => 'bbbb',
            'fieldThree' => 'cccc',
        ];
        
        $config = new SampleTestConfiguration($data);
        $expected = $data;
        
        self::assertSame($expected, $config->toArray(true));
    }
    
    public function test_it_allows_compare_with_other_configuration()
    {
        $data = [
            'fieldTwo' => 'bbbb',
            'fieldThree' => 'cccc',
        ];
        
        $config1 = new SampleTestConfiguration($data);
        
        self::assertTrue($config1->equals($config1));
        self::assertFalse($config1->equals($data));
        
        $data2 = array_merge(['fieldOne' => null], $data);
        self::assertTrue($config1->equals($data2));
        
        $config2 = clone $config1;
        self::assertTrue($config1->equals($config2));
        
        $config2->fieldOne = 'eeee';
        self::assertFalse($config1->equals($config2));
        
        $config1->fieldOne = 1;
        $config2->fieldOne = '1';
        self::assertFalse($config1->equals($config2));
        
        $config2->fieldOne = 1;
        self::assertTrue($config1->equals($config2));
    }
    
    public function test_it_can_be_merged_with_other_config_object_or_array()
    {
        $config1 = new SampleTestConfiguration([
            'fieldTwo' => 'bbbb',
            'fieldThree' => 'cccc',
        ]);
        
        $config2 = new SampleTestConfiguration([
            'fieldOne' => 'aaaa',
            'fieldThree' => null,
        ], true);
        
        $config3 = [
            'fieldOne' => null,
            'fieldTwo' => 'eeee',
            'fieldFour' => 'dddd',
        ];
        
        $config4 = new ArrayAccessImpl('ffff');
        
        
        $config = $config1->mergeCopyWith($config1);
        $expected = [
            'fieldOne' => null,
            'fieldTwo' => 'bbbb',
            'fieldThree' => 'cccc',
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config2);
        $expected = [
            'fieldOne' => 'aaaa',
            'fieldTwo' => 3,
            'fieldThree' => 'cccc',
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config2, false);
        $expected = [
            'fieldOne' => 'aaaa',
            'fieldTwo' => 3,
            'fieldThree' => null,
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config3);
        $expected = [
            'fieldOne' => 'aaaa',
            'fieldTwo' => 'eeee',
            'fieldThree' => null,
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config3, false);
        $expected = [
            'fieldOne' => null,
            'fieldTwo' => 'eeee',
            'fieldThree' => null,
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config4);
        $expected = [
            'fieldOne' => 'ffff',
            'fieldTwo' => 'eeee',
            'fieldThree' => null,
        ];
        self::assertSame($expected, $config->toArray());
        
        
        $config = $config->mergeCopyWith($config4, false);
        $expected = [
            'fieldOne' => 'ffff',
            'fieldTwo' => null,
            'fieldThree' => null,
        ];
        self::assertSame($expected, $config->toArray());
    }
}
