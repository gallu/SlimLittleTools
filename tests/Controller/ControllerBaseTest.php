<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Controller;

use SlimLittleTools\Controller\ControllerBase;

class ControllerBaseTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------

    //
    public function testCreateInstance()
    {
        //
        $obj = new ControllerBase(static::getContainer());
        $this->assertSame(is_object($obj), true);
    }
}
