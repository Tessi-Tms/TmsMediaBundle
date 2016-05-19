<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\DefaultRule;

class DefaultRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new DefaultRule(null);
    }

    public function testCheck()
    {
        $this->assertTrue($this->rule->check(array()));
    }
}