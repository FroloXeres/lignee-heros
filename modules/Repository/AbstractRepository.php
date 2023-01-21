<?php

namespace LdH\Repository;

use ReflectionException;

abstract class AbstractRepository extends \APP_DbObject
{
    protected string $class;
    protected string $table = '';
    protected array $keys = [];

    /** @var array<string, Field> */
    protected array $mappedFields = [];

    /**
     * @throws ReflectionException
     */
    public function __construct(string $class)
    {
        $this->class = $class;
        $this->initMetadata($class);
    }

    /**
     * @throws ReflectionException
     */
    protected function initMetadata(string $class): void
    {
        $reflexion = new \ReflectionClass($class);

        $this->parseClassDoc($reflexion->getDocComment());

        foreach ($reflexion->getProperties() as $property) {
            $field = $this->buildField($reflexion, $property);
            if ($field !== null) {
                $this->mappedFields[$field->name] = $field;
            }
        }
    }

    protected function parseClassDoc(string $doc): void
    {
        $annotations = [];
        preg_match('#@table="(.*?)"#s', $doc, $annotations);
        if (isset($annotations[1])) {
            $this->table = $annotations[1];
        }
    }

    protected function buildField(\ReflectionClass $class, \ReflectionProperty $property): ?Field
    {
        $annotations = [];
        $isKey = false;
        $column = $entityKey = $enum = null;

        preg_match('#@isKey#s', $property->getDocComment(), $annotations);
        if (isset($annotations[0])) {
            $isKey = true;
        }

        preg_match('#@column="(.*?)"#s', $property->getDocComment(), $annotations);
        if (isset($annotations[1])) {
            $column = $annotations[1];
        }

        preg_match('#@entityKey="(.*?)"#s', $property->getDocComment(), $annotations);
        if (isset($annotations[1])) {
            $entityKey = $annotations[1];
        }

        preg_match('#@enum="(.*?)"#s', $property->getDocComment(), $annotations);
        if (isset($annotations[1]) && $class->hasConstant($annotations[1])) {
            $enum = $class->getConstant($annotations[1]);
        }

        if ($column !== null) {
            $field = new Field();
            $field->isKey = $isKey;
            $field->name = $property->getName();
            $field->type = $property->getType() ? $property->getType()->getName() : null;
            $field->dbName = $column;
            $field->entityKey = $entityKey;
            $field->enum = $enum;

            if ($isKey) {
                $this->keys[]  = $property->getName();
            }

            return $field;
        }

        return null;
    }

    /** @return array<string> */
    protected function getFieldNames(array $filters = [], array $excludes = []): array
    {
        $fieldNames = array_keys($this->mappedFields);
        $filters    = !empty($filters) ? array_intersect($filters, $fieldNames) : $fieldNames;

        // todo: Use excludes

        return array_map([$this, 'protectFieldName'], $filters);
    }

    protected function protectFieldName(string $fieldName): string
    {
        return '`'.$this->mappedFields[$fieldName]->dbName.'`';
    }

    /**
     * @return array<string>
     *
     * @throws ReflectionException
     */
    protected function getFieldValues($object, array $filters = []): array
    {
        $filters = empty($filters) ? array_keys($this->mappedFields) : $filters;
        $values = [];

        $reflect = new \ReflectionObject($object);
        foreach ($filters as $fieldName) {
            if (array_key_exists($fieldName, $filters)) continue;

            $values[] = $this->getFieldValue($reflect->getProperty($fieldName), $this->mappedFields[$fieldName], $object);
        }

        return $values;
    }

    protected function getFieldValue(\ReflectionProperty $property, Field $field, $object): string
    {
        $property->setAccessible(true);
        $propertyValue = $property->getValue($object);

        switch ($field->type) {
            case null :
                $value = 'NULL';
                break;
            case 'int' :
                $value = $propertyValue !== null ? (string) $propertyValue : 'NULL';
                break;
            case 'float' :
                $value = $propertyValue !== null ? number_format($propertyValue, 2, '.', '') : 'NULL' ;
                break;
            case 'bool' :
                $value = $propertyValue === null ? 'NULL' : ($propertyValue ? '1' : '0');
                break;
            case 'string' :
                if ($field->enum !== null && !isset($field->enum[$propertyValue])) {
                    throw new \InvalidArgumentException('Try to set a forbidden value for ' . $property->getName());
                }

                $value = $propertyValue !== null ? "'".self::escapeStringForDB($propertyValue)."'" : 'NULL';
                break;
            default :
                if ($field->entityKey && $propertyValue) {
                    $reflexionEntity = new \ReflectionObject($propertyValue);
                    $key = $reflexionEntity->getProperty($field->entityKey);

                    // todo: Protected this key value (Get repository for it)
                    $key->setAccessible(true);
                    $keyValue = $key->getValue($propertyValue);
                    $value = $keyValue !== null ? "'".self::escapeStringForDB($keyValue)."'" : 'NULL';
                } else {
                    $value = 'NULL';
                }
                break;
        }

        return $value;
    }

    protected function getKeysForQuery($entity): string
    {
        $keys = [];
        foreach ($this->keys as $key) {
            $keys[] = sprintf(
                '%s = %s',
                $this->getFieldNames([$key])[0],
                $this->getFieldValues($entity, [$key])[0]
            );
        }

        return join(' AND ', $keys);
    }

    public function update($entity, array $filters = []): void
    {
        $updates = [];
        $fields = $this->getFieldNames($filters, $this->keys);
        $values = $this->getFieldValues($entity, $filters);
        foreach ($fields as $key => $field) {
            if ($field) {
                $updates[] = sprintf('%s = %s', $field, $values[$key]);
            }
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s',
            $this->table,
            join(', ', $updates),
            $this->getKeysForQuery($entity),
        );

        $this->query($sql);
    }

    /**
     * Execute update/delete queries
     */
    public function query(string $sql): array
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