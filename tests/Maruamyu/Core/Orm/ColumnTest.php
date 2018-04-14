<?php

namespace Maruamyu\Core\Orm;

class ColumnTest extends \PHPUnit\Framework\TestCase
{
    public function test_construct_and_getName()
    {
        $column = new Column('name');
        $this->assertEquals('name', $column->getName());
    }

    public function test_getDataType()
    {
        $column = new Column('name');
        $this->assertEquals(Column::DATA_TYPE_STRING, $column->getDataType());

        $column2 = new Column('gender', Column::DATA_TYPE_INT);
        $this->assertEquals(Column::DATA_TYPE_INT, $column2->getDataType());
    }

    public function test_isRequired()
    {
        $column = new Column('name');
        $this->assertFalse($column->isRequired());

        $column2 = new Column('gender', Column::DATA_TYPE_INT, true);
        $this->assertTrue($column2->isRequired());

        $column3 = new Column('age', Column::DATA_TYPE_INT, false);
        $this->assertFalse($column3->isRequired());
    }

    public function test_getPropertyName()
    {
        $column = new Column('name');
        $this->assertEquals('name', $column->getPropertyName());

        $column2 = new Column('name_kana', Column::DATA_TYPE_STRING, false, 'nameKana');
        $this->assertEquals('nameKana', $column2->getPropertyName());
    }
}
