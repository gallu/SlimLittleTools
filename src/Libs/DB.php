<?php
declare(strict_types=1);

namespace SlimLittleTools\Libs;

use Psr\Container\ContainerInterface;
use SlimLittleTools\WithStaticContainerBase;

/**
 * DBハンドルを広域で楽に使うためのクラス
 */

class DB extends WithStaticContainerBase
{
    public static function setContainer(ContainerInterface $container)
    {
        //
        parent::setContainer($container);
        //
        $make_class = new class($container) {
            private $container;
            public function __construct($container) {
                $this->container = $container;
            }
            public function make($name) {
                // nameからdataの取得
                $data = $this->container->get('settings')[$name];
                // 接続クラスに投げる
                $class = (string)@$data['connect_class'];
                if ('' === $class) {
                    $class = '\SlimLittleTools\Libs\ConnectPDO';
                }
                //
                return $class::connect($data);
            }
        };


        //
        $keys = array_keys($container->get('settings'));
        // デフォルトのDBハンドル設定
        if ((in_array('db', $keys))&&(false === $container->has('db'))) {
            $container->set('db', function() use ($make_class){ return $make_class->make('db'); } );
        }

        // 拡張のDBハンドル設定
        foreach ($keys as $key) {
            if (0 === strncmp('db_', $key, 3)) {
                $container->set($key, function() use ($key, $make_class){ return $make_class->make($key); } );
            }
        }
    }

    //
    public static function getHandle($suffix = '')
    {
        //
        if ('' === $suffix) {
            $key = 'db';
        } else {
            $key = "db_{$suffix}";
        }
        //
        return static::$container->get($key);
    }
}

// DB接続へのラッパー
class ContainerDbConnect
{
}
