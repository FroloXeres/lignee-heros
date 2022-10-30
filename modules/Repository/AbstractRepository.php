<?php

namespace LdH\Repository;

abstract class AbstractRepository extends \APP_DbObject
{
    protected string $table;

    /**
     * Execute update/delete queries
     */
    public function query(string $sql)
    {
        return $this->DBQuery($sql);
    }

    /**
     * Send back select result as indexed lines of columns data
     */
    public function selectAll(string $sql): array
    {
        return $this->getCollectionFromDb($sql);
    }

    /**
     * Send back select result as lines of columns data
     */
    public function selectAsObject(string $sql): array
    {
        return $this->getObjectListFromDB($sql);
    }

    public function getLastId(): int
    {
        return $this->DbGetLastId();
    }
}