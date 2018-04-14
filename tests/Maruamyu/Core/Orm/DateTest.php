<?php

namespace Maruamyu\Core\Orm;

class DateTest extends \PHPUnit\Framework\TestCase
{
    public function test_construct()
    {
        $date = new Date('0123-04-05');
        $this->assertEquals(123, $date->year);
        $this->assertEquals(4, $date->month);
        $this->assertEquals(5, $date->day);
    }

    public function test_nullObject()
    {
        $date = new Date();
        $this->assertEquals(0, $date->year);
        $this->assertEquals(0, $date->month);
        $this->assertEquals(0, $date->day);
    }

    public function test_fromYmd()
    {
        $date = Date::fromYmd(67, 8, 9);
        $this->assertEquals(67, $date->year);
        $this->assertEquals(8, $date->month);
        $this->assertEquals(9, $date->day);
    }

    public function test_toString()
    {
        $date = new Date();
        $date->year = 123;
        $date->month = 4;
        $date->day = 5;
        $this->assertEquals('0123-04-05', strval($date));
    }

    public function test_nullToString()
    {
        $date = new Date();
        $this->assertEquals('0000-00-00', strval($date));
    }

    public function test_check()
    {
        $date = new Date();

        $this->assertFalse($date->check());

        $date->year = 2000;
        $date->month = 2;
        $date->day = 29;
        $this->assertTrue($date->check());

        $date->year = 2001;
        $this->assertFalse($date->check());
    }
}
