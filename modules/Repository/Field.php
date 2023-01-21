<?php

namespace LdH\Repository;

class Field
{
    public bool $isKey = false;
    public string $name;
    public string $type;
    public string $dbName;
    public ?string $entityKey;
    public ?array $enum;
}