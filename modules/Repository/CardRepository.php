<?php

namespace LdH\Repository;

class CardRepository extends AbstractCardRepository
{
    public function __construct(string $class)
    {
        parent::__construct($class);
    }

    public function getPeopleData(): array
    {
        return $this->selectAll(sprintf(
            'SELECT * FROM `%s` ORDER BY %s',
            $this->table,
            join(', ', $this->getFieldNames(['type', 'location']))
        ));
    }
}
