<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Model;

use SlimLittleTools\Model\ModelCollection;

class ModelCollectionTest extends \SlimLittleTools\Tests\TestBase
{
    // -----------------------------------------------

    public function testAll()
    {
        $obj = new ModelCollection();
        $obj[] = new ModelMock();
        $obj[] = new ModelMock();
        $obj[] = new ModelMock();
        $r = $obj->toArray();
        $this->assertSame($r == [[1,2,3],[1,2,3],[1,2,3]], true);
    }
}

class ModelMock
{
    public function toArray()
    {
        return [1,2,3,];
    }
}
