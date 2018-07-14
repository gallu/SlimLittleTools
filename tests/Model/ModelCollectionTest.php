<?php

namespace SlimLittleTools\Tests\Model;

use SlimLittleTools\Model\ModelCollection;

class ModelCollectionTest extends \PHPUnit\Framework\TestCase
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

    public function testAll()
    {
        $obj = new ModelCollection();
        $obj[] = new ModelMock();
        $obj[] = new ModelMock();
        $obj[] = new ModelMock();
        $r = $obj->toArray();
        $this->assertSame($r == [[1,2,3],[1,2,3],[1,2,3]], true);
    }
}

class ModelMock
{
    public function toArray()
    {
        return [1,2,3,];
    }
}


