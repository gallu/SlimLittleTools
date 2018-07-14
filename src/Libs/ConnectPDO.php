<?php

namespace SlimLittleTools\Libs;

use SlimLittleTools\Libs\PDO;

class ConnectPDO
{
    //
    public static function connect($data)
    {
        // DSNの作成
        $dsn = static::makeDsn($data);
        $options = (isset($data['options']))? $data['options'] : [] ;

        // 接続
        $dbh = new PDO($dsn, $data['user'], $data['pass'], $options);
        return $dbh;
    }
    //
    protected static function makeDsn($data)
    {
        //
        $driver = static::_getData($data, 'driver', 'mysql');
        $host = static::_getData($data, 'host', 'localhost');
        $database = static::_getData($data, 'database', '');
        $dsn = "{$driver}:host={$host};dbname={$database}";
        //
        $charset = static::_getData($data, 'charset', '');
        if ('' !== $charset) {
            $dsn .= ";charset={$charset}";
        }
        $port = static::_getData($data, 'port', '');
        if ('' !== $port) {
            $dsn .= ";port={$port}";
        }

        //
        return $dsn;
    }

    // XXX PHP 7.0.0 <= になったらnull演算子に速攻書き換え
    protected static function _getData($data, $key, $default)
    {
        if ((isset($data[$key]))&&('' !== $data[$key])) {
            return $data[$key];
        }
        // else
        return $default;
    }
}
