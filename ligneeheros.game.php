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
  * ligneeheros.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('vendor/autoload.php');

use LdH\Entity\Cards\BoardCardInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use LdH\Service\StateCompilerPass;

use LdH\Service\CurrentStateService;
use LdH\Service\MapService;
use LdH\Service\CardService;
use LdH\Service\StateService;
use LdH\Service\PeopleService;
use LdH\Service\BonusService;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\Resource;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\Tile;
use LdH\Entity\Meeple;

class ligneeheros extends Table
{
    public $gamestate;

    /** @var Deck[] */
    public array $cards = [];

    /** @var Terrain[]  */
    public array $terrains = [];

    /** @var Tile[] */
    public array $map = [];

    /** @var Resource[]  */
    public array $resources = [];

    /** @var Meeple[]  */
    public array $meeples  = [];
    public \Deck $bgaMeeple;
    public ?PeopleService $people = null;

    public ?StateService $stateService = null;
    public ?CardService  $cardService  = null;
    public ?MapService  $mapService  = null;
    public ?BonusService $bonusService = null;

    /**
     * @var callable[]
     */
    protected array $stateArgMethods = [];

    /**
     * @var callable[]
     */
    protected array $stateActionMethods = [];

    /**
     * @var callable[]
     */
    protected array $actionMethods = [];

    private Container $container;

	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(CurrentStateService::CURRENT_STATES);

        // Use of Symfony DIC
        $this->initContainer();

        // Init cards deck
        $this->initDecks();

        // Init states
        $this->initStates();
    }

    /**
     * Use of Symfony DIC to simplify service definition and CompilerPass use
     *
     * @return void
     * @throws Exception
     */
    protected function initContainer()
    {
        $this->container = new ContainerBuilder();
        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('config/services.yaml');

        $this->container->addCompilerPass(new StateCompilerPass());
        $this->container->compile();

        $this->mapService = $this->getService(MapService::class);
        $this->mapService->setTerrains($this->terrains);
    }

    /**
     * Create decks from card list available in material.inc.php
     *
     * @return void
     * @throws Exception
     */
    protected function initDecks()
    {
        $this->cardService = $this->getService(CardService::class);

        foreach ($this->cards as $deck) {
            /** @var \Deck $bgaDeck */
            $bgaDeck = self::getNew( "module.common.deck" );
            $bgaDeck->init($deck->getType());

            $deck->setBgaDeck($bgaDeck);
        }

        $this->bgaMeeple = self::getNew( "module.common.deck" );
        $this->bgaMeeple->init(Meeple::BGA_TYPE);
    }

    /**
     * Use stateService to init
     *
     * @return void
     * @throws Exception
     */
    protected function initStates()
    {
        $this->stateService = $this->getService(StateService::class);

        // Fill available methods
        foreach ($this->getStateService()->getStateArgMethods() as $name => $argMethod) {
            if (is_callable($argMethod)) {
                $this->stateArgMethods[$name] = $argMethod->bindTo($this, $this);
            }
        }

        foreach ($this->getStateService()->getStateActionMethods($this) as $name => $stateActionMethod) {
            if (is_callable($stateActionMethod)) {
                $this->stateActionMethods[$name] = $stateActionMethod->bindTo($this, $this);
            }
        }

        foreach ($this->getStateService()->getActionMethods() as $name => $actionMethod) {
            if (is_callable($actionMethod)) {
                $this->actionMethods[$name] = $actionMethod->bindTo($this, $this);
            }
        }
    }

    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "ligneeheros";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/
        $this->initTables();
        $this->initGlobalValues();
        //$this->initStats();

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    private function initGlobalValues()
    {
        // Init global values with their initial values
        foreach (array_keys(CurrentStateService::CURRENT_STATES) as $stateName) {
            $this->setGameStateInitialValue($stateName, 0);
        }

        $this->setGameStateValue(CurrentStateService::GLB_TURN_LFT, CurrentStateService::LAST_TURN);
        $this->setGameStateValue(CurrentStateService::GLB_PEOPLE_CNT, CurrentStateService::START_PEOPLE);
        $this->setGameStateValue(CurrentStateService::GLB_FOOD_PRD, CurrentStateService::START_FOOD_PRD);
        $this->setGameStateValue(CurrentStateService::GLB_SCIENCE_PRD, CurrentStateService::START_SCIENCE_PRD);
        $this->setGameStateValue(CurrentStateService::GLB_LIFE, CurrentStateService::START_LIFE);
        $this->setGameStateValue(CurrentStateService::GLB_WAR_PWR, CurrentStateService::START_WAR_PWR);
        $this->setGameStateValue(CurrentStateService::GLB_CTY_DFS, CurrentStateService::START_CTY_DFS);
    }

    private function initStats()
    {
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        $this->initStat( 'table', 'table_won', 0 );
        $this->initStat( 'table', 'table_lost', 0 );
        $this->initStat( 'table', 'table_lost_turn', CurrentStateService::LAST_TURN );
        $this->initStat( 'player', 'player_lineage_elven_mage', 0 );
        $this->initStat( 'player', 'player_lineage_elven_savant', 0 );
        $this->initStat( 'player', 'player_lineage_humani_mage', 0 );
        $this->initStat( 'player', 'player_lineage_humani_worker', 0 );
        $this->initStat( 'player', 'player_lineage_nani_warrior', 0 );
        $this->initStat( 'player', 'player_lineage_nani_savant', 0 );
        $this->initStat( 'player', 'player_lineage_ork_worker', 0 );
        $this->initStat( 'player', 'player_lineage_ork_warrior', 0 );
        $this->initStat( 'player', 'player_finish_objective', 0 );
    }

    public function initTables()
    {
        // Generate map
        $this->mapService->createInitialMap($this->terrains);

        // Init cards
        if (!empty($this->cards)) {
            foreach ($this->cards as $deck) {
                $bgaDeck = $deck->getBgaDeck();

                $bgaDeck->createCards($deck->getBgaDeckData(), BoardCardInterface::LOCATION_DEFAULT);
                $bgaDeck->createCards($deck->getBgaDeckData(true), BoardCardInterface::LOCATION_HIDDEN);

                if (!$deck->isPublic()) {
                    $bgaDeck->shuffle(BoardCardInterface::LOCATION_DEFAULT);
                }
            }
        }
    }

    /*
        getAllDatas:

        Gather all information about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $currentPlayerId = (int) self::getCurrentPlayerId();    // !! We must only return information visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
        $result['isActive'] = $this->gamestate->isPlayerActive($this->getCurrentPlayerId());

        // Send materials
        $result['resources'] = $this->resources?? [];
        $result['terrains']  = $this->terrains?? [];

        // Send map details : Load Map from Db
        $result['map'] = $this->getMapTiles();

        // Game states
        $result['currentState'] = $this->getCurrentState();
        $result['tooltips'] = $this->getTooltips();

        // Cards
        $currentStateId  = $this->gamestate->state_id();
        $result['cards'] = $this->getCardService()->getPublicCards($this->cards, $currentStateId, $currentPlayerId);
        $result['decks'] = $this->getCardService()->getPublicDecks($this->cards);

        // Meeples
        $result['meeples'] = $this->meeples;
        $result['people'] = $this->getPeople();

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        return $result;
    }

    function addPlayersInfosForArgs($args): array
    {
        $args['players'] = [];

        return $args;
    }

    function getTooltips(): array
    {
        return [
            'id' => [
                'people-worker' => clienttranslate('Worker(s)'),
                'people-warrior' => clienttranslate('Warrior(s)'),
                'people-savant' => clienttranslate('Savant(s)'),
                'people-mage' => clienttranslate('Mage(s)'),
                'military-power' => clienttranslate('Warrior fight power'),
                'military-defense' => clienttranslate('Warrior defense'),
                'city-life' => clienttranslate('Growth (%count%/8 chance to produce new unit)'),
                'city-defense' => clienttranslate('City defense'),
                'harvest-food' => clienttranslate('Food harvested by each Free Worker (at the end of turn)'),
                'harvest-science' => clienttranslate('Science harvested by each Free Savant (at the end of turn)'),
                'stock-food' => clienttranslate('Food harvested / Food stock available'),
                'stock-science' => clienttranslate('Science harvested'),
                'stock-wood' => clienttranslate('Wood (resource harvested)'),
                'stock-animal' => clienttranslate('Animals (resource harvested)'),
                'stock-stone' => clienttranslate('Stone (resource harvested)'),
                'stock-metal' => clienttranslate('Metal (resource harvested)'),
                'stock-clay' => clienttranslate('Clay (resource harvested)'),
                'stock-paper' => clienttranslate('Paper (resource harvested)'),
                'stock-medic' => clienttranslate('Medicinal herbs  (resource harvested)'),
                'stock-gem' => clienttranslate('Gems (resource harvested)'),
            ],
            'class' => [
                '.tile .resource.wood' => [
                    clienttranslate('Resource: Wood'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.animal' => [
                    clienttranslate('Resource: Animal'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.stone' => [
                    clienttranslate('Resource: Stone'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.metal' => [
                    clienttranslate('Resource: Metal'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.clay' => [
                    clienttranslate('Resource: Clay'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.paper' => [
                    clienttranslate('Resource: Paper'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.medic' => [
                    clienttranslate('Resource: Medicinal herbs'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.gem' => [
                    clienttranslate('Resource: Gems'),
                    clienttranslate('Use 1 free worker to harvest'),
                ],
                '.tile .resource.food' => [
                    clienttranslate('Food'),
                    clienttranslate('Keep free worker(s) (Max. %count%) on this tile to harvest Food at the end of turn'),
                ],
                '.tile .resource.science' => [
                    clienttranslate('Science'),
                    clienttranslate('Keep free savant(s) on this tile to harvest at the end of turn'),
                ],
                '.wrapped-icon.free.worker' => clienttranslate('%count% free worker(s)'),
                '.wrapped-icon.moved.worker' => clienttranslate('%count% moved worker(s) (No more move possible)'),
                '.wrapped-icon.acted.worker' => clienttranslate('%count% acted worker(s) (No more action possible)'),
                '.wrapped-icon.free.warrior' => clienttranslate('%count% free warrior(s)'),
                '.wrapped-icon.moved.warrior' => clienttranslate('%count% moved warrior(s) (No more move possible)'),
                '.wrapped-icon.acted.warrior' => clienttranslate('%count% acted warrior(s) (No more action possible)'),
                '.wrapped-icon.free.savant' => clienttranslate('%count% free savant(s)'),
                '.wrapped-icon.moved.savant' => clienttranslate('%count% moved savant(s) (No more move possible)'),
                '.wrapped-icon.acted.savant' => clienttranslate('%count% acted savant(s) (No more action possible)'),
                '.wrapped-icon.free.mage' => clienttranslate('%count% free mage(s)'),
                '.wrapped-icon.moved.mage' => clienttranslate('%count% moved mage(s) (No more move possible)'),
                '.wrapped-icon.acted.mage' => clienttranslate('%count% acted mage(s) (No more action possible)'),
                '.player-board .ldh-leading' => clienttranslate('The first player to complete his two objectives becomes the leader and obtains this power'),
            ],
        ];
    }

    function getCurrentState(): array
    {
        $states = [
            'title' => [
                'turn'    => clienttranslate('Turn'),
                'people'  => clienttranslate('People:'),
                'harvest' => clienttranslate('Harvest:'),
                'military' => clienttranslate('Warrior:'),
                'city' => clienttranslate('City:'),
                'stock'   => clienttranslate('Stock:')
            ],
            'phase' => [
                'end' => clienttranslate('End phase begin'),
                'start' => clienttranslate('Turn ${turn} begin'),
            ]
        ];

        foreach (array_keys(CurrentStateService::CURRENT_STATES) as $stateName) {
            $states[$stateName] = (int) $this->getGameStateValue($stateName);
        }

        // Computed states
        $states['turn'] = $this->getCurrentTurn($states[CurrentStateService::GLB_TURN_LFT]);

        return $states;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $turnLeft = (int) $this->getGameStateValue(CurrentStateService::GLB_TURN_LFT);

        return (50 - $turnLeft) * 2;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function getCurrentTurn(?int $turnLeft = null): int {
        if ($turnLeft === null) {
            $turnLeft = (int) $this->getGameStateValue(CurrentStateService::GLB_TURN_LFT);
        }

        return (CurrentStateService::LAST_TURN - $turnLeft + 1);
    }

    function isLeaderPowerTurn(): bool {
        return $this->getCurrentTurn() % 3 === 0;
    }

    function getMapTiles(): array
    {
        if (empty($this->map)) {
            $this->map = $this->mapService->getMapTiles($this->terrains?? [], true);
        }
        return $this->map;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arg/actions methods and state action
////////////

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return void
     */
    function __call(string $name, array $arguments = []) {
        if (array_key_exists($name, $this->stateArgMethods)) {
            call_user_func_array($this->stateArgMethods[$name], $arguments);
        }

        if (array_key_exists($name, $this->stateActionMethods)) {
            call_user_func_array($this->stateActionMethods[$name], $arguments);
        }

        if (array_key_exists($name, $this->actionMethods)) {
            call_user_func_array($this->actionMethods[$name], $arguments);
        }
    }

    function callArgMethod(): array
    {
        $state = $this->gamestate->state();
        $methodName = 'arg' . $state['name'];
        if (array_key_exists($methodName, $this->stateArgMethods)) {
            return $this->stateArgMethods[$methodName]();
        }

        return [];
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//
    }

    public function getPeople(): PeopleService
    {
        if ($this->people === null) {
            $this->people = new PeopleService();
            $this->people->init(
                $this->bgaMeeple,
                $this->meeples
            );
        }

        return $this->people;
    }

    public function getDeck(string $type):? Deck
    {
        return array_key_exists($type, $this->cards) ?
            $this->cards[$type] : null;
    }

    public function getBonusService(): ?BonusService
    {
        if ($this->bonusService === null) {
            $this->bonusService = new BonusService($this);
        }
        return $this->bonusService;
    }

    /**
     * @return StateService|null
     */
    public function getStateService():? StateService
    {
        return $this->stateService;
    }

    /**
     * @return CardService|null
     */
    public function getCardService():? CardService
    {
        if ($this->cardService === null) {
            $this->cardService = $this->getService(CardService::class);
        }

        return $this->cardService;
    }

    /**
     * @param string $serviceName
     *
     * @return object|null
     *
     * @throws Exception
     */
    public function getService(string $serviceName):? object
    {
        return $this->container->get($serviceName);
    }

    public function debugArgs(string $message, array $args = []): void
    {
        $this->notifyAllPlayers('debug', $message, $args);
    }
}
