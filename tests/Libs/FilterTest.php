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
            't_katakana' => 'ああアアｱｱ',
            't_hirakana' => 'ああアアｱｱ',
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
            't_katakana' => 'katakana',
            't_hirakana' => 'hirakana',
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
        $this->assertSame($data['t_katakana'], 'アアアアアア');
        $this->assertSame($data['t_hirakana'], 'ああああああ');

        // zip
        $base_data = [
            'zip_1_1' => '123-4567',
            'zip_1_2' => '123-4567',
            'zip_1_3' => '123-4567',
            'zip_2_1' => '123 4567',
            'zip_2_2' => '123 4567',
            'zip_2_3' => '123 4567',
            'zip_3_1' => '1234567',
            'zip_3_2' => '1234567',
            'zip_3_3' => '1234567',
            'zip_error_1' => '123',
            'zip_error_2' => '123456789',
            'zip_error_3' => '',
        ];
        $rules = [
            'zip_1_1' => 'zip_hyphen',
            'zip_1_2' => 'zip_space',
            'zip_1_3' => 'zip_shorten',
            'zip_2_1' => 'zip_hyphen',
            'zip_2_2' => 'zip_space',
            'zip_2_3' => 'zip_shorten',
            'zip_3_1' => 'zip_hyphen',
            'zip_3_2' => 'zip_space',
            'zip_3_3' => 'zip_shorten',
            'zip_error_1' => 'zip_hyphen',
            'zip_error_2' => 'zip_space',
            'zip_error_3' => 'zip_space',
        ];
        //
        $data = Filter::exec($base_data, $rules);
        foreach(['zip_1_1', 'zip_2_1', 'zip_3_1'] as $s) {
            $this->assertSame($data[$s], '123-4567');
        }
        foreach(['zip_1_2', 'zip_2_2', 'zip_3_2'] as $s) {
            $this->assertSame($data[$s], '123 4567');
        }
        foreach(['zip_1_3', 'zip_2_3', 'zip_3_3'] as $s) {
            $this->assertSame($data[$s], '1234567');
        }
        $this->assertSame($data['zip_error_1'], '123');
        $this->assertSame($data['zip_error_2'], '123456789');
        $this->assertSame($data['zip_error_3'], '');

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
