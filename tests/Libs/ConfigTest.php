<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\Config;

class ConfigTest extends \SlimLittleTools\Tests\TestBase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass() : void
    {
        $settings = [
            'settings' => [
                'displayErrorDetails' => true, // set to false in production
                'test1' => 'string',

                // Renderer settings
                'renderer' => [
                    'template_path' => __DIR__ . '/../templates/',
                ],
            ],
        ];

        $app = static::getApp($settings);
        Config::setContainer($app->getContainer());
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

    public function testGet()
    {
        $this->assertSame(Config::get('displayErrorDetails'), true);
        $this->assertSame(Config::get('test1'), 'string');
        $this->assertSame(Config::get('test1', 'hoge'), 'string');

        $this->assertSame(Config::get('test_empty'), null);
        $this->assertSame(Config::get('test_empty', 'hoge'), 'hoge');

        $this->assertSame(is_array(Config::get('renderer')), true);
    }
    public function testHas()
    {
        $this->assertSame(Config::has('displayErrorDetails'), true);
        $this->assertSame(Config::has('test_empty'), false);
    }
}
