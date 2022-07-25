<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ligneeheros implementation : © FroloX nico.cleve@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * ligneeheros game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Explore;
use LdH\Entity\Cards\EndTurn;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Resource;
use LdH\Entity\Map\Variant;
use LdH\Entity\Meeple;
use LdH\Entity\Bonus;


/*--------------------------------------
 *              Meeple part
 * ------------------------------------- */

$worker = (new Meeple(Meeple::WORKER))
    ->setName(clienttranslate('Worker'))
    ->setDescription(clienttranslate("Whether farmers, fishermen or any type of craftsmen, they have to harvest enough food for everyone and to develop the infrastructure of Esperys"));
$warrior = (new Meeple(Meeple::WARRIOR))
    ->setName(clienttranslate('Warrior'))
    ->setDescription(clienttranslate("They have been trained to brave the worst dangers of Estiny. Their mission is to stand up for their peers, wherever they are. They are often at the forefront when exploring promising new lands."));
$savant = (new Meeple(Meeple::SAVANT))
    ->setName(clienttranslate('Erudite'))
    ->setDescription(clienttranslate("In all parts of society, scientists devote their time to testing and developing the inventions they have imagined in order to improve daily life for generations to come."));
$mage = (new Meeple(Meeple::MAGE))
    ->setName(clienttranslate('Wizard'))
    ->setDescription(clienttranslate("Estiny is a world filled with magic and mysteries. Thus, as long as we devote ourselves fully to it, it is possible to shape the primordial flows to cast powerful spells. Magic is capricious, but once tamed, wizards can cast spells that can help you in many ways."));
$monster = (new Meeple(Meeple::MONSTER))
    ->setName(clienttranslate('Creature'))
    ->setDescription(clienttranslate(""));
$elvenMage = (new Meeple(Meeple::ELVEN_MAGE))
    ->setName(clienttranslate("Fal'san'in unit"));
$elvenSavant = (new Meeple(Meeple::ELVEN_SAVANT))
    ->setName(clienttranslate('Reth\'los unit'));
$naniWarrior = (new Meeple(Meeple::NANI_WARRIOR))
    ->setName(clienttranslate('Khazhan unit'));
$naniSavant = (new Meeple(Meeple::NANI_SAVANT))
    ->setName(clienttranslate('Agrindorn unit'));
$humaniWorker = (new Meeple(Meeple::HUMANI_WORKER))
    ->setName(clienttranslate('Mournmorning unit'));
$humaniMage = (new Meeple(Meeple::HUMANI_MAGE))
    ->setName(clienttranslate('Mightmaster unit'));
$orkWarrior = (new Meeple(Meeple::ORK_WARRIOR))
    ->setName(clienttranslate('Gorzog unit'));
$orkWorker = (new Meeple(Meeple::ORK_WORKER))
    ->setName(clienttranslate('Dahkrum unit'));
$this->meeples = [
    $worker->getCode()  => $worker,
    $warrior->getCode() => $warrior,
    $savant->getCode()  => $savant,
    $mage->getCode()    => $mage,
    $monster->getCode() => $monster,
    $elvenMage->getCode()    => $elvenMage,
    $elvenSavant->getCode()  => $elvenSavant,
    $naniWarrior->getCode()  => $naniWarrior,
    $naniSavant->getCode()   => $naniSavant,
    $humaniWorker->getCode() => $humaniWorker,
    $humaniMage->getCode()   => $humaniMage,
    $orkWarrior->getCode()   => $orkWarrior,
    $orkWorker->getCode()    => $orkWorker
];


/*--------------------------------------
 * Map part : Terrain, Resources, Variants
 * ------------------------------------- */
$wood            = new Resource(clienttranslate('Wood'), 'wood', clienttranslate("Any plant offering enouth wood for construction or wooden objects manufacturing"));
$stone           = new Resource(clienttranslate('Stone'), 'stone', clienttranslate("As a complement to wood, stone offers a robust building material, but requires solid tools"));
$metal           = new Resource(clienttranslate('Metal'), 'metal', clienttranslate("Tin, copper, iron or any other alloy to make stronger weapons and tools"));
$paper           = new Resource(clienttranslate('Paper'), 'paper', clienttranslate("Here, it is any support allowing to preserve as writings, all forms of knowledge"));
$clay            = new Resource(clienttranslate('Clay'), 'clay', clienttranslate("This malleable material offers, once dried or cooked, incredible possibilities"));
$animal          = new Resource(clienttranslate('Animal'), 'animal', clienttranslate("Offering food (farmed or not), wool, leather and so many other products"));
$gem             = new Resource(clienttranslate('Gem'), 'gem', clienttranslate("These mineral formations, more or less rare, have pleasant colours and shapes"));
$this->resources = [
    $stone->getCode()  => $stone,
    $wood->getCode()   => $wood,
    $metal->getCode()  => $metal,
    $clay->getCode()   => $clay,
    $paper->getCode()  => $paper,
    $animal->getCode() => $animal,
    $gem->getCode()    => $gem
];

$mountain    = new Terrain(clienttranslate('Mountain'), Terrain::MOUNTAIN, false, [$stone, $metal, $gem]);
$plain       = new Terrain(clienttranslate('Plain'), Terrain::PLAIN, true, [$clay, $paper, $animal]);
$desert      = new Terrain(clienttranslate('Desert'), Terrain::DESERT);
$swamp       = new Terrain(clienttranslate('Swamp'), Terrain::SWAMP, true, [$wood, $paper]);
$hill        = new Terrain(clienttranslate('Hill'), Terrain::HILL, true, [$stone, $metal]);
$forest      = new Terrain(clienttranslate('Forest'), Terrain::FOREST, true, [$wood, $animal]);

$this->variants = [
    (new Variant(Variant::WATER))
        ->addBonus(new Bonus(1, Bonus::FOOD))
        ->setTerrains([]),
    (new Variant(Variant::LAIR))
        ->addBonus(new Bonus(1, Bonus::FOOD))
        ->addBonus((new Bonus(1, Bonus::BIRTH))->setType(Meeple::WARRIOR))
        ->setTerrains([]),
    (new Variant(Variant::RUINS))
        ->addBonus(new Bonus(1, Bonus::SCIENCE))
        ->addBonus((new Bonus(1, Bonus::DRAW_CARTE))->setType(AbstractCard::TYPE_MAGIC))
        ->setTerrains([]),
    (new Variant(Variant::TOWER))
        ->addBonus(new Bonus(1, Bonus::SCIENCE))
        ->addBonus((new Bonus(1, Bonus::BIRTH))->setType(Meeple::MAGE))
        ->addBonus((new Bonus(1, Bonus::DRAW_CARTE))->setType(AbstractCard::TYPE_MAGIC))
        ->setTerrains([])
];


/*--------------------------------------
 *          Cards part
 * ------------------------------------- */

$lineage = (new Deck(Deck::TYPE_LINEAGE))
    ->setName(clienttranslate('Lineage cards'))
    ->setIsLarge(true)
    ->setIsPublic(true);
$magic = (new Deck(Deck::TYPE_MAGIC))
    ->setName(clienttranslate('Sepll cards'));
$endOfTurn = (new Deck(Deck::TYPE_END_TURN))
    ->setName(clienttranslate('End of turn cards'));
$explore = (new Deck(Deck::TYPE_EXPLORE))
    ->setName(clienttranslate('Explore cards'));
$invention = (new Deck(Deck::TYPE_INVENTION))
    ->setName(clienttranslate('Invention cards'))
    ->setIsPublic(true);
$objective = (new Deck(Deck::TYPE_OBJECTIVE))
    ->setName(clienttranslate('Objective cards'));
$this->cards = [
    $lineage->getType()   => $lineage,
    $objective->getType() => $objective,
    $invention->getType() => $invention,
    $magic->getType()     => $magic,
    $explore->getType()   => $explore,
    $endOfTurn->getType() => $endOfTurn
];

//  Lineage
//----------
$lineage
    ->addCard((new Lineage(Meeple::ELVEN_MAGE))
        ->setName("Fal'san'in")
        ->setDescription(clienttranslate("Fal'San'In born awakened to magic for generations. Also, they look ageless."))
        ->setMeeple($elvenMage)
        ->setMeeplePower((new Bonus(5, Bonus::CONVERTER))->setType(Meeple::ELVEN_MAGE)->setDescription(clienttranslate("Can teach magic to 5 units")))
        ->setObjectiveBonus((new Bonus(5, Bonus::CONVERTER))->setType(Meeple::ELVEN_MAGE)->setDescription(clienttranslate("5 units more")))
    )
    ->addCard((new Lineage(Meeple::ELVEN_SAVANT))
        ->setName("Reth'los")
        ->setDescription(clienttranslate("The Reth'los are often cited for their inventions and their legendary ingenuity! Are they blessed by the gods?"))
        ->setMeeple($elvenSavant)
        ->setMeeplePower((new Bonus(1, Bonus::SCIENCE))->setDescription(clienttranslate("Produce 1 science more")))
        ->setObjectiveBonus((new Bonus(1, Bonus::SCIENCE))->setDescription(clienttranslate("1 science more")))
    )
    ->addCard((new Lineage(Meeple::NANI_WARRIOR))
        ->setName("Khazhan")
        ->setDescription(clienttranslate("The Khazhan are Proud and strong warriors, so brilliant that they inspire generations!"))
        ->setMeeple($naniWarrior)
        ->setMeeplePower((new Bonus(2, Bonus::SCIENCE))->setDescription(clienttranslate("Produce 2 science more for military inventions")))
        ->setObjectiveBonus((new Bonus(2, Bonus::SCIENCE))->setDescription(clienttranslate("2 science more")))
    )
    ->addCard((new Lineage(Meeple::NANI_SAVANT))
        ->setName("Agrindorn")
        ->setDescription(clienttranslate("The Agrindorn are unparalleled in making you love science and popularizing the most complex subjects."))
        ->setMeeple($naniSavant)
        ->setMeeplePower((new Bonus(5, Bonus::CONVERTER))->setType(Meeple::NANI_SAVANT)->setDescription(clienttranslate("Can teach science to 5 units")))
        ->setObjectiveBonus((new Bonus(5, Bonus::CONVERTER))->setType(Meeple::NANI_SAVANT)->setDescription(clienttranslate("5 units more")))
    )
    ->addCard((new Lineage(Meeple::HUMANI_WORKER))
        ->setName("Mournmorning")
        ->setDescription(clienttranslate("The Mournmorning have this gift, to communicate with nature. They manipulate magical streams as well as the scythe."))
        ->setMeeple($humaniWorker)
        ->setMeeplePower((new Bonus(1, Bonus::IS_ALSO))->setType(Meeple::MAGE)->setDescription(clienttranslate("Count as a mage to discover or launch Nature spells")))
        ->setObjectiveBonus((new Bonus(1, Bonus::IS_ALSO))->setType(Meeple::MAGE)->setDescription(clienttranslate("Count as one more")))
    )
    ->addCard((new Lineage(Meeple::HUMANI_MAGE))
        ->setName("Mightmaster")
        ->setDescription(clienttranslate("Channeling magical flows is child's play for Mightmaster. The most powerful wizards were Mightmaster."))
        ->setMeeple($humaniMage)
        ->setMeeplePower((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("His spells deals 1 damage more")))
        ->setObjectiveBonus((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("1 damage more")))
    )
    ->addCard((new Lineage(Meeple::ORK_WARRIOR))
        ->setName("Gorzog")
        ->setDescription(clienttranslate("\"When I'll grow up, I'll be as strong as a Gorzog!\" Quote from a young elven from Esperys."))
        ->setMeeple($orkWarrior)
        ->setMeeplePower((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("Power +1")))
        ->setObjectiveBonus((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("Power +1")))
    )
    ->addCard((new Lineage(Meeple::ORK_WORKER))
        ->setName("Dahkrum")
        ->setDescription(clienttranslate("Working soil does not scare the Dahkrum. They produce the finest food you can ever eat."))
        ->setMeeple($orkWorker)
        ->setMeeplePower((new Bonus(1, Bonus::FOOD))->setDescription(clienttranslate("Produce 1 food more")))
        ->setObjectiveBonus((new Bonus(1, Bonus::FOOD))->setDescription(clienttranslate("1 food more")))
    );
$this->cards[$lineage->getType()] = $lineage;

//      Objective
// -------------------

$this->cards[$objective->getType()] = $objective;

//      Invention
// -------------------
$smithing = (new Invention(Invention::SMITHING))
    ->setName(clienttranslate("Smithing"))
    ->setDescription(clienttranslate(""));
$invention
    ->addCard($smithing)

;
$this->cards[$invention->getType()] = $invention;

//      Spell
// -------------------

$this->cards[$magic->getType()] = $magic;

//      Explore
// -------------------
$explore
    ->addCard((new Explore(Explore::TYPE_DISEASE, Explore::DISEASE_NO_WIZARD))
        ->setName("Mentalite aïgué")
        ->setDescription(clienttranslate("Nearby wizards (1 tile distance) become workers."))
    )
    ->addCard((new Explore(Explore::TYPE_DISEASE, Explore::DISEASE_ACT_DONE))
        ->setName("Vide-boyau")
        ->setDescription(clienttranslate("Flip units on this tile to unavailable. They can't do more action this turn."))
    )
;
$this->cards[$explore->getType()] = $explore;

//      EndOfTurn
// -------------------
$endOfTurn
    ->addCard((new EndTurn(EndTurn::END_FLOOD))
        ->setName(clienttranslate("Flood"))
        ->setDescription(clienttranslate(""))
    )
;
$this->cards[$endOfTurn->getType()] = $endOfTurn;


/*--------------------------------------
 *          Fill terrains parts
 * ------------------------------------- */

$townHumanis = new City(clienttranslate('Espérys'), Terrain::TOWN_HUMANIS, true, [$clay, $animal]);
$townHumanis
    ->addUnit($worker)->addUnit($mage)
    ->addInvention($smithing)->addInvention($smithing)
;
$townElven   = new City(clienttranslate("Gala\'ar"), Terrain::TOWN_ELVEN, true, [$paper, $wood, $animal]);
$townElven
    ->addUnit($mage)->addUnit($savant)
    ->addInvention($smithing)->addInvention($smithing)
;
$townNani    = new City(clienttranslate('Nundurahl'), Terrain::TOWN_NANI, true, [$stone, $metal, $gem]);
$townNani
    ->addUnit($worker)->addUnit($warrior)
    ->addInvention($smithing)->addInvention($smithing)
;
$townOrk     = new City(clienttranslate('Arakh Dhul'), Terrain::TOWN_ORK, true, [$wood, $metal]);
$townOrk
    ->addUnit($warrior)->addUnit($warrior)
    ->addInvention($smithing)->addInvention($smithing)
;

$this->terrains = [
    $mountain->getCode()    => $mountain,
    $plain->getCode()       => $plain,
    $desert->getCode()      => $desert,
    $swamp->getCode()       => $swamp,
    $hill->getCode()        => $hill,
    $forest->getCode()      => $forest,
    $townHumanis->getCode() => $townHumanis,
    $townElven->getCode()   => $townElven,
    $townNani->getCode()    => $townNani,
    $townOrk->getCode()     => $townOrk
];
