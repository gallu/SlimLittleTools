<?php
declare(strict_types=1);

use SlimLittleTools\StaticBase;

class StaticBaseTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------

    /**
     * expectedException ErrorException
     */
    public function testGuard()
    {
        // XXX phpunit 9 になって、 @expectedException がきかなくなった？？？
        //$obj = new StaticBase();
        try {
            $obj = new \SlimLittleTools\StaticBase();
        } catch (\ErrorException $e) {
            $this->assertTrue(true);
            return ;
        }
        // else
        $this->assertTrue(false);
    }

    /**
     * expectedException ErrorException
     */
    public function testGuard2()
    {
        // XXX phpunit 9 になって、 @expectedException がきかなくなった？？？
        //$obj = unserialize('O:26:"SlimLittleTools\StaticBase":0:{}');
        try {
            $obj = unserialize('O:26:"SlimLittleTools\StaticBase":0:{}');
        } catch (\ErrorException $e) {
            $this->assertTrue(true);
            return ;
        }
        // else
        $this->assertTrue(false);

    }
}
