<?php

namespace SlimLittleTools\Libs;

use SlimLittleTools\WithStaticContainerBase;

/**
 * DBハンドルを広域で楽に使うためのクラス
 */

class DB extends WithStaticContainerBase
{
    public static function setContainer($container)
    {
        //
        parent::setContainer($container);
        //
        $keys = $container->get('settings')->keys();
        // デフォルトのDBハンドル設定
        if ((in_array('db', $keys))&&(false === $container->has('db'))) {
            $container['db'] = new ContainerDbConnect('db');
        }

        // 拡張のDBハンドル設定
        foreach ($keys as $key) {
            if (0 === strncmp('db_', $key, 3)) {
                $container[$key] = new ContainerDbConnect($key);
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
    public function __construct($name)
    {
        $this->name = $name;
    }
    //
    public function __invoke($container)
    {
        // nameからdataの取得
        $data = $container->get('settings')[$this->name];
        // 接続クラスに投げる
        $class = (string)@$data['connect_class'];
        if ('' === $class) {
            $class = '\SlimLittleTools\Libs\ConnectPDO';
        }
        //
        return $class::connect($data);
    }
//
    private $name;
}
