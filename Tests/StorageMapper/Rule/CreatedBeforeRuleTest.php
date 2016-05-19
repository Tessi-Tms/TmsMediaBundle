<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\CreatedBeforeRule;

class CreatedBeforeRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new CreatedBeforeRule(null);
    }

    public function testCheck()
    {
        $this->assertFalse($this->rule->check(array()));
    }
}