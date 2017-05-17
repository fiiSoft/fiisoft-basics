<?php

namespace FiiSoft\Test\Tools\Validation;

use FiiSoft\Tools\Validation\SpecificationValidator;

class SpecificationValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $spec = [
        'name' => 'foo',
        'attributes' => [
            '_default_' => [
                'maxLength' => 10,
            ],
            'defaultAttr',
            'enumAttr' => [
                'enum' => ['value1', 'value2'],
            ],
            'nonRequiredAttr' => [
                'required' => false,
            ],
            'longerAttr' => [
                'maxLength' => 35,
            ],
            'integerAttr' => [
                'type' => 'integer',
            ],
        ],
        'children' => [
            'foos' => [
                'required' => false,
                'mayBeChildless' => true,
                'children' => [
                    'bar',
                ]
            ],
            'zoos' => [
                'children' => [
                    'zoo' => [
                        'attributes' => [
                            'requiredAttr',
                            'optionalAttr' => [
                                'required' => false,
                            ],
                            'integerAttr' => [
                                'type' => 'integer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
    
    /** @var SpecificationValidator */
    private $validator;
    
    protected function setUp()
    {
        parent::setUp();
        $this->validator = new SpecificationValidator($this->spec);
    }
    
    public function test_new_validator_has_no_error()
    {
        self::assertNull($this->validator->getLastError());
    }
    
    public function test_it_can_detect_missing_node()
    {
        self::assertFalse($this->validator->isValid([]));
        self::assertSame('Item does not have name equal foo', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_missing_attributes()
    {
        $data = [
            'name' => 'foo',
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Item does not have attributes', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_unspecified_field()
    {
        $data = [
            'name' => 'foo',
            'attrtes' => [],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Item has unspecified fields: attrtes', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_missing_required_attribute()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Item does not have required attribute defaultAttr', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_exceeded_max_length_of_attribute_value()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something too long',
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Attribute defaultAttr length exceeded max 10 and is 18', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_invalid_enum_attribute_value()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'wrong value',
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame(
            'Attribute enumAttr value (wrong value) does not satisfy enum constraint',
            $this->validator->getLastError()
        );
    }
    
    public function test_it_can_detect_invalid_type_of_attribute_value()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 'wrong type',
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame(
            'Attribute integerAttr value (wrong type) is not an integer',
            $this->validator->getLastError()
        );
    }
    
    public function test_it_can_detect_missing_children()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 8,
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame(
            'Item foo cannot be empty but has no children',
            $this->validator->getLastError()
        );
    }
    
    public function test_it_can_detect_empty_children()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 8,
            ],
            'children' => [],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Child zoos is required but not found', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_unexpected_child()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 8,
            ],
            'children' => [
                0 => [
                    'name' => 'wroo',
                ]
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Item has unspecified child named wroo', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_element_without_value()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 8,
            ],
            'children' => [
                0 => [
                    'name' => 'foos',
                    'children' => [
                        0 => [
                            'name' => 'bar',
                        ],
                    ],
                ]
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Item bar is empty but cannot be', $this->validator->getLastError());
    }
    
    public function test_it_can_detect_missing_child()
    {
        $data = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'something',
                'enumAttr' => 'value1',
                'longerAttr' => 'this is longer text',
                'integerAttr' => 8,
            ],
            'children' => [
                0 => [
                    'name' => 'foos',
                    'children' => [
                        0 => [
                            'name' => 'bar',
                            'value' => 'value of bar',
                        ],
                    ],
                ],
            ],
        ];
    
        self::assertFalse($this->validator->isValid($data));
        self::assertSame('Child zoos is required but not found', $this->validator->getLastError());
    }
    
    public function test_validate_valid_data_should_return_true_from_isValid()
    {
        $validData = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'oooo foooo',
                'enumAttr' => 'value2',
                'nonRequiredAttr' => 'optional',
                'longerAttr' => 'long string over default length',
                'integerAttr' => 15,
            ],
            'children' => [
                0 => [
                    'name' => 'zoos',
                    'children' => [
                        0 => [
                            'name' => 'zoo',
                            'attributes' => [
                                'requiredAttr' => 'some value',
                                'integerAttr' => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    
        self::assertTrue($this->validator->isValid($validData), $this->validator->getLastError());
        self::assertNull($this->validator->getLastError());
    }
    
    public function test_validate_full_rich_data_still_valid_should_be_ok()
    {
        $validData = [
            'name' => 'foo',
            'attributes' => [
                'defaultAttr' => 'ala',
                'enumAttr' => 'value1',
                'longerAttr' => 'long string over default length',
                'integerAttr' => 10,
            ],
            'children' => [
                0 => [
                    'name' => 'zoos',
                    'children' => [
                        0 => [
                            'name' => 'zoo',
                            'attributes' => [
                                'requiredAttr' => 'some value',
                                'integerAttr' => 5,
                            ],
                        ],
                        1 => [
                            'name' => 'zoo',
                            'attributes' => [
                                'requiredAttr' => 'other val',
                                'optionalAttr' => 'ho ho ho',
                                'integerAttr' => 3,
                            ],
                        ],
                    ],
                ],
                1 => [
                    'name' => 'foos',
                    'children' => [
                        0 => [
                            'name' => 'bar',
                            'value' => 'value of bar',
                        ],
                    ],
                ],
            ],
        ];
        
        self::assertTrue($this->validator->isValid($validData), $this->validator->getLastError());
        self::assertNull($this->validator->getLastError());
    }
}
