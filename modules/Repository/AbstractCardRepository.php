<?php

namespace LdH\Repository;

use DateTime;
use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use ReflectionException;

abstract class AbstractCardRepository extends AbstractRepository
{
    protected ?string $entityLinked = null;

    /** @var array<string, Field> */
    protected array $boardCardFields = [];

    /**
     * @throws ReflectionException
     */
    protected function initMetadata(string $class): void
    {
        parent::initMetadata($class);

        if ($this->entityLinked) {
            $linkedReflexion = new \ReflectionClass($this->entityLinked);

            foreach ($linkedReflexion->getProperties() as $property) {
                $field = $this->buildField($linkedReflexion, $property);
                if ($field !== null) {
                    $this->boardCardFields[$field->name] = $field;
                }
            }
        }
    }

    protected function parseClassDoc(string $doc): void
    {
        parent::parseClassDoc($doc);

        preg_match('#@entityLinked="(.*?)"#s', $doc, $annotations);
        if (isset($annotations[1])) {
            $this->entityLinked = $annotations[1];
        }
    }

    /** @return array<string> */
    protected function getFieldNames(array $filters = [], array $excludes = []): array
    {
        $fieldNames = array_merge(
            array_keys($this->mappedFields),
            array_keys($this->boardCardFields)
        );
        $filters    = !empty($filters) ? array_intersect($filters, $fieldNames) : $fieldNames;

        // todo: Use excludes

        return array_map([$this, 'protectFieldName'], $filters);
    }

   protected function protectFieldName(string $fieldName): string
    {
        return array_key_exists($fieldName, $this->boardCardFields) ?
            '`'.$this->boardCardFields[$fieldName]->dbName.'`' :
            '`'.$this->mappedFields[$fieldName]->dbName.'`'
        ;
    }

    /** @return array<string> */
    protected function getBoardFieldNames(array $filters = [], array $excludes = []): array
    {
        $fieldNames = array_keys($this->boardCardFields);
        $filters    = !empty($filters) ? array_intersect($filters, $fieldNames) : $fieldNames;

        // todo: Use excludes
        foreach ($excludes as $exclude) {
            if (($found = array_search($exclude, $filters)) !== false) {
                unset($filters[$found]);
            }
        }

        return array_map([$this, 'protectFieldName'], $filters);
    }

    protected function getBoardFieldValues(BoardCardInterface $object, array $filters = []): array
    {
        $filters = empty($filters) ? array_keys($this->boardCardFields) : $filters;
        $values = [];

        $reflect = new \ReflectionObject($object);
        foreach ($filters as $fieldName) {
            if (array_key_exists($fieldName, $filters))
                continue;

            $values[] = $this->getFieldValue($reflect->getProperty($fieldName), $this->boardCardFields[$fieldName], $object);
        }

        return $values;
    }

    /**
     * @return array<string>
     *
     * @throws ReflectionException
     */
    protected function getFieldValues($object, array $filters = []): array
    {
        $filters = empty($filters) ?
            array_merge(
                array_keys($this->mappedFields),
                array_keys($this->boardCardFields)
            ) :
            $filters
        ;
        $values = [];

        $reflect = new \ReflectionObject($object);
        foreach ($filters as $fieldName) {
            if (array_key_exists($fieldName, $this->keys)) continue;

            $field = array_key_exists($fieldName, $this->mappedFields) ? $this->mappedFields[$fieldName] : $this->boardCardFields[$fieldName];
            $values[] = $this->getFieldValue($reflect->getProperty($fieldName), $field, $object);
        }

        return $values;
    }

    public function updateAllCards(AbstractCard $entity, array $filters = []): void
    {
        $fields = $this->getBoardFieldNames($filters, ['id']);

        foreach ($entity->getBoardCards() as $boardCard) {
            $updates = [];
            $values = $this->getBoardFieldValues($boardCard);
            foreach ($fields as $key => $field) {
                if ($field) {
                    $updates[] = sprintf('%s = %s', $field, $values[$key]);
                }
            }

            $sql = sprintf('UPDATE `%s` SET %s WHERE `%s` = %s',
                $this->table,
                join(', ', $updates),
                self::CARD_UNIQ_ID,
                $boardCard->getId(),
            );
            $this->query($sql);
        }
    }

    /**
     * @var array<string, AbstractCard> $cards
     * @throws ReflectionException
     */
    public function updateCardsFromDb(array $cards): void
    {
        $sql = sprintf(
            'SELECT %s, %s FROM `%s`',
            join(', ', $this->getFieldNames($this->keys)),
            join(', ', $this->getBoardFieldNames()),
            $this->table,
        );
        $dbCards = $this->getObjectListFromDB($sql);

        $indexed = [];
        foreach ($dbCards as $dbCard) {
            $ref = &$indexed;

            foreach ($this->keys as $key) {
                $index = $this->mappedFields[$key]->dbName;
                $dbIndex = $dbCard[$index];

                if (!array_key_exists($dbIndex, $ref)) {
                    $ref[$dbIndex] = [];
                }
                $ref = &$ref[$dbIndex];
            }

            $ref[] = $dbCard;
        }

        foreach ($cards as $card) {
            $boardCardType = $card::getBoardCardClassByCard();
            $card->setBoardCards([]);

            $ref = &$indexed;
            foreach ($this->keys as $key) {
                $reflexion = new \ReflectionObject($card);
                $keyProd = $reflexion->getProperty($key);
                $keyProd->setAccessible(true);

                $ref = &$ref[$keyProd->getValue($card)];
            }

            foreach ($ref as $dbCard) {
                $card->addBoardCard($this->buildBoardCard(
                    $boardCardType,
                    $dbCard
                ));
            }
        }
    }

    /** @param array<AbstractCard> $cards */
    public function moveCardsTo(
        array $cards,
        string $location = BoardCardInterface::LOCATION_DEFAULT,
        int $locationArg = BoardCardInterface::LOCATION_ARG_DEFAULT
    ): bool {
        foreach ($cards as $card) {
            $card->moveCardsTo($location, $locationArg);
        }

        $sql = sprintf(
            'UPDATE `%s` SET `card_location` = "%s", `card_location_arg` = %s WHERE `%s` IN (%s)',
            $this->table,
            $location,
            $locationArg,
            self::CARD_UNIQ_ID,
            join(', ', $this->getCardIds($cards))
        );
        return $this->DBQuery($sql);
    }

    /**
     * @param array<AbstractCard> $cards
     *
     * @return array<int>
     */
    public function getCardIds(array $cards): array
    {
        $cardIds = [];
        foreach ($cards as $card) {
            foreach ($card->getBoardCards() as $boardCard) {
                $cardIds[] = $boardCard->getId();
            }
        }

        return $cardIds;
    }

    public function updateCardFromDb(AbstractCard $card)
    {
        $sql = sprintf(
            'SELECT %s FROM `%s` WHERE %s',
            join(', ', $this->getBoardFieldNames()),
            $this->table,
            $this->getKeysForQuery($card),
        );
        $dbCard = $this->getObjectListFromDB($sql);

        $boardCardType = $card::getBoardCardClassByCard();
        $card->setBoardCards([
             $this->buildBoardCard(
                 $boardCardType,
                 $dbCard[0]
             )
        ]);
    }

    protected function buildBoardCard(string $boardCardType, array $boardData): BoardCardInterface
    {
        /** @var BoardCardInterface $boardCard */
        $boardCard = $boardCardType::buildBoardCard();

        $boardCardReflexion = new \ReflectionObject($boardCard);
        foreach ($this->boardCardFields as $boardCardField) {
            $property = $boardCardReflexion->getProperty($boardCardField->name);
            $property->setAccessible(true);
            $property->setValue($boardCard, $boardData[$boardCardField->dbName]);
        }

        return $boardCard;
    }

    // Method for history
    protected function historize(string $sql)
    {
        $this->history[date('Y-m-d H:i:s')] = $sql;
    }

    /** @return array<string, string> */
    public function getHistory(): array
    {
        return $this->history;
    }

    public function getLastQuery(): string
    {
        return end($this->history);
    }
}