<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\MimeTypesRule;

class MimeTypesRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new MimeTypesRule(null);
    }

    public function testCheck()
    {
        $this->assertFalse($this->rule->check(array()));
    }
}