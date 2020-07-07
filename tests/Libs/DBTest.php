<?php

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\DB;

class DBTest extends \PHPUnit\Framework\TestCase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass() : void
    {
        $settings = [
            'settings' => [
                //
                'db' => [
                    'connect_class' => '\SlimLittleTools\Libs\ConnectPDODummy',
                    'driver' => 'mysql',
                    'host' => '1.2.3.4',
                    'port' => '1234',
                    'database' => 'data1',
                    'user' => 'user',
                    'pass' => 'pass',
                    'charset' => 'utf8mb4',
                    'options' => ['opt1' => 1, 'opt2' => 2],
                ],
                // ギリギリまで省略
                'db_2nd' => [
                    'connect_class' => '\SlimLittleTools\Libs\ConnectPDODummy',
                    'database' => 'db',
                    'user' => '',
                    'pass' => '',
                ],
                // 「二つ目の拡張DBハンドル」が取得できるかの確認
                'db_3rd' => [
                    'connect_class' => '\SlimLittleTools\Libs\ConnectPDODummy',
                    'driver' => 'pgsql',
                    'host' => '',
                    'port' => '',
                    'database' => 'db2',
                    'user' => '',
                    'pass' => '',
                    'charset' => '',
                    'options' => [],
                ],
                // 実際のPDOハンドル(接続できないから例外が投げられてくること)
                'db_real' => [
                    'driver' => 'mysql',
                    'host' => '',
                    'database' => 'db_real',
                    'user' => '',
                    'pass' => '',
                    'charset' => 'utf8mb4',
                ],
            ],
        ];
        $app = new \Slim\App($settings);
        //
        DB::setContainer($app->getContainer());
    }
    // -----------------------------------------------

    public function testGetHandle()
    {
        //
        $obj = DB::getHandle();
        $this->assertSame($obj->dsn, 'mysql:host=1.2.3.4;dbname=data1;charset=utf8mb4;port=1234');
        $this->assertSame($obj->user, 'user');
        $this->assertSame($obj->pass, 'pass');

        $this->assertArrayHasKey('opt1', $obj->options);
        $this->assertArrayHasKey('opt2', $obj->options);
        $this->assertSame($obj->options['opt1'], 1);
        $this->assertSame($obj->options['opt2'], 2);

        //
        $obj = DB::getHandle('2nd');
        $this->assertSame($obj->dsn, 'mysql:host=localhost;dbname=db');
        $this->assertSame($obj->options, []);

        $obj = DB::getHandle('3rd');
        $this->assertSame($obj->dsn, 'pgsql:host=localhost;dbname=db2');

        $flg = false;
        try {
            $obj = DB::getHandle('real');
        } catch (\PDOException $e) {
            $flg = true;
        }
        $this->assertTrue($flg);
    }

}
