<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Middleware;

use SlimLittleTools\Middleware\SlimLittleToolsUse;
use SlimLittleTools\Libs\Http\Request;
use SlimLittleTools\Libs\Config;
//
use Slim\Http\Environment;
use Slim\Http\Response;

class SlimLittleToolsUseTest extends \SlimLittleTools\Tests\TestBase
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
        ];

        // Instantiate the application
        $app = new \Slim\App($settings);

        // Set up dependencies

        // Register middleware
        $app->add(new SlimLittleToolsUse($app->getContainer()));

        // Register routes
        $app->get('/', function (Request $request, Response $response, array $args) {
        });

        // Process the application
        $response = $app->process($request, $response);

        //
        $settings = Config::getAll();
        $this->assertGreaterThan(0, count($settings));
    }
}
