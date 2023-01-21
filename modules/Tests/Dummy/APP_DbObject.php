<?php

class APP_DbObject {
    public function DBQuery(string $sql): array {
        echo 'Execute: ' . $sql;

        return [];
    }

    public function getCollectionFromDb(string $sql): array {
        echo 'Execute: ' . $sql;

        return [];
    }

    public function getObjectListFromDB(string $sql): array {
        echo 'Execute: ' . $sql;

        return [];
    }

    public function DbGetLastId(): int
    {
        return 0;
    }
}