<?php

namespace SlimLittleTools\Libs;

use SlimLittleTools\Exception\DbException;

/**
 * PDO拡張クラス(拡張自体はできるだけ最低限に)
 */

class PDO extends \PDO
{
    /**
     * 接続RDBMSによって処理を変える箇所があるので、上書き
     */
    public function __construct($dsn, $user, $pass, $options = [])
    {
        // RDBによる処理の変更
        // XXX 当面、MySQLとPostgreSQLのみをサポート
        if (0 === strncasecmp($dsn, 'mysql:', strlen('mysql:'))) {
            $this->escape_token = '`';
        } elseif (0 === strncasecmp($dsn, 'pgsql:', strlen('pgsql:'))) {
            $this->escape_token = '"';
        } else {
            throw new DbException('現在、MySQL/PostgreSQL以外のDBはサポートしておりません申し訳ないです');
        }

        // 通常の処理
        parent::__construct($dsn, $user, $pass, $options);
    }

    /**
     * SQL識別子のエスケープ処理
     *
     * XXX 含むvalidation
     */
    public function escapeIdentifier($s)
    {
        // validate
        $len = strlen($s);
        for ($i = 0; $i < $len; ++$i) {
            if (true === ctype_alnum($s[$i])) {
                continue;
            }
            if ('_' === $s[$i]) {
                continue;
            }
            // else
            throw new DbException('カラム名またはテーブル名に不適切な文字が入っています');
        }

        // escape
        $e = $this->escape_token;
        $s = $e . str_replace($e, "{$e}{$e}", $s) . $e;

        //
        return $s;
    }

    //
    public function isTran()
    {
        return $this->tran_flg;
    }

    //
    public function beginTransaction()
    {
        $r = parent::beginTransaction();
        if (true === $r) {
            $this->tran_flg = true;
        }
        return $r;
    }

    //
    public function rollBack()
    {
        $r = parent::rollBack();
        if (true === $r) {
            $this->tran_flg = false;
        }
        return $r;
    }

    //
    public function commit()
    {
        $r = parent::commit();
        if (true === $r) {
            $this->tran_flg = false;
        }
        return $r;
    }

    /**
     * SQLをプリペアドステートメントで比較的楽に実行するためのラッパー
     *
     * 基本的には「SELECT」を想定しています。
     *
     * @return mix 失敗したらfalse、成功したらPDOStatementインスタンス
     */
    public function preparedQuery($sql, $data = [])
    {
        // データ保持
        static::$sql = $sql;
        static::$data = $data;

        //
        $pre = $this->prepare($sql);
        if (false === $pre) {
            static::$error = $this->errorinfo()[2];
            return false;
        }

        // XXX 組みなおしたほうがよさそう
        foreach ($data as $k => $v) {
            if ((is_int($v))||(is_float($v))) {
                $data_type = \PDO::PARAM_INT;
            } elseif (null === $v) {
                $data_type = \PDO::PARAM_NULL;
            } elseif (is_bool($v)) {
                $data_type = \PDO::PARAM_BOOL;
            } else {
                $data_type = \PDO::PARAM_STR;
            }
            $pre->bindValue($k, $v, $data_type);
        }
        //
        $r = $pre->execute();
        if (true === $r) {
            return $pre;
        }
        // else
        static::$error = $pre->errorinfo()[2];
        return $r;
    }

    /**
     * 直前に preparedQuery で発行したSQL文の取得
     */
    public static function getSql()
    {
        return static::$sql;
    }

    /**
     * 直前に preparedQuery で発行した「SQL文に充てたdata」の取得
     */
    public static function getData()
    {
        return static::$data;
    }

    /**
     * preparedQuery でエラーが出たときの errorinfo()[2]
     */
    public static function getError()
    {
        return static::$error;
    }


    //private:
    private $tran_flg = false; // トランザクション判定用フラグ
    protected $escape_token; // SQL識別子用のエスケープ文字

    // 直前のデータ＆エラー情報保持用
    protected static $sql = '';
    protected static $data = [];
    protected static $error = '';
}
