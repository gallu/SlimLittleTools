<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Libs;

use SlimLittleTools\Libs\PDO;

class PDOTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------
    public function getDbHandle()
    {
        static $dbh = null;
        if (null === $dbh) {
            // XXX
            $dbh = new PDO('mysql:dbname=slim_tools;host=localhost', 'slim_tools', 'slim_tools');
        }
        return $dbh;
    }

    //
    public function testValidIdentifier()
    {
        $dbh = $this->getDbHandle();
        $this->assertSame($dbh->escapeIdentifier('abcde'), '`abcde`');
        $this->assertSame($dbh->escapeIdentifier('ab1cde'), '`ab1cde`');
        //$this->assertSame($dbh->escapeIdentifier('a`e'), '`a``e`'); // 現状のルールだとこれははじかれるので、未テスト
    }

    public function testTran()
    {
        //
        $dbh = $this->getDbHandle();
        $this->assertSame($dbh->isTran(), false);
        //
        $dbh->beginTransaction();
        $this->assertSame($dbh->isTran(), true);
        //
        $dbh->commit();
        $this->assertSame($dbh->isTran(), false);

        //
        $dbh->beginTransaction();
        $this->assertSame($dbh->isTran(), true);
        $dbh->rollBack();
        $this->assertSame($dbh->isTran(), false);
    }

    /*
// テスト用テーブル
DROP TABLE IF EXISTS prepared_query_test;
CREATE TABLE prepared_query_test (
    prepared_query_test_id INT NOT NULL,
    val VARCHAR(64) NOT NULL,
    val2 VARCHAR(64) NOT NULL,
    PRIMARY KEY(prepared_query_test_id)
);
INSERT INTO prepared_query_test(prepared_query_test_id, val, val2) VALUES(1, '1_val', '1_val2');
INSERT INTO prepared_query_test(prepared_query_test_id, val, val2) VALUES(2, '2_val', '2_val2');
INSERT INTO prepared_query_test(prepared_query_test_id, val, val2) VALUES(3, '3_val', '3_val2');
INSERT INTO prepared_query_test(prepared_query_test_id, val, val2) VALUES(4, '4_val', '4_val2');
     */
    //
    public function testPreparedQuery()
    {
        //
        $dbh = $this->getDbHandle();
        //
        $pre = $dbh->preparedQuery('SELECT * FROM prepared_query_test WHERE prepared_query_test_id = :id;', ['id' => 1]);
        $data = $pre->fetch();
        $this->assertSame($data['val'], '1_val');
        $this->assertSame($data['val2'], '1_val2');

        //
        $pre = $dbh->preparedQuery('SELECT * FROM prepared_query_test WHERE val = :val;', ['val' => '3_val']);
        $data = $pre->fetch();
        $this->assertSame($data['prepared_query_test_id'], 3);
        $this->assertSame($data['val'], '3_val');
        $this->assertSame($data['val2'], '3_val2');
    }


    /**
     * サポート外のRDB
     *
     * expectedException \SlimLittleTools\Exception\DbException
     */
    public function testNotSupported()
    {
        //$dbh = new PDO('oracle:dbname=slim_tools;host=localhost', 'slim_tools', 'XXXXXX');
        try {
            $dbh = new PDO('oracle:dbname=slim_tools;host=localhost', 'slim_tools', 'XXXXXX');
        } catch (\SlimLittleTools\Exception\DbException $e) {
            $this->assertTrue(true);
            return ;
        }
        //
        $this->assertTrue(false);
    }

    /**
     * 不適切なカラム名(またはテーブル名)
     *
     * @expectedException \SlimLittleTools\Exception\DbException
     */
    public function testInvalidIdentifier()
    {
        //$dbh = $this->getDbHandle();
        //$dbh->escapeIdentifier('abc-de');
        try  {
            $dbh = $this->getDbHandle();
            $dbh->escapeIdentifier('abc-de');
        } catch (\SlimLittleTools\Exception\DbException $e) {
            $this->assertTrue(true);
            return ;
        }
        //
        $this->assertTrue(false);
    }
}
