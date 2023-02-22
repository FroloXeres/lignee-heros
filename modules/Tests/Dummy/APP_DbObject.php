<?php

class APP_DbObject {
    public function DBQuery(string $sql): bool {
        return true;
    }

    public function getCollectionFromDb(string $sql): array {
        return [];
    }

    public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array {
        return [];
    }

    public function DbGetLastId(): int
    {
        return 0;
    }

    public function escapeStringForDB(string $value): string
    {
        return $value;
    }
}