<?php

use SlimLittleTools\WithContainerBase;
use Slim\Collection;

class WithContainerBaseTest extends \PHPUnit\Framework\TestCase
{
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
    // -----------------------------------------------

    //
    public function testCreateInstance()
    {
        $obj = new WithContainerBase(new Collection());
        $this->assertSame(is_object($obj), true);
    }
}
