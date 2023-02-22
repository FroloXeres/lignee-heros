<?php

namespace LdH\Tests\Repository;

use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\DefaultBoardCard;
use LdH\Entity\Cards\Disease;
use LdH\Entity\Cards\Fight;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\InventionBoardCard;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\LineageBoardCard;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\ObjectiveBoardCard;
use LdH\Entity\Cards\Other;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Cards\SpellBoardCard;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\Tile;
use LdH\Entity\Meeple;
use LdH\Repository\AbstractCardRepository;
use LdH\Repository\AbstractRepository;
use LdH\Repository\Field;
use PHPUnit\Framework\TestCase;

require_once "./../Dummy/APP_DbObject.php";

class AbstractRepositoryTest extends TestCase
{
    protected AbstractRepository $repository;

    public function testMapRepositoryMetadata()
    {
        $reflexion = $this->initRepository(Tile::class);

        $classProp = $reflexion->getProperty('class');
        $classProp->setAccessible(true);
        $this->assertEquals(Tile::class, $classProp->getValue($this->repository));

        $tableProp = $reflexion->getProperty('table');
        $tableProp->setAccessible(true);
        $this->assertEquals('map', $tableProp->getValue($this->repository));

        $keysProp = $reflexion->getProperty('keys');
        $keysProp->setAccessible(true);
        $this->assertEquals(['id'], $keysProp->getValue($this->repository));

        $mappedProp = $reflexion->getProperty('mappedFields');
        $mappedProp->setAccessible(true);
        $mapped = $mappedProp->getValue($this->repository);
        $this->assertEquals(['id', 'x', 'y', 'howFar', 'flip', 'disabled', 'terrain'], array_keys($mapped));

        foreach ($mapped as $field) {
            $this->assertInstanceOf(Field::class, $field);
        }

        $this->assertEquals(
            ['tile_id', 'tile_x', 'tile_y', 'tile_far', 'tile_revealed', 'tile_disabled', 'tile_terrain'],
            $this->extractFields($mapped, 'dbName')
        );

        $this->assertEquals(
            ['int', 'int', 'int', 'int', 'bool', 'bool', Terrain::class],
            $this->extractFields($mapped, 'type')
        );

        $this->assertEquals('code', $mapped['terrain']->entityKey);
    }

    public function testCardRepositoryMetadata()
    {
        $cardClasses = [
            Lineage::class => [
                'table' => 'lineage',
                'mapped' => ['objectiveCompleted', 'leader'],
                'dbName' => ['card_completed', 'card_leader'],
                'type' => ['bool', 'bool']
            ],
            Objective::class => [
                'table' => 'objective',
                'mapped' => ['completed'],
                'dbName' => ['card_completed'],
                'type' => ['bool']
            ],
            Spell::class => [
                'table' => 'spell',
                'mapped' => ['activated'],
                'dbName' => ['card_activated'],
                'type' => ['bool']
            ],
            Invention::class => [
                'table' => 'invention',
                'mapped' => ['activated'],
                'dbName' => ['card_activated'],
                'type' => ['bool']
            ],
            Fight::class => [
                'table' => 'explore_fight',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
            Other::class => [
                'table' => 'explore_other',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
            Disease::class => [
                'table' => 'explore_disease',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
        ];

        foreach ($cardClasses as $cardClass => $testData) {
            $reflexion = $this->initRepository($cardClass);

            $classProp = $reflexion->getProperty('class');
            $classProp->setAccessible(true);
            $this->assertEquals($cardClass, $classProp->getValue($this->repository));

            $tableProp = $reflexion->getProperty('table');
            $tableProp->setAccessible(true);
            $this->assertEquals($testData['table'], $tableProp->getValue($this->repository));

            $keysProp = $reflexion->getProperty('keys');
            $keysProp->setAccessible(true);
            $this->assertEquals(['type', 'type_arg'], $keysProp->getValue($this->repository));

            $mappedProp = $reflexion->getProperty('mappedFields');
            $mappedProp->setAccessible(true);
            $mapped = $mappedProp->getValue($this->repository);
            $this->assertEquals(['type', 'type_arg'], array_keys($mapped));

            $boardProp = $reflexion->getProperty('boardCardFields');
            $boardProp->setAccessible(true);
            $boards = $boardProp->getValue($this->repository);
            $this->assertEquals(array_merge($testData['mapped'], ['id', 'location', 'location_arg']), array_keys($boards));

            foreach ($mapped as $field) {
                $this->assertInstanceOf(Field::class, $field);
            }
            foreach ($boards as $field) {
                $this->assertInstanceOf(Field::class, $field);
            }

            $this->assertEquals(
                ['card_type', 'card_type_arg'],
                $this->extractFields($mapped, 'dbName')
            );
            $this->assertEquals(
                array_merge($testData['dbName'], ['card_id', 'card_location', 'card_location_arg']),
                $this->extractFields($boards, 'dbName'),
            );

            $this->assertEquals(
                ['string', 'int'],
                $this->extractFields($mapped, 'type'),
            );
            $this->assertEquals(
                array_merge($testData['type'], ['int', 'string', 'int']),
                $this->extractFields($boards, 'type'),
            );
        }
    }

    public function testQueryBuilderUpdateTile()
    {
        $this->initRepository(Tile::class);
        $tile = new Tile(1, 0, 0, 0, false, true);
        $this->repository->update($tile, ['x', 'y', 'howFar', 'disabled', 'flip']);
        $this->assertEquals(
            'UPDATE `map` SET `tile_x` = 0, `tile_y` = 0, `tile_far` = 0, `tile_disabled` = 0, `tile_revealed` = 1 WHERE `tile_id` = 1',
            $this->repository->getLastQuery()
        );
    }

    public function testQueryBuilderUpdateObjective()
    {
        $this->initRepository(Objective::class);
        $objective = new Objective(Objective::RESEARCHER);
        $objective->setBoardCards([
          (new ObjectiveBoardCard())
              ->setId(1)
              ->setLocation(BoardCardInterface::LOCATION_HAND)
              ->setLocationArg(987654)
              ->setCompleted(true)
        ]);
        $this->repository->updateAllCards($objective);
        $this->assertEquals(
            sprintf(
                "UPDATE `objective` SET `card_completed` = 1, `card_location` = '%s', `card_location_arg` = %s WHERE `card_id` = %s",
                BoardCardInterface::LOCATION_HAND,
                987654,
                1
            ),
            $this->repository->getLastQuery()
        );
    }

    public function testQueryBuilderUpdateInvention()
    {
        $this->initRepository(Invention::class);
        $invention = new Invention(Invention::TYPE_GROWTH, Invention::CLOTHES);
        $invention->setBoardCards([
            (new InventionBoardCard())
                ->setId(1)
                ->setLocation(BoardCardInterface::LOCATION_DEFAULT)
                ->setLocationArg(BoardCardInterface::LOCATION_ARG_DEFAULT)
                ->setActivated(true)
        ]);
        $this->repository->updateAllCards($invention);
        $this->assertEquals(
            sprintf(
                "UPDATE `invention` SET `card_activated` = 1, `card_location` = '%s', `card_location_arg` = %s WHERE `card_id` = %s",
                BoardCardInterface::LOCATION_DEFAULT,
                BoardCardInterface::LOCATION_ARG_DEFAULT,
                1
            ),
            $this->repository->getLastQuery()
        );
    }

    public function testUpdateCardFromDbForSpell()
    {
        $this->repository = new class(Lineage::class) extends AbstractCardRepository {
            private int $index = 0;
            public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array {
                $values = [
                    ['card_completed' => "0", 'card_leader' => "0", 'card_id' => "1", 'card_location' => BoardCardInterface::LOCATION_DEFAULT, 'card_location_arg' => "0"],
                    ['card_completed' => "1", 'card_leader' => "1", 'card_id' => "2", 'card_location' => BoardCardInterface::LOCATION_HAND, 'card_location_arg' => "456789"],
                ];
                return $values[$this->index++];
            }
        };

        $lineage = (new Lineage(Meeple::HUMANI_MAGE));
        $this->repository->updateCardFromDb($lineage);

        $boardLineage = $lineage->getBoardCard(1);
        $this->assertEquals(1, $lineage->getCardCount());
        $this->assertInstanceOf(LineageBoardCard::class, $boardLineage);
        $this->assertEquals(false, $boardLineage->isObjectiveCompleted());
        $this->assertEquals(false, $boardLineage->isLeader());
        $this->assertEquals(BoardCardInterface::LOCATION_DEFAULT, $boardLineage->getLocation());
        $this->assertEquals(0, $boardLineage->getLocationArg());

        $lineage2 = (new Lineage(Meeple::HUMANI_WORKER));
        $this->repository->updateCardFromDb($lineage2);

        $boardLineage = $lineage2->getBoardCard(2);
        $this->assertEquals(1, $lineage2->getCardCount());
        $this->assertInstanceOf(LineageBoardCard::class, $boardLineage);
        $this->assertEquals(true, $boardLineage->isObjectiveCompleted());
        $this->assertEquals(true, $boardLineage->isLeader());
        $this->assertEquals(BoardCardInterface::LOCATION_HAND, $boardLineage->getLocation());
        $this->assertEquals(456789, $boardLineage->getLocationArg());
    }

    public function testUpdateCardFromDbForLineage()
    {
        $this->repository = new class(Spell::class) extends AbstractCardRepository {
            private int $index = 0;
            public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array {
                $values = [
                    ['card_activated' => "0", 'card_id' => "1", 'card_location' => BoardCardInterface::LOCATION_DEFAULT, 'card_location_arg' => "0"],
                    ['card_activated' => "1", 'card_id' => "2", 'card_location' => BoardCardInterface::LOCATION_HAND, 'card_location_arg' => "456789"],
                ];
                return $values[$this->index++];
            }
        };

        $fireControl = (new Spell(Spell::TYPE_COMBAT, Spell::FIRE_CONTROL));
        $this->repository->updateCardFromDb($fireControl);

        $boardSpell = $fireControl->getBoardCard(1);
        $this->assertEquals(1, $fireControl->getCardCount());
        $this->assertInstanceOf(SpellBoardCard::class, $boardSpell);
        $this->assertEquals(false, $boardSpell->isActivated());
        $this->assertEquals(BoardCardInterface::LOCATION_DEFAULT, $boardSpell->getLocation());
        $this->assertEquals(0, $boardSpell->getLocationArg());

        $animalFriend = (new Spell(Spell::TYPE_NATURE, Spell::ANIMAL_FRIENDSHIP));
        $this->repository->updateCardFromDb($animalFriend);

        $boardSpell = $animalFriend->getBoardCard(2);
        $this->assertEquals(1, $animalFriend->getCardCount());
        $this->assertInstanceOf(SpellBoardCard::class, $boardSpell);
        $this->assertEquals(true, $boardSpell->isActivated());
        $this->assertEquals(BoardCardInterface::LOCATION_HAND, $boardSpell->getLocation());
        $this->assertEquals(456789, $boardSpell->getLocationArg());
    }

    public function testUpdateCardFromDbForFight()
    {
        $this->repository = new class(Fight::class) extends AbstractCardRepository {
            public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array {
                return ['card_activated' => "0", 'card_id' => "1", 'card_location' => BoardCardInterface::LOCATION_ON_TABLE, 'card_location_arg' => "0"];
            }
        };

        $centaurs = (new Fight(Fight::CENTAURS, 10, false));
        $this->repository->updateCardFromDb($centaurs);

        $boardSpell = $centaurs->getBoardCard(1);
        $this->assertEquals(1, $centaurs->getCardCount());
        $this->assertInstanceOf(DefaultBoardCard::class, $boardSpell);
        $this->assertEquals(BoardCardInterface::LOCATION_ON_TABLE, $boardSpell->getLocation());
        $this->assertEquals(0, $boardSpell->getLocationArg());
    }

    public function testUpdateCardsFromDb()
    {
        $this->repository = new class(Objective::class) extends AbstractCardRepository {
            public function getObjectListFromDB(string $sql, bool $bUniqueValue = false ): array {
                return [
                    ['card_type' => Objective::DA_VINCI, 'card_type_arg' => '0', 'card_completed' => "0", 'card_id' => "1", 'card_location' => BoardCardInterface::LOCATION_DEFAULT, 'card_location_arg' => "0"],
                    ['card_type' => Objective::DA_VINCI, 'card_type_arg' => '0', 'card_completed' => "1", 'card_id' => "2", 'card_location' => BoardCardInterface::LOCATION_HAND, 'card_location_arg' => "12345679"],
                    ['card_type' => Objective::ARCHMAGE, 'card_type_arg' => '0', 'card_completed' => "1", 'card_id' => "3", 'card_location' => BoardCardInterface::LOCATION_HAND, 'card_location_arg' => "12345678"],
                ];
            }
        };

        $objectives = [
            Objective::DA_VINCI => new Objective(Objective::DA_VINCI),
            Objective::ARCHMAGE => new Objective(Objective::ARCHMAGE),
        ];
        $this->repository->updateCardsFromDb($objectives);

        /** @var Objective $daVinciObj */
        $daVinciObj = $objectives[Objective::DA_VINCI];
        $this->assertEquals(2, $daVinciObj->getCardCount());

        /** @var ObjectiveBoardCard $daVinciBoardCard1 */
        $daVinciBoardCard1 = $daVinciObj->getBoardCard(1);
        $this->assertInstanceOf(ObjectiveBoardCard::class, $daVinciBoardCard1);
        $this->assertEquals(false, $daVinciBoardCard1->isCompleted());
        $this->assertEquals(BoardCardInterface::LOCATION_DEFAULT, $daVinciBoardCard1->getLocation());
        $this->assertEquals(0, $daVinciBoardCard1->getLocationArg());

        $daVinciBoardCard2 = $daVinciObj->getBoardCard(2);
        $this->assertInstanceOf(ObjectiveBoardCard::class, $daVinciBoardCard2);
        $this->assertEquals(true, $daVinciBoardCard2->isCompleted());
        $this->assertEquals(BoardCardInterface::LOCATION_HAND, $daVinciBoardCard2->getLocation());
        $this->assertEquals(12345679, $daVinciBoardCard2->getLocationArg());

        /** @var Objective $archmageObj */
        $archmageObj = $objectives[Objective::ARCHMAGE];
        $this->assertEquals(1, $archmageObj->getCardCount());

        $archmageBoardCard = $archmageObj->getBoardCard(3);
        $this->assertInstanceOf(ObjectiveBoardCard::class, $archmageBoardCard);
        $this->assertEquals(true, $archmageBoardCard->isCompleted());
        $this->assertEquals(BoardCardInterface::LOCATION_HAND, $archmageBoardCard->getLocation());
        $this->assertEquals(12345678, $archmageBoardCard->getLocationArg());
    }

    protected function initRepository(string $class): \ReflectionObject
    {
        if ($class !== Tile::class) {
            $this->repository = new class($class) extends AbstractCardRepository {};
        } else {
            $this->repository = new class($class) extends AbstractRepository {};
        }

        return new \ReflectionObject($this->repository);
    }

    protected function extractFields(array $mapped, $fieldName): array
    {
        return array_values(
            array_map(
                function(Field $field) use ($fieldName) {
                    return $field->$fieldName;
                },
                $mapped
            )
        );
    }
}