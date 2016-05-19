<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\MaxSizeRule;

class MaxSizeRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new MaxSizeRule(null);
    }

    public function testCheck()
    {
        $this->assertFalse($this->rule->check(array()));
    }
}