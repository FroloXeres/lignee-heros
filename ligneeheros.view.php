<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ligneeheros implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * ligneeheros.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in ligneeheros_ligneeheros.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );

use LdH\Repository\MapRepository;
use LdH\Service\MapService;
use LdH\Entity\Deck;

class view_ligneeheros_ligneeheros extends game_view
{
    function getGameName() {
        return "ligneeheros";
    }

    /**
     * Create default tiles for hex map
     */
    function populateMapBlock() {
        $this->page->begin_block('ligneeheros_ligneeheros', 'MAP_TILES');

        // Load Map from Db
        $tiles = MapService::buildMapFromDb(
            self::getCollectionFromDb(MapRepository::getMapQry()),
            $this->game->terrains,
            $this->game->variants
        );

        // Prepare HTML/CSS map
        foreach ($tiles as $tile) {
            $this->page->insert_block('MAP_TILES', [
                'ID'      => $tile->getId(),
                'COORD'   => $tile->getX() . '_' . $tile->getY(),
                'CLASS'   => MapService::getClass($tile),
                'HOW_FAR' => MapService::getDistanceToDisplay($tile)
            ]);
        }
    }

    function createDecks() {
        $this->page->begin_block('ligneeheros_ligneeheros', 'DECKS');
        $this->page->begin_block('ligneeheros_ligneeheros', 'CARDS');

        foreach ($this->game->decks as $type => $deck) {
            /** @var Deck $ldhDeck */
            $ldhDeck = $this->game->cards[$type];

            $this->page->insert_block('DECKS', [
                'NAME'     => $ldhDeck->getName(),
                'TYPE'     => $type,
                'LARGE'    => $ldhDeck->isLarge()? 'large' : '',
                'CAN_DRAW' => $ldhDeck->canDraw()? '' : 'inactive',
                'COUNT'    => count($ldhDeck->getCards())
            ]);

            if ($ldhDeck->isPublic()) {
                foreach ($ldhDeck as $card) {
                    $this->page->insert_block('CARDS', [
                        'ID'          => $card->getType(),
                        'SUB_ID'      => $card->getTypeArg(),
                        'DECK'        => $type,
                        'NAME'        => $card->getName(),
                        'DESCRIPTION' => $card->getDescription()
                    ]);
                }
            }
        }
    }

  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/
        $this->populateMapBlock();

        // Create cards if needed
        $this->createDecks();

        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock -->
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "ligneeheros_ligneeheros", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
}
