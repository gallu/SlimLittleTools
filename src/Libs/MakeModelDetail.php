<?php

namespace SlimLittleTools\Libs;

/**
 * DBのカラム名一覧を取得するクラス
 */
class MakeModelDetail
{
    // 実行ポイント
    public static function exec(array $argv)
    {
        //
        $dbh = \DB::getHandle();

        //
        $sql = 'SHOW TABLE STATUS ';
        // 引数にテーブル名が指定されていたらそのテーブルを対象とする
        if (2 <= count($argv)) {
            array_shift($argv);
            $sql .= ' WHERE Name in (' .  implode(', ', array_map(function ($s) use($dbh) { return $dbh->escapeIdentifier($s); }, $argv)) . ');';
            $sql = str_replace('`', "'", $sql); // XXX かなり場当たりだなぁ………
        } else {
            // 全テーブルを対象とする
            // XXX database名、もうちょっと奇麗に取れないかねぇ……
            $sql .= ' FROM ' . $dbh->escapeIdentifier($GLOBALS['settings']['settings']['db']['database']) . ';';
        }
//var_dump($sql); //exit;
        $r = $dbh->preparedQuery($sql, []); // XXX エスケープどうすっかねぇ……
//var_dump($r, $dbh->errorInfo()); //exit;
        $awk = $r->fetchAll(\PDO::FETCH_ASSOC);
        $target = [];
        foreach($awk as $row) {
            $target[$row['Name']] = $row['Comment'];
        }

//var_dump($target); exit;

        //
        foreach($target as $table => $comment) {
            //
            echo "テーブル {$table} 処理中\n";

            // テーブルの情報を引き出す

            // カラムの一覧とPK情報を取り出す
            $pk = [];
            $col = [];
            $type = [];
            $r = $dbh->preparedQuery('SHOW FULL COLUMNS FROM ' . $dbh->escapeIdentifier($table) . ';', []); // XXX エスケープどうすっかねぇ……
            $awk = $r->fetchAll(\PDO::FETCH_ASSOC);
            foreach($awk as $datum) {
                // PK 確認
                if ('PRI' === $datum['Key']) {
                    $pk[] = $datum['Field'];
                }
                // col用の配列作成
                $mono_col = static::$mono_col;
                $mono_col = str_replace('%%%col_name%%%', $datum['Field'], $mono_col);
                $mono_col = str_replace('%%%comment%%%', $datum['Comment'], $mono_col);
                $mono_col = str_replace('%%%type%%%', $datum['Type'], $mono_col);
                //
                $col[] = $mono_col;

                //
                $mono_col_type = static::$mono_col_type;
                $mono_col_type = str_replace('%%%col_name%%%', $datum['Field'], $mono_col_type);
                $mono_col_type = str_replace('%%%comment%%%', $datum['Comment'], $mono_col_type);
                $mono_col_type = str_replace('%%%type%%%', $datum['Type'], $mono_col_type);
                //
                $type[] = $mono_col_type;
            }
            $col = implode("\n", $col);
            $type = implode("\n", $type);
//var_dump($col, $type);

            // pkが１つなら文字列、２つ以上なら配列にする
            if (1 === count($pk)) {
                $pk = $pk[0];
            } else if (0 === count($pk)) {
                $pk = '';
            }
            $pk = var_export($pk, true);

            // テーブル名からクラス名を作成
            $class = implode('', array_map(function($s) { if ('' === $s) { return '_'; } return ucfirst($s); }, explode('_', $table))) . 'Detail';

            // ファイルの中身の作成
            $text = static::$text;
            $text = str_replace('%%%class%%%', $class, $text);
            $text = str_replace('%%%pk%%%', $pk, $text);
            $text = str_replace('%%%table%%%', $table, $text);
            $text = str_replace('%%%table_comment%%%', $comment, $text);
            $text = str_replace('%%%colmuns%%%', $col, $text);
            $text = str_replace('%%%colmuns_type%%%', $type, $text);
//var_dump($text); //exit;

            // 出力先ファイル名の作成
            $fn = BASEPATH . "/app/Model/Detail/{$class}.php";
//var_dump($fn); exit;
            // 対象ディレクトリに出力する
            file_put_contents($fn, $text, LOCK_EX);
        }
    }

// 出力内容
private static $mono_col = <<<'EOL'
        '%%%col_name%%%' => '%%%comment%%%',	// %%%comment%%%	%%%type%%%
EOL;
private static $mono_col_type = <<<'EOL'
        '%%%col_name%%%' => '%%%type%%%',	// %%%comment%%%	%%%type%%%
EOL;


private static $text = <<<'EOL'
<?php
declare(strict_types=1);

namespace App\Model\Detail;

/*
 * XXX このファイルは自動生成なので書き込みをしないでください
 */

// %%%table%%%
// %%%table_comment%%%
trait %%%class%%% {
    // pk
    protected $pk = %%%pk%%%;
    // テーブル名
    protected $table = '%%%table%%%';

    // カラム一覧
    protected $colmuns = [
%%%colmuns%%%%
    ];

    // カラム型一覧
    protected $colmuns_type = [
%%%colmuns_type%%%%
    ];

    /**
     * コメント付きで全カラム取得
     *
     * @param $delimiter string コメントを区切る区切り文字。空文字なら「コメントは区切らず全部返す」
     * @return array [カラム名 => コメント, ...]の配列
     */
    public static function getAllColmunsWithComment(string $delimiter = '') : array
    {
        // 区切りがいらないんなら速やかに返却
        if ('' === $delimiter) {
            return (new static())->colmuns;
        }
        // else
        // 区切りがいるんなら処理して返す
        $ret = [];
        foreach((new static())->colmuns as $k => $v) {
            $ret[$k] = explode($delimiter, $v)[0];
        }
        return $ret;
    }

    /**
     * 全カラム取得
     *
     * @return array [カラム名ト, ...]の配列
     */
    public static function getAllColmuns() : array
    {
        return array_keys(static::getAllColmunsWithComment());
    }

    /**
     * PKを除く、コメント付きで全カラム取得
     *
     * @param $delimiter string コメントを区切る区切り文字。空文字なら「コメントは区切らず全部返す」
     * @return array [カラム名 => コメント, ...]の配列
     */
    public static function getAllColmunsWithCommentWithoutPk(string $delimiter = '') : array
    {
        // まず全一覧を取得
        $ret = static::getAllColmunsWithComment($delimiter);

        // pk把握
        $pks = static::getPkName();
        if (is_string($pks)) {
            $pks = [$pks];
        }

        // pkを削除
        foreach($pks as $pk) {
            unset($ret[$pk]);
        }

        //
        return $ret;
    }

    /**
     * PKを除く、全カラム取得
     *
     * @return array [カラム名ト, ...]の配列
     */
    public static function getAllColmunsWithoutPk()
    {
        return array_keys(static::getAllColmunsWithCommentWithoutPk());
    }

    /**
     * 日付系の型か確認
     *
     * XXX 一端 "DATE", "DATETIME", "TIMESTAMP" を「日付系の型」とする
     *
     * @param $name string カラム名
     * @return boolean 日付系の型ならtrue、そうでなければfalse
     */
    public static function isColumnTypeDate(string $name)
    {
        // 先に確認
        $list = (new static())->colmuns_type;
        if (false === isset($list[$name])) {
            throw new \ErrorException('存在しないカラム名が指定されました');

        }
        // 型の把握
        $type = strtolower($list[$name]);

        // 判定
        // DATE または DATETIME
        if ( ('date' === $type) || ('datetime' === $type) ) {
            return true;
        }
        // "TIMESTAMP"は、RDBによっては「 without time zone」とか「with time zone」とか付くようなので少し配慮
        // あと、このロジックだと timestamptz(PostgreSQL独自拡張)も一応拾える想定
        if (0 === strncmp('timestamp', $type, 9)) {
            return true;
        }

        // 上述以外ならfalse
        return false;
    }


}
EOL;

}

