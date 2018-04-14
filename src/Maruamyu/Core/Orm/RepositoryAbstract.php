<?php

namespace Maruamyu\Core\Orm;

/**
 * ORM Repository abstract class
 */
abstract class RepositoryAbstract implements RepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * initialize repository
     *
     * @param \PDO $pdo PDO handler
     */
    public function __construct(\PDO $pdo)
    {
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
        $this->pdo = $pdo;
    }

    /**
     * return PDO handler
     *
     * @return \PDO PDO handler
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * execute SQL query
     *
     * @param string $query SQL
     * @return \PDOStatement PDOStatement
     */
    public function executeQuery($query)
    {
        return $this->pdo->query($query);
    }

    /**
     * prepare PDO statement
     *
     * @param string $sql SQL
     * @return \PDOStatement PDOStatement
     */
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * begin transaction.
     *
     * @return bool true if succeeded, else false
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * in transaction?
     *
     * @return bool true if in transaction, else false
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * commit transaction.
     *
     * @return bool true if succeeded, else false
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * rollback transaction.
     *
     * @return bool true if succeeded, else false
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    /**
     * execute INSERT
     *
     * @param EntityInterface $entity entity
     * @return EntityInterface inserted entity
     * @throws \RuntimeException if failed
     */
    public function insert(EntityInterface $entity)
    {
        static $statement = null;
        if (!$statement) {
            $query = static::buildInsertQuery($entity);
            $statement = $this->prepare($query);
        }

        $columnMap = $entity::getColumnMap();
        $autoIncrementColumnName = $entity::getAutoIncrementColumnName();
        foreach ($columnMap as $columnName => $column) {
            if ($columnName === $autoIncrementColumnName) {
                continue;
            }
            $bindFrom = $column->getPropertyName();
            $dataType = $column->getDataType();
            $succeeded = $statement->bindValue($columnName,
                static::convertToPDOBindValue($entity->$bindFrom, $dataType),
                static::getPDODataType($dataType));
            if (!$succeeded) {
                $errorMsg = 'bindValue failed. ($columnName=' . $columnName . ')';
                throw new \RuntimeException($errorMsg);
            }
        }
        $succeeded = $statement->execute();
        if (!$succeeded) {
            $errorMsg = 'insert failed. ($entity = ' . $entity . ')';
            throw new \RuntimeException($errorMsg);
        }
        if (strlen($autoIncrementColumnName) > 0) {
            $entity->$autoIncrementColumnName = $this->getPDO()->lastInsertId();
        }
        return $entity;
    }

    /**
     * build INSERT query
     *
     * @param EntityInterface $entity entity
     * @return string query
     */
    protected static function buildInsertQuery(EntityInterface $entity)
    {
        $columnMap = $entity::getColumnMap();
        $autoIncrementColumnName = $entity::getAutoIncrementColumnName();
        $sqlColumnNameList = [];
        $sqlBindTokenList = [];
        foreach ($columnMap as $columnName => $column) {
            if ($columnName === $autoIncrementColumnName) {
                continue;
            }
            $sqlColumnNameList[] = '`' . $columnName . '`';
            $sqlBindTokenList[] = ':' . $columnName;
        }
        return 'INSERT INTO `' . $entity::getTableName() . '`'
            . ' (' . join(', ', $sqlColumnNameList) . ')'
            . ' VALUES (' . join(', ', $sqlBindTokenList) . ')';
    }

    /**
     * execute bulk INSERT
     *
     * @param EntityInterface[] $entities
     * @return int inserted rows count
     * @throws \RuntimeException if failed
     */
    public function bulkInsert(array $entities)
    {
        if (empty($entities)) {
            return 0;
        }
        $entities = array_values($entities);  # assoc -> array
        $query = static::buildBulkInsertQuery($entities[0], count($entities));
        $statement = $this->prepare($query);
        $columnMap = $entities[0]::getColumnMap();
        $autoIncrementColumnName = $entities[0]::getAutoIncrementColumnName();
        foreach ($entities as $idx => $entity) {
            foreach ($columnMap as $columnName => $column) {
                if ($columnName === $autoIncrementColumnName) {
                    continue;
                }
                $bindFrom = $column->getPropertyName();
                $dataType = $column->getDataType();
                $succeeded = $statement->bindValue($columnName . '_' . $idx,
                    static::convertToPDOBindValue($entity->$bindFrom, $dataType),
                    static::getPDODataType($dataType));
                if (!$succeeded) {
                    $errorMsg = 'bindValue failed. ($idx=' . $idx . ', $columnName=' . $columnName . ')';
                    throw new \RuntimeException($errorMsg);
                }
            }
        }
        $succeeded = $statement->execute();
        if (!$succeeded) {
            $errorMsg = 'bulk insert failed.';
            throw new \RuntimeException($errorMsg);
        }
        return $statement->rowCount();
    }

    /**
     * build INSERT query
     *
     * @param EntityInterface $entity entity
     * @param int $count
     * @return string query
     */
    protected static function buildBulkInsertQuery(EntityInterface $entity, $count)
    {
        $columnMap = $entity::getColumnMap();
        $autoIncrementColumnName = $entity::getAutoIncrementColumnName();
        $columnNameList = [];
        $sqlColumnNameList = [];
        foreach ($columnMap as $columnName => $column) {
            if ($columnName === $autoIncrementColumnName) {
                continue;
            }
            $columnNameList[] = $columnName;
            $sqlColumnNameList[] = '`' . $columnName . '`';
        }
        $sqlPlaceHolderLines = [];
        for ($idx = 0; $idx < $count; $idx++) {
            $sqlBindTokenList = [];
            foreach ($columnNameList as $columnName) {
                $sqlBindTokenList[] = ':' . $columnName . '_' . $idx;
            }
            $sqlPlaceHolderLines[] = '(' . join(', ', $sqlBindTokenList) . ')';
        }
        return 'INSERT INTO `' . $entity::getTableName() . '`'
            . ' (' . join(', ', $sqlColumnNameList) . ')'
            . ' VALUES ' . join(', ', $sqlPlaceHolderLines);
    }

    /**
     * execute UPDATE
     *
     * @param EntityInterface $entity enitty
     * @return int updated rows count
     * @throws \RuntimeException if failed
     */
    public function update(EntityInterface $entity)
    {
        $columnMap = $entity::getColumnMap();
        $primaryKeyWhereList = [];
        $isPrimaryKeyColumnName = [];
        foreach ($entity::getPrimaryKeyColumnNames() as $columnName) {
            $isPrimaryKeyColumnName[$columnName] = true;
            $primaryKeyWhereList[] = '(`' . $columnName . '` = :' . $columnName . ')';
        }
        $isUpdatedColumnName = [];
        $sqlUpdateColumnList = [];
        foreach ($columnMap as $columnName => $column) {
            if (isset($isPrimaryKeyColumnName[$columnName])) {
                continue;
            }
            if ($entity->columnIsModified($columnName)) {
                $isUpdatedColumnName[$columnName] = true;
                $sqlUpdateColumnList[] = '`' . $columnName . '` = :' . $columnName . '';
            }
        }
        if (empty($isUpdatedColumnName)) {
            return 0;
        }
        $query = 'UPDATE `' . $entity::getTableName() . '`'
            . ' SET ' . join(', ', $sqlUpdateColumnList)
            . ' WHERE ' . join(' AND ', $primaryKeyWhereList);
        $statement = $this->prepare($query);
        foreach ($columnMap as $columnName => $column) {
            if (
                (isset($isPrimaryKeyColumnName[$columnName]) == false)
                && (isset($isUpdatedColumnName[$columnName]) == false)
            ) {
                continue;
            }
            $bindFrom = $column->getPropertyName();
            $dataType = $column->getDataType();
            $succeeded = $statement->bindValue($columnName,
                static::convertToPDOBindValue($entity->$bindFrom, $dataType),
                static::getPDODataType($dataType));
            if (!$succeeded) {
                $errorMsg = 'bindValue failed. ($columnName=' . $columnName . ')';
                throw new \RuntimeException($errorMsg);
            }
        }
        $succeeded = $statement->execute();
        if (!$succeeded) {
            $errorMsg = 'update failed. ($entity = ' . $entity . ')';
            throw new \RuntimeException($errorMsg);
        }
        return $statement->rowCount();
    }

    /**
     * execute DELETE
     *
     * @param EntityInterface $entity entity
     * @return int deleted rows count
     * @throws \RuntimeException if failed
     */
    public function delete(EntityInterface $entity)
    {
        static $statement = null;
        if (!$statement) {
            $query = $this->buildDeleteQuery($entity);
            $statement = $this->prepare($query);
        }
        $columnMap = $entity::getColumnMap();
        foreach ($entity::getPrimaryKeyColumnNames() as $columnName) {
            $column = $columnMap[$columnName];
            $bindFrom = $column->getPropertyName();
            if (is_null($entity->$bindFrom)) {
                $errorMsg = 'primary key is null. ($columnName=' . $columnName . ')';
                throw new \RuntimeException($errorMsg);
            }
            $dataType = $column->getDataType();
            $succeeded = $statement->bindValue($columnName,
                static::convertToPDOBindValue($entity->$bindFrom, $dataType),
                static::getPDODataType($dataType));
            if (!$succeeded) {
                $errorMsg = 'bindValue failed. ($columnName=' . $columnName . ')';
                throw new \RuntimeException($errorMsg);
            }
        }
        $succeeded = $statement->execute();
        if (!$succeeded) {
            $errorMsg = 'delete failed. ($entity = ' . $entity . ')';
            throw new \RuntimeException($errorMsg);
        }
        return $statement->rowCount();
    }

    /**
     * build DELETE query
     *
     * @param EntityInterface $entity entity
     * @return string query
     */
    protected static function buildDeleteQuery(EntityInterface $entity)
    {
        $sqlPrimaryKeyWhereList = [];
        foreach ($entity::getPrimaryKeyColumnNames() as $columnName) {
            $sqlPrimaryKeyWhereList[] = '(`' . $columnName . '` = :' . $columnName . ')';
        }
        return 'DELETE FROM `' . $entity::getTableName() . '`'
            . ' WHERE ' . join(' AND ', $sqlPrimaryKeyWhereList)
            . ' LIMIT 1';
    }

    /**
     * convert to PDO bindValue value from PHP's data type value
     *
     * @param mixed $value PHP value
     * @param int $dataType internal data type value (see Column)
     * @return mixed for bindValue
     * @see Column
     * @see \PDOStatement::bindValue()
     */
    protected static function convertToPDOBindValue($value, $dataType = null)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($dataType) {
            case Column::DATA_TYPE_DATE:
                return strval($value);

            case Column::DATA_TYPE_DATETIME:
                return $value->format('Y-m-d H:i:s');

            case Column::DATA_TYPE_JSON:
                return JsonCodec::encode($value);

            case Column::DATA_TYPE_SERIALIZE:
                return serialize($value);

            default:
                return $value;
        }
    }

    /**
     * convert to PHP's data type from fetched value
     *
     * @param int $dataType internal data type value (see Column)
     * @return int PDO bind value data type (\PDO::PARAM_*)
     * @see Column
     * @see \PDOStatement::bindValue()
     */
    protected static function getPDODataType($dataType)
    {
        switch ($dataType) {
            case Column::DATA_TYPE_BOOL:
                return \PDO::PARAM_BOOL;

            case Column::DATA_TYPE_INT:
            case Column::DATA_TYPE_FLOAT:
                return \PDO::PARAM_INT;

            case Column::DATA_TYPE_STRING:
            case Column::DATA_TYPE_DECIMAL:
            case Column::DATA_TYPE_DATE:
            case Column::DATA_TYPE_DATETIME:
            case Column::DATA_TYPE_JSON:
            case Column::DATA_TYPE_SERIALIZE:
            default:
                return \PDO::PARAM_STR;
        }
    }
}
