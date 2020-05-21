<?php
declare(strict_types=1);

use SlimLittleTools\WithStaticContainerBase;

class WithStaticContainerBaseTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------

    //
    public function testSetContainer()
    {
        $r = WithStaticContainerBase::setContainer(static::getContainer());
        $this->assertSame($r, null);
    }
}
