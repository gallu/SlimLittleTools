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
        } else if (0 === strncasecmp($dsn, 'pgsql:', strlen('pgsql:'))) {
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
        for($i = 0; $i < $len; ++$i) {
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
        //
//var_dump($sql);
        $pre = $this->prepare($sql);
//var_dump($pre);
//var_dump($this->errorinfo());

        // XXX 組みなおしたほうがよさそう
        foreach($data as $k => $v) {
            if ( (is_int($v))||(is_float($v)) ) {
                $data_type = \PDO::PARAM_INT;
            } else if (null === $v) {
                $data_type = \PDO::PARAM_NULL;
            } else if (is_bool($v)) {
                $data_type = \PDO::PARAM_BOOL;
            } else {
                $data_type = \PDO::PARAM_STR;
            }
            $pre->bindValue($k, $v, $data_type);
        }
        //
        $r = $pre->execute();
//echo "-------------------------\n";
//var_dump($sql);
//var_dump($data);
//var_dump($r);
//var_dump($pre->errorinfo());
//echo "-------------------------\n";
        if (true === $r) {
//var_dump($pre);
            return $pre;
        }
        // else
        return $r;
    }

    //private:
    private $tran_flg = false; // トランザクション判定用フラグ
    protected $escape_token; // SQL識別子用のエスケープ文字
}
