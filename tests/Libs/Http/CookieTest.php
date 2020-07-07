<?php

namespace SlimLittleTools\Tests\Libs\Http;

use SlimLittleTools\Libs\Http\Cookies;

class CookieTest extends \PHPUnit\Framework\TestCase
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

    public function testSecurity()
    {
        $cookies = new Cookies(Cookies::parseHeader('param=%2F;inv_param=%C0%AF'));

        $this->assertSame($cookies->get('param'), '/');
        $this->assertSame($cookies->get('inv_param'), '');
    }

    public function testSet()
    {
        $cookies = new Cookies();
        $cookies->set('aaa', 100);
        $cookies->set('bba', 200, ['hostonly' => true, 'secure' => true]);
        $cookies->delete('ddd');
        $cookies->set('eee', 'test');
        //
        $headers = $cookies->toHeaders();
        $this->assertSame(in_array('aaa=100', $headers), true);
        $this->assertSame(in_array('eee=test', $headers), true);
        $this->assertSame(in_array('bba=200; secure; HostOnly', $headers), true);
        $this->assertSame(in_array('ddd=; expires=Thu, 01-Jan-1970 00:00:01 UTC; HttpOnly', $headers), true);
    }
}
