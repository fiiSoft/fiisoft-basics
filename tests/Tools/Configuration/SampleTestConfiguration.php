<?php

namespace FiiSoft\Test\Tools\Configuration;

use FiiSoft\Tools\Configuration\AbstractConfiguration;

class SampleTestConfiguration extends AbstractConfiguration
{
    public $fieldOne;
    
    public $fieldTwo = 3;
    
    public $fieldThree = 'default';
}