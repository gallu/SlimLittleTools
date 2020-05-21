<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\Container;

class ContainerTestHoge {
}

class ContainerTest extends \SlimLittleTools\Tests\TestBase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass() : void
    {
        $settings = [
            'settings' => [
                'displayErrorDetails' => true, // set to false in production
            ],
        ];
        //
        $settings['test'] = function($c) {
            $obj = new \stdClass();
            return $obj;
        };
        $settings['hoge'] = function($c) {
            $obj = new ContainerTestHoge();
            return $obj;
        };
        //
        $app = static::getApp($settings);
        Container::setContainer($app->getContainer());
    }
    // テストメソッドごとの開始前メソッド
    protected function setUp() : void
    {
    }
    // -----
    // テストメソッドごとの終了メソッド
    protected function tearDown() : void
    {
    }
    // 一回だけ実行される終了メソッド
    public static function tearDownAfterClass() : void
    {
    }
    // -----------------------------------------------

    public function testAny()
    {
        //
        $this->assertSame(get_class(Container::getContainer()), 'DI\\Container');

        //
        $this->assertSame(get_class(Container::get('test')), 'stdClass');
        $this->assertSame(get_class(Container::get('hoge')), 'SlimLittleTools\\Tests\\Libs\\ContainerTestHoge');
    }
}
