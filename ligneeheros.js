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
    "dojo", "dojo/on", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/zone"
],
function (dojo, on, declare) {
    return declare("bgagame.ligneeheros", ebg.core.gamegui, {
        constructor: function(){
            this.map       = [];
            this.resources = [];
            this.terrains  = [];
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
            // Don't forget to use Mobile config: https://en.doc.boardgamearena.com/Your_game_mobile_version
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
            this.initPeople(gamedatas.people);

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },

        setupGameState: function(gamedatas)
        {
            this.currentState  = gamedatas.currentState;
            this.decks         = gamedatas.decks;
            this.cards         = gamedatas.cards;
            this.selectedCards = [];

            this.initCartridge();
            this.initCards();
            this.initEvents()
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
            this.mapZones  = {};
        },

        setupMap: function()
        {
            let _self       = this;
            let tileTerrain = {};
            let tileContent = null;
            _self.map.forEach(function(tile) {
                tileTerrain = _self.replaceIconsInObject(
                    _self.terrains[tile.terrain]
                );
                tileContent = _self.format_block('jstpl_tile', {
                    id: tile.id,
                    count: tileTerrain.resources.length,
                    resource1: tileTerrain.resources[0]? _self.getIconAsText(tileTerrain.resources[0]) : '',
                    resource2: tileTerrain.resources[1]? _self.getIconAsText(tileTerrain.resources[1]) : '',
                    resource3: tileTerrain.resources[2]? _self.getIconAsText(tileTerrain.resources[2]) : '',
                    name: tileTerrain.name,
                    bonus: tileTerrain.bonusAsTxt,
                    food: tileTerrain.food? '' : 'none',
                    foodCount: tileTerrain.food,
                    foodIcon: _self.getIconAsText('food'),
                    science: tileTerrain.science? '' : 'none',
                    scienceIcon: _self.getIconAsText('science')
                });
                dojo.place(tileContent, 'tile-content-'+tile.id);
                dojo.query('#tile-' + tile.id + ' .map-hex-content')
                    .addClass('tile_reveal tile_' + tileTerrain.code);
            });

            _self.initMapZones();
            _self.scrollToTile(0, 0);
        },

        initMapZones: function()
        {
            const _self = this;
            const tiles = dojo.query('.tile:not(.tile_disabled)');
            tiles.forEach(function(tile) {
                let item = tile.closest('.map-hex-item');
                let id = item.id.replace('tile-', '');

                ['warrior', 'worker', 'mage', 'savant', 'lineage'].forEach(function(unitType) {
                    let zone = new ebg.zone();
                    let code = _self.getZoneIdByTileIdAndType(id, unitType);
                    zone.create(_self, code);
                    zone.setPattern('custom');

                    _self.createProxy(zone, 'updateDisplay', _self.zoneDisplayItemsMiddleWare);
                    zone.itemIdToCoords = _self.mapZoneCoords;
                    _self.mapZones[code] = zone;
                });
            });
        },

        /**
         * @param {Object}   object     Object to override
         * @param {String}   method     Method of object to override
         * @param {function} extended   Extended method for object
         */
        createProxy: function(object, method, extended)
        {
            const existingMethod = object[method];
            object[method] = function () {
                existingMethod.call(object);
                extended.call(object);
            };
        },

        zoneDisplayItemsMiddleWare: function()
        {
            const _self = this;
            const countByWeight = [0, 0, 0];
            const idByWeight = [null, null, null];
            this.items.forEach(function(item) {
                countByWeight[item.weight]++;
                idByWeight[item.weight] = item.id;
            });
            countByWeight.forEach(function(count, weight) {
                const $units = dojo.query('#'+idByWeight[weight]);
                if (!$units.length) return ;
                if (countByWeight[weight] > 1) {
                    $units[0].setAttribute('data-count', count);
                } else {
                    $units[0].removeAttribute('data-count');
                }
            });
        },

        mapZoneCoords: function(i, width, maxWidth, count) {
            const item = this.items[i];

            return {
                x: item.weight * (width - this.item_margin),
                y: 0,
                w: width,
                h: width
            };
        },

        getZoneIdByTileIdAndType: function(id, type)
        {
            let zoneType = 'lineage';
            switch (type) {
                case 'warrior':
                case 'worker':
                case 'mage':
                case 'savant':
                    zoneType = type;
                    break;
            }

            return 'map-explore-' + id + '-' + zoneType;
        },

        initPeople: function(people)
        {
            this.initMapPeople(people.byPlace.map, people.units);
            this.initInventionPeople(people.byPlace.invention, people.units);
            this.initSpellPeople(people.byPlace.spell, people.units);
        },

        initInventionPeople: function(byInvention, units)
        {

        },

        initSpellPeople: function(bySpell, units)
        {

        },

        getDomIdByUnit: function(unit)
        {
            return 'unit-' + unit.id;
        },

        initMapPeople: function(byMap, units)
        {
            for (let location in byMap) {
                let key = byMap[location];
                let unit = units[key];

                let zone = this.mapZones[this.getZoneIdByTileIdAndType(unit.location, unit.type)];
                const domUnitId = this.getDomIdByUnit(unit);
                let domUnits = dojo.query(domUnitId);
                if (domUnits.length) {
                    // Move to new place ?
                    //zone.move

                    this.updateUnitStatus(domUnits[0], unit.status);
                } else {
                    // Add new unit to map
                    this.createUnit(unit.type, domUnitId, unit.status);
                    zone.placeInZone(domUnitId, this.getUnitStatusPriority(unit.status));
                }
            }
        },

        getUnitStatusPriority: function(status)
        {
            switch (status) {
                case 'acted': return 0;
                case 'moved': return 1;
                default: return 2;
            }
        },

        updateUnitStatus: function(domUnit, status)
        {
            domUnit.classList.remove('free');
            domUnit.classList.remove('moved');
            domUnit.classList.remove('acted');

            domUnit.classList.add(status);
        },

        createUnit: function(iconId, domUnitId, unitStatus)
        {
            const newUnit = this.getWrappedIcon(iconId, domUnitId, unitStatus);
            dojo.place(newUnit, 'new-unit');
        },

        scrollToTile: function(x, y)
        {
            const map = dojo.query('#map-zone');
            const tile = dojo.query('[data-coord="'+x+'_'+y+'"]');
            if (map.length && tile.length) {
                map[0].scrollTo(tile[0].offsetLeft / 2, tile[0].offsetTop / 2);
            }
        },

        getCard: function(type, location, id)
        {
            if (
                this.cards[type] !== 'undefined' &&
                this.cards[type][location] !== 'undefined'
            ) {
                return this.cards[type][location].find(card => card.id === id);
            }
            return {};
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
                let cardTpl = _self.replaceIconsInObject(card);
                cardTpl.textAsIcons = (cardTpl.text.indexOf('svg') !== -1) ? 'text_as_icon' : '';

                let cardContent = _self.format_block('jstpl_card_recto', cardTpl);
                cardContent = cardContent.replaceAll('[none]', '');

                const iconify = ['lineage', 'objective', 'spell', 'invention'];
                if (iconify.includes(card.deck)) {
                    cardContent = cardContent.replaceAll('['+card.icon+']', _self.getIconAsText(card.icon));
                    ['science', 'fight', 'city', 'growth', 'nature', 'spell', 'healing', 'foresight', 'science'].forEach(
                        (iconId) => cardContent = cardContent.replace('['+iconId+']', _self.getIconAsText(iconId))
                    );
                }
                if (card.deck === 'lineage') {
                    ['objective', 'leading', 'fight', 'end_turn', card.meeple].forEach(
                        (iconId) => cardContent = cardContent.replace('['+iconId+']', _self.getIconAsText(iconId))
                    );
                }

                dojo.place(cardContent, domQuery);
            });
        },

        initEvents: function()
        {
            this.evts = {};
            this.evts['onChooseLineage'] = on(dojo.query('#floating-cards'), 'click', this.onChooseLineage.bind(this));


        },

        removeEvent: function(key)
        {
            if (typeof this.evts[key] !== 'undefined') {
                this.evts[key].remove();
                delete this.evts[key];
            }
        },

        onChooseLineage: function(event)
        {
            const card = event.target.closest('.card.lineage');
            if (card === null) return;

            dojo.query('#floating-cards .card').forEach((thisCard) => {thisCard.classList.remove('selected');});
            card.classList.add('selected');

            const id = card.getAttribute('data-id');
            const selectedCard = this.getCard('lineage', 'deck', id);
            this.selectedCards = [id];

            dojo.query('#pagemaintitletext').text(_('You choose to play with lineage: ') + selectedCard.name);

            dojo.query('#chooseLineage')[0].classList.remove('hidden');
            dojo.query('#cancelChooseLineage')[0].classList.remove('hidden');
        },

        getIcon(iconId, addClass = '')
        {
            const $clone = document.querySelector('#icons svg#'+iconId).cloneNode(true);
            if (addClass.length) {
                $clone.classList.add(addClass);
            }

            return $clone;
        },
        getWrappedIcon(iconId, wrapId, cssClass = '')
        {
            return '<div id="' + wrapId + '" class="wrapped-icon">' + this.getIconAsText(iconId, cssClass) + '</div>';
        },
        getIconAsText(iconId, cssClass = '')
        {
            let addClass = cssClass.length ? [cssClass] : [];
            if (['worker', 'warrior', 'savant', 'mage', 'all', 'monster'].includes(iconId)) {
                addClass.push(iconId);
                iconId = 'unit';
            }

            const $icon = document.querySelector('#icons svg#'+iconId)
            if ($icon !== null) {
                const $clone = $icon.cloneNode(true);
                if (addClass.length) {
                    addClass.forEach((cssClass) => $clone.classList.add(cssClass));
                }
                return $clone.outerHTML;
            } else return iconId;
        },

        initCartridge: function()
        {
            const cartridge = this.format_block('jstpl_cartridge', {turn: 1});
            dojo.place(cartridge, 'player_boards', 'before');

            this.$turn   = document.querySelector('h2#turn');

            this.$peopleTitle = document.querySelector('#people-title');
            this.$peopleAll = document.querySelector('#people-people');

            this.$peopleWorker = document.querySelector('#people-worker');
            this.$peopleWarrior = document.querySelector('#people-warrior');
            this.$peopleSavant = document.querySelector('#people-savant');
            this.$peopleMage = document.querySelector('#people-mage');
            dojo.place(this.getIcon('unit', 'worker'), this.$peopleWorker, 'first');
            dojo.place(this.getIcon('unit', 'warrior'), this.$peopleWarrior, 'first');
            dojo.place(this.getIcon('unit', 'savant'), this.$peopleSavant, 'first');
            dojo.place(this.getIcon('unit', 'mage'), this.$peopleMage, 'first');

            this.$harvestTitle = document.querySelector('#harvest-title');
            this.$foodHarvest = document.querySelector('#harvest-food');
            this.$scienceHarvest = document.querySelector('#harvest-science');
            dojo.place(this.getIcon('food'), this.$foodHarvest, 'first');
            dojo.place(this.getIcon('science'), this.$scienceHarvest, 'first');

            this.$militaryTitle = document.querySelector('#military-title');
            this.$powerMilitary = document.querySelector('#military-power');
            this.$defenseMilitary = document.querySelector('#military-defense');
            dojo.place(this.getIcon('power'), this.$powerMilitary, 'first');
            dojo.place(this.getIcon('defense_warrior'), this.$defenseMilitary, 'first');

            this.$cityTitle = document.querySelector('#city-title');
            this.$cityLife = document.querySelector('#city-life');
            this.$cityDefense = document.querySelector('#city-defense');
            dojo.place(this.getIcon('growth'), this.$cityLife, 'first');
            dojo.place(this.getIcon('defense_city'), this.$cityDefense, 'first');

            this.$stockTitle = document.querySelector('#stock-title');
            this.$foodStock = document.querySelector('#stock-food');
            this.$scienceStock = document.querySelector('#stock-science');
            dojo.place(this.getIcon('food_stock'), this.$foodStock, 'first');
            dojo.place(this.getIcon('science_stock'), this.$scienceStock, 'first');

            this.$woodStock = document.querySelector('#stock-wood');
            this.$animalStock = document.querySelector('#stock-animal');
            this.$stoneStock = document.querySelector('#stock-stone');
            this.$metalStock = document.querySelector('#stock-metal');
            this.$clayStock = document.querySelector('#stock-clay');
            this.$paperStock = document.querySelector('#stock-paper');
            this.$medicStock = document.querySelector('#stock-medic');
            this.$gemStock = document.querySelector('#stock-gem');
            dojo.place(this.getIcon('wood'), this.$woodStock, 'first');
            dojo.place(this.getIcon('animal'), this.$animalStock, 'first');
            dojo.place(this.getIcon('stone'), this.$stoneStock, 'first');
            dojo.place(this.getIcon('metal'), this.$metalStock, 'first');
            dojo.place(this.getIcon('clay'), this.$clayStock, 'first');
            dojo.place(this.getIcon('paper'), this.$paperStock, 'first');
            dojo.place(this.getIcon('medic'), this.$medicStock, 'first');
            dojo.place(this.getIcon('gem'), this.$gemStock, 'first');

            this.updateCartridge();
        },
        updateCartridge: function()
        {
            this.updateTurn();
            this.updatePeople();
            this.updateHarvest();
            this.updateMilitary();
            this.updateCity();
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

        updateMilitary: function()
        {
            this.$militaryTitle.innerHTML = this.currentState.title.military;

            this.$powerMilitary.dataset.count = this.currentState.warriorPower;
            this.$defenseMilitary.dataset.count = this.currentState.warriorDefense;
        },

        updateCity: function()
        {
            this.$cityTitle.innerHTML = this.currentState.title.city;

            this.$cityLife.dataset.count = this.currentState.life;
            this.$cityDefense.dataset.count = this.currentState.cityDefense;
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
            for (let attr in cardObject) {
                if (cardObject.hasOwnProperty(attr)) {
                    let value = cardObject[attr];
                    if (typeof value === 'string' || value instanceof String) {
                        [... value.matchAll(/\[invention\] \[([a-z_]+)\]/ig)].forEach((found) => {
                            value = value.replace(found[0], '<div class="double">'+this.getIconAsText('invention')+this.getIconAsText(found[1])+'</div>');
                        });
                        [... value.matchAll(/\[spell\] \[([a-z_]+)\]/ig)].forEach((found) => {
                            value = value.replace(found[0], '<div class="double">'+this.getIconAsText('spell')+this.getIconAsText(found[1])+'</div>');
                        });
                        [... value.matchAll(/\[([a-z_]+)\]/ig)].forEach((found) => {
                            value = value.replace(found[0], this.getIconAsText(found[1]));
                        });
                        cardObject[attr] = value;
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
            switch( stateName ) {
            case 'ChooseLineage' :
                const chooseBtn = dojo.query('#chooseLineage');
                const cancelBtn = dojo.query('#cancelChooseLineage');
                if (chooseBtn.length && cancelBtn.length) {
                    chooseBtn.forEach((elt) => elt.classList.add('hidden'));
                    cancelBtn.forEach((elt) => elt.classList.add('hidden'));
                }
                break;
            case 'DrawObjective' :
                console.log('DrawObjective');
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            switch( stateName ) {
            case 'ChooseLineage':
                dojo.query('#floating-cards .card.lineage').remove();
                this.removeEvent('onChooseLineage');
                break;
            }
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName ) {
                    case 'ChooseLineage' :
                        this.addActionButton( 'chooseLineage', _('Yes'), 'onSelectLineage' );
                        this.addActionButton( 'cancelChooseLineage', _('No'), 'onUnselectLineage' );
                        break;
/*
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
        
            Here, you can define some utility methods that you can use everywhere in your javascript
            script.
        
        */
        ajaxCallWrapper: function(action, args, onResponse, onError)
        {
            if (typeof  args === 'undefined' || typeof args.lock === 'undefined') {
                args.lock = true;
            }
            if (typeof onResponse === 'undefined') {
                onResponse = (result) => {console.log(result);};
            }
            if (typeof onError === 'undefined') {
                onError = (error) => {console.log(error);};
            }

            this.ajaxcall(
                '/' + this.game_name + '/' + this.game_name + '/' + action + '.html',
                args,
                this,
                onResponse,
                onError
            );
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        onSelectLineage: function()  {
            if (this.selectedCards[0] !== 'undefined' && this.checkAction('selectLineage')) {
                this.ajaxCallWrapper('selectLineage', {lineage: this.selectedCards[0]});
            }
        },

        onUnselectLineage: function() {
            this.selectedCards = [];
            dojo.query('#floating-cards .card').forEach((thisCard) => {
                thisCard.classList.remove('selected');
            });

            dojo.query('#pagemaintitletext').text(_('Please, select your lineage'));
            dojo.query('#chooseLineage')[0].classList.add('hidden');
            dojo.query('#cancelChooseLineage')[0].classList.add('hidden');
        },

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
            dojo.subscribe( 'debug', this, "onDebug" );

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

        onDebug: function(sentData)
        {
            console.log(sentData);
        }
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
