<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\MinSizeRule;

class MinSizeRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new MinSizeRule(null);
    }

    public function testCheck()
    {
        $this->assertFalse($this->rule->check(array()));
    }
}