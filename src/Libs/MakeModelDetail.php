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
            }
            $col = implode("\n", $col);

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
        '%%%col_name%%%',	// %%%comment%%%	%%%type%%%
EOL;


private static $text = <<<'EOL'
<?php
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

    // 全カラム取得
    public static function getAllColmuns()
    {
        return (new static())->colmuns;
    }
    // PKを除く全カラム取得
    public static function getAllColmunsWithoutPk()
    {
        // カラム取得
        $colmuns = (new static())->colmuns;

        // pk把握
        $pk = static::getPkName();
        if (is_string($pk)) {
            $pk = [$pk];
        }

        // diffして整理
        $colmuns = array_values(array_diff($colmuns, $pk));

        //
        return $colmuns;
    }
}
EOL;

}

