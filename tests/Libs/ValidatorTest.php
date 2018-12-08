<?php

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
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

    public function testMono()
    {
        //
        // valid
        $data = [
            't_required' => 'aaa',
            't_datetime' => '2018-07-01T20:18:57+09:00',
            't_alpha' => 'abcdIJDK',
            't_alpha_num' => '10POLz',
            't_min_length' => '12345',
            't_max_length' => '12345',
            't_range_length' => '12345',
            't_min_m_length' => 'あ2345',
            't_max_m_length' => 'あ2345',
            't_range_m_length' => 'あいうえお',
            't_min_number' => 100,
            't_max_number' => 100,
            't_range_number' => 100,
            't_compare_with' => 'pass',
            't_compare_with_check' => 'pass',
            't_int' => '12345',
            't_float' => '12.345',
            't_zip_1' => '123-4567',
            't_zip_2' => '123 4567',
            't_zip_3' => '1234567',
            't_tel_1' => '03-1234-5678',
            't_tel_2' => '03 1234 5678',
            't_tel_3' => '0312345678',
            't_tel_4' => '090-1234-5678',
            't_tel_5' => '090 1234 5678',
            't_tel_6' => '09012345678',
            't_tel_7' => '098-765-5678',
            't_tel_8' => '0987-65-5678',
            't_tel_9' => '0120-123-456',
        ];
        $rules = [
            't_required' => 'required',
            't_datetime' => 'datetime',
            't_alpha' => 'alpha',
            't_alpha_num' => 'alpha_num',
            't_min_length' => 'min_length:5',
            't_max_length' => 'max_length:5',
            't_range_length' => 'range_length:4-6',
            't_min_m_length' => 'min_m_length:5',
            't_max_m_length' => 'max_m_length:5',
            't_range_m_length' => 'range_m_length:4-6',
            't_min_number' => 'min_number:99',
            't_max_number' => 'max_number:101',
            't_range_number' => 'range_number:99-101',
            't_compare_with' => 'compare_with',
            't_compare_with_check' => 'required',
            't_int' => 'int',
            't_float' => 'float',
            't_zip_1' => 'zip',
            't_zip_2' => 'zip',
            't_zip_3' => 'zip',
            't_tel_1' => 'tel',
            't_tel_2' => 'tel',
            't_tel_3' => 'tel',
            't_tel_4' => 'tel',
            't_tel_5' => 'tel',
            't_tel_6' => 'tel',
            't_tel_7' => 'tel',
            't_tel_8' => 'tel',
            't_tel_9' => 'tel',
        ];
        //
        $res = Validator::validate($data, $rules);
        $this->assertSame($res->isValid(), true);
        //
        $this->assertSame(in_array('t_compare_with_check', $res->getCheckedColmun()), true);

        // ------------------------
        // invalid
        //
        $res = Validator::validate(['hoge' => 'aaa'], ['t_required' => 'required']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_required' => ''], ['t_required' => 'required']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_datetime' => 'abcdefg'], ['t_datetime' => 'datetime']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_alpha' => 'k;09'], ['t_alpha' => 'alpha']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_min_length' => '1234'], ['t_min_length' => 'min_length:5']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_max_length' => '123456'], ['t_max_length' => 'max_length:5']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_range_length' => '123'], ['t_range_length' => 'range_length:4-6']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_range_length' => '1234567'], ['t_range_length' => 'range_length:4-6']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_min_m_length' => 'あいうえ'], ['t_min_m_length' => 'min_m_length:5']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_max_m_length' => 'あ23456'], ['t_max_m_length' => 'max_m_length:5']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_range_m_length' => 'あ23'], ['t_range_m_length' => 'range_m_length:4-6']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_range_m_length' => 'あ234567'], ['t_range_m_length' => 'range_m_length:4-6']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_min_number' => 98], ['t_min_number' => 'min_number:99']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_max_number' => 102], ['t_max_number' => 'max_number:101']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_range_number' => 98], ['t_range_number' => 'range_number:99-101']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_range_number' => 102], ['t_range_number' => 'range_number:99-101']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_compare_with' => 'pass', 't_compare_with_check' => 'pass2'], ['t_compare_with' => 'compare_with']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_compare_with' => 'pass'], ['t_compare_with' => 'compare_with']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_int' => '12.3'], ['t_int' => 'int']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_float' => 'abc'], ['t_float' => 'float']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_zip' => 'abc'], ['t_zip' => 'zip']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_zip' => '123456789'], ['t_zip' => 'zip']);
        $this->assertSame($res->isValid(), false);
        //
        $res = Validator::validate(['t_tel' => 'abc'], ['t_tel' => 'tel']);
        $this->assertSame($res->isValid(), false);
        $res = Validator::validate(['t_tel' => '123456'], ['t_tel' => 'tel']);
        $this->assertSame($res->isValid(), false);

    }

    public function testMultiError()
    {
        //
        $rules = [
            'error' => 'int | range_length:50-60 | datetime | alpha | ',
            'error2' => 'required',
            'valid' => 'required|int|range_m_length:3-6',
        ];
        $data = [
            'error' => 'Jz;019',
            'valid' => '12345',
        ];
        //
        $res = Validator::validate($data, $rules);
        $this->assertSame($res->isValid(), false);
        $error = $res->getError();
        //
        $this->assertSame(isset($error['valid']), false);
        $this->assertSame(isset($error['error']), true);
        $this->assertSame(isset($error['error2']), true);
        //
        $this->assertSame(in_array('int', $error['error']), true);
        $this->assertSame(in_array('range_length', $error['error']), true);
        $this->assertSame(in_array('datetime', $error['error']), true);
        $this->assertSame(in_array('alpha', $error['error']), true);
        //
        $this->assertSame(in_array('required', $error['error2']), true);
    }

    public function testErrorAdd()
    {
        // 正常なのをfalseにする
        $res = Validator::validate([], []);
        $this->assertSame($res->isValid(), true);
        $res->setResultFalse();
        $this->assertSame($res->isValid(), false);

        // errorオブジェクトに追加を入れる
        $res = Validator::validate([], ['test' => 'required']);
        $error = $res->getError();
        $this->assertSame($error, ['test' => ['required']]);
        $res->addError(['test' => ['hoge'], 'test2' => ['foo']]);
        $error = $res->getError();
        $this->assertSame($error, ['test' => ['required', 'hoge'], 'test2' => ['foo']]);
    }
}
