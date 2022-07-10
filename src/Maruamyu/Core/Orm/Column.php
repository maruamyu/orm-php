<?php

namespace Maruamyu\Core\Orm;

/**
 * ORM column config class
 */
class Column
{
    /** @var integer options bit value : skip on INSERT */
    const OPTIONS_SKIP_ON_INSERT = 1;

    /** @var integer options bit value : skip on UPDATE */
    const OPTIONS_SKIP_ON_UPDATE = 2;

    /** @var integer options bit value : skip on INSERT or UPDATE */
    const OPTIONS_SKIP_ON_INSERT_OR_UPDATE = 3;


    /**
     * data type: boolean
     */
    const DATA_TYPE_BOOL = 1;

    /**
     * data type: integer
     *
     * @note warning: If you are dealing with large numbers that overflow PHP's integer,
     *   please use DATA_TYPE_DECIMAL (string).
     */
    const DATA_TYPE_INT = 2;

    /**
     * data type: float
     *
     * @note warning: float data type has rounding error.
     *   If you are dealing with sensitive values,
     *   please use DATA_TYPE_DECIMAL (string).
     */
    const DATA_TYPE_FLOAT = 3;

    /**
     * data type: decimal
     *
     * Process with string type.
     */
    const DATA_TYPE_DECIMAL = 4;

    /**
     * data type: date
     *
     * @see Date
     */
    const DATA_TYPE_DATE = 11;

    /**
     * data type: datetime
     *
     * @see \DateTimeInterface
     */
    const DATA_TYPE_DATETIME = 12;

    /**
     * data type: string
     */
    const DATA_TYPE_STRING = 21;

    /**
     * data type: JSON
     *
     * JsonCodec::encode() when INSERT or UPDATE
     * JsonCodec::decode() when EntityAbstract::bindFromFetchedRow()
     */
    const DATA_TYPE_JSON = 31;

    /**
     * data type: PHP serialize
     *
     * serialize() when INSERT or UPDATE
     * unserialize() when EntityAbstract::bindFromFetchedRow()
     */
    const DATA_TYPE_SERIALIZE = 32;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $dataType;

    /**
     * @var bool
     */
    protected $isRequired;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var int
     */
    protected $optionsValue;

    /**
     * @param string $name column name
     * @param int $dataType data type (const of this class)
     * @param bool $isRequired true if required column
     * @param string $propertyName bind to property name
     * @param integer $optionsValue
     */
    public function __construct($name, $dataType = self::DATA_TYPE_STRING, $isRequired = false, $propertyName = null, $optionsValue = 0)
    {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->isRequired = !!($isRequired);
        $this->propertyName = $propertyName;
        $this->optionsValue = $optionsValue;
    }

    /**
     * @return string column name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int data type value of const
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return bool true if `NOT NULL`
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * @return string binding object property name
     */
    public function getPropertyName()
    {
        if (is_null($this->propertyName)) {
            return $this->name;
        } else {
            return $this->propertyName;
        }
    }

    /**
     * @return bool true if skip on INSERT
     */
    public function isSkipOnInsert()
    {
        return !!($this->optionsValue & static::OPTIONS_SKIP_ON_INSERT);
    }

    /**
     * @return bool true if skip on UPDATE
     */
    public function isSkipOnUpdate()
    {
        return !!($this->optionsValue & static::OPTIONS_SKIP_ON_UPDATE);
    }
}
