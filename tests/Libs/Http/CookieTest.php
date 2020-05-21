<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Libs\Http;

use SlimLittleTools\Libs\Http\Cookies;

class CookieTest extends \PHPUnit\Framework\TestCase
{
    // -----------------------------------------------

    public function testSecurity()
    {
        $cookies = new Cookies(Cookies::parseHeader('param=%2F;inv_param=%C0%AF'));

        $this->assertSame($cookies->get('param'), '/');
        $this->assertSame($cookies->get('inv_param'), '');
    }

    public function testSet()
    {
        // 
        $cookies = new Cookies();
        $cookies->set('bbb', ['value' => '200', 'hostonly' => true, 'secure' => true]);
        $cookies->delete('ddd');
        $cookies->set('eee', 'test');
        $cookies->set('fff', ['value' => 'test', 'samesite' => 'Strict']);
        $headers = $cookies->toHeaders();
        $this->assertSame(in_array('bbb=200; secure; HostOnly', $headers), true);
        $this->assertSame(in_array('eee=test', $headers), true);
        $this->assertSame(in_array('ddd=; expires=Thu, 01-Jan-1970 00:00:01 UTC; HttpOnly', $headers), true);
        $this->assertSame(in_array('fff=test; SameSite=Strict', $headers), true);

        //
        $flg = false;
        try {
            $cookies = new Cookies();
            $cookies->set('aaa', 100);
            $headers = $cookies->toHeaders();
        } catch (\TypeError $e) {
            $flg = true;
        }
        $this->assertTrue($flg);
    }
}
