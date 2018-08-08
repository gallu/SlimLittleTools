<?php

namespace SlimLittleTools\Tests\Libs\Http;

use SlimLittleTools\Libs\Http\Request;
use Slim\Http\Environment;

class RequestTest extends \PHPUnit\Framework\TestCase
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

    public function testGetParam()
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?param=%2F&inv_param=%C0%AF', // inv_param: invalid(非最短形式)
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        //
        $this->assertSame($request->getParam('param'), '/');
        $this->assertSame($request->getQueryParam('param'), '/');
        //
        $this->assertSame($request->getParam('inv_param'), '');
        $this->assertSame($request->getQueryParam('inv_param'), '');

        
        // Create a mock environment for testing with
        $requestUri = '/?param=%2F&inv_param=%C0%AF'; // inv_param: invalid(非最短形式)
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/',
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        $request = $request->withParsedBody(['param' => "\x2F", 'inv_param' => "\xC0\xAF"]);

        //
        $this->assertSame($request->getParam('param'), '/');
        $this->assertSame($request->getParsedBodyParam('param'), '/');
        //
        $this->assertSame($request->getParam('inv_param'), '');
        $this->assertSame($request->getParsedBodyParam('inv_param'), '');
    }

    //
    public function testGetSpecifiedParams()
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?a=1&b=2&c=3&d=4',
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        //
        $data = $request->getSpecifiedParams(['a', 'c']);
        $this->assertSame(isset($data['a']), true);
        $this->assertSame(isset($data['c']), true);
        $this->assertSame(isset($data['b']), false);
        $this->assertSame(isset($data['d']), false);
    }
}
