<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Middleware;

use SlimLittleTools\Middleware\AddHeader;
use SlimLittleTools\Libs\Http\Request;
//
use Slim\Psr7\Response;

class AddHeaderTest extends \SlimLittleTools\Tests\TestBase
{
    // テストメソッドごとの開始前メソッド
    protected function setUp() : void
    {
        // 一端スキップ
        $this->markTestSkipped();
    }


    // -----------------------------------------------
    public function testAddHeader()
    {
        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = [
            'settings' => [
                'add_response_header' => [
                    'X-hoge' => 'foo',
                ],
            ],
        ];


        // Register routes
        $app->get('/', function (Request $request, Response $response, array $args) {
            //echo 'test';
        });

        // Process the application
        $response = $app->process($request, $response);

        //
        $headers = $response->getHeaders();
        //
        $this->assertSame(isset($headers['X-Frame-Options']), true);
        $this->assertSame($headers['X-Content-Type-Options'][0], 'nosniff');
        //
        $this->assertSame(isset($headers['X-hoge']), true);
        $this->assertSame($headers['X-hoge'][0], 'foo');
    }
}
