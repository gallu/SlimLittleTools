<?php

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\Container;

class ContainerTestHoge {
}

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass()
    {
        $settings = [
            'settings' => [
                'displayErrorDetails' => true, // set to false in production
            ],
        ];
        //
        $app = new \Slim\App($settings);
        $container = $app->getContainer();
        //
        Container::setContainer($container);
        // XXX あえて後ろに
        $container['test'] = function($c) {
            $obj = new \stdClass();
            return $obj;
        };
        $container['hoge'] = function($c) {
            $obj = new ContainerTestHoge();
            return $obj;
        };
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

    public function testAny()
    {
        //
        $this->assertSame(get_class(Container::getContainer()), 'Slim\\Container');

        //
        $this->assertSame(get_class(Container::get('test')), 'stdClass');
        $this->assertSame(get_class(Container::get('hoge')), 'SlimLittleTools\\Tests\\Libs\\ContainerTestHoge');
    }
}
