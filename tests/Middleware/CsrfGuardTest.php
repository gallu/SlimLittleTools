<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Middleware;

//
//session_start();

use SlimLittleTools\Middleware\CsrfGuard;
use SlimLittleTools\Libs\Http\Request;
//
use Slim\Http\Environment;
use Slim\Http\Response;
use SlimLittleTools\Libs\Config;

class CsrfGuardTest extends \SlimLittleTools\Tests\TestBase
{
    // テストメソッドごとの開始前メソッド
    protected function setUp() : void
    {
        // 一端スキップ
        $this->markTestSkipped();
    }

    // -----------------------------------------------
    public function testCsrfAvoidance()
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/foo',
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = [
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true, // 必須！！
            ],
        ];
        $CSRF_not_covered_list = [
            'foo',
        ];

        // Instantiate the application
        $app = new \Slim\App($settings);
        Config::setContainer($app->getContainer()); // settingsのチェックをconfigクラス経由でやっているので、必須

        // Set up dependencies

        // Register middleware
        $app->add((new CsrfGuard())->setNotCoveredList(
            $CSRF_not_covered_list
        ));

        // Register routes
        $app->post('/foo', function (Request $request, Response $response, array $args) {
            //echo 'test';
        })->setName('foo');

        // Process the application
        $response = $app->process($request, $response);

        $this->assertSame($response->getStatusCode(), 200);
    }
}
