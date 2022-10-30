<?php

namespace LdH\Repository;

abstract class AbstractRepository extends \APP_DbObject
{
    protected string $table;

    public function query(string $sql)
    {
        return $this->DBQuery($sql);
    }

    public function selectAll(string $sql): array
    {
        return $this->getCollectionFromDb($sql);
    }

    public function selectAsObject(string $sql): array
    {
        return $this->getObjectListFromDB($sql);
    }

    public function getLastId(): int
    {
        return $this->DbGetLastId();
    }
}