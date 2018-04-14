<?php

namespace Maruamyu\Core\Orm;

/**
 * ORM Entity class interface
 */
interface EntityInterface
{
    /**
     * initialize.
     * record bind to property.
     *
     * @param array $record
     */
    public function __construct(array $record = null);

    /**
     * @return string
     */
    public function __toString();

    /**
     * validate this instance
     *
     * @return bool true if valid, else false
     */
    public function isValid();

    /**
     * @return bool true if has original values, else false
     */
    public function hasOriginalValues();

    /**
     * @return bool true if modified, else false
     */
    public function isModified();

    /**
     * @param string $columnName
     * @return bool true if modified, else false
     */
    public function columnIsModified($columnName);

    /**
     * return table name
     *
     * @return string table name
     */
    public static function getTableName();

    /**
     * return column metadata map ({column_name => Column instance})
     *
     * @return Column[] map of column
     */
    public static function getColumnMap();

    /**
     * return `AUTO_INCREMENT` column name
     *
     * @return string|null `AUTO_INCREMENT` column name, or null if not exist
     */
    public static function getAutoIncrementColumnName();

    /**
     * return list of PRIMARY KEY column names
     *
     * @return string[] list of PRIMARY KEY column names
     */
    public static function getPrimaryKeyColumnNames();
}
