<?php

namespace Maruamyu\Core\Orm;

class EntityTest extends \PHPUnit\Framework\TestCase
{
    public function test_bindRecord()
    {
        $record = [
            'id' => 1,
            'name' => 'hoge',
            'caption' => 'ほげほげ',
            'released_on' => '2018-01-01',
            'created_at' => '2018-01-01 00:00:00',
            'updated_at' => '2018-01-02 00:00:00',
        ];
        $hogeEntity = new HogeEntity($record);
        $this->assertEquals(1, $hogeEntity->id);
        $this->assertEquals('hoge', $hogeEntity->name);
        $this->assertEquals('ほげほげ', $hogeEntity->caption);
        $this->assertEquals(new Date('2018-01-01'), $hogeEntity->releasedOn);
        $this->assertEquals(new \DateTime('2018-01-01 00:00:00'), $hogeEntity->createdAt);
        $this->assertEquals(new \DateTime('2018-01-02 00:00:00'), $hogeEntity->updatedAt);
    }

    public function test_toString()
    {
        $record = [
            'id' => 1,
            'name' => 'hoge',
            'caption' => 'ほげほげ',
            'released_on' => '2018-01-01',
            'created_at' => '2018-01-01 00:00:00',
            'updated_at' => '2018-01-02 00:00:00',
        ];
        $hogeEntity = new HogeEntity($record);

        $jsonString = strval($hogeEntity);
        $json = json_decode($jsonString, true);

        $this->assertNotNull($json);
        $this->assertCount(6, $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('caption', $json);
        $this->assertArrayHasKey('released_on', $json);
        $this->assertArrayHasKey('created_at', $json);
        $this->assertArrayHasKey('updated_at', $json);
        $this->assertEquals(1, $json['id']);
        $this->assertEquals('hoge', $json['name']);
        $this->assertEquals('ほげほげ', $json['caption']);
        $this->assertEquals('2018-01-01', $json['released_on']);
        $this->assertEquals((new \DateTime('2018-01-01 00:00:00'))->format(\DateTime::ATOM), $json['created_at']);
        $this->assertEquals((new \DateTime('2018-01-02 00:00:00'))->format(\DateTime::ATOM), $json['updated_at']);
    }

    public function test_toJson()
    {
        $record = [
            'id' => 1,
            'name' => 'hoge',
            'caption' => 'ほげほげ',
            'released_on' => '2018-01-01',
            'created_at' => '2018-01-01 00:00:00',
            'updated_at' => '2018-01-02 00:00:00',
        ];
        $hogeEntity = new HogeEntity($record);

        $jsonString = $hogeEntity->toJson();
        $json = json_decode($jsonString, true);

        $this->assertNotNull($json);
        $this->assertCount(6, $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('caption', $json);
        $this->assertArrayHasKey('released_on', $json);
        $this->assertArrayHasKey('created_at', $json);
        $this->assertArrayHasKey('updated_at', $json);
        $this->assertEquals(1, $json['id']);
        $this->assertEquals('hoge', $json['name']);
        $this->assertEquals('ほげほげ', $json['caption']);
        $this->assertEquals('2018-01-01', $json['released_on']);
        $this->assertEquals((new \DateTime('2018-01-01 00:00:00'))->format(\DateTime::ATOM), $json['created_at']);
        $this->assertEquals((new \DateTime('2018-01-02 00:00:00'))->format(\DateTime::ATOM), $json['updated_at']);
    }

    public function test_toArray()
    {
        $record = [
            'id' => 1,
            'name' => 'hoge',
            'caption' => 'ほげほげ',
            'released_on' => '2018-01-01',
            'created_at' => '2018-01-01 00:00:00',
            'updated_at' => '2018-01-02 00:00:00',
        ];
        $hogeEntity = new HogeEntity($record);

        $data = $hogeEntity->toArray();

        $this->assertNotNull($data);
        $this->assertCount(6, $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('caption', $data);
        $this->assertArrayHasKey('released_on', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('hoge', $data['name']);
        $this->assertEquals('ほげほげ', $data['caption']);
        $this->assertEquals(new Date('2018-01-01'), $data['released_on']);
        $this->assertEquals(new \DateTime('2018-01-01 00:00:00'), $data['created_at']);
        $this->assertEquals(new \DateTime('2018-01-02 00:00:00'), $data['updated_at']);
    }

    public function test_isValid()
    {
        $hogeEntity = new HogeEntity();
        $this->assertFalse($hogeEntity->isValid());

        $hogeEntity->id = 1;
        $this->assertFalse($hogeEntity->isValid());

        $hogeEntity->name = 'hoge';
        $this->assertTrue($hogeEntity->isValid());
    }

    public function test_hasOriginalValues()
    {
        $hogeEntity = new HogeEntity();
        $this->assertFalse($hogeEntity->hasOriginalValues());

        $record = ['id' => 1];
        $hogeEntity2 = new HogeEntity($record);
        $this->assertTrue($hogeEntity2->hasOriginalValues());
    }

    public function test_isModified()
    {
        $hogeEntity = new HogeEntity();
        $this->assertFalse($hogeEntity->isModified());

        $hogeEntity->id = 1;
        $this->assertTrue($hogeEntity->isModified());
    }

    public function test_columnIsModified()
    {
        $hogeEntity = new HogeEntity();
        $this->assertFalse($hogeEntity->columnIsModified('id'));
        $this->assertFalse($hogeEntity->columnIsModified('name'));

        $hogeEntity->id = 1;
        $this->assertTrue($hogeEntity->columnIsModified('id'));
        $this->assertFalse($hogeEntity->columnIsModified('name'));

        $hogeEntity->name = 'hoge';
        $this->assertTrue($hogeEntity->columnIsModified('id'));
        $this->assertTrue($hogeEntity->columnIsModified('name'));
    }

    public function test_getTableName()
    {
        $this->assertEquals('hoge', HogeEntity::getTableName());
    }

    public function test_getColumnMap()
    {
        /** @var array $columnMap */
        $columnMap = HogeEntity::getColumnMap();
        $this->assertCount(6, $columnMap);
        $this->assertArrayHasKey('id', $columnMap);
        $this->assertArrayHasKey('name', $columnMap);
        $this->assertArrayHasKey('caption', $columnMap);
        $this->assertArrayHasKey('released_on', $columnMap);
        $this->assertArrayHasKey('created_at', $columnMap);
        $this->assertArrayHasKey('updated_at', $columnMap);
        $this->assertInstanceOf(Column::class, $columnMap['id']);
        $this->assertInstanceOf(Column::class, $columnMap['name']);
        $this->assertInstanceOf(Column::class, $columnMap['caption']);
        $this->assertInstanceOf(Column::class, $columnMap['released_on']);
        $this->assertInstanceOf(Column::class, $columnMap['created_at']);
        $this->assertInstanceOf(Column::class, $columnMap['updated_at']);
        /** @var Column[] $columnMap */
        $this->assertEquals('id', $columnMap['id']->getName());
        $this->assertEquals('name', $columnMap['name']->getName());
        $this->assertEquals('caption', $columnMap['caption']->getName());
        $this->assertEquals('released_on', $columnMap['released_on']->getName());
        $this->assertEquals('created_at', $columnMap['created_at']->getName());
        $this->assertEquals('updated_at', $columnMap['updated_at']->getName());
    }

    public function test_getAutoIncrementColumnName()
    {
        $this->assertEquals('id', HogeEntity::getAutoIncrementColumnName());
    }

    public function test_getPrimaryKeyColumnNames()
    {
        $this->assertEquals(['id'], HogeEntity::getPrimaryKeyColumnNames());
    }
}

class HogeEntity extends EntityAbstract
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $caption;

    /**
     * @var Date
     */
    public $releasedOn;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @var \DateTime
     */
    public $updatedAt;

    /**
     * return table name
     *
     * @return string table name
     */
    public static function getTableName()
    {
        return 'hoge';
    }

    /**
     * return column metadata map ({column_name => Column instance})
     *
     * @return Column[] map of column
     */
    public static function getColumnMap()
    {
        return [
            'id' => new Column('id', Column::DATA_TYPE_INT, true),
            'name' => new Column('name', Column::DATA_TYPE_STRING, true),
            'caption' => new Column('caption', Column::DATA_TYPE_STRING, false),
            'released_on' => new Column('released_on', Column::DATA_TYPE_DATE, false, 'releasedOn'),
            'created_at' => new Column('created_at', Column::DATA_TYPE_DATETIME, false, 'createdAt', Column::OPTIONS_SKIP_ON_INSERT_OR_UPDATE),
            'updated_at' => new Column('updated_at', Column::DATA_TYPE_DATETIME, false, 'updatedAt', Column::OPTIONS_SKIP_ON_INSERT_OR_UPDATE),
        ];
    }

    /**
     * return `AUTO_INCREMENT` column name
     *
     * @return string|null `AUTO_INCREMENT` column name, or null if not exist
     */
    public static function getAutoIncrementColumnName()
    {
        return 'id';
    }

    /**
     * return list of PRIMARY KEY column names
     *
     * @return string[] list of PRIMARY KEY column names
     */
    public static function getPrimaryKeyColumnNames()
    {
        return ['id'];
    }
}
