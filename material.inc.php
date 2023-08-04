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
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\Disease;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Cards\Other;
use LdH\Entity\Cards\Fight;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Resource;
use LdH\Entity\Meeple;
use LdH\Entity\Bonus;


/*--------------------------------------
 *              Meeple part
 * ------------------------------------- */

$worker = (new Meeple(Meeple::WORKER))
    ->setName(clienttranslate('Ouvrier'))
    ->setPlural(clienttranslate('Ouvriers'))
    ->setDescription(clienttranslate("Whether farmers, fishermen or any type of craftsmen, they have to harvest enough food for everyone and to develop the infrastructure of Esperys"));
$warrior = (new Meeple(Meeple::WARRIOR))
    ->setName(clienttranslate('Guerrier'))
    ->setPlural(clienttranslate('Guerriers'))
    ->setDescription(clienttranslate("They have been trained to brave the worst dangers of Estiny. Their mission is to stand up for their peers, wherever they are. They are often at the forefront when exploring promising new lands."));
$savant = (new Meeple(Meeple::SAVANT))
    ->setName(clienttranslate('Savant'))
    ->setPlural(clienttranslate('Savants'))
    ->setDescription(clienttranslate("In all parts of society, scientists devote their time to testing and developing the inventions they have imagined in order to improve daily life for generations to come."));
$mage = (new Meeple(Meeple::MAGE))
    ->setName(clienttranslate('Mage'))
    ->setPlural(clienttranslate('Mages'))
    ->setDescription(clienttranslate("Estiny is a world filled with magic and mysteries. Thus, as long as we devote ourselves fully to it, it is possible to shape the primordial flows to cast powerful spells. Magic is capricious, but once tamed, wizards can cast spells that can help you in many ways."));
$all = (new Meeple(Meeple::ALL))
    ->setName(clienttranslate('Any meeple'))
    ->setPlural(clienttranslate('Any meeple'))
    ->setDescription(clienttranslate('Any meeple (Worker, Warrior, Savant or Mage)'));
$monster = (new Meeple(Meeple::MONSTER))
    ->setName(clienttranslate('Créature'))
    ->setPlural(clienttranslate('Créatures'))
    ->setDescription(clienttranslate(""));
$elvenMage = (new Meeple(Meeple::ELVEN_MAGE))
    ->setName(clienttranslate("Fal'san'in"))
    ->setPlural(clienttranslate("Fal'san'in lineage"));
$elvenSavant = (new Meeple(Meeple::ELVEN_SAVANT))
    ->setName(clienttranslate("Reth'los"))
    ->setPlural(clienttranslate("Reth'los lineage"));
$naniWarrior = (new Meeple(Meeple::NANI_WARRIOR))
    ->setName(clienttranslate('Khazhan'))
    ->setPlural(clienttranslate("Khazhan lineage"));
$naniSavant = (new Meeple(Meeple::NANI_SAVANT))
    ->setName(clienttranslate('Agrindorn'))
    ->setPlural(clienttranslate("Agrindorn lineage"));
$humaniWorker = (new Meeple(Meeple::HUMANI_WORKER))
    ->setName(clienttranslate('Mournmorning'))
    ->setPlural(clienttranslate("Mournmorning lineage"));
$humaniMage = (new Meeple(Meeple::HUMANI_MAGE))
    ->setName(clienttranslate('Mightmaster'))
    ->setPlural(clienttranslate("Mightmaster lineage"));
$orkWarrior = (new Meeple(Meeple::ORK_WARRIOR))
    ->setName(clienttranslate('Gorzog'))
    ->setPlural(clienttranslate("Gorzog lineage"));
$orkWorker = (new Meeple(Meeple::ORK_WORKER))
    ->setName(clienttranslate('Dahkrum'))
    ->setPlural(clienttranslate("Dahkrum lineage"));
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
$wood            = new Resource(clienttranslate('Bois'), Resource::WOOD, clienttranslate("Any plant offering enouth wood for construction or wooden objects manufacturing"));
$stone           = new Resource(clienttranslate('Pierre'), Resource::STONE, clienttranslate("As a complement to wood, stone offers a robust building material, but requires solid tools"));
$metal           = new Resource(clienttranslate('Métal'), Resource::METAL, clienttranslate("Tin, copper, iron or any other alloy to make stronger weapons and tools"));
$paper           = new Resource(clienttranslate('Papier'), Resource::PAPER, clienttranslate("Here, it is any support allowing to preserve as writings, all forms of knowledge"));
$clay            = new Resource(clienttranslate('Argile'), Resource::CLAY, clienttranslate("This malleable material offers, once dried or cooked, incredible possibilities"));
$animal          = new Resource(clienttranslate('Animal'), Resource::ANIMAL, clienttranslate("Offering food (farmed or not), wool, leather and so many other products"));
$gem             = new Resource(clienttranslate('Pierre précieuse'), Resource::GEM, clienttranslate("These mineral formations, more or less rare, have pleasant colours and shapes"));
$medic           = new Resource(clienttranslate('Plante médicinal'), Resource::MEDIC, clienttranslate("Nature offers its benefits to those who know how to observe and learn."));
$this->resources = [
    $stone->getCode()  => $stone,
    $wood->getCode()   => $wood,
    $metal->getCode()  => $metal,
    $clay->getCode()   => $clay,
    $paper->getCode()  => $paper,
    $animal->getCode() => $animal,
    $gem->getCode()    => $gem,
    $medic->getCode()  => $medic
];

$mountain       = new Terrain(clienttranslate('Montagne'), Terrain::MOUNTAIN, 0, false, [$stone, $metal, $gem]);
$mountainLair   = new Terrain(clienttranslate('Montagne - Repaire'), Terrain::MOUNTAIN_LAIR, 0, false, [$stone, $metal, $gem]);
$mountainLair
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::WARRIOR))
    ->addBonus(new Bonus(10, Bonus::FOOD_FOUND))
;
$mountainLake   = new Terrain(clienttranslate('Lac de montagne'), Terrain::MOUNTAIN_LAKE, 1, false, [$stone, $metal, $animal]);

$mountainWood   = new Terrain(clienttranslate('Montagne boisée'), Terrain::MOUNTAIN_WOOD, 0, false, [$wood, $stone, $metal]);
$mountainDrakon = new Terrain(clienttranslate('Repaire du Drakon'), Terrain::MOUNTAIN_DRAKON);
$mountainDrakon->setDrakon(true);
$mountainRiver  = new Terrain(clienttranslate('Rivières de montagne'), Terrain::MOUNTAIN_RIVER, 1, false, [$stone, $metal, $gem]);

$plain          = new Terrain(clienttranslate('Plaine'), Terrain::PLAIN, 2, false, [$clay, $animal]);
$plainLake      = new Terrain(clienttranslate('Plaine lacustre'), Terrain::PLAIN_LAKE, 3, false, [$paper, $clay, $animal]);
$plainWood      = new Terrain(clienttranslate('Plaine boisée'), Terrain::PLAIN_WOOD, 1, false, [$wood, $animal]);
$plainDesert    = new Terrain(clienttranslate('Plaine désertique'), Terrain::PLAIN_DESERT, 0, false, [$clay]);
$plainRiverRuin = new Terrain(clienttranslate('Plaine - Ruines'), Terrain::PLAIN_RIVER_RUIN, 3, false, [$paper, $clay, $animal]);
$plainRiverRuin
    ->addBonus(new Bonus(10, Bonus::SCIENCE_FOUND))
    ->addBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_INVENTION));
;

$desert         = new Terrain(clienttranslate('Désert'), Terrain::DESERT);
$desertStone    = new Terrain(clienttranslate('Désert de pierre'), Terrain::DESERT_STONE, 0, false, [$stone, $gem]);

$swamp          = new Terrain(clienttranslate('Marais'), Terrain::SWAMP, 0, false, [$paper, $medic]);
$swampLair      = new Terrain(clienttranslate('Marais - Repaire'), Terrain::SWAMP_LAIR, 0, false, [$paper, $medic]);
$swampLair
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::WARRIOR))
    ->addBonus(new Bonus(10, Bonus::FOOD_FOUND))
;
$swampTower     = new Terrain(clienttranslate('Marais - Tour de mage'), Terrain::SWAMP_TOWER, 0, true, [$paper, $medic]);
$swampTower
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::MAGE))
    ->addBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_MAGIC))
;

$hill           = new Terrain(clienttranslate('Collines'), Terrain::HILL, 2, false, [$stone, $metal]);
$hillPlateau    = new Terrain(clienttranslate('Plateaux'), Terrain::HILL_PLATEAU, 1, false, [$stone]);
$hillWoodRiver  = new Terrain(clienttranslate('Collines boisées'), Terrain::HILL_WOOD_RIVER, 3, false, [$wood, $metal, $animal]);
$hillRuin       = new Terrain(clienttranslate('Collines - Ruines'), Terrain::HILL_RUIN, 2, false, [$stone, $metal]);
$hillRuin
    ->addBonus(new Bonus(10, Bonus::SCIENCE_FOUND))
    ->addBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_INVENTION));
;
$hillLake       = new Terrain(clienttranslate('Collines et lac'), Terrain::HILL_LAKE, 3, false, [$stone, $metal, $animal]);
$hillWoodLair   = new Terrain(clienttranslate('Collines - Repaire'), Terrain::HILL_WOOD_LAIR, 1, false, [$wood, $gem]);
$hillWoodLair
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::WARRIOR))
    ->addBonus(new Bonus(10, Bonus::FOOD_FOUND))
;
$forest         = new Terrain(clienttranslate('Forêt'), Terrain::FOREST, 0, false, [$wood, $animal]);
$forestTower    = new Terrain(clienttranslate('Forêt - Tour de mage'), Terrain::FOREST_TOWER, 0, true, [$wood, $animal]);
$forestTower
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::MAGE))
    ->addBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_MAGIC))
;
$forestLair     = new Terrain(clienttranslate('Froêt - Repaire'), Terrain::FOREST_LAIR, 0, false, [$wood, $animal]);
$forestLair
    ->addBonus(new Bonus(1, Bonus::BIRTH, Meeple::WARRIOR))
    ->addBonus(new Bonus(10, Bonus::FOOD_FOUND))
;
$forestDense    = new Terrain(clienttranslate('Forêt dense'), Terrain::FOREST_DENSE, 0, false, [$wood, $wood, $medic]);
$forestRuin     = new Terrain(clienttranslate('Forêt - Ruines'), Terrain::FOREST_RUIN, 0, false, [$wood, $animal]);
$forestRuin
    ->addBonus(new Bonus(10, Bonus::SCIENCE_FOUND))
    ->addBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_INVENTION));
;

/*--------------------------------------
 *          Cards part
 * ------------------------------------- */

$lineage = (new Deck(Deck::TYPE_LINEAGE))
    ->setName(clienttranslate('Lineage cards'))
    ->setIsLarge(true)
    ->setIsPublic(true);
$objective = (new Deck(Deck::TYPE_OBJECTIVE))
    ->setName(clienttranslate('Objective cards'));
$magic = (new Deck(Deck::TYPE_MAGIC))
    ->setName(clienttranslate('Sepll cards'));
$invention = (new Deck(Deck::TYPE_INVENTION))
    ->setName(clienttranslate('Invention cards'));
$exploreFight = (new Deck(Deck::TYPE_EXPLORE_FIGHT))
    ->setName(clienttranslate('Explore : Fight cards'));
$exploreOther = (new Deck(Deck::TYPE_EXPLORE_OTHER))
    ->setName(clienttranslate('Explore : Other cards'));
$exploreDisease = (new Deck(Deck::TYPE_EXPLORE_DISEASE))
    ->setName(clienttranslate('Explore : Disease cards'));
$this->cards = [
    $lineage->getType()        => $lineage,
    $objective->getType()      => $objective,
    $invention->getType()      => $invention,
    $magic->getType()          => $magic,
    $exploreFight->getType()   => $exploreFight,
    $exploreOther->getType()   => $exploreOther,
    $exploreDisease->getType() => $exploreDisease
];

//      Objective
// -------------------
$objElvenMage = (new Objective(Objective::ELVEN_MAGE))
    ->setName("Objectif des Fal'san'in")
    ->setDescription(clienttranslate("Avoir 10+ mages"))
    ->setNeed(Objective::NEED_UNITS)
    ->setNeedCount(10)
    ->setSubNeed(Objective::NEED_SUB_MAGE);
$objElvenSavant = (new Objective(Objective::ELVEN_SAVANT))
    ->setName(clienttranslate("Objectif des Reth'los"))
    ->setDescription(clienttranslate("Récolter 20+ sciences en un tour"))
    ->setNeed(Objective::NEED_HARVEST)
    ->setNeedCount(20)
    ->setSubNeed(Objective::NEED_SUB_SCIENCE);
$objNaniWarrior = (new Objective(Objective::NANI_WARRIOR))
    ->setName(clienttranslate("Objectif des Khazhan"))
    ->setDescription(clienttranslate("Découvrir 5+ inventions militaires"))
    ->setNeed(Objective::NEED_INVENTION)
    ->setNeedCount(5)
    ->setSubNeed(Objective::NEED_SUB_FIGHT);
$objNaniSavant = (new Objective(Objective::NANI_SAVANT))
    ->setName(clienttranslate("Objectif des Agrindorn"))
    ->setDescription(clienttranslate("Avoir 10+ savants"))
    ->setNeed(Objective::NEED_UNITS)
    ->setNeedCount(10)
    ->setSubNeed(Objective::NEED_SUB_SAVANT);
$objHumaniWorker = (new Objective(Objective::HUMANI_WORKER))
    ->setName(clienttranslate("Objectif des Mournmorning"))
    ->setDescription(clienttranslate("Maîtriser 3+ sorts (nature)"))
    ->setNeed(Objective::NEED_SPELL)
    ->setNeedCount(5)
    ->setSubNeed(Objective::NEED_SUB_NATURE);
$objHumaniMage = (new Objective(Objective::HUMANI_MAGE))
    ->setName(clienttranslate("Objectif des Mightmaster"))
    ->setDescription(clienttranslate("Maîtriser 10+ sorts"))
    ->setNeed(Objective::NEED_SPELL)
    ->setNeedCount(10);
$objOrkWarrior = (new Objective(Objective::ORK_WARRIOR))
    ->setName(clienttranslate("Objectif des Gorzog"))
    ->setDescription(clienttranslate("Avoir 20+ guerriers"))
    ->setNeed(Objective::NEED_UNITS)
    ->setNeedCount(20)
    ->setSubNeed(Objective::NEED_SUB_WARRIOR);
$objOrkWorker = (new Objective(Objective::ORK_WORKER))
    ->setName(clienttranslate("Objectif des Dahkrum"))
    ->setDescription(clienttranslate("Avoir 30+ ouvriers"))
    ->setNeed(Objective::NEED_UNITS)
    ->setNeedCount(30)
    ->setSubNeed(Objective::NEED_SUB_WORKER);
$objective
    ->addCard($objNaniSavant)
    ->addCard($objOrkWorker)
    ->addCard($objElvenMage)
    ->addCard($objOrkWarrior)
    ->addCard($objNaniWarrior)
    ->addCard($objHumaniWorker)
    ->addCard($objHumaniMage)
    ->addCard($objElvenSavant)
    ->addCard((new Objective(Objective::ABUNDANCE))
        ->setName("Abondance")
        ->setDescription(clienttranslate("Produire 30+ nourritures en un tour"))
        ->setNeed(Objective::NEED_HARVEST)
        ->setNeedCount(30)
        ->setSubNeed(Objective::NEED_SUB_FOOD)
    )
    ->addCard((new Objective(Objective::DA_VINCI))
        ->setName("Da Vinci")
        ->setDescription(clienttranslate("Découvrir 10 inventions"))
        ->setNeed(Objective::NEED_INVENTION)
        ->setNeedCount(10)
    )
    ->addCard((new Objective(Objective::ARCHMAGE))
        ->setName("Archimage")
        ->setDescription(clienttranslate("Maîtriser 10 sorts"))
        ->setNeed(Objective::NEED_SPELL)
        ->setNeedCount(10)
    )
    ->addCard((new Objective(Objective::CAUTIOUS_EXPLORER))
        ->setName("Explorateur prudent")
        ->setDescription(clienttranslate("Explorer toutes les tuiles I"))
        ->setNeed(Objective::NEED_EXPLORE)
        ->setNeedCount(6)
        ->setSubNeed(Objective::NEED_SUB_FAR_I)
    )
    ->addCard((new Objective(Objective::TO_WORLD_ENDING))
        ->setName("Jusqu'au bout du monde")
        ->setDescription(clienttranslate("Explorer 3 tuiles III"))
        ->setNeed(Objective::NEED_EXPLORE)
        ->setNeedCount(3)
        ->setSubNeed(Objective::NEED_SUB_FAR_III)
    )
    ->addCard((new Objective(Objective::ARTEFACT_LOVER))
        ->setName("Artefact lover")
        ->setDescription(clienttranslate("Découvrir une tour de mage"))
        ->setNeed(Objective::NEED_EXPLORE)
        ->setSubNeed(Objective::NEED_SUB_TOWER)
    )
    ->addCard((new Objective(Objective::IN_WOLF_MOUTH))
        ->setName("Dans la gueule du loup")
        ->setDescription(clienttranslate("Découvrir un repaire de monstres "))
        ->setNeed(Objective::NEED_EXPLORE)
        ->setSubNeed(Objective::NEED_SUB_LAIR)
    )
    ->addCard((new Objective(Objective::ARCHAEOLOGIST))
        ->setName("Archéologue")
        ->setDescription(clienttranslate("Découvrir les ruines d'une ancienne cité "))
        ->setNeed(Objective::NEED_EXPLORE)
        ->setSubNeed(Objective::NEED_SUB_RUINS)
    )
    ->addCard((new Objective(Objective::WE_NEED_ALL))
        ->setName("Il nous les faut toutes")
        ->setDescription(clienttranslate("Produire 1+ resource de chaque en un tour"))
        ->setNeed(Objective::NEED_HARVEST)
        ->setNeedCount(1)
        ->setSubNeed(Objective::NEED_SUB_RESOURCE_ALL)
    )
    ->addCard((new Objective(Objective::IN_CASE_OF))
        ->setName("Au cas où...")
        ->setDescription(clienttranslate("Produire 5+ ressources identiques, en un tour"))
        ->setNeed(Objective::NEED_HARVEST)
        ->setNeedCount(5)
        ->setSubNeed(Objective::NEED_SUB_RESOURCE_ONE)
    )
    ->addCard((new Objective(Objective::SURVIVOR))
        ->setName("Survivant")
        ->setDescription(clienttranslate("Survivre jusqu'au 20e tour"))
        ->setNeed(Objective::NEED_SURVIVE)
        ->setNeedCount(20)
    )
    ->addCard((new Objective(Objective::NOT_EVEN_HURT))
        ->setName("Même pas mal")
        ->setDescription(clienttranslate("Gagner un combat sans aucun blessé"))
        ->setNeed(Objective::NEED_WIN_FIGHT)
        ->setSubNeed(Objective::NEED_SUB_NO_WOUND)
    )
    ->addCard((new Objective(Objective::WARMONGER))
        ->setName("Belliciste")
        ->setDescription(clienttranslate("Découvrir 4 inventions de type Militaire"))
        ->setNeed(Objective::NEED_INVENTION)
        ->setNeedCount(4)
        ->setSubNeed(Objective::NEED_SUB_FIGHT)
    )
    ->addCard((new Objective(Objective::RESEARCHER))
        ->setName("Chercheur")
        ->setDescription(clienttranslate("Découvrir 4 inventions de type Science"))
        ->setNeed(Objective::NEED_INVENTION)
        ->setNeedCount(4)
        ->setSubNeed(Objective::NEED_SUB_SCIENCE)
    )
    ->addCard((new Objective(Objective::MAGISTER))
        ->setName("Evocateur")
        ->setDescription(clienttranslate("Maîtriser 3 sorts de type Combat"))
        ->setNeed(Objective::NEED_SPELL)
        ->setNeedCount(3)
        ->setSubNeed(Objective::NEED_SUB_FIGHT)
    )
    ->addCard((new Objective(Objective::ESTINY_CHILD))
        ->setName("Enfants d'Estiny")
        ->setDescription(clienttranslate("Maîtriser 2 sorts de type Nature"))
        ->setNeed(Objective::NEED_SPELL)
        ->setNeedCount(2)
        ->setSubNeed(Objective::NEED_SUB_NATURE)
    )
;
$this->cards[$objective->getType()] = $objective;

//  Lineage
//----------
$lineage
    ->addCard((new Lineage(Meeple::NANI_SAVANT))
        ->setName("Agrindorn")
        ->setDescription(clienttranslate("Les Agrindorn n'ont pas leur pareil pour faire aimer et comprendre les sujets les plus complexes."))
        ->setMeeple($naniSavant)
        ->setMeeplePower((new Bonus(3, Bonus::CONVERTER, Meeple::SAVANT)))
        ->setObjective($objNaniSavant)
        ->setObjectiveBonus((new Bonus(1, Bonus::CONVERTER, Meeple::NANI_SAVANT))->setDescription(clienttranslate("1 unité en +")))
        ->setLeadingBonus(new Bonus(2, Bonus::SCIENCE, Bonus::BONUS_MULTIPLY))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::ORK_WORKER))
        ->setName("Dahkrum")
        ->setDescription(clienttranslate("Travailler la terre n'effrai pas les Dahkrum. Ils produisent les meilleurs denrées que vous pourrez jamais manger."))
        ->setMeeple($orkWorker)
        ->setMeeplePower((new Bonus(1, Bonus::FOOD, Meeple::ORK_WORKER)))
        ->setObjective($objOrkWorker)
        ->setObjectiveBonus((new Bonus(1, Bonus::FOOD, Meeple::ORK_WORKER))->setDescription(clienttranslate("[food]+1")))
        ->setLeadingBonus(new Bonus(1, Bonus::FOOD, $warrior->getCode()))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::ELVEN_MAGE))
        ->setName("Fal'san'in")
        ->setDescription(clienttranslate("Les Fal'San'In naissent éveillés à la magie depuis des générations. En outre, ils semblent sans âge."))
        ->setMeeple($elvenMage)
        ->setMeeplePower((new Bonus(3, Bonus::CONVERTER, Meeple::MAGE)))
        ->setObjective($objElvenMage)
        ->setObjectiveBonus((new Bonus(1, Bonus::CONVERTER, Meeple::MAGE))->setDescription(clienttranslate("1 unit more")))
        ->setLeadingBonus(new Bonus(1, Bonus::BIRTH, $mage->getCode()))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::ORK_WARRIOR))
        ->setName("Gorzog")
        ->setDescription(clienttranslate("\"Quand je serais grand, je serais aussi fort qu'un Gorzog !\" Citation d'un jeune elven d'Esperys."))
        ->setMeeple($orkWarrior)
        ->setMeeplePower(new Bonus(1, Bonus::POWER, Meeple::ORK_WARRIOR))
        ->setObjective($objOrkWarrior)
        ->setObjectiveBonus(new Bonus(1, Bonus::POWER))
        ->setLeadingType(Lineage::LEADING_TYPE_FIGHT)
        ->setLeadingBonus(new Bonus(5, Bonus::MEEPLE_POWER_UP))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::NANI_WARRIOR))
        ->setName("Khazhan")
        ->setDescription(clienttranslate("Les Khazhan sont de Fiers et Forts guerriers, si brillants qu'ils inspirent les générations !"))
        ->setMeeple($naniWarrior)
        ->setMeeplePower((new Bonus(2, Bonus::SCIENCE, Bonus::INVENTION_FIGHT))->setDescription(clienttranslate("+2[science] for [invention] [fight]")))
        ->setObjective($objNaniWarrior)
        ->setObjectiveBonus((new Bonus(2, Bonus::SCIENCE, Bonus::INVENTION_FIGHT))->setDescription(clienttranslate("+2 [science]")))
        ->setLeadingBonus(new Bonus(1, Bonus::SCIENCE, $warrior->getCode()))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::HUMANI_WORKER))
        ->setName("Mournmorning")
        ->setDescription(clienttranslate("Les Mournmorning ont ce don, de communiquer avec la nature. Ils manipulent les flux aussi bien que la faux."))
        ->setMeeple($humaniWorker)
        ->setMeeplePower((new Bonus(1, Bonus::IS_ALSO, Meeple::MAGE))->setDescription(clienttranslate("= [mage] for [spell] [nature]")))
        ->setObjective($objHumaniWorker)
        ->setObjectiveBonus((new Bonus(1, Bonus::IS_ALSO, Meeple::MAGE))->setDescription(clienttranslate("Count as one more")))
        ->setLeadingBonus(new Bonus(1, Bonus::DRAW_CARD, AbstractCard::TYPE_MAGIC))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::HUMANI_MAGE))
        ->setName("Mightmaster")
        ->setDescription(clienttranslate("Canaliser les flux magiques est un jeu d'enfant pour les Mightmaster. Ils ont engendré les mages les plus puissants."))
        ->setMeeple($humaniMage)
        ->setMeeplePower((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("+[spell] : +1[power]")))
        ->setObjective($objHumaniMage)
        ->setObjectiveBonus((new Bonus(1, Bonus::POWER))->setDescription(clienttranslate("+1[power]")))
        ->setLeadingType(Lineage::LEADING_TYPE_FIGHT)
        ->setLeadingBonus(new Bonus(5, Bonus::SPELL_RECAST))
        ->setArtist('Kevins Darnis')
    )
    ->addCard((new Lineage(Meeple::ELVEN_SAVANT))
        ->setName("Reth'los")
        ->setDescription(clienttranslate("On cite souvent les Reth'los pour leurs inventions et leur ingéniosité légendaire ! Sont-ils bénis des dieux ?"))
        ->setMeeple($elvenSavant)
        ->setMeeplePower((new Bonus(1, Bonus::SCIENCE, Meeple::ELVEN_SAVANT)))
        ->setObjective($objElvenSavant)
        ->setObjectiveBonus((new Bonus(1, Bonus::SCIENCE))->setDescription(clienttranslate("[science]+1")))
        ->setLeadingBonus(new Bonus(1, Bonus::BIRTH, $savant->getCode()))
        ->setArtist('Kevins Darnis')
    )
;
$this->cards[$lineage->getType()] = $lineage;

//      Invention
// -------------------
$cityCenter = (new Invention(Invention::TYPE_START, Invention::CENTER))
    ->setName(clienttranslate("centre ville"))
    ->addUnit($all)
    ->setOr(true)
    ->addGive(new Bonus(1, Bonus::CONVERT, $warrior->getCode()))
    ->addGive(new Bonus(1, Bonus::CONVERT, $worker->getCode()))
    ->setArtist('Kevin Darnis');
$huts = (new Invention(Invention::TYPE_START, Invention::HUT))
    ->setName(clienttranslate("Huttes"))
    ->setDescription(clienttranslate("[end_turn] Lancez le dé de Croissance"))
    ->setUnits([$all, $all])
    ->addGive(new Bonus(1, Bonus::GROWTH))
    ->addGive(new Bonus(1, Bonus::BIRTH, $worker->getCode()))
    ->setArtist('Kevin Darnis');;
$stock = (new Invention(Invention::TYPE_START, Invention::STOCK))
    ->setName(clienttranslate("Hangar"))
    ->setDescription(clienttranslate("Stock les ressources en attendant leur utilisation"))
    ->setArtist('Kevin Darnis');;
$mound = (new Invention(Invention::TYPE_START, Invention::MOUND))
    ->setName(clienttranslate("Talus"))
    ->setDescription(clienttranslate("Défense de la Cité +1."))
    ->addGive(new Bonus(1, Bonus::DEFENSE_CITY))
    ->setArtist('Kevin Darnis');;
$stoneCutting = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::STONE_CUTTING))
    ->setName(clienttranslate("Taille de la pierre"))
    ->setDescription(clienttranslate("Les bâtiments solides sont bâtis en pierre de taille"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($stone)
    ->addUnit($worker)
    ->addGive(new Bonus(3, Bonus::RESOURCE, $stone->getCode()));
$hunting = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::HUNTING))
    ->setName(clienttranslate("Chasse"))
    ->setDescription(clienttranslate("La viande complète le repas et nourris son homme"))
    ->setArtist('Kevin Darnis')
    ->setScience(3)
    ->addResource($animal)
    ->setUnits([$worker, $warrior])->setOr(true)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$fishing = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::FISHING))
    ->setName(clienttranslate("Pêche"))
    ->setDescription(clienttranslate("Mieux vaut apprendre à pêcher qu'offrir un poisson"))
    ->setArtist('Kevin Darnis')
    ->setScience(3)
    ->addResource($animal)
    ->setUnits([$worker, $warrior])->setOr(true)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$school = (new Invention(Invention::TYPE_SCIENCE, Invention::SCHOOL))
    ->setName(clienttranslate("Ecole"))
    ->setDescription(clienttranslate("Chaque enfant a son avenir entre ses mains"))
    ->setArtist('Kevins Darnis')
    ->setScience(5)
    ->setOr(true)
    ->setResources([$animal, $clay])
    ->addUnit($savant)
    ->addGive((new Bonus(1, Bonus::BIRTH_ALL))->setDescription(clienttranslate("[growth] : [warrior] / [worker] / [mage] / [savant]")))
;
$stoneCircle = (new Invention(Invention::TYPE_MAGICAL, Invention::STONE_CIRCLE))
    ->setName(clienttranslate("Cercle de pierre"))
    ->setDescription(clienttranslate("Regorgeant de magie, il vous permet de contrôler ses flux"))
    ->setArtist('Kevin Darnis')
    ->setScience(2)
    ->addResource($gem)
    ->addUnit($all)
    ->addGive(new Bonus(1, Bonus::CONVERT, $mage->getCode()));
$tools = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::TOOLS))
    ->setName(clienttranslate("Outils"))
    ->setDescription(clienttranslate("Les outils, toujours s'améliorent"))
    ->setArtist('Kevins Darnis')
    ->setScience(3)
    ->setResources([$stone, $metal])->setOr(true)
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::FOOD))
    ->addGive(new Bonus(2, Bonus::FOOD));
$longBow = (new Invention(Invention::TYPE_FIGHT, Invention::LONG_BOW))
    ->setName(clienttranslate("Arc long"))
    ->setDescription(clienttranslate("Vos ennemis ne verront pas la mort fondre sur eux"))
    ->setArtist('Kevins Darnis')
    ->setScience(10)
    ->addResource($wood)
    ->addGive((new Bonus(1, Bonus::DISTANT_POWER))->setDescription("Nearby&nbsp; [warrior] : [power]+1"));
$pottery = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::POTTERY))
    ->setName(clienttranslate("Poterie"))
    ->setDescription(clienttranslate("A l'abri de l'air et de la lumière"))
    ->setArtist('Kevin Darnis')
    ->setScience(3)
    ->addResource($clay)
    ->addUnit($worker)
    ->addGive(new Bonus(5, Bonus::STOCK));
$granary = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::GRANARY))
    ->setName(clienttranslate("Grenier"))
    ->setDescription(clienttranslate("Protège les récoltes des intempéries et des animaux sauvages"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addGive(new Bonus(10, Bonus::STOCK));
$metallurgy = (new Invention(Invention::TYPE_FIGHT, Invention::METALLURGY))
    ->setName(clienttranslate("Métallurgie"))
    ->setDescription(clienttranslate("Maîtrise la puissance des métaux par le feu !"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($metal)
    ->addUnit($worker)
    ->addGive(new Bonus(3, Bonus::RESOURCE, $metal->getCode()));
$fence = (new Invention(Invention::TYPE_FIGHT, Invention::FENCE))
    ->setName(clienttranslate("Palissade"))
    ->setDescription(clienttranslate("Première ligne de défense : Quelques pièces de bois"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($wood)
    ->addUnit($warrior)
    ->addGive(new Bonus(1, Bonus::DEFENSE_CITY));
$irrigation = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::IRRIGATION))
    ->setName(clienttranslate("Irrigation"))
    ->setDescription(clienttranslate("Le contrôle de l'eau fut la base des grandes civilisations"))
    ->setArtist('Kevins Darnis')
    ->setScience(5)
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::FOOD));
$domestication = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::DOMESTICATION))
    ->setName(clienttranslate("Domestication"))
    ->setDescription(clienttranslate("Certains animaaux peuvent être domestiqués, pas tous..."))
    ->setArtist('Kevins Darnis')
    ->setScience(10)
    ->setResources([$animal, $animal])
    ->addGive(new Bonus(1, Bonus::MOVE));
$wheel = (new Invention(Invention::TYPE_FIGHT, Invention::WHEEL))
    ->setName(clienttranslate("Roue"))
    ->setDescription(clienttranslate("On va plus loin quand la roue tourne rond"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->setResources([$wood, $metal])
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::MOVE));
$oven = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::OVEN))
    ->setName(clienttranslate("Four"))
    ->setDescription(clienttranslate("De nouvelles recette, plus nutritives sont nées des fours"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($wood)
    ->addUnit($worker)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$writing = (new Invention(Invention::TYPE_SCIENCE, Invention::WRITING))
    ->setName(clienttranslate("Ecriture"))
    ->setDescription(clienttranslate("Les mots s'envolent, les écrits restent"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addResource($paper)->addResource($clay)->setOr(true)
    ->addUnit($savant)
    ->addGive(new Bonus(1, Bonus::SCIENCE, Meeple::SAVANT));
$soap = (new Invention(Invention::TYPE_GROWTH, Invention::SOAP))
    ->setName(clienttranslate("Savon"))
    ->setDescription(clienttranslate("Ca sent le propre, j'adore !"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addGive(new Bonus(1, Bonus::GROWTH))
    ->addGive(new Bonus(0, Bonus::DISEASE, 'I'));
$bellows = (new Invention(Invention::TYPE_FIGHT, Invention::BELLOWS))
    ->setName(clienttranslate("Soufflet"))
    ->setDescription(clienttranslate("Plus chaud pour des métaux plus résistants"))
    ->setArtist('Kevins Darnis')
    ->setScience(15)
    ->setResources([$wood, $metal])
    ->setUnits([$worker, $worker])
    ->addGive(new Bonus(1, Bonus::DEFENSE_WARRIOR))
    ->addGive(new Bonus(1, Bonus::POWER, Meeple::WARRIOR));
$glass = (new Invention(Invention::TYPE_SCIENCE, Invention::GLASS))
    ->setName(clienttranslate("Verre"))
    ->setDescription(clienttranslate("Du sable, né le verre !"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($stone)
    ->addUnit($savant)
    ->addGive(new Bonus(1, Bonus::SCIENCE, Meeple::SAVANT))
    ->addGive(new Bonus(5, Bonus::STOCK));
$toilets = (new Invention(Invention::TYPE_GROWTH, Invention::TOILETS))
    ->setName(clienttranslate("Toilettes"))
    ->setDescription(clienttranslate("Mais, comment faisions-nous avant de les inventer ?"))
    ->setArtist('Kevins Darnis')
    ->setScience(15)
    ->addGive(new Bonus(1, Bonus::GROWTH));
$bricks = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::BRICKS))
    ->setName(clienttranslate("Briques en terre cuite"))
    ->setDescription(clienttranslate("Simple comme un bloc d'argile, résistant comme la pierre"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($clay)
    ->addGive(new Bonus(2, Bonus::RESOURCE, $stone->getCode()));
$grindstone = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::GRINDSTONE))
    ->setName(clienttranslate("Meule"))
    ->setDescription(clienttranslate("Les céréales deviennent farine, la farine devient du pain !"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($stone)
    ->addUnit($worker)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$steel = (new Invention(Invention::TYPE_FIGHT, Invention::STEEL))
    ->setName(clienttranslate("Acier"))
    ->setDescription(clienttranslate("Plus risistant que le fer !"))
    ->setArtist('Kevin Darnis')
    ->setScience(20)
    ->setResources([$wood, $stone, $metal])
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::POWER, Meeple::WARRIOR));
$shield = (new Invention(Invention::TYPE_FIGHT, Invention::SHIELD))
    ->setName(clienttranslate("Bouclier"))
    ->setDescription(clienttranslate("Bon pour se défendre des crocs et des griffes"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->setResources([$wood, $metal])
    ->addGive(new Bonus(1, Bonus::DEFENSE_WARRIOR));
$inoculation = (new Invention(Invention::TYPE_GROWTH, Invention::INOCULATION))
    ->setName(clienttranslate("Inoculation"))
    ->setDescription(clienttranslate("Accoutumer le corps pour qu'il se défende par lui-même"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($medic)
    ->setUnits([$savant, $savant])
    ->addGive(new Bonus(1, Bonus::GROWTH))
    ->addGive(new Bonus(0, Bonus::DISEASE, 'III'));
$pulley = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::PULLEY))
    ->setName(clienttranslate("Poulie"))
    ->setDescription(clienttranslate("La poulie permet des constructions plus grandes"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addResource($wood)
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::RESOURCE, $wood->getCode()))
    ->addGive(new Bonus(1, Bonus::RESOURCE, $metal->getCode()))
    ->addGive(new Bonus(1, Bonus::RESOURCE, $stone->getCode()));
$crossbow = (new Invention(Invention::TYPE_FIGHT, Invention::CROSSBOW))
    ->setName(clienttranslate("Arbalète"))
    ->setDescription(clienttranslate("Droit au but, trait mortel"))
    ->setArtist('Kevins Darnis')
    ->setScience(25)
    ->addResource($wood)
    ->addUnit($savant)
    ->addGive((new Bonus(2, Bonus::DISTANT_POWER))->setDescription("[warrior] proche : [power]+2"));
$blastFurnace = (new Invention(Invention::TYPE_FIGHT, Invention::BLAST_FURNACE))
    ->setName(clienttranslate("Haut fourneau"))
    ->setDescription(clienttranslate("Les meilleurs métaux en sortent !"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->setResources([$wood, $wood, $metal])
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::POWER, Meeple::WARRIOR))
    ->addGive(new Bonus(1, Bonus::DEFENSE_WARRIOR));
$wall = (new Invention(Invention::TYPE_FIGHT, Invention::WALL))
    ->setName(clienttranslate("Muraille"))
    ->setDescription(clienttranslate("Le bois cède la place à la pierre, bien plus résistante"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->setResources([$stone, $stone])
    ->setUnits([$warrior, $warrior])
    ->addGive(new Bonus(2, Bonus::DEFENSE_CITY));
$herbalism = (new Invention(Invention::TYPE_GROWTH, Invention::HERBALISM))
    ->setName(clienttranslate("Herboristerie"))
    ->setDescription(clienttranslate("Les Savants apprennent à soigner avec le pouvoir des plantes"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addGive((new Bonus(1, Bonus::GROWTH))->setDescription("[savant] + [medic] : [healing] [disease]"));
$waterFilter = (new Invention(Invention::TYPE_SCIENCE, Invention::WATER_FILTER))
    ->setName(clienttranslate("Filtre à eau"))
    ->setDescription(clienttranslate("Le meilleur allier pour combattre les maladies : de l'eau saine"))
    ->setArtist('Kevins Darnis')
    ->setScience(15)
    ->addResource($clay)
    ->addGive((new Bonus(2, Bonus::SAVANT_HEALING))->setDescription('[savant] : [healing] x2'));
$anesthesia = (new Invention(Invention::TYPE_SCIENCE, Invention::ANESTHESIA))
    ->setName(clienttranslate("Anesthesia"))
    ->setDescription(clienttranslate("La magie n'est plus la seule à pouvoir sauver les gens de la mort"))
    ->setArtist('Kevins Darnis')
    ->setScience(20)
    ->addResource($medic)
    ->addGive((new Bonus(4, Bonus::SAVANT_HEALING))->setDescription('[savant] x2 : [healing] x4'));
$sewer  = (new Invention(Invention::TYPE_GROWTH, Invention::SEWER))
    ->setName(clienttranslate("Egoûts"))
    ->setDescription(clienttranslate("Un endroit plus propre grâce à un lieu de saletés"))
    ->setArtist('Kevin Darnis')
    ->setScience(20)
    ->setResources([$stone, $stone])
    ->addGive(new Bonus(1, Bonus::GROWTH))
    ->addGive(new Bonus(0, Bonus::DISEASE, 'II'));
$alchemy = (new Invention(Invention::TYPE_SCIENCE, Invention::ALCHEMY))
    ->setName(clienttranslate("Alchimie"))
    ->setDescription(clienttranslate("Extraire l'essence des plantes..."))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($medic)
    ->addUnit($savant)
    ->addGive(new Bonus(1, Bonus::SCIENCE, Meeple::SAVANT));
$maths = (new Invention(Invention::TYPE_SCIENCE, Invention::MATHS))
    ->setName(clienttranslate("Mathématiques"))
    ->setDescription(clienttranslate("Analyser, calculer et prédire, grâce aux mathématiques"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($paper)
    ->addGive(new Bonus(1, Bonus::SCIENCE, Meeple::SAVANT));
$clothes = (new Invention(Invention::TYPE_GROWTH, Invention::CLOTHES))
    ->setName(clienttranslate("Vêtements renforcés"))
    ->setDescription(clienttranslate("De meilleurs vêtements pour faire face aux dangers du monde"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->addResource($animal)
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::GROWTH));
$roads = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::ROADS))
    ->setName(clienttranslate("Routes"))
    ->setDescription(clienttranslate("Toutes les routes mènent à notre destinée..."))
    ->setArtist('Kevins Darnis')
    ->setScience(10)
    ->setResources([$stone, $stone])
    ->addUnit($worker)
    ->addGive(new Bonus(1, Bonus::MOVE));
$cooler = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::COOLER))
    ->setName(clienttranslate("Glacière"))
    ->setDescription(clienttranslate("La fraicheur du sol conserve la nourriture plus longtemps"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addUnit($worker)
    ->addGive(new Bonus(10, Bonus::STOCK));
$fermenting = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::FERMENTING))
    ->setName(clienttranslate("Fermentation"))
    ->setDescription(clienttranslate("Sacez-vous combien de nourriture fermentée vous mangez tous les jours ?"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addResource($animal)
    ->addUnit($worker)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$festival = (new Invention(Invention::TYPE_GROWTH, Invention::FESTIVAL))
    ->setName(clienttranslate("Festival"))
    ->setDescription(clienttranslate("Allons à la fête !"))
    ->setArtist('Kevin Darnis')
    ->setScience(5)
    ->setResources([$wood, $clay, $animal])
    ->addUnit($worker)
    ->addGive(new Bonus(-10, Bonus::FOOD_FOUND))
    ->addGive(new Bonus(3, Bonus::GROWTH));
$gemCutting = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::GEM_CUTTING))
    ->setName(clienttranslate("Taille de gemme"))
    ->setDescription(clienttranslate("De la pierre brute, le plus beau des joyaux"))
    ->setArtist('Kevin Darnis')
    ->setScience(15)
    ->addResource($gem)
    ->addUnit($worker)
    ->addGive(new Bonus(3, Bonus::RESOURCE, $gem->getCode()));
$rearing = (new Invention(Invention::TYPE_DEVELOPMENT, Invention::REARING))
    ->setName(clienttranslate("Elevage"))
    ->setDescription(clienttranslate("Pourquoi chasser, quand on peut élever sa nourriture ?"))
    ->setArtist('Kevin Darnis')
    ->setScience(10)
    ->addResource($animal)
    ->addUnit($worker)
    ->addGive(new Bonus(5, Bonus::FOOD_FOUND));
$invention
    ->addCard($cityCenter)
    ->addCard($huts)
    ->addCard($stock)
    ->addCard($mound)
    ->addCard($stoneCutting)
    ->addCard($hunting)
    ->addCard($fishing)
    ->addCard($school)
    ->addCard($stoneCircle)
    ->addCard($tools)
    ->addCard($longBow)
    ->addCard($pottery)
    ->addCard($granary)
    ->addCard($metallurgy)
    ->addCard($fence)
    ->addCard($irrigation)
    ->addCard($domestication)
    ->addCard($wheel)
    ->addCard($oven)
    ->addCard($writing)
    ->addCard($soap)
    ->addCard($bellows)
    ->addCard($glass)
    ->addCard($toilets)
    ->addCard($bricks)
    ->addCard($grindstone)
    ->addCard($steel)
    ->addCard($shield)
    ->addCard($inoculation)
    ->addCard($pulley)
    ->addCard($crossbow)
    ->addCard($blastFurnace)
    ->addCard($wall)
    ->addCard($herbalism)
    ->addCard($waterFilter)
    ->addCard($anesthesia)
    ->addCard($sewer)
    ->addCard($alchemy)
    ->addCard($maths)
    ->addCard($clothes)
    ->addCard($roads)
    ->addCard($cooler)
    ->addCard($fermenting)
    ->addCard($festival)
    ->addCard($gemCutting)
    ->addCard($rearing)
;
$this->cards[$invention->getType()] = $invention;

//      Spell
// -------------------
$magic
    ->addCard((new Spell(Spell::TYPE_HEALING, Spell::HEAL))
        ->setName(clienttranslate("Soin"))
        ->setDescription(clienttranslate("[all] x2 : [healing]"))
        ->setWhen(Spell::WHEN_FIGHT_END_ROUND)
        ->setTarget(Spell::TARGET_UNIT_ANY)
        ->setTargetCount(2)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCost($medic)
        ->setEffect(Spell::EFFECT_HEAL)
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::MAGIC_MISSILE))
        ->setName(clienttranslate("Salve magique"))
        ->setDescription(clienttranslate("+1 [power]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_FIGHT_POWER)
        ->setRange(Spell::RANGE_NEARBY)
        ->addGive(new Bonus(1, Bonus::DISTANT_POWER))
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::FIRE_CONTROL))
        ->setName(clienttranslate("Contrôle du feu"))
        ->setDescription(clienttranslate("+2 [power]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_FIGHT_POWER)
        ->setRange(Spell::RANGE_TILE)
        ->addGive(new Bonus(2, Bonus::DISTANT_POWER))
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::LIGHTNING))
        ->setName(clienttranslate("Foudre"))
        ->setDescription(clienttranslate("+5 [power]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_FIGHT_POWER)
        ->setRange(Spell::RANGE_TILE)
        ->setCasterCount(2)
        ->addGive(new Bonus(5, Bonus::DISTANT_POWER))
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::METEOR))
        ->setName(clienttranslate("Météores"))
        ->setDescription(clienttranslate("+7 [power]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_FIGHT_POWER)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCasterCount(2)
        ->setCost($gem)
        ->addGive(new Bonus(7, Bonus::DISTANT_POWER))
    )
    ->addCard((new Spell(Spell::TYPE_HEALING, Spell::GROUPED_HEAL))
        ->setName(clienttranslate("Soin de groupe"))
        ->setDescription(clienttranslate("[all] x4 : [healing]"))
        ->setWhen(Spell::WHEN_FIGHT_END_ROUND)
        ->setCasterCount(2)
        ->setTarget(Spell::TARGET_UNIT_ANY)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCasterCount(2)
        ->setCost($medic)
        ->setEffect(Spell::EFFECT_HEAL)
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::SACRIFICE))
        ->setName(clienttranslate("Sacrifice"))
        ->setDescription(clienttranslate("+4 [power], mais [mage] meurt"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_FIGHT_POWER)
        ->setRange(Spell::RANGE_TILE)
        ->setEffect(Spell::EFFECT_DIE)
        ->addGive(new Bonus(4, Bonus::DISTANT_POWER))
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::WEAKNESS))
        ->setName(clienttranslate("Faiblesse"))
        ->setDescription(clienttranslate("-1 [power] : Créature(s)"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_MONSTER)
        ->setRange(Spell::RANGE_NEARBY)
        ->addGive(new Bonus(-1, Bonus::POWER))
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::LIANA_PRISON))
        ->setName(clienttranslate("Prison de lianes"))
        ->setDescription(clienttranslate("Fin [fight] : Créatures ne peuvent pas attaquer la cité"))
        ->setWhen(Spell::WHEN_FIGHT_END_ROUND)
        ->setTarget(Spell::TARGET_MONSTER)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCost($wood)
        ->setEffect(Spell::EFFECT_CANCEL)
    )
    ->addCard((new Spell(Spell::TYPE_ENCHANT, Spell::ENCHANT))
        ->setName(clienttranslate("Enchantement"))
        ->setDescription(clienttranslate("[warrior]x1 : +1 [power] & +1 [defense_warrior]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_UNIT_WARRIOR)
        ->setTargetCount(1)
        ->setRange(Spell::RANGE_TILE)
        ->setGives([
            new Bonus(1, Bonus::POWER, Meeple::WARRIOR),
            new Bonus(1, Bonus::DEFENSE_WARRIOR)
        ])
    )
    ->addCard((new Spell(Spell::TYPE_ENCHANT, Spell::GREAT_ENCHANT))
        ->setName(clienttranslate("Arsenal magique"))
        ->setDescription(clienttranslate("Jusqu'à 5 [warrior] : +1 [power] & +1 [defense_warrior]"))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_UNIT_WARRIOR)
        ->setTargetCount(5)
        ->setRange(Spell::RANGE_TILE)
        ->setCasterCount(2)
        ->setCost($gem)
        ->setGives([
            new Bonus(1, Bonus::POWER, Meeple::WARRIOR),
            new Bonus(1, Bonus::DEFENSE_WARRIOR)
        ])
    )
    ->addCard((new Spell(Spell::TYPE_HEALING, Spell::CURE))
        ->setName(clienttranslate("Remède"))
        ->setDescription(clienttranslate("[all] x1 : [healing] [disease] I & II"))
        ->setWhen(Spell::WHEN_EXPLORE_DISEASE)
        ->setTarget(Spell::TARGET_UNIT_ANY)
        ->setTargetCount(1)
        ->setRange(Spell::RANGE_TILE)
        ->setEffect(Spell::EFFECT_CURE)
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::FOG))
        ->setName(clienttranslate("Brouillard"))
        ->setDescription(clienttranslate("Annule [fight]. Déplacer les unités sur une tuile explorée adjacente."))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_MONSTER)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCasterCount(2)
        ->setCost($gem)
        ->setEffect(Spell::EFFECT_CANCEL)
    )
    ->addCard((new Spell(Spell::TYPE_FORESIGHT, Spell::SHARP_EYE))
        ->setName(clienttranslate("Oeil de lynx"))
        ->setDescription(clienttranslate("Regarder la tuile adjacente. Aucune [explore]."))
        ->setWhen(Spell::WHEN_TURN_ANY)
        ->setTarget(Spell::TARGET_TILE)
        ->setRange(Spell::RANGE_NEARBY)
        ->setEffect(Spell::EFFECT_WATCH)
    )
    ->addCard((new Spell(Spell::TYPE_FORESIGHT, Spell::CLEAR_VISION))
        ->setName(clienttranslate("Clairevoyance"))
        ->setDescription(clienttranslate("Regarder une tuile. Aucune [explore]."))
        ->setWhen(Spell::WHEN_TURN_ANY)
        ->setTarget(Spell::TARGET_TILE)
        ->setRange(Spell::RANGE_ENDLESS)
        ->setCost($gem)
        ->setEffect(Spell::EFFECT_WATCH)
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::YOUTH))
        ->setName(clienttranslate("Jouvence"))
        ->setDescription(clienttranslate("[end_turn] [growth] : +1 [worker] (Max 1 / [turn])."))
        ->setWhen(Spell::WHEN_GROWTH)
        ->setRange(Spell::RANGE_CITY)
        ->setCasterCount(2)
        ->setCost($animal)
        ->setTimes(Spell::TIMES_ONE)
        ->addGive(new Bonus(1, Bonus::BIRTH, $worker->getCode()))
    )
    ->addCard((new Spell(Spell::TYPE_FORESIGHT, Spell::LUKE))
        ->setName(clienttranslate("Chance"))
        ->setDescription(clienttranslate("Cancel Various explore event if it affect only one target."))
        ->setWhen(Spell::WHEN_EXPLORE_OTHER)
        ->setCasterCount(2)
        ->setCost($gem)
        ->setEffect(Spell::EFFECT_CANCEL)
    )
    ->addCard((new Spell(Spell::TYPE_FORESIGHT, Spell::PROBABILITY))
        ->setName(clienttranslate("Probability control"))
        ->setDescription(clienttranslate("After a die throw, you can use this spell to flip it."))
        ->setWhen(Spell::WHEN_DIE_THROW)
        ->setTarget(Spell::TARGET_DICE)
        ->setEffect(Spell::EFFECT_FLIP)
        ->setCasterCount(2)
        ->setCost($gem)
    )
    ->addCard((new Spell(Spell::TYPE_FORESIGHT, Spell::GENIUS))
        ->setName(clienttranslate("Masterstroke"))
        ->setDescription(clienttranslate("+2 [science]"))
        ->setWhen(Spell::WHEN_TURN_ANY)
        ->setTarget(Spell::TARGET_NO)
        ->setRange(Spell::RANGE_CITY)
        ->addGive(new Bonus(2, Bonus::SCIENCE_FOUND))
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::GROWTH))
        ->setName(clienttranslate("Growth"))
        ->setDescription(clienttranslate("+2 [food] if tile is harvested"))
        ->setWhen(Spell::WHEN_FOOD_HARVEST)
        ->setTarget(Spell::TARGET_TILE)
        ->setRange(Spell::RANGE_NEARBY)
        ->addGive(new Bonus(2, Bonus::FOOD_FOUND))
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::FERTILE_LAND))
        ->setName(clienttranslate("Fertile land"))
        ->setDescription(clienttranslate("[worker] : [food] +1 on target tile"))
        ->setWhen(Spell::WHEN_FOOD_HARVEST)
        ->setTarget(Spell::TARGET_TILE)
        ->setRange(Spell::RANGE_TILE)
        ->addGive(new Bonus(1, Bonus::FOOD, Meeple::WORKER))
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::WEATHER_CONTROl))
        ->setName(clienttranslate("Weather control"))
        ->setDescription(clienttranslate("[worker] : [food] x2 on target tile"))
        ->setWhen(Spell::WHEN_FOOD_HARVEST)
        ->setTarget(Spell::TARGET_TILE)
        ->setRange(Spell::RANGE_NEARBY)
        ->setCasterCount(2)
        ->setCost($gem)
        ->setEffect(Spell::EFFECT_HARVEST_TWICE)
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::CREATION))
        ->setName(clienttranslate("Creation"))
        ->setDescription(clienttranslate("Add one resource of your choice to [city]"))
        ->setWhen(Spell::WHEN_TURN_ANY)
        ->setTarget(Spell::TARGET_RESOURCE_ANY)
        ->setRange(Spell::RANGE_CITY)
        ->setEffect(Spell::EFFECT_NEW_RESOURCE)
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::STAMINA))
        ->setName(clienttranslate("Stamina"))
        ->setDescription(clienttranslate("Target [worker] : Harvest 2 resources instead of one"))
        ->setWhen(Spell::WHEN_RESOURCE_HARVEST)
        ->setTarget(Spell::TARGET_UNIT_WORKER)
        ->setRange(Spell::RANGE_TILE)
        ->setEffect(Spell::EFFECT_HARVEST_TWICE)
    )
    ->addCard((new Spell(Spell::TYPE_NATURE, Spell::ANIMAL_FRIENDSHIP))
        ->setName(clienttranslate("Animal friendship"))
        ->setDescription(clienttranslate("Add until [animal] x3"))
        ->setWhen(Spell::WHEN_TURN_ANY)
        ->setTarget(Spell::TARGET_NO)
        ->setRange(Spell::RANGE_CITY)
        ->addGive(new Bonus(2, Bonus::RESOURCE, $animal->getCode()))
    )
    ->addCard((new Spell(Spell::TYPE_HEALING, Spell::GREAT_CURE))
        ->setName(clienttranslate("Great cure"))
        ->setDescription(clienttranslate("[all] x3 : [healing] [disease]"))
        ->setWhen(Spell::WHEN_EXPLORE_DISEASE)
        ->setTarget(Spell::TARGET_UNIT_ANY)
        ->setTargetCount(3)
        ->setRange(Spell::RANGE_TILE)
        ->setCost($medic)
        ->setEffect(Spell::EFFECT_CURE)
    )
    ->addCard((new Spell(Spell::TYPE_COMBAT, Spell::EXHAUSTING))
        ->setName(clienttranslate("Exhausting"))
        ->setDescription(clienttranslate("-2 [power] for opponent."))
        ->setWhen(Spell::WHEN_FIGHT_START_ROUND)
        ->setTarget(Spell::TARGET_MONSTER)
        ->setRange(Spell::RANGE_TILE)
        ->addGive(new Bonus(-2, Bonus::POWER))
    )
;
$this->cards[$magic->getType()] = $magic;

//      Disease
// -------------------
$exploreDisease
    ->addCard((new Disease(Disease::LEVEL_2, Disease::NO_WIZARD))
        ->setName("Mentalite aïgué")
        ->setDescription(clienttranslate("Wizards on tile become workers."))
        ->setAge(AbstractCard::AGE_II)
    )
    ->addCard((new Disease(Disease::LEVEL_2, Disease::NO_WIZARD))
        ->setName("Mentalite aïgué")
        ->setDescription(clienttranslate("Wizards on tile become workers."))
        ->setAge(AbstractCard::AGE_III),
  2
    )
    ->addCard((new Disease(Disease::LEVEL_3, Disease::ACTED_ZONE))
        ->setName("Vide-boyau")
        ->setDescription(clienttranslate("Flip units (1 tile distance) to unavailable. They can't do more action this turn."))
        ->setAge(AbstractCard::AGE_I)
    )
    ->addCard((new Disease(Disease::LEVEL_3, Disease::ACTED_ZONE))
        ->setName("Vide-boyau")
        ->setDescription(clienttranslate("Flip units (1 tile distance) to unavailable. They can't do more action this turn."))
        ->setAge(AbstractCard::AGE_II)
    )
    ->addCard((new Disease(Disease::LEVEL_3, Disease::ACTED_ZONE))
        ->setName("Vide-boyau")
        ->setDescription(clienttranslate("Flip units (1 tile distance) to unavailable. They can't do more action this turn."))
        ->setAge(AbstractCard::AGE_III)
    )
    ->addCard((new Disease(Disease::LEVEL_1, Disease::ACTED_MOVED_HEAL))
        ->setName("Fièvre draconique")
        ->setDescription(clienttranslate("Units on tile can't move or do anything until they are healed."))
        ->setAge(AbstractCard::AGE_III)
    )
    ->addCard((new Disease(Disease::LEVEL_1, Disease::DEAD))
        ->setName("Mort-soif")
        ->setDescription(clienttranslate("Units on tile die if not healed this turn."))
        ->setAge(AbstractCard::AGE_III)
    )
    ->addCard((new Disease(Disease::LEVEL_1, Disease::ACTED_HEAL))
        ->setName("Creuse-os")
        ->setDescription(clienttranslate("Units on tile can't do anything until they are healed (Move is possible)."))
        ->setAge(AbstractCard::AGE_II)
    )
    ->addCard((new Disease(Disease::LEVEL_2, Disease::ACTED_MOVED))
        ->setName("Jambe-coton")
        ->setDescription(clienttranslate("Units on tile can't move or do anything this turn."))
        ->setAge(AbstractCard::AGE_I)
    )
    ->addCard((new Disease(Disease::LEVEL_2, Disease::ACTED_MOVED))
        ->setName("Jambe-coton")
        ->setDescription(clienttranslate("Units on tile can't move or do anything this turn."))
        ->setAge(AbstractCard::AGE_II)
    )
    ->addCard((new Disease(Disease::LEVEL_2, Disease::ACTED_MOVED))
        ->setName("Jambe-coton")
        ->setDescription(clienttranslate("Units on tile can't move or do anything this turn."))
        ->setAge(AbstractCard::AGE_III)
    )
;
$this->cards[$exploreDisease->getType()] = $exploreDisease;

$exploreFight
    ->addCard((new Fight(Fight::PIC_BULL, 5))
        ->setName(clienttranslate("Pic-bull"))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $animal->getCode()),
            new Bonus(5, Bonus::FOOD_FOUND)
        ])
    )
    ->addCard((new Fight(Fight::CRAWLING, 1))
        ->setName(clienttranslate("Grouilleux")),
        2
    )
    ->addCard((new Fight(Fight::LIZARDS, 10, true))
        ->setName(clienttranslate("Hunter-lizards"))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $clay->getCode()),
            new Bonus(1, Bonus::RESOURCE, $metal->getCode())
        ])
    )
    ->addCard((new Fight(Fight::SAND_WORM, 15))
        ->setName(clienttranslate("Sand worm"))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $gem->getCode()))
    )
    ->addCard((new Fight(Fight::LICH, 20, true))
        ->setName(clienttranslate("Lich"))
        ->setDescription(clienttranslate("Until it is killed, Lich stay on tile and send a monster to city each turn."))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $gem->getCode()))
    )
    ->addCard((new Fight(Fight::WOLFS, 3))
        ->setName(clienttranslate("Wolf pack"))
        ->setDescription(clienttranslate("Until they are killed, wolf pack attack an occupied nearby tile next turn."))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $animal->getCode())),
        2
    )
    ->addCard((new Fight(Fight::BASILISK, 8))
        ->setName(clienttranslate("Basilisk"))
        ->setDescription(clienttranslate("Only Cure spell can save units wounded by basilisk."))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $stone->getCode()),
            new Bonus(1, Bonus::RESOURCE, $gem->getCode())
        ])
    )
    ->addCard((new Fight(Fight::ELEMENTAL, 15, true))
        ->setName(clienttranslate("Stone elemental"))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $stone->getCode()))
    )
    ->addCard((new Fight(Fight::UNDEAD, 5, true))
        ->setName(clienttranslate("Undead"))
        ->setDescription(clienttranslate("For each unit killed, add +1 power to Undead for next fight round")),
        2
    )
    ->addCard((new Fight(Fight::VAMPIRES, 10))
        ->setName(clienttranslate("Vampires"))
        ->setDescription(clienttranslate("On fight ending, send an Undead monster (+1 Power x Killed units) to city."))
    )
    ->addCard((new Fight(Fight::GIANT_SPIDERS, 5))
        ->setName(clienttranslate("Giant spiders")),
        2
    )
    ->addCard((new Fight(Fight::GIANTS, 15, true))
        ->setName(clienttranslate("Giants"))
        ->setDescription(clienttranslate("City defense is 0 for giants."))
    )
    ->addCard((new Fight(Fight::SAND_SPIRIT, 15))
        ->setName(clienttranslate("Sand spirits"))
        ->setDescription(clienttranslate("You can't use fight spells against Sand spirits."))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $gem->getCode()))
    )
    ->addCard((new Fight(Fight::BRIGANDS, 8))
        ->setName(clienttranslate("Brigands"))
        ->setDescription(clienttranslate("Until they are killed, Brigands steal every resource produced on nearby tiles."))
        ->addGive(new Bonus(1, Bonus::RESOURCE, $gem->getCode())),
        2
    )
    ->addCard((new Fight(Fight::SORCERER, 15))
        ->setName(clienttranslate("Sorcerer"))
        ->setDescription(clienttranslate("Warriors power is ignored except if their weapons are enchanted."))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $paper->getCode()),
            new Bonus(1, Bonus::RESOURCE, $gem->getCode()),
            new Bonus(2, Bonus::DRAW_CARD, AbstractCard::TYPE_MAGIC),
        ])
    )
    ->addCard((new Fight(Fight::CENTAURS, 10))
        ->setName(clienttranslate("Centaurs"))
        ->setDescription(clienttranslate("It is impossible to flee Centaurs at the end of a fight round."))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $animal->getCode()),
            new Bonus(5, Bonus::SCIENCE_FOUND)
        ])
    )
;
$this->cards[$exploreFight->getType()] = $exploreFight;

$exploreOther
    ->addCard((new Other(Other::ABANDONED_GEM_MINE))
        ->setName(clienttranslate("Gem mine"))
        ->setDescription(clienttranslate("It still contains some beautiful gems"))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $wood->getCode()),
            new Bonus(3, Bonus::RESOURCE, $gem->getCode())
        ])
    )
    ->addCard((new Other(Other::ABANDONED_METAL_MINE))
        ->setName(clienttranslate("Metal mine"))
        ->setDescription(clienttranslate("It still contains some metal ore"))
        ->setGives([
            new Bonus(1, Bonus::RESOURCE, $wood->getCode()),
            new Bonus(3, Bonus::RESOURCE, $metal->getCode())
        ])
    )
    ->addCard((new Other(Other::RUINED_COTTAGE))
        ->setName(clienttranslate("Ruined cottage"))
        ->setDescription(clienttranslate("Among the rubble you discover interesting writtings"))
        ->addGive(new Bonus(5, Bonus::SCIENCE_FOUND)),
        2
    )
    ->addCard((new Other(Other::TELLURIC_CROSSING))
        ->setName(clienttranslate("Telluric crossing"))
        ->setDescription(clienttranslate("You may change until 3 units into mage"))
        ->addGive(new Bonus(3, Bonus::CONVERT, $mage->getCode())),
        2
    )
    ->addCard((new Other(Other::HERMIT_WEAPON_MASTER))
        ->setName(clienttranslate("Hermit weapon master"))
        ->setDescription(clienttranslate("You may change until 3 units into warrior"))
        ->addGive(new Bonus(3, Bonus::CONVERT, $warrior->getCode())),
        2
    )
    ->addCard((new Other(Other::WISH_FOUNTAIN))
        ->setName(clienttranslate("Wish fountain"))
        ->setDescription(clienttranslate("You may change until 3 units into an other type of your choice"))
        ->addGive(new Bonus(3, Bonus::CONVERT, $all->getCode()))
    )
    ->addCard((new Other(Other::LOST_LIBRARY))
        ->setName(clienttranslate("Lost library"))
        ->setDescription(clienttranslate("You discover a huge knowledge place, miraculously preserved"))
        ->setGives([
            new Bonus(10, Bonus::SCIENCE_FOUND),
            new Bonus(2, Bonus::DRAW_CARD, AbstractCard::TYPE_INVENTION)
        ])
    )
    ->addCard((new Other(Other::FIRE))
        ->setName(clienttranslate("Fire"))
        ->setDescription(clienttranslate("Impossible to harvest on this tile (Food, Resources, Science)"))
    )
    ->addCard((new Other(Other::EXPLOSIVE_ARTEFACT))
        ->setName(clienttranslate("Explosive artefact"))
        ->setDescription(clienttranslate("An artefact found during exploration has exploded and has killed 1 Mage and 1 Savant"))
    )
    ->addCard((new Other(Other::DAMAGED_FRUITS))
        ->setName(clienttranslate("Damaged fruits"))
        ->setDescription(clienttranslate("Fruits taken during exploration have rotted and destroyed harvests (Food stock is now 0)")),
        2
    )
    ->addCard((new Other(Other::DROUGHT))
        ->setName(clienttranslate("Drought"))
        ->setDescription(clienttranslate("Plains and hills on this tile and nearby can't produce any food this turn"))
    )
    ->addCard((new Other(Other::FLOOD))
        ->setName(clienttranslate("Flood"))
        ->setDescription(clienttranslate("Resources can't be harvested on this and nearby tiles this turn"))
    )
    ->addCard((new Other(Other::LANDSLIDE))
        ->setName(clienttranslate("Landslide"))
        ->setDescription(clienttranslate("Until 2 units died during exploration, no possible healing."))
    )
    ->addCard((new Other(Other::INHABITED_CAVE))
        ->setName(clienttranslate("Inhabited cave"))
        ->setDescription(clienttranslate("Case inhabitant join us."))
        ->setGives([
            new Bonus(1, Bonus::BIRTH, $worker->getCode()),
            new Bonus(1, Bonus::BIRTH, $warrior->getCode()),
            new Bonus(5, Bonus::FOOD_FOUND),
            new Bonus(2, Bonus::SCIENCE_FOUND)
        ])
    )
    ->addCard((new Other(Other::CURSE))
        ->setName(clienttranslate("Curse"))
        ->setDescription(clienttranslate("Explorers found a cursed ancient tomb. All exploring units died."))
    )
;
$this->cards[$exploreOther->getType()] = $exploreOther;

/*--------------------------------------
 *          Fill terrains parts
 * ------------------------------------- */

$townHumanis = new City(clienttranslate('Espérys'), Terrain::TOWN_HUMANIS, 5, true, [$clay]);
$townHumanis
    ->addUnit($worker)->addUnit($mage)
    ->addInvention($pottery)->addInvention($irrigation)
;
$townElven   = new City(clienttranslate("Gala'ar"), Terrain::TOWN_ELVEN, 5, true, [$clay, $medic]);
$townElven
    ->addUnit($mage)->addUnit($savant)
    ->addInvention($writing)->addInvention($herbalism)
;
$townNani    = new City(clienttranslate('Nundurahl'), Terrain::TOWN_NANI, 5, true, [$stone, $metal]);
$townNani
    ->addUnit($worker)->addUnit($warrior)
    ->addInvention($metallurgy)->addInvention($stoneCutting)
;
$townOrk     = new City(clienttranslate('Arakh Dhul'), Terrain::TOWN_ORK, 5, true, [$wood, $metal]);
$townOrk
    ->addUnit($warrior)->addUnit($warrior)
    ->addInvention($metallurgy)->addInvention($fence)
;

$this->terrains = [
    $mountain->getCode()       => $mountain,
    $mountainLair->getCode()   => $mountainLair,
    $mountainLake->getCode()   => $mountainLake,
    $mountainRiver->getCode()  => $mountainRiver,
    $mountainDrakon->getCode() => $mountainDrakon,
    $mountainWood->getCode()   => $mountainWood,

    $plain->getCode()          => $plain,
    $plainDesert->getCode()    => $plainDesert,
    $plainLake->getCode()      => $plainLake,
    $plainRiverRuin->getCode() => $plainRiverRuin,
    $plainWood->getCode()      => $plainWood,

    $desert->getCode()         => $desert,
    $desertStone->getCode()    => $desertStone,

    $swamp->getCode()          => $swamp,
    $swampLair->getCode()      => $swampLair,
    $swampTower->getCode()     => $swampTower,

    $hill->getCode()           => $hill,
    $hillLake->getCode()       => $hillLake,
    $hillPlateau->getCode()    => $hillPlateau,
    $hillRuin->getCode()       => $hillRuin,
    $hillWoodLair->getCode()   => $hillWoodLair,
    $hillWoodRiver->getCode()  => $hillWoodRiver,

    $forest->getCode()         => $forest,
    $forestRuin->getCode()     => $forestRuin,
    $forestDense->getCode()    => $forestDense,
    $forestLair->getCode()     => $forestLair,
    $forestTower->getCode()    => $forestTower,

    $townHumanis->getCode()    => $townHumanis,
    $townElven->getCode()      => $townElven,
    $townNani->getCode()       => $townNani,
    $townOrk->getCode()        => $townOrk
];
