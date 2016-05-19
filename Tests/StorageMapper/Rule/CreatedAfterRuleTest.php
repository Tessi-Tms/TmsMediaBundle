<?php

namespace Tms\Bundle\MediaBundle\Tests\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\CreatedAfterRule;

class CreatedAfterRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rule = new CreatedAfterRule(null);
    }

    public function testCheck()
    {
        $this->assertFalse($this->rule->check(array()));
    }
}