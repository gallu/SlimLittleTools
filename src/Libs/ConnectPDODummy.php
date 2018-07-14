<?php

namespace SlimLittleTools\Libs;

class ConnectPDODummy extends ConnectPDO
{
    //
    public static function connect($data)
    {
        // DSNの作成
        $dsn = static::makeDsn($data);
        $options = (isset($data['options']))? $data['options'] : [] ;

        // 接続
        $dbh = new \stdClass();
        $dbh->dsn = $dsn;
        $dbh->user = $data['user'];
        $dbh->pass = $data['pass'];
        $dbh->options = $options;
        return $dbh;
    }
}
