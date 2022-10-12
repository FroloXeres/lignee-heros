/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ligneeheros implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * ligneeheros.js
 *
 * ligneeheros user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.ligneeheros", ebg.core.gamegui, {
        constructor: function(){
            console.log('ligneeheros constructor');

            this.map       = [];
            this.resources = [];
            this.terrains  = [];
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( gamedatas );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
            }

            this.setupGameState(gamedatas);
            this.setupGameData(gamedatas);
            this.setupMap();

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },

        setupGameState: function(gamedatas)
        {
            this.currentState = gamedatas.currentState;
            this.decks        = gamedatas.decks;
            this.cards        = gamedatas.cards;

            this.$turn   = document.querySelector('#turn');

            this.$peopleTitle = document.querySelector('#people-title');
            this.$peopleAll = document.querySelector('#people-people');
            this.$peopleWorker = document.querySelector('#people-worker');
            this.$peopleWarrior = document.querySelector('#people-warrior');
            this.$peopleSavant = document.querySelector('#people-savant');
            this.$peopleMage = document.querySelector('#people-mage');

            this.$harvestTitle = document.querySelector('#harvest-title');
            this.$foodHarvest = document.querySelector('#harvest-food');
            this.$scienceHarvest = document.querySelector('#harvest-science');

            this.$stockTitle = document.querySelector('#stock-title');
            this.$foodStock = document.querySelector('#stock-food');
            this.$scienceStock = document.querySelector('#stock-science');
            this.$woodStock = document.querySelector('#stock-wood');
            this.$animalStock = document.querySelector('#stock-animal');
            this.$stoneStock = document.querySelector('#stock-stone');
            this.$metalStock = document.querySelector('#stock-metal');
            this.$clayStock = document.querySelector('#stock-clay');
            this.$paperStock = document.querySelector('#stock-paper');
            this.$medicStock = document.querySelector('#stock-medic');
            this.$gemStock = document.querySelector('#stock-gem');

            this.updateCartridge();
            this.initCards();
        },

        setupGameData: function(gamedatas)
        {
            this.resources = gamedatas.resources;
            for (let code in this.resources) {
                if (this.resources.hasOwnProperty(code)) {
                    this.resources[code].name = _(this.resources[code].name);
                    this.resources[code].description = _(this.resources[code].description);
                }
            }

            this.terrains  = gamedatas.terrains;
            for (let code in this.terrains) {
                if (this.terrains.hasOwnProperty(code)) {
                    this.terrains[code].name = _(this.terrains[code].name);
                }
            }

            this.map       = gamedatas.map;
        },

        setupMap: function()
        {
            let _self       = this;
            let tileTerrain = {};
            let tileContent = null;
            _self.map.forEach(function(tile) {
                tileTerrain = _self.terrains[tile.terrain];
                tileContent = _self.format_block('jstpl_tile', {
                    count: tileTerrain.resources.length,
                    resource1: tileTerrain.resources[0]? tileTerrain.resources[0] : '',
                    resource2: tileTerrain.resources[1]? tileTerrain.resources[1] : '',
                    resource3: tileTerrain.resources[2]? tileTerrain.resources[2] : '',
                    name: tileTerrain.name,
                    food: tileTerrain.food? '' : 'none',
                    foodCount: tileTerrain.food,
                    science: tileTerrain.science? '' : 'none'
                });
                dojo.place(tileContent, 'tile-content-'+tile.id);
                dojo.query('#tile-' + tile.id + ' .map-hex-content')
                    .addClass('tile_reveal tile_' + tileTerrain.code);
            });
        },

        initCards: function()
        {
            for (let type in this.cards) {
                if (this.cards.hasOwnProperty(type)) {
                    this.initLocation(this.cards[type], type);
                }
            }
        },
        initLocation: function(cardLocation, type)
        {
            for (let location in cardLocation) {
                if (cardLocation.hasOwnProperty(location)) {
                    this.initCardStore(cardLocation[location], type, location);
                }
            }
        },
        initCardStore: function(cards, type, location)
        {
            if (cards.length) {
                let visibleDeck = ['invention', 'spell'].includes(type);
                let domQuery    = visibleDeck ? type : 'floating-cards';
                if (location === 'deck' && visibleDeck) {
                    this.createDeck(type, domQuery, cards.length);
                } else {
                    this.createCardsInLocation(cards, type, location, domQuery);
                }
            }
        },
        createDeck: function(type, domQuery, count)
        {
            let deck        = this.decks[type];
            let deckContent = this.format_block('jstpl_card_verso', {
                large: deck.large ? 'large' : '',
                canDraw: deck.canDraw ? 'inactive' : '',
                type: type,
                name: deck.name,
                count: count
            });
            dojo.place(deckContent, domQuery);
        },
        createCardsInLocation: function(cards, type, location, domQuery)
        {
            let _self = this;
            cards.forEach(function(card) {
                let cardContent = _self.format_block('jstpl_card_recto', _self.replaceIconsInObject(card));
                dojo.place(cardContent, domQuery);
            });
        },

        updateCartridge: function()
        {
            this.updateTurn();
            this.updatePeople();
            this.updateHarvest();
            this.updateStock();
        },

        updateTurn: function()
        {
            this.$turn.dataset.turn = this.currentState.turn;
            this.$turn.innerHTML    = this.currentState.title.turn + ' ' + this.$turn.dataset.turn;
        },

        updatePeople: function()
        {
            this.$peopleTitle.innerHTML = this.currentState.title.people;

            this.$peopleAll.dataset.count = this.currentState.peopleCount;
            this.$peopleWorker.dataset.count = this.currentState.workerCount;
            this.$peopleWarrior.dataset.count = this.currentState.warriorCount;
            this.$peopleSavant.dataset.count = this.currentState.savantCount;
            this.$peopleMage.dataset.count = this.currentState.mageCount;
        },

        updateHarvest: function()
        {
            this.$harvestTitle.innerHTML = this.currentState.title.harvest;

            this.$foodHarvest.dataset.count = this.currentState.foodProduction;
            this.$scienceHarvest.dataset.count = this.currentState.scienceProduction;
        },

        updateStock: function()
        {
            this.$stockTitle.innerHTML = this.currentState.title.stock;

            this.$foodStock.dataset.count = this.currentState.foodStock;
            this.$scienceStock.dataset.count = this.currentState.scienceStock;

            this.$woodStock.dataset.count = this.currentState.woodStock;
            this.$animalStock.dataset.count = this.currentState.animalStock;
            this.$stoneStock.dataset.count = this.currentState.stoneStock;
            this.$metalStock.dataset.count = this.currentState.metalStock;
            this.$clayStock.dataset.count = this.currentState.clayStock;
            this.$paperStock.dataset.count = this.currentState.paperStock;
            this.$medicStock.dataset.count = this.currentState.medicStock;
            this.$gemStock.dataset.count = this.currentState.gemStock;
        },

        replaceIconsInObject: function(cardObject)
        {
            const regex = /\[([a-z_]+)\]/ig;

            for (let attr in cardObject) {
                if (cardObject.hasOwnProperty(attr)) {
                    let value = cardObject[attr];
                    if (typeof value === 'string' || value instanceof String) {
                        cardObject[attr] = value.replaceAll(regex, '<div class="icon cube $1"></div>');
                    }
                }
            }

            return cardObject;
        },

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName ) {
            case 'GameInit' :
                console.log('GameInit');
                break;
            case 'ChooseLineage' :
                console.log('ChooseLineage');
                break;
            case 'DrawObjective' :
                console.log('DrawObjective');
                break;
            /* Example:
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/ligneeheros/ligneeheros/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your ligneeheros.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },

        // TODO: from this point and below, you can write your game notifications handling methods

        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
