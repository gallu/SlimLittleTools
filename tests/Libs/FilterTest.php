<?php

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\Filter;

class FilterTest extends \PHPUnit\Framework\TestCase
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

    public function testFilter()
    {
        //
        $base_data = [
            't_trim' => ' te st ',
            't_strtolower' => 'AbC',
            't_strtoupper' => 'AbC',
            't_floor' => '1.23',
            't_ceil' => '1.23',
            't_abs' => '-1',
            't_int' => '100',
            't_string' => 200,
        ];
        $rules = [
            't_trim' => 'trim',
            't_strtolower' => 'strtolower',
            't_strtoupper' => 'strtoupper',
            't_floor' => 'floor',
            't_ceil' => 'ceil',
            't_abs' => 'abs',
            't_int' => 'int',
            't_string' => 'string',
        ];
        $data = Filter::exec($base_data, $rules);

        //
        $this->assertSame($data['t_trim'], 'te st');
        $this->assertSame($data['t_strtolower'], 'abc');
        $this->assertSame($data['t_strtoupper'], 'ABC');
        $this->assertSame($data['t_floor'], 1.0);
        $this->assertSame($data['t_ceil'], 2.0);
        $this->assertSame($data['t_abs'], 1);
        $this->assertSame($data['t_int'], 100);
        $this->assertSame($data['t_string'], '200');
    }

    //
    public function testFilterMulti()
    {
        $data = Filter::exec(['test' => "\t A b C  \n"], ['test' => 'trim|strtolower']);
        $this->assertSame($data['test'], 'a b c');
    }

    /**
     * @expectedException ErrorException
     */
    public function testNoRule()
    {
        $data = Filter::exec(['test' => '1'], ['test' => 'int|string|hoge']);
    }

}
