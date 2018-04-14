<?php

namespace Maruamyu\Core\Orm;

/**
 * DATE Object
 */
class Date
{
    /**
     * @var int
     */
    public $year;

    /**
     * @var int
     */
    public $month;

    /**
     * @var int
     */
    public $day;

    /**
     * @param string $date YYYY-MM-DD
     */
    public function __construct($date = null)
    {
        if (!is_string($date)) {
            $date = strval($date);
        }
        if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)$/u', $date, $matches)) {
            $this->year = intval($matches[1], 10);
            $this->month = intval($matches[2], 10);
            $this->day = intval($matches[3], 10);
        } else {
            $this->year = 0;
            $this->month = 0;
            $this->day = 0;
        }
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return static
     */
    public static function fromYmd($year, $month, $day)
    {
        $date = new static();
        $date->year = intval($year, 10);
        $date->month = intval($month, 10);
        $date->day = intval($day, 10);
        return $date;
    }

    /**
     * @return string YYYY-MM-DD
     */
    public function __toString()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    /**
     * @return bool true if valid date, else false
     * @see checkdate()
     */
    public function check()
    {
        return checkdate($this->month, $this->day, $this->year);
    }
}
