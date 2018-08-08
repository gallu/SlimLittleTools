<?php

namespace SlimLittleTools\Tests\Model;

use SlimLittleTools\Model\ModelBase;
use SlimLittleTools\Libs\DB;

/*

テスト用テーブル
DROP TABLE IF EXISTS mode_1;
CREATE TABLE mode_1 (
  mode_1_id VARCHAR(64) NOT NULL,
  val VARCHAR(64),
  val_guard VARCHAR(64),
  created_at DATETIME,
  updated_at DATETIME,
  PRIMARY KEY(mode_1_id)
);

DROP TABLE IF EXISTS mode_2;
CREATE TABLE mode_2 (
  mode_2_id INT NOT NULL,
  mode_2_key VARCHAR(64) NOT NULL,
  val VARCHAR(64),
  PRIMARY KEY(mode_2_id, mode_2_key)
);

DROP TABLE IF EXISTS mode_3;
CREATE TABLE mode_3 (
  mode_3_id INT NOT NULL AUTO_INCREMENT,
  val VARCHAR(64),
  created_at DATETIME,
  updated_at DATETIME,
  PRIMARY KEY(mode_3_id)
);

*/

// テスト用モデル
class TestModel extends ModelBase
{
    protected $table = 'mode_1';
    protected $pk = 'mode_1_id';
    protected $guard = ['val_guard'];
    protected $created_at = 'created_at';
    protected $updated_at = 'updated_at';
    //
    protected $validate_insert = [
        'val_guard' => 'required|alpha_num|range_length:10-20',
    ];
    // update固有
    protected $validate_update = [
        'val' => 'required|alpha_num|range_length:10-20',
    ];
    // insert / update共通
    protected $validate = [
        'mode_1_id' => 'required|int',
    ];

    // filterルール設定
    // insert固有
    protected $filter_insert = [
    ];
    // update固有
    protected $filter_update = [
    ];
    // 共通
    protected $filter = [
        'mode_1_id' => 'int',
        'val_guard' => 'trim|strtolower',
    ];
}
// テスト用モデル
class TestModelMultiKey extends ModelBase
{
    protected $table = 'mode_2';
    protected $pk = ['mode_2_id', 'mode_2_key'];
}
// テスト用モデル
class TestModelAutoIncrement extends ModelBase
{
    protected $table = 'mode_3';
    protected $pk = 'mode_3_id';
    protected $auto_increment = true; // AUTO_INCREMENTがPKなテーブル
    protected $created_at = true; // デフォルト名
    protected $updated_at = true; // デフォルト名
}
// テスト用モデル(存在しないテーブル)
class TestModelNotExist extends ModelBase
{
    protected $table = 'mode_no_exist';
}
// テスト用モデル(DB接続先が違う)
class TestModelAnotherDB extends ModelBase
{
    protected $db_suffix = 'hoge';
}

// テスト本体
class ModelTest extends \PHPUnit\Framework\TestCase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass()
    {
        // DBハンドル用の設定をcontainerに入れておく
        $settings = [
            'settings' => [
                //
                'db' => [
                    'driver' => 'mysql',
                    'host' => 'localhost',
                    'database' => 'slim_tools',
                    'user' => 'slim_tools',
                    'pass' => 'XXXXXX',
                    'charset' => 'utf8mb4',
                    'options' => [\PDO::ATTR_EMULATE_PREPARES => false],
                ],
            ],
        ];
        $app = new \Slim\App($settings);
        //
        DB::setContainer($app->getContainer());

    }
    // テストメソッドごとの開始前メソッド
    protected function setUp()
    {
        // リアルなDB接続が必要なので、一旦スキップ
        $this->markTestSkipped();
    }
    // -----
    // テストメソッドごとの終了メソッド
    protected function tearDown()
    {
    }
    // 一回だけ実行される終了メソッド
    public static function tearDownAfterClass()
    {
    }

    // -----------------------------------------------
    public function testAll()
    {
        // 先にお掃除
        $dbh = DB::getHandle();
        $dbh->query('delete from mode_1;');
        $dbh->query('delete from mode_2;');

        // checkPkの確認
        // 単キー 2種
        $this->assertSame(TestModel::checkPk(['mode_1_id' => 'aaa']), 1);
        $this->assertSame(TestModel::checkPk(['test' => 'aaa']), -1);
        // 複合主キー 3種
        $this->assertSame(TestModelMultiKey::checkPk(['mode_2_id' => 'aaa', 'mode_2_key' => 222]), 1);
        $this->assertSame(TestModelMultiKey::checkPk(['mode_2_id' => 'aaa', 'test' => 222]), 0);
        $this->assertSame(TestModelMultiKey::checkPk(['id' => 'aaa']), -1);

        // DB接続
        $this->assertSame(TestModel::getDbSuffix(), '');
        $this->assertSame(TestModelAnotherDB::getDbSuffix(), 'hoge'); // 共通DBではない箇所への接続

        // 単純key: created_at / updated_at あり, compare_withあり(データ削除)
        // insert + filter
        $r = TestModel::insert(['mode_1_id' => '1', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
        $this->assertNotSame($r, null);
        $this->assertSame(get_class($r), TestModel::class);
        $this->assertSame($r->mode_1_id, 1);
        $this->assertSame($r->hoge, null); // 存在しないカラム名のチェック

        // 重複によるエラー
        $r = TestModel::insert(['mode_1_id' => '1', 'val' => 'XXXXXXXXXXXXX', 'val_guard' => 'XXXXXXXXXXXXxxxxxx', ]);
        $this->assertSame($r, null);

        // find(ない)
        $test_model = TestModel::find(123);
        $this->assertSame($test_model, null);
        // find(ある)
        $test_model = TestModel::find(1);
        $this->assertSame(get_class($test_model), TestModel::class);

        // update + filter
        $this->assertSame($test_model->val, 'Val0123456789'); //「修正項目が変わっている」前を確認
        $r = $test_model->update(['val' => 'valval1234', 'val_guard' => 'valguard0123456789']);
        $this->assertNotSame($r, false);
        $this->assertSame($test_model->val, 'valval1234'); //「修正項目が変わっている」事を確認

        // findBy
        $obj = TestModel::findBy('val_guard', 'valguard0123456789');
        $this->assertSame(get_class($obj), TestModel::class);
        $this->assertSame($obj->mode_1_id, 1);

        // 複合主キー : created_at / updated_at なし
        // insert
        $r = TestModelMultiKey::insert([
            'mode_2_id' => 1,
            'mode_2_key' => 'key',
            'val' => 'val',
            ]);
        $this->assertNotSame($r, null);
        $this->assertSame(get_class($r), TestModelMultiKey::class);
        $this->assertSame($r->val, 'val');

        // find
        $objMulti = TestModelMultiKey::find(['mode_2_id' => 1,'mode_2_key' => 'key']);
        $this->assertSame(get_class($objMulti), TestModelMultiKey::class);
        // update
        $r = $objMulti->update(['val' => 'valxxx']);
        $this->assertSame($r, true);
        // findBy
        $objMulti2 = TestModelMultiKey::findBy(['val' => 'valxxx']);
        $this->assertSame(get_class($objMulti2), TestModelMultiKey::class);

        // auto_incrementの確認
        // insert
        $obj3 = TestModelAutoIncrement::insert(['val' => 'test']);
        // find
        $this->assertSame(is_numeric($obj3->mode_3_id), true);

        //
        $data = TestModelAutoIncrement::findByAll('val', 'test');
        $this->assertLessThanOrEqual(count($data->toArray()), 1);
        //
        $data = TestModelAutoIncrement::findByAll(['val' => 'testdummy']);
        $this->assertSame(count($data->toArray()), 0);

        // トランザクションの確認
        $test_model = TestModel::find(1);
        $this->assertNotContains(' FOR UPDATE', strtoupper(TestModel::getJustBeforeQuery()));
        $this->assertSame($dbh->isTran(), false); // isTran()
        // begin
        $dbh->beginTransaction();
        $this->assertSame($dbh->isTran(), true); // isTran()
        {
            // find
            $test_model = TestModel::find(1);
            $this->assertContains(' FOR UPDATE', strtoupper(TestModel::getJustBeforeQuery()));
        }
        // commit
        $dbh->commit();
        $this->assertSame($dbh->isTran(), false); // isTran()

        // 「空のfindByAll」
        $r = TestModel::findByAll();
        $this->assertNotSame($r, null);

        // 複合主キー delete
        $r = $objMulti->delete();
        $this->assertNotSame($r, false);
        $obj = TestModelMultiKey::find(['mode_2_id' => 1,'mode_2_key' => 'key']);
        $this->assertSame($obj, null);

        // 単純key delete
        $r = $test_model->delete();
        $this->assertNotSame($r, false);
        $obj = TestModel::find(1);
        $this->assertSame($obj, null);

        // auto_increment delete
        $obj3_id = $obj3->mode_3_id;
        $r = $obj3->delete();
        $this->assertNotSame($r, false);
        $obj = TestModelAutoIncrement::find($obj3_id);
        $this->assertSame($obj, null);
    }

    // 違うDBハンドルを使うクラスの確認
    // XXX

    /**
     * validateの例外
     *
     * @expectedException \SlimLittleTools\Exception\ModelValidateException
     */
    public function testModelValidateException()
    {
        TestModel::insert(['val' => 'abc']);
    }

    /**
     * updateでガード句の例外
     *
     * @depends testAll
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException()
    {
        $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
        $r->update(['val_guard' => 'XXXXXXXXXXXXXXXX']);
    }

    /**
     * insertでauto_incrementの例外
     *
     * @depends testAll
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException2()
    {
        $obj3 = TestModelAutoIncrement::insert(['mode_3_id' => 100, 'val' => 'test']);
    }

    /**
     * 単純なSQLのエラー例外: 存在しないカラム
     *
     * expectedException \SlimLittleTools\Exception\DbException
     * @expectedException \Error
    public function testDbException()
    {
        $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', 'dummy' => '999']);
    }
XXXXXX
     */

    /**
     * 単純なSQLのエラー例外: 存在しないテーブル
     *
     * @expectedException \SlimLittleTools\Exception\DbException
    public function testDbException2()
    {
// XXX
        $this->assertSame('', '');
    }
     */

    /**
     * 複合主キーの時にfindでstring(またはint)を指定したケース
     *
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
    public function testDbException2()
    {
// XXX
        $this->assertSame('', '');
    }
     */


}

