<?php
declare(strict_types=1);

use SlimLittleTools\WithContainerBase;

class WithContainerBaseTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------

    //
    public function testCreateInstance()
    {
        $obj = new WithContainerBase(static::getContainer());
        $this->assertSame(is_object($obj), true);
    }
}
