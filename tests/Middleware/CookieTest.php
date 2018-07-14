<?php

namespace SlimLittleTools\Tests\Middleware;

use SlimLittleTools\Middleware\Cookie;
use SlimLittleTools\Libs\Http\Request;
//
use Slim\Http\Environment;
use Slim\Http\Response;

class CookieTest extends \PHPUnit\Framework\TestCase
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

    public function testAddHeader()
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = [
            'settings' => [
                'cookie' => [
                    'httponly' => true,
                    //'secure' => true,
                ],
            ],
        ];

        // Instantiate the application
        $app = new \Slim\App($settings);

        // Set up dependencies

        // Register middleware
        $app->add(new Cookie($app->getContainer()));

        // Register routes
        $app->get('/',  function (Request $request, Response $response, array $args) {
            $this->get('cookie')->set('test', '123');
            $this->get('cookie')->set('test2', '987');
        });

        // Process the application
        $response = $app->process($request, $response);
        $headers = $response->getHeaders();

        //
        $this->assertSame(isset($headers['Set-Cookie']), true);
        $this->assertSame($headers['Set-Cookie'][0], 'test=123; HttpOnly');
        $this->assertSame($headers['Set-Cookie'][1], 'test2=987; HttpOnly');



    }
}
