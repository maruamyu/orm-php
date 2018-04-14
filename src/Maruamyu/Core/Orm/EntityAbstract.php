<?php

namespace Maruamyu\Core\Orm;

/**
 * ORM Entity abstract class
 */
abstract class EntityAbstract implements EntityInterface
{
    /**
     * original values on bindFromFetchedRow()
     *
     * @var array
     */
    protected $_originalValues;

    /**
     * initialize.
     * record bind to property.
     *
     * @param array $record
     */
    public function __construct(array $record = null)
    {
        $this->_originalValues = [];
        if (!is_null($record)) {
            $this->bindFromFetchedRow($record);
        }
    }

    /**
     * @return string JSON
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * to Hash data
     *
     * @return array
     */
    public function toArray()
    {
        $converted = [];
        foreach (static::getColumnMap() as $columnName => $column) {
            $bindFrom = $column->getPropertyName();
            $converted[$columnName] = $this->$bindFrom;
        }
        return $converted;
    }

    /**
     * to JSON string
     *
     * @return string
     */
    public function toJson()
    {
        # not using toArray(), because need Date and DateTime to string
        $forJson = [];
        foreach (static::getColumnMap() as $columnName => $column) {
            $bindFrom = $column->getPropertyName();
            $value = $this->$bindFrom;
            if (is_null($value) == false) {
                switch ($column->getDataType()) {
                    case Column::DATA_TYPE_DATE:
                        /** @var Date $value */
                        $value = strval($value);
                        break;
                    case Column::DATA_TYPE_DATETIME:
                        /** @var \DateTime $value */
                        $value = $value->format(\DateTime::ATOM);
                        break;
                }
            }
            $forJson[$columnName] = $value;
        }
        return JsonCodec::encode($forJson);
    }

    /**
     * validate this instance
     *
     * @note default is check only `NOT NULL`. please override.
     * @return bool true if valid, else false
     */
    public function isValid()
    {
        $columnMap = static::getColumnMap();
        $autoIncrementColumnName = static::getAutoIncrementColumnName();
        foreach ($columnMap as $name => $column) {
            if ($name === $autoIncrementColumnName) {
                continue;
            }
            if ($column->isRequired()) {
                $propertyName = $column->getPropertyName();
                if (is_null($this->$propertyName)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool true if has original values, else false
     */
    public function hasOriginalValues()
    {
        # = is not empty
        return !(empty($this->_originalValues));
    }

    /**
     * bind to this instance from record
     *
     * @param array $fetchedRow record
     */
    protected function bindFromFetchedRow(array $fetchedRow)
    {
        $tableName = static::getTableName();
        $columnMap = static::getColumnMap();
        foreach ($columnMap as $name => $column) {
            $key = $tableName . '.' . $name;
            if (array_key_exists($key, $fetchedRow) == false) {
                $key = $name;
            }
            $value = null;
            if (isset($fetchedRow[$key])) {
                $value = static::convertFetchedValue($fetchedRow[$key], $column->getDataType());
            }
            $bindTo = $column->getPropertyName();
            $this->$bindTo = $value;
            $this->_originalValues[$bindTo] = $value;
        }
    }

    /**
     * @return bool true if modified, else false
     */
    public function isModified()
    {
        foreach (array_keys(static::getColumnMap()) as $columnName) {
            if ($this->columnIsModified($columnName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $columnName
     * @return bool true if modified, else false
     */
    public function columnIsModified($columnName)
    {
        $column = static::getColumn($columnName);
        $bindTo = $column->getPropertyName();

        if (isset($this->_originalValues[$bindTo]) == false) {
            # original is null. current is not null?
            return !(is_null($this->$bindTo));
        }

        # original is not null. current is null?
        if (is_null($this->$bindTo)) {
            return true;
        }

        switch ($column->getDataType()) {
            case Column::DATA_TYPE_DECIMAL:
            case Column::DATA_TYPE_DATE:
                # string vs string
                return (strval($this->$bindTo) !== strval($this->_originalValues[$bindTo]));

            case Column::DATA_TYPE_DATETIME:
                # \DateTime compare
                return ($this->$bindTo != $this->_originalValues[$bindTo]);

            case Column::DATA_TYPE_JSON:
                # JSON string vs JSON string
                return (JsonCodec::encode($this->$bindTo) !== JsonCodec::encode($this->_originalValues[$bindTo]));

            case Column::DATA_TYPE_SERIALIZE:
                # serialized string vs serialized string
                return (serialize($this->$bindTo) !== serialize($this->_originalValues[$bindTo]));

            default:
                # typed compare
                return ($this->$bindTo !== $this->_originalValues[$bindTo]);
        }
    }

    /**
     * @param string $columnName
     * @return Column
     * @throws \RuntimeException if invalid $columnName
     */
    protected static function getColumn($columnName)
    {
        $columnMap = static::getColumnMap();
        if (isset($columnMap[$columnName])) {
            return $columnMap[$columnName];
        } else {
            $errorMsg = 'invalid $columnName=' . $columnName;
            throw new \RuntimeException($errorMsg);
        }
    }

    /**
     * convert to PHP's data type from fetched value
     *
     * @param string $value fetched value
     * @param int $dataType internal data type value (const of this class)
     * @return mixed converted value
     */
    protected static function convertFetchedValue($value, $dataType = null)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($dataType) {
            case Column::DATA_TYPE_BOOL:
                return !!($value);  # 0 or 1 -> false or true

            case Column::DATA_TYPE_INT:
                return intval($value, 10);

            case Column::DATA_TYPE_FLOAT:
                return floatval($value);

            case Column::DATA_TYPE_DATE:
                return new Date($value);

            case Column::DATA_TYPE_DATETIME:
                return new \DateTime($value);

            case Column::DATA_TYPE_JSON:
                return JsonCodec::decode($value);

            case Column::DATA_TYPE_SERIALIZE:
                return unserialize($value);

            case Column::DATA_TYPE_STRING:
            case Column::DATA_TYPE_DECIMAL:
            default:
                return $value;
        }
    }
}
