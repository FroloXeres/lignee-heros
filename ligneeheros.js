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

            this.status = {
                isInitializing: false,
                state: {
                    lineageChosen: true,

                },
                currentState: {},

            };
            this.actions = {};
            this.indexed = {};
            this.playerLineage = null;
            this.playerUnit = null;
            this.$fullScreen = document.getElementById('fullscreen-message');
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

            this.isActive = gamedatas.isActive;
            new Promise((resolve) => {
                this.status.isInitializing = true;
                resolve(gamedatas);
            })
            .then(this.setupGameState.bind(this))
            .then(() => this.setupGameData(gamedatas))
            .then(() => this.initCards(gamedatas))
            .then(() => this.setupMap())
            .then(() => this.initPeople(gamedatas.people))
            .then(() => this.initTooltips(gamedatas.tooltips))
            .then(() => this.setupNotifications())
            .then(() => this.status.isInitializing = false)
            .then(() => this.postInitCardUpdate())
            .then(() => this.initInteractEvents());
        },

        setupGameState: function(gamedatas)
        {
            //console.log('Gate state init')
            this.status.currentState  = gamedatas.currentState;
            this.initCartridge();
            this.initEvents()
        },

        setupGameData: function(gamedatas)
        {
            //console.log('Init resources/map');
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
            //console.log('Init map');
            let _self       = this;
            let tileTerrain = {};
            let tileContent = null;
            for (let tileId in _self.map) {
                let tile = _self.map[tileId];

                let harvested = {
                    resource1: tile.resource1 === true ? ' used' : '',
                    resource2: tile.resource2 === true ? ' used' : '',
                    resource3: tile.resource3 === true ? ' used' : ''
                };
                tileTerrain = _self.replaceIconsInObject(
                    _self.terrains[tile.terrain]
                );
                tileContent = _self.format_block('jstpl_tile', {
                    id: tile.id,
                    count: tileTerrain.resources.length,
                    resource1: tileTerrain.resources[0]? _self.getIconAsText(tileTerrain.resources[0]) : '',
                    resource2: tileTerrain.resources[1]? _self.getIconAsText(tileTerrain.resources[1]) : '',
                    resource3: tileTerrain.resources[2]? _self.getIconAsText(tileTerrain.resources[2]) : '',
                    resource1Class: tileTerrain.resources[0]? tileTerrain.resources[0] + harvested.resource1 : '',
                    resource2Class: tileTerrain.resources[1]? tileTerrain.resources[1] + harvested.resource2 : '',
                    resource3Class: tileTerrain.resources[2]? tileTerrain.resources[2] + harvested.resource3 : '',
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
            }

            _self.initMapZones();
            _self.initZoom();
            _self.scrollToTile(0, 0);
        },

        initZoom: function()
        {
            this.ZOOMS = [50, 70, 90, 110, 130];
            this.MAX_ZOOM = 4;
            this.MIN_ZOOM = 0;
            this.$mapZone = dojo.query('.map-hex-grid')[0];
            this.$zoom = dojo.query('#zoom')[0];
            this.$zoomOut = dojo.query('#zoom_out_icon')[0];
            this.$zoomIn = dojo.query('#zoom_in_icon')[0];
            on(this.$zoomIn, 'click', this.onZoomIn.bind(this));
            on(this.$zoomOut, 'click', this.onZoomOut.bind(this));
        },

        onZoomIn: function()
        {
            if (this.getZoom() < this.MAX_ZOOM) {
                this.incDecZoom();
                this.applyZoom();
                this.toggleZoom();
            }
        },
        onZoomOut: function()
        {
            if (this.getZoom() > this.MIN_ZOOM) {
                this.incDecZoom(false);
                this.applyZoom();
                this.toggleZoom();
            }
        },
        toggleZoom: function () {
            this.$zoomIn.style.color = (this.getZoom() === this.MAX_ZOOM) ? '#666' : '#000';
            this.$zoomOut.style.color = (this.getZoom() === this.MIN_ZOOM) ? '#666' : '#000';
        },
        applyZoom: function() {
            this.$mapZone.style.width = this.ZOOMS[this.getZoom()]+'em';
            this.updateMapZones();
        },
        getZoom: function() {return parseInt(this.$zoom.dataset.zoom);},
        incDecZoom: function(inc = true) {this.$zoom.dataset.zoom = this.getZoom() + (inc ? 1 : -1);},

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

                    _self.addMiddleware(zone, 'updateDisplay', _self.zoneDisplayItemsMiddleWare);
                    zone.itemIdToCoords = _self.mapZoneCoords;
                    _self.mapZones[code] = zone;
                });
            });
        },
        updateMapZones: function()
        {
            for (let code in this.mapZones) {
                if (this.mapZones.hasOwnProperty(code)) {
                    this.mapZones[code].updateDisplay();
                }
            }
        },

        /**
         * @param {Object}   object         Object
         * @param {String}   method         Method of object on witch condition is applied
         * @param {function} conditional    Conditional method
         */
        addConditionalCheck: function(object, method, conditional)
        {
            const existingMethod = object[method];
            object[method] = function () {
                if (conditional()) {
                    existingMethod.call(object);
                }
            };
        },

        /**
         * @param {Object}   object     Object to override
         * @param {String}   method     Method of object to override
         * @param {function} extended   Extended method for object
         * @param {Boolean}  before     Execute middleware method before
         */
        addMiddleware: function(object, method, extended, before = true)
        {
            const existingMethod = object[method];
            object[method] = function () {
                before ? extended.call(object) : existingMethod.call(object);
                before ? existingMethod.call(object) : extended.call(object);
            };
        },

        zoneDisplayItemsMiddleWare: function()
        {
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
                x: 0,
                y: item.weight * (width - this.item_margin),
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

        // All about People/Unit
        initPeople: function(people)
        {
            //console.log('Init people');
            this.initMapPeople(people.byPlace.map, people.byType, people.units);
            this.initInventionPeople(people.byPlace.invention, people.units);
            this.initSpellPeople(people.byPlace.spell, people.units);
        },
        initInventionPeople: function(byInvention, units)
        {
            //console.log('Init invention people');
        },
        initSpellPeople: function(bySpell, units)
        {
            //console.log('Init spell people');
        },
        getDomIdByUnit: function(unit)
        {
            return 'unit-' + unit.id;
        },
        initMapPeople: function(byMap, byType, units)
        {
            //console.log('Init map people');
            // Save player unit if defined
            if (this.playerLineage) {
                let id = byType[this.playerLineage.meeple];
                this.playerUnit = units[id];
            }

            for (let id in byMap) {
                let key = byMap[id];
                let unit = units[key];
                if(this.playerUnit && unit.type === this.playerUnit.type) continue;

                this.updateOrCreateUnit(unit);
            }

            // Be sure to render player unit at the end
            if (this.playerUnit) {
                this.updateOrCreateUnit(this.playerUnit);
            }
        },
        updateOrCreateUnit: function(unit)
        {
            let zoneId = this.getZoneIdByTileIdAndType(unit.location, unit.type);
            let zone = this.mapZones[zoneId];
            const domUnitId = this.getDomIdByUnit(unit);
            let domUnits = dojo.query(domUnitId);
            if (domUnits.length) {
                // todo: Move to new place ?
                //zone.move

                this.updateUnitStatus(domUnits[0], unit.status);
            } else {
                // Add new unit to map
                this.createUnit(unit.type, domUnitId, unit.status);

                // todo: Animate unit creation (From board to map)

                zone.placeInZone(domUnitId, this.getPriorityByUnitStatus(unit.status));
            }
        },
        getPriorityByUnitStatus: function(status)
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

        // All about cards
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
        initCards: function(gamedatas)
        {
            //console.log('Init cards');
            this.selectedCards = [];

            // Init zones
            this.cardZones = {
                invention: {deck: null, onTable: null, hand: null},
                spell: {deck: null, onTable: null, hand: null},
                floating: null,
                overall: null,
            };
            for (let type in this.cardZones) {
                for (let location in this.cardZones[type]) {
                    this.cardZones[type][location] = this.createCardZone(type+'-'+location);
                }
            }

            // Floating-cards
            this.cardZones.floating = this.createCardZone('floating-cards');
            this.cardZones.overall = this.createCardZone('overall-cards');

            // Init cards
            this.cards = gamedatas.cards;
            for (let type in this.cards) {
                if (['invention', 'spell'].includes(type) && this.cards.hasOwnProperty(type)) {
                    this.initLocation(this.cards[type], type);
                }
            }

            // Lineage in hands // Others are displayed only on ChooseLineageState
            if (this.cards?.lineage?.hand?.length) {
                this.initPlayerLineages(this.cards.lineage.hand, this.cards?.objective?.hand);
            }

            this.indexCards();
        },
        indexCards: function()
        {
            const _self = this;
            for (let type in _self.cards) {
                const locations = _self.cards[type];
                for (let location in locations) {
                    const cards = locations[location];
                    for (let id in cards) {
                        const card = cards[id];
                        _self.indexed[card.id] = card;
                    }
                }
            }
        },
        postInitCardUpdate: function()
        {
            //console.log('Post init cards');
            if (this.cardZones.invention.deck.items.length) {
                this.cardZones.invention.deck.updateDisplay();
            }
            if (this.cardZones.invention.onTable.items.length) {
                this.cardZones.invention.onTable.updateDisplay();
            }
            if (this.cardZones.invention.hand.items.length) {
                this.cardZones.invention.hand.updateDisplay();
            }
            if (this.cardZones.spell.deck.items.length) {
                this.cardZones.spell.deck.updateDisplay();
            }
            if (this.cardZones.spell.onTable.items.length) {
                this.cardZones.spell.onTable.updateDisplay();
            }
            if (this.cardZones.spell.onTable.items.length) {
                this.cardZones.spell.onTable.updateDisplay();
            }
            if (this.cardZones.floating.items.length) {
                this.cardZones.floating.updateDisplay();
            }
            if (this.cardZones.overall.items.length) {
                this.cardZones.overall.updateDisplay();
            }
        },
        initInteractEvents: function()
        {
            this.evts['onHarvestResource'] = on(dojo.query('.resource.interactive'), 'click', this.onHarvestResource.bind(this));
        },

        initPlayerLineages: function(lineages, objectives = null)
        {
            const _self = this;
            lineages.forEach(function(lineage) {
                _self.initPlayerLineage(lineage);
            });

            if (objectives.length && objectives[0].id) {
                this.initPlayerObjective(objectives[0]);
            }
        },
        initPlayerLineage: function (lineage)
        {
            if (lineage.location_arg === this.player_id) {
                this.playerLineage = lineage;
            }

            const _self = this;
            lineage = _self.replaceIconsInObject(lineage);
            const lineageBoard = _self.format_block('jstpl_lineage_board', {
                playerId: lineage.location_arg,
                name: lineage.name,
                lineageIcon: _self.getIconAsText('lineage'),
                meeple: _self.getIconAsText(lineage.meeple),
                meeplePower: lineage.meeplePower,
                objectiveCompleted: lineage.completed ? 'icon-complete' : '',
                objectiveIcon: _self.getIconAsText('objective'),
                objective: lineage.objective,
                leader: lineage.leader ? 'leader' : '',
                leadingIcon: _self.getIconAsText('leading'),
                leadTypeIcon: _self.getIconAsText(lineage.leadType),
                leadType: lineage.leadType,
                leadPower: lineage.leadPower
            });

            dojo.place(lineageBoard, 'overall_player_board_' + lineage.location_arg, 'end');
        },
        initPlayerObjective: function(objective)
        {
            const qryPic = '#overall_player_board_' + this.player_id + ' .hidden-one picture';
            const qryLabel = '#overall_player_board_' + this.player_id + ' .hidden-one label';

            objective = this.replaceIconsInObject(objective);
            const $pic = dojo.query(qryPic);
            if ($pic.length) {
                $pic[0].title = objective.name;
                $pic[0].className = objective.completed ? 'icon-complete' : '';
                $pic[0].innerHTML = this.getIconAsText(objective.icon);
            }

            const $label = dojo.query(qryLabel);
            if ($label.length) {
                $label[0].innerHTML = objective.text;
            }
        },
        initLineageCards: function()
        {
            this.distributeCards('lineage', 'deck');
        },
        distributeCards: function (type, location)
        {
            if (this.cards === undefined) this.cards = this.gamedatas.cards;
            if (this.cards[type] === undefined || this.cards[type][location] === undefined) return ;

            let wait = 500;
            this.createDeck(type, this.cards[type][location].length, 'overall-cards', false);
            this.cards[type][location].forEach((card) => {
                window.setTimeout(() => this.moveCardFromDeckToLocation(card, type, location), wait);
                wait += 1000;
            });
        },
        createCardZone: function(code)
        {
            const _self = this;
            let zone = new ebg.zone();
            zone.create(this, code);
            zone.setPattern('custom');

            switch (code) {
                case 'floating-cards':
                    zone.itemIdToCoords = this.lineageCardZoneCoords;
                    break;
                case 'spell-deck':
                case 'invention-deck':
                case 'overall-cards':
                    zone.itemIdToCoords = function() {return {x: 0, y: 0, w: 155, h: 245};};
                    break;
                default:
                    zone.itemIdToCoords = this.cardZoneCoords;
            }
            this.addConditionalCheck(zone, 'updateDisplay', () => !_self.isInitializing);

            return zone;
        },
        lineageCardZoneCoords: function(i, zoneWidth, zoneHeight, count) {
            this.item_margin = 15;
            let cardWidth = 228;
            let pileCnt = Math.floor((zoneWidth + this.item_margin) / (cardWidth + this.item_margin));
            let modulo = i % pileCnt;
            let line = Math.floor(i / pileCnt);
            return {
                x: modulo * (cardWidth + this.item_margin),
                y: line * (356 + this.item_margin),
                w: cardWidth,
                h: 356
            };
        },
        cardZoneCoords: function(i, zoneWidth, zoneHeight, count) {
            let cardWidth = 155;
            let pileCnt = Math.floor((zoneWidth + this.item_margin) / (cardWidth + this.item_margin));
            let modulo = i % pileCnt;
            let line = Math.floor(i / pileCnt);
            return {
                x: modulo * (cardWidth + this.item_margin) + (this.item_margin * (line - 1)),
                y: line * 30,
                w: cardWidth,
                h: 245
            };
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
                if (location === 'deck' && visibleDeck) {
                    this.createDeck(type, cards.length);
                } else {
                    this.createCardsInLocation(cards, type, location);
                }
            }
        },
        createDeck: function(type, count, place = 'new-card', moveInZone = true)
        {
            let deck        = this.gamedatas.decks[type];
            let deckContent = this.format_block('jstpl_card_verso', {
                large: deck.large ? 'large' : '',
                canDraw: deck.canDraw ? 'inactive' : '',
                type: type,
                name: deck.name,
                count: count
            });
            dojo.place(deckContent, place);

            if (moveInZone) {
                this.cardZones[type]['deck'].placeInZone('deck-' + type, 0);
            }
        },
        createCardsInLocation: function(cards, type, location)
        {
            const _self = this;
            let flip = type === 'lineage' || type === 'objective';
            cards.forEach(function(card) {
                let cardId = _self.createCardInLocation(card, type, location, 'new-card', flip);
                _self.moveCardToZone(card, cardId, type, location);
            });
        },
        moveCardToZone: function(card, cardId, type, location)
        {
            const target = !['invention', 'spell'].includes(type) ? this.cardZones.floating : this.cardZones[type][location];
            target.placeInZone(cardId, this.getPriorityByCard(card));
        },
        createCardInLocation: function(card, type, location, where, flip = false)
        {
            let cardId = card.id;
            let cardTpl = this.replaceIconsInObject(card);
            cardTpl.textAsIcons = (cardTpl.text.indexOf('svg') !== -1) ? 'text_as_icon' : '';

            let cardContent = this.format_block('jstpl_card_recto', cardTpl);
            cardContent = cardContent.replaceAll('[none]', '');

            const iconify = ['lineage', 'objective', 'spell', 'invention'];
            if (iconify.includes(card.deck)) {
                cardContent = cardContent.replaceAll('['+card.icon+']', this.getIconAsText(card.icon));
                ['science', 'fight', 'city', 'growth', 'nature', 'spell', 'healing', 'foresight', 'science'].forEach(
                    (iconId) => cardContent = cardContent.replace('['+iconId+']', this.getIconAsText(iconId))
                );
            }
            if (card.deck === 'lineage') {
                ['objective', 'leading', 'fight', 'end_turn', card.meeple].forEach(
                    (iconId) => cardContent = cardContent.replace('['+iconId+']', this.getIconAsText(iconId))
                );
            }

            if (flip) {
                cardTpl.deckType = type;
                let versoContent = this.format_block('jstpl_card_recto_back', cardTpl);
                let $div = document.createElement('div');
                let $divInner = document.createElement('div');
                cardId = 'flip-' + card.id;
                $div.id = cardId;
                $div.classList.add('flip');
                $divInner.classList.add('flip-inner');
                $divInner.innerHTML = versoContent + cardContent;
                $div.appendChild($divInner);
                document.getElementById(where).appendChild($div);
                this.cardZones.overall.placeInZone(cardId, 0);

                window.setTimeout(() => document.getElementById(cardId).classList.add('flipped'), 100);
            } else {
                dojo.place(cardContent, where);
            }

            return cardId;
        },
        moveCardFromDeckToLocation: function(card, type, location)
        {
            let $deck = document.getElementById('deck-' + type);
            let counter = this.changeDeckCounter($deck, -1);
            let cardId = this.createCardInLocation(card, type, location, 'overall-cards', true);
            this.moveCardToZone(card, cardId, type, location);

            if (!counter) {
                $deck.remove();
            }
        },
        changeDeckCounter: function ($deck, change)
        {
            let $counter = $deck.getElementsByClassName('counter')[0];
            let counter = parseInt($counter.innerHTML);
            let newCounter = counter + change;
            $counter.innerHTML = newCounter;

            return newCounter;
        },
        getPriorityByCard: function(card)
        {
            let priority;
            switch (card.type) {
                default: priority = 10;
            }
            priority += card.name.charCodeAt(0);

            return priority;
        },

        // Tooltips
        initTooltips: function(tooltips)
        {
            //console.log('Init tooltips');
            for (let domId in tooltips.id) {
                if (tooltips.id.hasOwnProperty(domId)) {
                    this.addTooltipToId(domId, tooltips.id[domId]);
                }
            }
            for (let cssClass in tooltips.class) {
                if (tooltips.class.hasOwnProperty(cssClass)) {
                    this.addTooltipByClass(cssClass, tooltips.class[cssClass]);
                }
            }
        },
        addTooltipByClass: function(key, data)
        {
            const _self = this;
            let targets = dojo.query(key);
            targets.forEach(function(target) {
                _self.addTooltipToId(target.id, data);
            });
        },
        addTooltipToId: function (key, data) {
            let hasAction = data instanceof Array;
            let info = hasAction ? _(data[0]) : _(data);
            let action = hasAction ? _(data[1]) : '';

            let target = document.getElementById(key);
            if (target !== null && (info.includes('%count%') || action.includes('%count%'))) {
                let count = target.dataset.count ? target.dataset.count : 1;
                info = info.replace('%count%', count);
                action = action.replace('%count%', count);
            }

            this.addTooltip(key, info, action);
        },

        initEvents: function()
        {
            //console.log('Init events');
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
            if (this.status.state.lineageChosen) return;

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
            return '<div id="' + wrapId + '" class="wrapped-icon interactive ' + iconId + ' ' + cssClass + '">' + this.getIconAsText(iconId, cssClass) + '</div>';
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
            //console.log('Init cartridge');
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

        updateCartridgeElement: function($element, value, duration = 1000)
        {
            let incFct = value < 0 ?
                () => {$element.dataset.count = parseInt($element.dataset.count) - 1} :
                () => {$element.dataset.count = parseInt($element.dataset.count) + 1}
            ;
            let howMany = Math.abs(value);
            let part = Math.floor(duration / howMany);
            for (let i = 1; i <= howMany; i++) {
                window.setTimeout(incFct, part * i);
            }
        },

        updateCartridgeStock: function($element, value, duration = 1000)
        {
            let incFct = value < 0 ?
                function() {$element.dataset.stock = parseInt($element.dataset.stock) - 1} :
                function() {$element.dataset.stock = parseInt($element.dataset.stock) + 1}
            ;
            let howMany = Math.abs(value);
            let part = Math.floor(duration / howMany);
            for (let i = 1; i <= howMany; i++) {
                window.setTimeout(incFct, part * i);
            }
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

            this.$turn.dataset.turn = this.status.currentState?.turn || 1;
            this.$turn.innerHTML    = this.status.currentState?.title?.turn + ' ' + this.$turn.dataset.turn;
        },

        updatePeople: function()
        {
            this.$peopleTitle.innerHTML = this.status.currentState?.title?.people || '';

            this.$peopleAll.dataset.count = this.status.currentState?.peopleCount || 0;
            this.$peopleWorker.dataset.count = this.status.currentState?.workerCount || 0;
            this.$peopleWarrior.dataset.count = this.status.currentState?.warriorCount || 0;
            this.$peopleSavant.dataset.count = this.status.currentState?.savantCount || 0;
            this.$peopleMage.dataset.count = this.status.currentState?.mageCount || 0;
        },

        updateHarvest: function()
        {
            this.$harvestTitle.innerHTML = this.status.currentState?.title?.harvest || '';

            this.$foodHarvest.dataset.count = this.status.currentState?.foodProduction || 0;
            this.$scienceHarvest.dataset.count = this.status.currentState?.scienceProduction || 0;
        },

        updateMilitary: function()
        {
            this.$militaryTitle.innerHTML = this.status.currentState?.title?.military || '';

            this.$powerMilitary.dataset.count = this.status.currentState?.warriorPower || 0;
            this.$defenseMilitary.dataset.count = this.status.currentState?.warriorDefense || 0;
        },

        updateCity: function()
        {
            this.$cityTitle.innerHTML = this.status.currentState?.title?.city || '';

            this.$cityLife.dataset.count = this.status.currentState?.life || 0;
            this.$cityDefense.dataset.count = this.status.currentState?.cityDefense || 0;
        },

        updateStock: function()
        {
            this.$stockTitle.innerHTML = this.status.currentState?.title?.stock || '';

            this.$foodStock.dataset.count = this.status.currentState?.food;
            this.$scienceStock.dataset.count = this.status.currentState?.science || 0;
            this.$foodStock.dataset.stock = this.status.currentState?.foodStock || 0;

            this.$woodStock.dataset.count = this.status.currentState?.woodStock || 0;
            this.$animalStock.dataset.count = this.status.currentState?.animalStock || 0;
            this.$stoneStock.dataset.count = this.status.currentState?.stoneStock || 0;
            this.$metalStock.dataset.count = this.status.currentState?.metalStock || 0;
            this.$clayStock.dataset.count = this.status.currentState?.clayStock || 0;
            this.$paperStock.dataset.count = this.status.currentState?.paperStock || 0;
            this.$medicStock.dataset.count = this.status.currentState?.medicStock || 0;
            this.$gemStock.dataset.count = this.status.currentState?.gemStock || 0;
        },

        replaceIconsInObject: function(cardObject)
        {
            for (let attr in cardObject) {
                if (cardObject.hasOwnProperty(attr)) {
                    let value = cardObject[attr];
                    if (typeof value === 'string' || value instanceof String) {
                        cardObject[attr] = this.replaceIconsInString(value);
                    }
                }
            }

            return cardObject;
        },

        replaceIconsInString: function(toReplace)
        {
            [... toReplace.matchAll(/\[invention\] \[([a-z_]+)\]/ig)].forEach((found) => {
                toReplace = toReplace.replace(found[0], '<div class="double">'+this.getIconAsText('invention')+this.getIconAsText(found[1])+'</div>');
            });
            [... toReplace.matchAll(/\[spell\] \[([a-z_]+)\]/ig)].forEach((found) => {
                toReplace = toReplace.replace(found[0], '<div class="double">'+this.getIconAsText('spell')+this.getIconAsText(found[1])+'</div>');
            });
            [... toReplace.matchAll(/\[([a-z_]+)\]/ig)].forEach((found) => {
                toReplace = toReplace.replace(found[0], this.getIconAsText(found[1]));
            });

            return toReplace;
        },

        displayFullScreenMessage: function(message, duration = 3000)
        {
            window.clearTimeout(this.fullScreenHideTimer);
            window.clearTimeout(this.fullScreenCleanTimer);

            this.$fullScreen.innerHTML = '<h1>' + message + '</h1>';
            this.$fullScreen.classList.add('display');

            this.fullScreenHideTimer = window.setTimeout(() => this.$fullScreen.classList.remove('display'), duration);
            this.fullScreenCleanTimer = window.setTimeout(() => this.$fullScreen.innerHTML = '', duration + 2000);
        },

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log('EnteringState:');
            console.log(args);

            this.actions = {};
            switch( stateName ) {
            case 'ChooseLineage' :
                debugger;
                this.initLineageCards();

                const chooseBtn = dojo.query('#chooseLineage');
                const cancelBtn = dojo.query('#cancelChooseLineage');
                if (chooseBtn.length && cancelBtn.length) {
                    chooseBtn.forEach((elt) => elt.classList.add('hidden'));
                    cancelBtn.forEach((elt) => elt.classList.add('hidden'));
                }
                break;
            case 'Principal' :
                this.isActive = args['isActive'];
                this.actions = args.args.actions;

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
            case 'EndPrincipal':
                
                break;
            }
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            switch(stateName) {
                case 'ChooseLineage': this.status.state.lineageChosen = !this.isCurrentPlayerActive(); break;
            }

            if(this.isCurrentPlayerActive()) {
                if (args.actions !== undefined) {
                    this.actions = args.actions;
                }

                switch(stateName) {
                    case 'ChooseLineage':
                        this.addActionButton( 'chooseLineage', _('Yes'), 'onSelectLineage' );
                        this.addActionButton( 'cancelChooseLineage', _('No'), 'onUnselectLineage' );
                        break;
                    case 'ScienceHarvestBonus':
                        this.addActionButton( 'sbPass', _('Pass'), 'onScienceBonusPass' );
                        break;
                    // case 'Principal':
                    //  break;
                    default:
                        for (let action in this.actions) {
                            let buttonName = this.actions[action].button === undefined ? this.actions[action] : this.actions[action].button;
                            let lastStatusUpdate = this.actions[action].status || {};
                            this.addActionButton(action, buttonName, (evt) => {
                                dojo.stopEvent(evt);
                                if (!this.checkAction(action)) return;

                                let jsMethod = 'onAct' + action.charAt(0).toUpperCase() + action.slice(1);
                                if (this[jsMethod]) {
                                    this[jsMethod](action, lastStatusUpdate);
                                } else {
                                    this.ajaxCallWrapper(action, {}, (response) => {}, (isError) => {});
                                }
                            });
                        }
                        break;
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
        onActPTurnPass: function(action) {
            this.ajaxCallWrapper(action, {}, (response) => {}, (isError) => {});
        },

        onActResourceHarvest: function(action, status) {
            console.log(action, status);

            //this.ajaxCallWrapper(action, {}, (response) => {}, (isError) => {});
        },

        onHarvestResource: function(evt) {
            evt.stopPropagation();

            let dom = evt.currentTarget;
            if (dom.classList.contains('used')) {

                console.log('Resource already used');
                return ;
            }

            let parts = dom.id.match(/(\d+)-(\d+)/g)[0].split('-');
            let tileId = parseInt(parts[0]);
            let unitId = this.getFirstAvailableHarvesterOnTile(tileId);
            if (unitId === null) {
                console.log('No free harvester found');
                return ;
            }

            let resourceNb = parseInt(parts[1]);
            let resourceCode = this.getResourceCodeOnTileByPos(tileId, resourceNb);
            if (resourceCode === null) {
                console.log('Wrong resource. Maybe it is not available anymore');
                return ;
            }

            this.ajaxCallWrapper('resourceHarvest', {tileId: tileId, unitId: unitId, resource: resourceCode}, (response) => {
                debugger;
                console.log(response);
                dom.classList.add('used');

                // Animate resource token, from tile to cartridge

                // Animate harvester, flip to acted side and move to zone

                // Update available actions

            }, (isError) => {
                debugger;
                console.log(isError);
            });
        },

        getFirstAvailableHarvesterOnTile: function (tileId)
        {
            let tileHarvestInfo = this.actions?.resourceHarvest?.status[tileId];
            if (tileHarvestInfo !== undefined) {
                return tileHarvestInfo.harvesters.slice(-1);
            }
            return null;
        },

        getResourceCodeOnTileByPos: function (tileId, pos)
        {
            let tileHarvestInfo = this.actions?.resourceHarvest?.status[tileId];
            if (tileHarvestInfo !== undefined) {
                return Object.keys(tileHarvestInfo.resources)[pos - 1];
            }
            return null;
        },

        onSelectLineage: function(evt)  {
            dojo.stopEvent(evt);

            if (this.selectedCards[0] !== 'undefined' && this.checkAction('selectLineage') && !this.status.state.lineageChosen) {
                this.status.state.lineageChosen = true;
                this.ajaxCallWrapper(
                    'selectLineage',
                    {lineage: this.selectedCards[0]},
                    (response) => {},
                    (isError) => {},
                );
            }
        },

        onUnselectLineage: function(evt) {
            dojo.stopEvent(evt);

            this.selectedCards = [];
            dojo.query('#floating-cards .card').forEach((thisCard) => {
                thisCard.classList.remove('selected');
            });

            dojo.query('#pagemaintitletext').text(_('Please, select your lineage'));
            dojo.query('#chooseLineage')[0].classList.add('hidden');
            dojo.query('#cancelChooseLineage')[0].classList.add('hidden');
        },

        onPass: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction('pTurnPass')) return;

            this.ajaxCallWrapper('pTurnPass', {}, (response) => {}, (isError) => {});
        },

        onScienceBonusPass: function (evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction('shBonusPass')) return;

            this.ajaxCallWrapper('shBonusPass', {}, (response) => {}, (isError) => {});
        },

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
            //console.log('Init notifications');
            dojo.subscribe( 'debug', this, "onDebug" );

            // Animation after lineage choose
            dojo.subscribe('playerChooseLineage', this, 'onLineageChosen');
            this.notifqueue.setSynchronous('playerChooseLineage', 1500);

            dojo.subscribe('playerDrawObjective', this, 'onObjectiveDrawn');
            this.notifqueue.setSynchronous('playerDrawObjective', 1500);

            dojo.subscribe('ntfyEndTurn', this, 'onEndPhase');
            this.notifqueue.setSynchronous('ntfyEndTurn', 1000);

            dojo.subscribe('ntfyScienceHarvest', this, 'onScienceHarvest');
            dojo.subscribe('ntfyFoodHarvest', this, 'onFoodHarvest');
            this.notifqueue.setSynchronous('ntfyFoodHarvest', 1000);

            dojo.subscribe('ntfyDisabledCards', this, 'onDisabledCards');
            this.notifqueue.setSynchronous('ntfyDisabledCards', 1000);

            dojo.subscribe('ntfyDiedPeople', this, 'onDiedPeople');
            this.notifqueue.setSynchronous('ntfyDiedPeople', 1000);

            dojo.subscribe('ntfyFoodStock', this, 'onFeedEnded');
            this.notifqueue.setSynchronous('ntfyFoodStock', 1000);

            dojo.subscribe('ntfyStartTurn', this, 'onStartTurn');
            this.notifqueue.setSynchronous('ntfyStartTurn', 1000);

            dojo.subscribe('ntfyResourceHarvested', this, 'onResourceHarvested');

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },

        /* @Override */
        format_string_recursive : function format_string_recursive(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;
                    log = this.replaceIconsInString(log);
                }
            } catch (e) {
                console.error(log, args, "Exception thrown", e.stack);
            }

            return this.inherited({callee: format_string_recursive}, arguments);
        },

        onDebug: function(message)
        {
            console.log(message);
        },

        onLineageChosen: function (notif)
        {
            const lineage = this.indexed[notif?.args?.lineageId] || null;
            if (lineage) {
                lineage.location_arg = parseInt(notif.args.playerId);

                // Move card to player board
                this.slideToObjectAndDestroy(lineage.id, 'overall_player_board_' + lineage.location_arg, 1000, 0);

                // Create lineage cartridge using chosen lineage
                window.setTimeout(() => this.initPlayerLineage(lineage), 1000);

                // Lineage unit is added to city
                if (notif?.args?.unit) {
                    this.updateOrCreateUnit(notif.args.unit);
                }
            }
        },

        onObjectiveDrawn: function(notif) {
            let objective = notif.args.objective || null;
            if (objective) {
                // Objective card is added (Hidden side appear, returned and go to player board)
                this.createDeck('objective', 1, 'overall-cards', false);
                this.slideToObjectAndDestroy('deck-objective', 'overall_player_board_' + this.player_id, 1000, 0);

                window.setTimeout(() => this.initPlayerObjective(objective), 1000);
            }
        },

        onStartTurn: function (notif) {
            // Update turn in cartridge
            this.status.currentState.turn = notif.args.turn;
            this.updateTurn();

            // Display new turn on screen
            this.displayFullScreenMessage(this.$turn.innerHTML);
        },

        onResourceHarvested: function(notif) {
            //

        },

        onDisabledCards: function (notif) {
            console.log('Disabled cards:');
            console.log(notif.args.disabled);
        },

        onDiedPeople: function(notif) {
            // Remove units
            const unitIds = notif.args.died;
            const _self = this;
            unitIds.forEach((unitId) => {

            });
        },

        onFeedEnded: function (notif) {
            this.updateCartridgeElement(this.$foodStock, parseInt(notif.args.foodUpdate));
        },

        onEndPhase: function (notif) {
            // Display end phase start on screen
            this.displayFullScreenMessage(this.status.currentState?.phase?.end);
        },

        onScienceHarvest: function(notif) {
            // Animate science from map to Cartridge
            // 'savantHarvesters', 'scienceMultiplier', 'populationBonus', 'lineageBonus'

            // Update science stock
            this.updateCartridgeElement(this.$scienceStock, parseInt(notif.args.total));
        },

        onFoodHarvest: function (notif) {
            // Animate food from map to Cartridge


            // Update food stock
            this.updateCartridgeElement(this.$foodStock, parseInt(notif.args.total));
        }
    });
});
