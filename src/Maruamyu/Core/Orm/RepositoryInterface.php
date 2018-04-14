<?php

namespace Maruamyu\Core\Orm;

/**
 * ORM Repository abstract class interface
 */
interface RepositoryInterface
{
    /**
     * initialize repository
     *
     * @param \PDO $PDOHandler PDO handler
     */
    public function __construct(\PDO $PDOHandler);

    /**
     * return PDO handler
     *
     * @return \PDO PDO handler
     */
    public function getPDO();

    /**
     * prepare PDO statement
     *
     * @param string $sql SQL
     * @return \PDOStatement PDOStatement
     */
    public function prepare($sql);

    /**
     * execute INSERT
     *
     * @param EntityInterface $entity entity
     * @return EntityInterface inserted entity
     * @throws \RuntimeException if failed
     */
    public function insert(EntityInterface $entity);

    /**
     * execute bulk INSERT
     *
     * @param EntityInterface[] $entities entities
     * @return int inserted rows count
     * @throws \RuntimeException if failed
     */
    public function bulkInsert(array $entities);

    /**
     * execute UPDATE
     *
     * @param EntityInterface $entity enitty
     * @return int updated rows count
     * @throws \RuntimeException if failed
     */
    public function update(EntityInterface $entity);

    /**
     * execute DELETE
     *
     * @param EntityInterface $entity entity
     * @return int deleted rows count
     * @throws \RuntimeException if failed
     */
    public function delete(EntityInterface $entity);

    /**
     * @return string entity class name
     */
    public static function getEntityClass();
}
