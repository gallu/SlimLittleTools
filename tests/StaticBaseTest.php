<?php

use SlimLittleTools\StaticBase;

class StaticBaseTest extends \PHPUnit\Framework\TestCase
{
/*
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass()
    {
    }
    // テストメソッドごとの開始前メソッド
    protected function setUp()
    {
    }
    // -----
    // テストメソッドごとの終了メソッド
    protected function tearDown()
    {
    }
    // 一回だけ実行される終了メソッド
    public static function tearDownAfterClass()
    {
    }
*/
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
