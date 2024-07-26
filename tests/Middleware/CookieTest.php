<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Middleware;

use SlimLittleTools\Middleware\Cookie;
use SlimLittleTools\Libs\Http\Request;

class CookieTest extends \SlimLittleTools\Tests\TestBase
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
        // Set up a request object
        $request = $this->createServerRequest('/');
        $responseFactory = $this->getResponseFactory();

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

        // Register middleware
        $app->add(new Cookie($app->getContainer()));

        // Register routes
        // XXX
        $mw = function ($request, $handler) use ($responseFactory) {
            $response = $responseFactory->createResponse();
            $this->get('cookie')->set('test', '123');
            $this->get('cookie')->set('test2', '987');
            return $response;
        };
        // XXX
        $mw2 = new ContentLengthMiddleware();

        $middlewareDispatcher = $this->createMiddlewareDispatcher(
            $this->createMock(RequestHandlerInterface::class),
            null
        );
        $middlewareDispatcher->addCallable($mw);
        $middlewareDispatcher->addMiddleware($mw2);
        $response = $middlewareDispatcher->handle($request);

        // XXX
        $this->assertSame('4', $response->getHeaderLine('Content-Length'));
        //
        $headers = $response->getHeaders();
        $this->assertSame(isset($headers['Set-Cookie']), true);
        $this->assertSame($headers['Set-Cookie'][0], 'test=123; HttpOnly');
        $this->assertSame($headers['Set-Cookie'][1], 'test2=987; HttpOnly');
    }
}
