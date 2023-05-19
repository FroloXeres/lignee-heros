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

class Utility {
    static PEOPLE_TYPES = ['worker', 'warrior', 'savant', 'mage'];
    static RESOURCE_TYPES = ['wood', 'animal', 'stone', 'metal', 'clay', 'paper', 'medic', 'gem'];


    /**
     * @param {Object}   object     Object to override
     * @param {String}   method     Method of object to override
     * @param {function} extended   Extended method for object
     * @param {Boolean}  before     Execute middleware method before
     */
    static addMiddleware(object, method, extended, before = true) {
        const existingMethod = object[method];
        object[method] = function () {
            before ? extended.call(object) : existingMethod.call(object);
            before ? existingMethod.call(object) : extended.call(object);
        };
    };

    static getUniqId(prefix = '') {
        return (prefix.length ? prefix + '-' : '') + Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))
    }

    static getIcon(iconId, addClass = '') {
        const $clone = document.querySelector('#icons svg#'+iconId)?.cloneNode(true);
        if ($clone === undefined) {
            console.error('Icon '+iconId+' not found');
            return document.createElement('svg');
        }
        if (addClass.length) {
            $clone.classList.add(addClass);
        }

        return $clone;
    };

    static getWrappedIcon(iconId, wrapId = null, cssClass = '') {
        if (wrapId === null) wrapId = Utility.getUniqId(iconId);
        return '<div id="' + wrapId + '" class="wrapped-icon interactive ' + iconId + ' ' + cssClass + '">' + Utility.getIconAsText(iconId, cssClass) + '</div>';
    };

    static getIconAsText(iconId, cssClass = '') {
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
    };

    static replaceIconsInObject(cardObject) {
        for (let attr in cardObject) {
            if (cardObject.hasOwnProperty(attr)) {
                let value = cardObject[attr];
                if (typeof value === 'string' || value instanceof String) {
                    cardObject[attr] = Utility.replaceIconsInString(value);
                }
            }
        }

        return cardObject;
    };

    static replaceIconsInString(toReplace) {
        [... toReplace.matchAll(/\[invention\] \[([a-z_]+)\]/ig)].forEach((found) => {
            toReplace = toReplace.replace(found[0], '<div class="double">'+Utility.getIconAsText('invention')+Utility.getIconAsText(found[1])+'</div>');
        });
        [... toReplace.matchAll(/\[spell\] \[([a-z_]+)\]/ig)].forEach((found) => {
            toReplace = toReplace.replace(found[0], '<div class="double">'+Utility.getIconAsText('spell')+Utility.getIconAsText(found[1])+'</div>');
        });
        [... toReplace.matchAll(/\[([a-z_]+)\]/ig)].forEach((found) => {
            toReplace = toReplace.replace(found[0], Utility.getIconAsText(found[1]));
        });

        return toReplace;
    };
}

class Animation {
    static TYPE_UNIT_MOVE = 'moveUnit';
    static TYPE_UNIT_WHEEL = 'unitWheel';
    static TYPE_UNIT_ACT = 'actUnit';
    static TYPE_UNIT_DIED = 'diedUnit';
    static TYPE_UPDATE_CARTRIDGE = 'updateCartridge';
    static TYPE_MOVE_TO_CARTRIDGE = 'moveResourceToCartridge';
    static TYPE_CARD_FLIP = 'flipCard';
    static TYPE_CARD_ACTIVATE = 'activateCard';
    static TYPE_CARD_DISABLE = 'disableCard';
    static TYPE_CARD_DRAW = 'drawCard';
    static TYPE_CARD_DISTRIBUTE = 'distributeCard';

    type = null;

    // Dom element Id
    subject = null;

    // Dom element Id
    target = null;

    duration = 0;

    timers = [];

    callback = null;

    constructor(type, subject, target = null, duration = 0) {
        this.type = type;
        this.subject = subject;
        this.target = target;
        this.duration = duration;
    }

    animate(callback) {
        this.callback = callback;

        switch (this.type) {
            case Animation.TYPE_UNIT_ACT: this.unitAct(); break;
            case Animation.TYPE_MOVE_TO_CARTRIDGE: this.moveToCartridge(); break;
            default:
                console.log("Animate ["+this.type+"] of #" + this.subject);
                this.onAnimationEnd();
                break;
        }
    }

    stop() {
        this.timers.forEach(timer => window.clearTimeout(timer));
    }

    onAnimationEnd() {
        this.timers.forEach(timer => window.clearTimeout(timer));
        this.callback();
    }

    /** Unit change zone and flip from "moved/free" to "acted" */
    unitAct() {
        console.log('Unit act' + this.subject.id);

        this.onAnimationEnd();
    }

    moveToCartridge() {
        let $start = game.map.getTileResourceDom(this.subject, this.target);
        if ($start === null) return ;

        let $end = game.cartridge.getResourceDom(this.target);
        if ($end === null) return ;

        game.slideTemporaryObject(Utility.getWrappedIcon(this.target), 'floating-cards', $start, $end, this.duration, 0);

        this.onAnimationEnd();
    }
}
class CartridgeAnimation extends Animation {
    constructor(subject, target, duration) {
        super(Animation.TYPE_UPDATE_CARTRIDGE, subject, target, duration);
    }

    // this.subject is Cartridge class
    // this.target is key/state object

    animate(callback) {
        this.callback = callback;

        let $element = this.subject.$state[this.target.key][this.target.state];
        let before = parseInt($element.dataset.count);
        let after = this.subject.state[this.target.key][this.target.state];
        let value = after - before;
        if (before === after) callback();

        $element.style.color = 'darkred';
        let incFct = value < 0 ?
            () => {$element.dataset.count = parseInt($element.dataset.count) - 1} :
            () => {$element.dataset.count = parseInt($element.dataset.count) + 1}
        ;
        let howMany = Math.abs(value);
        let part = Math.floor(this.duration / howMany);
        for (let i = 1; i <= howMany; i++) {
            this.timers.push(
                window.setTimeout(incFct, part * i)
            );
        }
        this.timers.push(
            window.setTimeout(
                () => this.onAnimationEnd($element),
                this.duration
            )
        );
    }

    onAnimationEnd($element) {
        $element.style.color = null;
        super.onAnimationEnd();
    }
}
class DistributeCardAnimation extends Animation {
    constructor(subject, target, duration) {
        super(Animation.TYPE_CARD_DISTRIBUTE, subject, target, duration);
    }

    // this.subject is CardManager
    // this.target is card cards/type/location object

    animate(callback) {
        this.callback = callback;

        let wait = 500;
        let $deck = document.getElementById(this.target.type + '-' + this.target.location);
        let deckExists = $deck !== null && $deck.innerHTML.length;

        !deckExists && this.subject.createDeck(this.target.type, this.target.cards.length, 'overall-cards', false);
        this.target.cards.forEach((card) => {
            this.timers.push(
                window.setTimeout(() => this.subject.moveCardFromDeckToLocation(card, this.target.type, this.target.location, deckExists), wait)
            );
            wait += 1000;
        });

        this.timers.push(window.setTimeout(() => this.onAnimationEnd(), wait));
    }
}

class Animator {
    toAnimate = [];
    animation = null;
    animationInProgress = false;

    constructor() {

    }

    addAnimation(animation, delay = 0) {
        let letsGo = () => {
            this.toAnimate.push(animation);
            if (!this.animationInProgress) this.launchAnimations();
        };

        if (delay) window.setTimeout(() => letsGo(), delay);
        else letsGo();
    }

    launchAnimations() {
        if (this.toAnimate.length) {
            this.animationInProgress = true;
            this.animation = this.toAnimate.shift();
            this.animation.animate(() => {
                // End of animation
                this.animation = null;
                this.animationInProgress = false;

                this.launchAnimations();
            });
        }
    }
}

class Cards {
    static VISIBLE_DECKS = ['invention', 'spell'];

    /** @param {Animator} animator */
    animator = null;

    /** @param {bgagame.ligneeheros} game */
    game = null;

    decks = null;
    cards = {};
    indexed = {
        invention: {
            onTable: {},
            hand: {}
        },
        spell: {
            onTable: {},
            hand: {}
        },
        lineage: {
            onTable: {},
            hand: {}
        },
        objective: {
            hand: {}
        }
    };

    cardZones = {
        invention: {deck: null, onTable: null, hand: null},
        spell: {deck: null, onTable: null, hand: null},
        floating: null,
        overall: null,
    };

    /**
     * @param {bgagame.ligneeheros} game
     * @param {Animator} animator
     */
    constructor(game, animator) {
        this.game = game;
        this.animator = animator;
    }

    init(decks) {
        this.decks = decks;
        this.initZones();

    }

    initZones() {
        for (let type in this.cardZones) {
            for (let location in this.cardZones[type]) {
                this.cardZones[type][location] = this.createZone(type+'-'+location);
            }
        }

        // Floating-cards
        this.cardZones.floating = this.createZone('floating-cards');
        this.cardZones.overall = this.createZone('overall-cards');
    }
    createZone(code) {
        const _self = this;
        let zone = new ebg.zone();
        zone.create(this.game, code);
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
        this.addConditionalCheck(zone, 'updateDisplay', () => !_self.game.isInitializing);

        return zone;
    }

    // Zone coords
    lineageCardZoneCoords(i, zoneWidth, zoneHeight, count) {
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
    }
    cardZoneCoords(i, zoneWidth, zoneHeight, count) {
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
    }

    /**
     * @param {Object}   object         Object
     * @param {String}   method         Method of object on witch condition is applied
     * @param {function} conditional    Conditional method
     */
    addConditionalCheck(object, method, conditional){
        const existingMethod = object[method];
        object[method] = function () {
            if (conditional()) {
                existingMethod.call(object);
            }
        };
    }

    // Lineages
    initPlayerLineages(lineages, objectives = null){
        const _self = this;
        lineages.forEach(function(lineage) {
            _self.initPlayerLineage(lineage);
        });

        if (objectives.length && objectives[0].id) {
            this.initPlayerObjective(objectives[0]);
        }
    }
    initPlayerLineage(lineage){
        if (lineage.location_arg === this.player_id) {
            this.playerLineage = lineage;
        }

        const _self = this;
        lineage = Utility.replaceIconsInObject(lineage);
        const lineageBoard = _self.game.format_block('jstpl_lineage_board', {
            playerId: lineage.location_arg,
            name: lineage.name,
            lineageIcon: Utility.getIconAsText('lineage'),
            meeple: Utility.getIconAsText(lineage.meeple),
            meeplePower: lineage.meeplePower,
            objectiveCompleted: lineage.completed ? 'icon-complete' : '',
            objectiveIcon: Utility.getIconAsText('objective'),
            objective: lineage.objective,
            leader: lineage.leader ? 'leader' : '',
            leadingIcon: Utility.getIconAsText('leading'),
            leadTypeIcon: Utility.getIconAsText(lineage.leadType),
            leadType: lineage.leadType,
            leadPower: lineage.leadPower
        });

        dojo.place(lineageBoard, 'overall_player_board_' + lineage.location_arg, 'end');
    }
    initPlayerObjective(objective) {
        const qryPic = '#overall_player_board_' + this.game.player_id + ' .hidden-one picture';
        const qryLabel = '#overall_player_board_' + this.game.player_id + ' .hidden-one label';

        objective = Utility.replaceIconsInObject(objective);
        const $pic = dojo.query(qryPic);
        if ($pic.length) {
            $pic[0].title = objective.name;
            $pic[0].className = objective.completed ? 'icon-complete' : '';
            $pic[0].innerHTML = Utility.getIconAsText(objective.icon);
        }

        const $label = dojo.query(qryLabel);
        if ($label.length) {
            $label[0].innerHTML = objective.text;
        }
    }

    indexCards(cards) {
        for (let type in cards) {
            for (let location in cards[type]) {
                this.indexed[type][location] = {};
                for (let key in cards[type][location]) {
                    const card = cards[type][location][key];
                    card.location = location;
                    this.cards[card.id] = card;
                    this.indexed[type][location][card.id] = document.getElementById(card.id);
                }
            }
        }
    }

    createDeck(type, count, place = 'new-card', moveInZone = true) {
        let deck        = this.decks[type];
        let deckContent = this.game.format_block('jstpl_card_verso', {
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
    }
    createCardsInLocation(cards, type, location) {
        const _self = this;
        let flip = type === 'lineage' || type === 'objective';
        cards.forEach(function(card) {
            let cardId = _self.createCardInLocation(card, type, location, 'new-card', flip);
            _self.moveCardToZone(card, cardId, type, location);
        });
    }
    moveCardToZone(card, cardId, type, location) {
        const target = !['invention', 'spell'].includes(type) ? this.cardZones.floating : this.cardZones[type][location];
        target.placeInZone(cardId, this.getPriorityByCard(card));
    }
    createCardInLocation(card, type, location, where, flip = false) {
        let cardId = card.id;0
        let cardTpl = Utility.replaceIconsInObject(card);
        cardTpl.textAsIcons = (cardTpl.text.indexOf('svg') !== -1) ? 'text_as_icon' : '';

        let cardContent = this.game.format_block('jstpl_card_recto', cardTpl);
        cardContent = cardContent.replaceAll('[none]', '');

        const iconify = ['lineage', 'objective', 'spell', 'invention'];
        if (iconify.includes(card.deck)) {
            cardContent = cardContent.replaceAll('['+card.icon+']', Utility.getIconAsText(card.icon));
            ['science', 'fight', 'city', 'growth', 'nature', 'spell', 'healing', 'foresight', 'science'].forEach(
                (iconId) => cardContent = cardContent.replace('['+iconId+']', Utility.getIconAsText(iconId))
            );
        }
        if (card.deck === 'lineage') {
            ['objective', 'leading', 'fight', 'end_turn', card.meeple].forEach(
                (iconId) => cardContent = cardContent.replace('['+iconId+']', Utility.getIconAsText(iconId))
            );
        }

        if (flip) {
            cardTpl.deckType = type;
            let versoContent = this.game.format_block('jstpl_card_recto_back', cardTpl);
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
    }
    moveCardFromDeckToLocation(card, type, location, deckExists = false) {
        let deckId = 'deck-' + type;
        let $deck = document.getElementById(deckId);
        let counter = this.changeDeckCounter($deck, -1);
        let cardId = this.createCardInLocation(card, type, location, deckExists ? deckId : 'overall-cards', !deckExists);
        this.moveCardToZone(card, cardId, type, location);

        if (!counter) {
            $deck.remove();
        }
    }
    changeDeckCounter($deck, change, absolute = false) {
        let $counter = $deck.getElementsByClassName('counter')[0];
        let newCounter;

        if (absolute) {
            $counter.innerHTML = change;
        } else {
            let counter = parseInt($counter.innerHTML);
            newCounter = counter + change;
            $counter.innerHTML = newCounter;
        }

        return absolute ? change : newCounter;
    }
    getPriorityByCard(card) {
        let priority;
        switch (card.type) {
            default: priority = 10;
        }
        priority += card.name.charCodeAt(0);

        return priority;
    }

    createOrUpdateDeck(type, cardLength) {
        let deckId = 'deck-' + type;
        let $deck = document.getElementById(deckId);
        if ($deck !== null) {
            this.changeDeckCounter($deck, cardLength, true);
        } else {
            this.createDeck(type, cardLength);
        }
    }

    checkForCard(type, location, card) {
        if (this.indexed[type][location][card.id] === undefined) {
            // New card
            this.createCardInLocation(card, type, location, 'new-card');
            this.moveCardToZone(card, card.id, type, location);
        } else {
            // Remove card?
            console.log('Have to remove card', card);
        }
    }

    removeCards(toDestroy) {
        for (let cardId in toDestroy) {
            document.getElementById(cardId).remove();
        }
    }

    update(cards, animate = false) {
        cards?.lineage?.hand?.length && this.initPlayerLineages(cards.lineage.hand, cards?.objective?.hand);
        cards?.lineage?.deck?.length && this.animator.addAnimation(
            new DistributeCardAnimation(this, {cards: cards.lineage.deck, type: 'lineage', location: 'deck'})
        );

        for (const [type, locations] of Object.entries(cards)) {
            if (['lineage', 'objective'].includes(type)) continue;

            for (const [location, cardList] of Object.entries(locations)) {
                if (location === 'deck') {
                    this.createOrUpdateDeck(type, cardList.length);
                    continue;
                }

                if (cardList.length && type === 'spell' && location === 'onTable') {
                    this.game.initMasterSpell();
                }

                let cardsToRemove = Object.assign({}, this.indexed[type][location]); // Clone
                cardList.forEach(card => {
                    cardsToRemove[card.id] !== undefined && delete cardsToRemove[card.id];

                    this.checkForCard(type, location, card);
                });
                this.removeCards(cardsToRemove);
            }
        }

        this.indexCards(cards);
    }
}

class Cartridge {
    static ANIMATION_DURATION = 1000;

    /** @param {Animator} animator */
    animator = null;

    state = {
        title: {
            turn: '',
            people: '',
            harvest: '',
            military: '',
            city: '',
            stock: '',
        },
        stock: {
            food: 0,
        },
        count: {
            turn: 1,
            people: 10,
            worker: 0,
            warrior: 0,
            savant: 0,
            mage: 0,
            food: 0,
            foodStock: 0,
            science: 0,
            foodProduction: 2,
            scienceProduction: 1,
            warriorPower: 1,
            warriorDefense: 0,
            life: 1,
            cityDefense: 1,
            wood: 0,
            animal: 0,
            stone: 0,
            metal: 0,
            clay: 0,
            paper: 0,
            medic: 0,
            gem: 0,
        }
    };
    $state = {
        title: {},
        count: {},
    };

    people = {};
    resources = {};

    built = false;

    /** @param {Animator} animator */
    constructor(animator) {
        this.animator = animator;
    }

    update(currentState, animate = false) {
        if (!this.built) return;

        ['count', 'title', 'stock'].forEach(key => {
            if (currentState[key] !== undefined) {
                for (let state in currentState[key]) {
                    this.state[key][state] = currentState[key][state];

                    this.updateDom(key, state, animate);
                }
            }
        });
    }

    build(game) {
        // Create cartridge
        document.getElementById('player_boards').insertAdjacentHTML('beforebegin', game.format_block('jstpl_cartridge', {turn: this.state.count.turn}));

        for (let title in this.state.title) {
            this.$state.title[title] = document.getElementById(title + '-title');
        }

        for (let count in this.state.count) {
            this.$state.count[count] = document.getElementById('ctg-' + count);
            if (this.$state.count[count] === null) continue;

            if (this.$state.count[count].dataset?.icon !== undefined) {
                let icon = this.$state.count[count].dataset.icon.split(',');
                this.$state.count[count].appendChild(
                    Utility.getIcon(icon[0], icon[1] || '')
                );
            }
        }

        this.built = true;
    }

    getTurnTitle() {
        return this.state.title.turn + ' ' + this.state.count.turn; // todo: Translate
    }

    updateDom(key, state, animate = false) {
        if (animate && key === 'title') animate = false;

        if (animate && this.$state.count[state] !== null) {
            this.animator.addAnimation(new CartridgeAnimation(
                this,
                {key: key, state: state},
                Cartridge.ANIMATION_DURATION
            ));
        } else {
            switch (key) {
                case 'title':
                    this.$state.title[state].innerHTML = state === 'turn' ?
                        this.getTurnTitle() :
                        this.state.title[state] // todo: Translate
                    ;
                    break;
                case 'count':
                case 'stock':
                    if (this.$state.count[state] !== null) {
                        this.$state.count[state].dataset[key] = this.state[key][state];
                    }
                    break;
            }
        }
    }

    getResourceDom(resourceCode) {
        if (this.$state.count[resourceCode] !== undefined) {
            return this.$state.count[resourceCode];
        }
        return null;
    }
}

class UnitWheel extends EventTarget {
    static EVENT_CREATE_WHEEL = 'createWheelEvent';
    static EVENT_CLOSE_WHEEL = 'closeWheelEvent';
    $unitWheel = null;
    $wheel = null;
    $all = null;

    origin = {x: 0, y: 0};
    $parent = null;
    $units = [];
    unitStyles = null;
    unitWidth = {};
    onSelect = null;

    constructor() {
        super();

        this.unitStyles = new Map();
        this.$unitWheel = document.getElementById('unitWheels');
        this.$wheel = this.$unitWheel.querySelector('.wheel');
        this.$all = this.$unitWheel.querySelector('#selectAll');

        this.$unitWheel.addEventListener(UnitWheel.EVENT_CLOSE_WHEEL, (event) => this.closeWheel(event));
        this.$unitWheel.addEventListener('click', (event) => this.onClickWheel(event));
        this.$all.addEventListener('click', (event) => this.onSelectAll(event), true);
        this.addEventListener(UnitWheel.EVENT_CREATE_WHEEL, (event) => this.createWheel(event));
    }

    onSelectAll(event) {
        event.preventDefault();
        event.stopPropagation();

        let selectedCount = this.$wheel.getElementsByClassName('selected').length;
        let hasToSelectAll = selectedCount < this.$units.length;
        for (let $unit of this.$units) {
            if (
                (hasToSelectAll && !$unit.classList.contains('selected'))
                || (!hasToSelectAll && $unit.classList.contains('selected'))
            ) {
                this.onSelect($unit);
            }
            if ($unit.classList.contains('selected')) selectedCount++;
        }
        this.updateSelectedCount();

        return false;
    }

    updateSelectedCount() {
        let count = this.$wheel.getElementsByClassName('selected').length;
        for (let $unit of this.$units) {
            if (!count) {
                delete $unit.dataset.selected;
            } else {
                $unit.dataset.selected = count;
            }
        }
    }

    onClickWheel(event) {
        let $unit = event.target.closest('.wrapped-icon');
        if ($unit !== null) {
            this.onSelect($unit);
            this.updateSelectedCount();
        } else {
            this.closeWheel();
        }
    }

    updateWheel(count, elementWidth) {
        let widthNeeded = count * 1.5 * elementWidth;
        let diameter = Math.round(widthNeeded / Math.PI);
        let radius = diameter / 2;
        this.$wheel.style.width = diameter + 'px';
        this.$wheel.style.height = diameter + 'px';
        let surfacePos = this.$unitWheel.getBoundingClientRect();
        this.$wheel.style.left = (this.origin.x - (surfacePos.left + window.scrollX) - radius) + 'px';
        this.$wheel.style.top = (this.origin.y - (surfacePos.top + window.scrollY) - radius) + 'px';

        let origin = {x: radius, y: radius};
        let currentPosition = {x: radius, y: radius * 2};
        let ange = (360 / count) * (Math.PI / 180);
        for (let $unit of this.$units) {
            currentPosition = this.getNextPosition(origin, currentPosition, ange);
            $unit.style.left = (currentPosition.x - (this.unitWidth.width / 2)) + 'px';
            $unit.style.top = (currentPosition.y - (this.unitWidth.width / 2)) + 'px';
        }
    }

    getNextPosition(origin, current, angle) {
        let cosAngle = Math.cos(angle);
        let sinAngle = Math.sin(angle);
        let xWidth = current.x - origin.x;
        let yWidth = current.y - origin.y;
        let x = (xWidth * cosAngle) - (yWidth * sinAngle) + origin.x;
        let y = (xWidth * sinAngle) + (yWidth * cosAngle) + origin.y;
        return {x: parseInt(x), y: parseInt(y)};
    }

    createWheel(event) {
        let $unit = event.detail.$unit;
        this.origin = {x: event.detail.click.pageX, y: event.detail.click.pageY};
        this.onSelect = event.detail.select;

        let isUnitStack = $unit.hasAttribute('data-count');
        if (isUnitStack) {
            let brotherSelector = '.wrapped-icon.'+event.detail.unit.status;
            this.$parent = $unit.parentElement;
            this.$units = this.$parent.querySelectorAll(brotherSelector);

            this.openWheel();
            this.updateSelectedCount();
        } // Else
    }

    openWheel(event) {
        let $firstUnit = this.$units.item(0);
        this.unitWidth = $firstUnit.firstChild.getBoundingClientRect();

        this.$unitWheel.classList.add('open');

        for (const [key, $unit] of Object.entries(this.$units)) {
            this.unitStyles.set(key, {width: $unit.style.width, height: $unit.style.height, top: $unit.style.top, left: $unit.style.left});
            $unit.style.width = this.unitWidth.width + 'px';
            $unit.style.height = this.unitWidth.height + 'px';

            this.$wheel.append($unit);
        }
        this.updateWheel(this.$units.length + 1, this.unitWidth.width);
    }
    closeWheel() {
        for (const [key, $unit] of Object.entries(this.$units)) {
            for (const [style, value] of Object.entries(this.unitStyles.get(key))) {
                $unit.style[style] = value;
            }
            this.$parent.append($unit);
        }
        this.$unitWheel.classList.remove('open');
    }
}
class LdhMap {
    static ZONES = ['warrior', 'worker', 'mage', 'savant', 'lineage'];

    game = null;

    /** @type {People} */
    people = null;

    mapZones = [];
    unitWheel = null;

    revealed = [];
    terrains = [];
    resources = [];

    animator = null;

    /**
     * @param {bgagame.ligneeheros} game
     * @param {Animator} animator*
     */
    constructor(game, animator) {
        this.game = game;
        this.animator = animator;

        this.people = new People(this, this.animator);
    }

    init(revealed, terrains, resources) {
        this.revealed = revealed;
        this.terrains = terrains;
        this.resources = resources;
        this.unitWheel = new UnitWheel();
    }
    initUnits(meeple) {
        this.people.init(meeple);
    }

    buildMap(animate= false) {
        let _self = this;
        let tileTerrain = {};
        let tileContent = null;
        for (let tileId in _self.revealed) {
            let tile = _self.revealed[tileId];

            let harvested = {
                resource1: tile.resource1 === true ? ' used' : '',
                resource2: tile.resource2 === true ? ' used' : '',
                resource3: tile.resource3 === true ? ' used' : ''
            };
            tileTerrain = Utility.replaceIconsInObject(
                _self.terrains[tile.terrain]
            );
            tileContent = this.game.format_block('jstpl_tile', {
                id: tile.id,
                count: tileTerrain.resources.length,
                resource1: tileTerrain.resources[0] ? Utility.getIconAsText(tileTerrain.resources[0]) : '',
                resource2: tileTerrain.resources[1] ? Utility.getIconAsText(tileTerrain.resources[1]) : '',
                resource3: tileTerrain.resources[2] ? Utility.getIconAsText(tileTerrain.resources[2]) : '',
                resource1Class: tileTerrain.resources[0] ? tileTerrain.resources[0] + harvested.resource1 : '',
                resource2Class: tileTerrain.resources[1] ? tileTerrain.resources[1] + harvested.resource2 : '',
                resource3Class: tileTerrain.resources[2] ? tileTerrain.resources[2] + harvested.resource3 : '',
                name: tileTerrain.name,
                bonus: tileTerrain.bonusAsTxt,
                food: tileTerrain.food ? '' : 'none',
                foodCount: tileTerrain.food,
                foodIcon: Utility.getIconAsText('food'),
                science: tileTerrain.science ? '' : 'none',
                scienceIcon: Utility.getIconAsText('science')
            });
            document.getElementById('tile-content-' + tile.id).innerHTML = tileContent;

            let revealed = document.getElementById('tile-' + tile.id)?.getElementsByClassName('map-hex-content');
            revealed.length && [...revealed].forEach(($tile) => {
                $tile.classList.add('tile_reveal');
                $tile.classList.add('tile_' + tileTerrain.code);
            });
        }

        this.initMapZones();
        this.game.gamedatas.gamestate.id > 3 && this.initEvents();
        this.initZoom();
        this.scrollToTile(0, 0);
    }

    initEvents() {
        const $unitZones = document.querySelectorAll('.tile:not(.tile_disabled) .map-explore');
        for (let $zone of $unitZones) {
            $zone.addEventListener('click', (event) => {
                let $unit = event.target.closest('.wrapped-icon');
                if ($unit !== null) {
                    this.people.onSelectUnit($unit) && this.unitWheel.dispatchEvent(
                        new CustomEvent(
                            UnitWheel.EVENT_CREATE_WHEEL,
                            {
                                detail: {
                                    click: event,
                                    $unit: $unit,
                                    unit: this.people.getUnitById(People.guessUnitId($unit)),
                                    select: ($unit) => this.people.onSelectUnit($unit)
                                }
                            }
                        )
                    );
                }
            });
        }

        const $tiles = document.querySelectorAll('.map-hex-content');
        for (let $tile of $tiles) {
            $tile.addEventListener('click', (event) => {
                const $clickedTile = event.target.classList.contains('map-hex-content') ? event.target : event.target.closest('.map-hex-content');
                if ($clickedTile === null || !$clickedTile.classList.contains('selected')) return;

                const tileId = LdhMap.getTileId($clickedTile.closest('.map-hex-item'));

                // UnSelect other tiles, confirm move
                this.game.ajaxCallWrapper('move', {tileId: tileId, unitIds: JSON.stringify(Object.keys(this.people.selectedUnits))}, (response) => console.log(response));
            });
        }
    }

    getTileResourceDom(tileId, resourceCode = null) {
        return resourceCode !== null ?
            document.querySelector('#tile-' + tileId + ' .resources .resource.' + resourceCode) :
            document.querySelectorAll('#tile-' + tileId + ' .resources .resource')
        ;
    }

    update(tiles) {
        for (const [tileId, update] of Object.entries(tiles)) {
            if (update.resources !== undefined) {
                this.updateTileResources(tileId, update.terrain, update.resources);
            }
        }
    }

    updateTileResources(tileId, terrain, resources) {
        let tile = this.revealed[tileId];
        let $resources = this.getTileResourceDom(tileId);
        for (const [resourceCode, used] of Object.entries(resources)) {
            for (let key in $resources) {
                let $resource = $resources[key];
                if ($resource.classList.contains(resourceCode)) {
                    let index = this.terrains[terrain].resources.indexOf(resourceCode) + 1;
                    this.revealed[tileId]['resource' + index] = used;

                    if (used) $resource.classList.add('used');
                    else $resource.classList.remove('used');

                    break;
                }
            }
        }
    }

    highlightTiles(tileIds) {
        this.unHighlightAllTiles();
        tileIds.forEach((id) => {
            LdhMap.getTileContentByTileId(id)?.classList?.add('selected');
        });
    }

    unHighlightAllTiles() {
        let $tiles = document.querySelectorAll('.tile');
        for (let $tile of $tiles) {
            $tile.classList.remove('selected');
        }
    }

    scrollToTile(x, y)
    {
        const map = dojo.query('#map-zone');
        const tile = dojo.query('[data-coord="'+x+'_'+y+'"]');
        if (map.length && tile.length) {
            map[0].scrollTo(tile[0].offsetLeft / 2, tile[0].offsetTop / 2);
        }
    }

    getTileZones(tileId) {
        let zones = [];
        LdhMap.ZONES.forEach(
            zoneType => zones.push(
                this.getMapZone(
                    LdhMap.getZoneIdByTileIdAndType(tileId, zoneType)
                )
            )
        );
        return zones;
    }

    getMapZone(zoneId) {
        return this.mapZones[zoneId];
    }

    // Zones
    initMapZones() {
        const _self = this;
        const tiles = dojo.query('.tile:not(.tile_disabled)');
        tiles.forEach(function(tile) {
            let $item = tile.closest('.map-hex-item');
            let id = LdhMap.getTileId($item);

            LdhMap.ZONES.forEach(function(unitType) {
                let zone = new ebg.zone();
                let code = LdhMap.getZoneIdByTileIdAndType(id, unitType);
                zone.create(_self.game, code);
                zone.setPattern('custom');

                Utility.addMiddleware(zone, 'updateDisplay', _self.zoneDisplayItemsMiddleWare);
                zone.itemIdToCoords = _self.mapZoneCoords;
                _self.mapZones[code] = zone;
            });
        });
    }
    updateMapZones() {
        for (let code in this.mapZones) {
            if (this.mapZones.hasOwnProperty(code)) {
                this.mapZones[code].updateDisplay();
            }
        }
    }
    mapZoneCoords(i, width, maxWidth, count) {
        const item = this.items[i];

        return {
            x: 0,
            y: item.weight * (width - this.item_margin),
            w: width,
            h: width
        };
    }
    static getZoneIdByTileIdAndType(id, type) {
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
    }
    static getTileContentByTileId(id) {
        return document.getElementById('tile-' + id)?.querySelector('.tile');
    }
    static getTileId($tile) {
        let id = $tile?.id?.replace('tile-', '');
        return id !== undefined ? parseInt(id) : null;
    }
    zoneDisplayItemsMiddleWare() {
        /** @var this */
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
    }

    // Zoom
    initZoom() {
        this.ZOOMS = [50, 70, 90, 110, 130];
        this.MAX_ZOOM = 4;
        this.MIN_ZOOM = 0;
        this.$mapZone = document.getElementsByClassName('map-hex-grid').item(0);
        this.$zoom = document.getElementById('zoom');

        this.$zoomOut = document.getElementById('zoom_out_icon');
        this.$zoomOut.addEventListener('click', () => this.onZoomOut());

        this.$zoomIn = document.getElementById('zoom_in_icon');
        this.$zoomIn.addEventListener('click', () => this.onZoomIn());
    }
    onZoomIn() {
        if (this.getZoom() < this.MAX_ZOOM) {
            this.incDecZoom();
            this.applyZoom();
            this.toggleZoom();
        }
    }
    onZoomOut() {
        if (this.getZoom() > this.MIN_ZOOM) {
            this.incDecZoom(false);
            this.applyZoom();
            this.toggleZoom();
        }
    }
    toggleZoom() {
        this.$zoomIn.style.color = (this.getZoom() === this.MAX_ZOOM) ? '#666' : '#000';
        this.$zoomOut.style.color = (this.getZoom() === this.MIN_ZOOM) ? '#666' : '#000';
    }
    applyZoom() {
        this.$mapZone.style.width = this.ZOOMS[this.getZoom()]+'em';
        this.updateMapZones();
    }
    getZoom() {return parseInt(this.$zoom.dataset.zoom);}
    incDecZoom(inc = true) {this.$zoom.dataset.zoom = this.getZoom() + (inc ? 1 : -1);}
}

class People {
    /** @type {LdhMap} */
    map = null;

    /** @type {Animator} */
    animator = null;

    meeple = {};
    playerUnit = {};
    selectedUnits = {};
    byTile = {};
    byType = {};
    byStatus = {};
    harvesters = {};
    byId = {};
    units = [];
    $units = [];
    moves = null;
    explore = null;

    /**
     * @param {LdhMap} map
     * @param {Animator} animator
     */
    constructor(map, animator) {
        this.animator = animator;
        this.map = map;
    }

    static guessUnitId($unit) {
        if ($unit instanceof Element && $unit.classList.contains('wrapped-icon')) {
            return parseInt($unit.id.replace('unit-', ''));
        }
        return null;
    }

    selectUnit($unit, unit) {
        if (this.selectedUnits[unit.id] !== undefined) {
            // Unselect this unit
            $unit.classList.remove('selected');
            delete this.selectedUnits[unit.id];
        } else {
            // Select this unit
            $unit.classList.add('selected');
            this.selectedUnits[unit.id] = unit;
        }

        this.guessActions();
    }

    init(types)  {
        this.meeple = types;
    }

    update(people) {
        this.updateUnits(people.units);
    }

    updateMoves(moves) {
        this.moves = moves;
    }

    updateExplore(tiles) {
        this.explore = tiles;
    }

    guessActions() {
        if (!Object.values(this.selectedUnits).length) {
            // No units, get standard actions
            this.resetMoves();
        } else {
            this.initMovesIfPossible();
        }
    }

    resetMoves() {
        let $selectedTiles = document.querySelectorAll('.map-hex-content.selected');
        if ($selectedTiles.length) {
            for (let $tile of $selectedTiles) {
                $tile.classList.remove('selected');
            }
        }
    }

    initMovesIfPossible() {
        let moves = [];
        let freeUnits = Object.values(this.selectedUnits).filter(unit => unit.status === 'free');

        // Can move only if all selected units can move
        if (this.moves && freeUnits.length) { // === this.selectedUnits.length) {
            freeUnits.forEach((unit) => {
                if (this.moves[unit.id] !== undefined) {
                    this.moves[unit.id].forEach((tileId) => {
                        !moves.includes(tileId) && moves.push(tileId);
                    });
                }
            });
        }
        this.map.highlightTiles(moves);
    }

    updateIndexesAndGetDiedUnits(units) {
        let diedUnits = new Map();
        this.units.forEach((unit) => diedUnits.set(unit.id, unit));

        this.byType = {};
        this.byTile = {};
        this.byId = {};
        this.byStatus = {};
        for (let idx in units) {
            let unit = units[idx];

            if (this.byType[unit.type] === undefined) this.byType[unit.type] = [];
            this.byType[unit.type].push(idx);

            if (this.byTile[unit.location] === undefined) this.byTile[unit.location] = [];
            this.byTile[unit.location].push(idx);

            if (this.byStatus[unit.status] === undefined) this.byStatus[unit.status] = [];
            this.byStatus[unit.status].push(idx);

            this.byId[unit.id] = idx;

            diedUnits.delete(unit.id);
        }

        return diedUnits;
    }

    getUnitById(unitId) {
        if (this.byId[unitId] !== undefined) {
            return this.units[
                this.byId[unitId]
            ];
        }
        return null;
    }

    updateUnits(units) {
        let diedUnits = this.updateIndexesAndGetDiedUnits(units);
        for (const [unitId, unit] of diedUnits) {
            this.unitDie(unit);
        }

        let zones = new Set();
        this.units = units;
        for (let tileId in this.byTile) {
            let unitsOnTile = this.byTile[tileId];
            unitsOnTile.forEach((idx) => {
                let unit = units[idx];
                if(!this.playerUnit || unit.type !== this.playerUnit.type) {
                    zones.add(
                        this.updateOrCreateUnit(unit)
                    );
                }
            });
        }
        if (this.playerUnit.id !== undefined) {
            this.updateOrCreateUnit(this.playerUnit);
        }

        zones.forEach((zoneId) => {
            let zone = this.map.getMapZone(zoneId);
            zone.updateDisplay();
        });
    }

    updateUnit(unit) {
        let zoneId = this.updateOrCreateUnit(unit);

        let zone = this.map.getMapZone(zoneId);
        zone.updateDisplay();
    }

    initPlayerUnit(playerLineage) {
        let unitPos = this.byType[playerLineage.meeple];
        if (unitPos && this.units[unitPos] !== undefined) {
            this.playerUnit = this.units[unitPos];
        }
    }

    getDomIdByUnit(unit) {
        return 'unit-' + unit.id;
    }
    updateOrCreateUnit(unit) {
        let zoneId = LdhMap.getZoneIdByTileIdAndType(unit.location, unit.type);
        let zone = this.map.getMapZone(zoneId);
        const domUnitId = this.getDomIdByUnit(unit);
        let domUnits = document.getElementById(domUnitId);
        if (domUnits) {
            // Change unit status (if needed)
            if (this.updateUnitStatus(domUnits, unit.status)) {
                zone.removeFromZone(domUnitId, false);
                zone.placeInZone(domUnitId, this.getPriorityByUnitStatus(unit.status));
            }
        } else {
            // Add new unit to map
            this.createUnit(unit.type, domUnitId, unit.status, unit.id);
            zone.placeInZone(domUnitId, this.getPriorityByUnitStatus(unit.status));
        }

        return zoneId;
    }

    unitDie(unit) {
        // Remove unit from map
        const domUnitId = this.getDomIdByUnit(unit);
        document.getElementById(domUnitId).remove();
    }

    getPriorityByUnitStatus(status){
        switch (status) {
            case 'acted': return 0;
            case 'moved': return 1;
            default: return 2;
        }
    }
    updateUnitStatus(domUnit, status){
        if (domUnit.classList.contains(status)) return false;

        ['free', 'moved', 'acted'].forEach((forStatus) => {
            if (forStatus !== status) {
                domUnit.classList.remove(forStatus);
                domUnit.firstChild.classList.remove(forStatus);
            }
        });
        domUnit.classList.add(status);
        domUnit.firstChild.classList.add(status);

        return true;
    }
    createUnit(iconId, domUnitId, unitStatus, unitId){
        const newUnit = Utility.getWrappedIcon(iconId, domUnitId, unitStatus);
        dojo.place(newUnit, 'new-unit');

        // Unit click event (select) seams to be impossible, listen on zone/wheel
    }

    onSelectUnit($unit) {
        let unitId = People.guessUnitId($unit);
        let unit = this.getUnitById(unitId);
        if (unit === null || unit.status === 'acted') {
            return false;
        }

        this.selectUnit($unit, unit);
        return true;
    }
}

let game = null;
define([
    "dojo", "dojo/on", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/zone"
],
function (dojo, on, declare) {
    return declare("bgagame.ligneeheros", ebg.core.gamegui, {
        constructor: function(){
            this.gamedatas = {};
            this.status = {
                isInitializing: false,
                state: {
                    lineageChosen: true,
                    spellToMasterChosen: true,
                },
            };
            this.actions = {};
            this.indexed = {};
            this.playerLineage = null;
            this.playerUnit = null;
            this.$fullScreen = document.getElementById('fullscreen-message');

            this.animator = new Animator();
            this.map = new LdhMap(this, this.animator);
            this.cartridge = new Cartridge(this.animator);
            this.cardManager = new Cards(this, this.animator);

            game = this;
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
        setup: function(gamedatas) {
            // Don't forget to use Mobile config: https://en.doc.boardgamearena.com/Your_game_mobile_version
            console.log(gamedatas);

            this.isActive = gamedatas.isActive;
            this.map.init(gamedatas.map, gamedatas.terrains, gamedatas.resources);
            this.map.initUnits(gamedatas.meeple);
            this.cardManager.init(gamedatas.decks);

            new Promise((resolve) => {
                this.status.isInitializing = true;
                resolve();
            })
            .then(() => {
                this.cartridge.build(this);
                this.map.buildMap();
                this.initEvents();
                this.translateGameData();
            })
            .then(() => this.cartridge.update(gamedatas.currentState.cartridge))
            .then(() => this.map.people.update(gamedatas.people))
            .then(() => this.map.people.updateMoves(gamedatas.moves))
            .then(() => this.map.people.updateExplore(gamedatas.explore))
            .then(() => this.cardManager.update(gamedatas.cards))
            .then(() => this.initTooltips(gamedatas.tooltips))
            .then(() => this.setupNotifications())
            .then(() => this.status.isInitializing = false)
            .then(() => this.initInteractEvents());
        },

        translateGameData: function()
        {
            for (let code in this.gamedatas.resources) {
                if (this.gamedatas.resources.hasOwnProperty(code)) {
                    this.gamedatas.resources[code].name = _(this.gamedatas.resources[code].name);
                    this.gamedatas.resources[code].description = _(this.gamedatas.resources[code].description);
                }
            }

            for (let code in this.gamedatas.terrains) {
                if (this.gamedatas.terrains.hasOwnProperty(code)) {
                    this.gamedatas.terrains[code].name = _(this.gamedatas.terrains[code].name);
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
            this.evts['onHarvestResource'] = on(dojo.query('.resources .resource.interactive'), 'click', this.onHarvestResource.bind(this));

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
            lineage = Utility.replaceIconsInObject(lineage);
            const lineageBoard = _self.format_block('jstpl_lineage_board', {
                playerId: lineage.location_arg,
                name: lineage.name,
                lineageIcon: Utility.getIconAsText('lineage'),
                meeple: Utility.getIconAsText(lineage.meeple),
                meeplePower: lineage.meeplePower,
                objectiveCompleted: lineage.completed ? 'icon-complete' : '',
                objectiveIcon: Utility.getIconAsText('objective'),
                objective: lineage.objective,
                leader: lineage.leader ? 'leader' : '',
                leadingIcon: Utility.getIconAsText('leading'),
                leadTypeIcon: Utility.getIconAsText(lineage.leadType),
                leadType: lineage.leadType,
                leadPower: lineage.leadPower
            });

            dojo.place(lineageBoard, 'overall_player_board_' + lineage.location_arg, 'end');
        },
        initPlayerObjective: function(objective)
        {
            const qryPic = '#overall_player_board_' + this.player_id + ' .hidden-one picture';
            const qryLabel = '#overall_player_board_' + this.player_id + ' .hidden-one label';

            objective = Utility.replaceIconsInObject(objective);
            const $pic = dojo.query(qryPic);
            if ($pic.length) {
                $pic[0].title = objective.name;
                $pic[0].className = objective.completed ? 'icon-complete' : '';
                $pic[0].innerHTML = Utility.getIconAsText(objective.icon);
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
        initSpellCards: function()
        {
            this.distributeCards('spell', 'onTable', true);
        },
        distributeCards: function (type, location, deckExists = false)
        {
            if (this.cards === undefined) this.cards = this.gamedatas.cards;
            if (this.cards[type] === undefined || this.cards[type][location] === undefined) return ;

            let wait = 500;
            !deckExists && this.createDeck(type, this.cards[type][location].length, 'overall-cards', false);
            this.cards[type][location].forEach((card) => {
                window.setTimeout(() => this.moveCardFromDeckToLocation(card, type, location, deckExists), wait);
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

                // Check for masterSpell "state"
                if (type === 'spell' && location === 'onTable') {
                    this.initMasterSpell();
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
            let cardTpl = Utility.replaceIconsInObject(card);
            cardTpl.textAsIcons = (cardTpl.text.indexOf('svg') !== -1) ? 'text_as_icon' : '';

            let cardContent = this.format_block('jstpl_card_recto', cardTpl);
            cardContent = cardContent.replaceAll('[none]', '');

            const iconify = ['lineage', 'objective', 'spell', 'invention'];
            if (iconify.includes(card.deck)) {
                cardContent = cardContent.replaceAll('['+card.icon+']', Utility.getIconAsText(card.icon));
                ['science', 'fight', 'city', 'growth', 'nature', 'spell', 'healing', 'foresight', 'science'].forEach(
                    (iconId) => cardContent = cardContent.replace('['+iconId+']', Utility.getIconAsText(iconId))
                );
            }
            if (card.deck === 'lineage') {
                ['objective', 'leading', 'fight', 'end_turn', card.meeple].forEach(
                    (iconId) => cardContent = cardContent.replace('['+iconId+']', Utility.getIconAsText(iconId))
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
        moveCardFromDeckToLocation: function(card, type, location, deckExists = false)
        {
            let deckId = 'deck-' + type;
            let $deck = document.getElementById(deckId);
            let counter = this.changeDeckCounter($deck, -1);
            let cardId = this.createCardInLocation(card, type, location, deckExists ? deckId : 'overall-cards', !deckExists);
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
            this.evts = {};
            this.evts['onChooseLineage'] = on(dojo.query('#floating-cards'), 'click', this.onChooseLineage.bind(this));
            this.evts['onChooseSpell'] = on(dojo.query('#spell-onTable'), 'click', this.onChooseSpell.bind(this));
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
            const selectedCard = this.cardManager.cards[id];
            this.selectedCards = [id];

            this.changeActionTitle('You choose to play with lineage: ', selectedCard.name);

            document.getElementById('chooseLineage').classList.remove('hidden');
            document.getElementById('cancelChooseLineage').classList.remove('hidden');
        },

        initMasterSpell: function ()
        {
            this.changeActionTitle('Please, select spell you will master');
            document.getElementById('generalactions').innerHTML = '';
            document.getElementById('spell-onTable').scrollIntoView({behavior: 'smooth', block: 'center', inline: 'start'});

            this.addActionButton( 'chooseSpell', _('Yes'), 'onSelectSpell' );
            this.addActionButton( 'cancelChooseSpell', _('No'), 'onUnselectSpell' );
            document.getElementById('chooseSpell').classList.add('hidden');
            document.getElementById('cancelChooseSpell').classList.add('hidden');

            this.status.state.spellToMasterChosen = false;
        },
        onChooseSpell: function(event)
        {
            if (this.status.state.spellToMasterChosen) return;

            const card = event.target.closest('.card.spell');
            if (card === null) return;

            dojo.query('#spell-onTable .card').forEach((thisCard) => {thisCard.classList.remove('selected');});
            card.classList.add('selected');

            const id = card.getAttribute('data-id');
            const selectedCard = this.cardManager.cards[id];
            this.selectedCards = [id];

            this.changeActionTitle('You choose to master spell: ', selectedCard.name);
            document.getElementById('chooseSpell').classList.remove('hidden');
            document.getElementById('cancelChooseSpell').classList.remove('hidden');
        },

        displayFullScreenMessage: function(message, duration = 3000, translate = true)
        {
            window.clearTimeout(this.fullScreenHideTimer);
            window.clearTimeout(this.fullScreenCleanTimer);

            if (message.message !== undefined) {
                duration = message.duration;
                translate = message.translate;
                message = message.message;
            }

            this.$fullScreen.innerHTML = '<h1>' + (translate ? _(message) : message) + '</h1>';
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
            console.log('EnteringState:', stateName, args);
            let $chooseBtn, $cancelBtn;

            switch( stateName ) {
            case 'ChooseLineage' :
                //this.initLineageCards();

                $chooseBtn = document.getElementById('chooseLineage');
                $cancelBtn = document.getElementById('cancelChooseLineage');
                if ($chooseBtn !== null && $cancelBtn !== null) {
                    $chooseBtn.classList.add('hidden');
                    $cancelBtn.classList.add('hidden');
                }
                break;
            case 'Principal' :
                this.isActive = args['isActive'];

                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log('onLeavingState:', stateName);
            switch( stateName ) {
                case 'ChooseLineage':
                // Remove lineage cards without passing by cardManager...?
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
            console.log('onUpdateActionButtons', stateName, args, this.actions);
            switch(stateName) {
                case 'ChooseLineage': this.status.state.lineageChosen = !this.isCurrentPlayerActive(); break;
            }

            if (args?.actions !== undefined) {
                this.updateActions(args.actions);
            }

            if(this.isCurrentPlayerActive()) {
                switch(stateName) {
                    case 'ChooseLineage':
                        this.addActionButton( 'chooseLineage', _('Yes'), 'onSelectLineage' );
                        this.addActionButton( 'cancelChooseLineage', _('No'), 'onUnselectLineage' );
                        break;
                    case 'ScienceHarvestBonus':
                        this.addActionButton( 'sbPass', _('Pass'), 'onScienceBonusPass' );
                        break;
                    default:
                        for (let action in this.actions) {
                            if (this.actions[action].button === false) return;
                            let blocking = this.actions[action].blocking !== undefined && this.actions[action].blocking;

                            let buttonName = this.actions[action].button === undefined ? this.actions[action] : this.actions[action].button;
                            let lastStatusUpdate = this.actions[action].status || {};
                            this.addActionButton(action, buttonName, (evt) => {
                                dojo.stopEvent(evt);
                                if (!this.checkAction(action)) return;

                                let jsMethod = 'onAct' + action.charAt(0).toUpperCase() + action.slice(1);
                                if (this[jsMethod]) {
                                    this[jsMethod](action, lastStatusUpdate);
                                } else {
                                    //blocking && this.startBlockingState();
                                    this.ajaxCallWrapper(action);
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
            if (typeof args === 'undefined') {args = {};}
            if (typeof args.lock === 'undefined') {args.lock = true;}
            if (typeof onResponse === 'undefined') {
                onResponse = (result) => {console.log('ActionResponse', action, result);};
            }
            if (typeof onError === 'undefined') {
                onError = (error) => {error && console.log('ActionError', error);};
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
        confirmAction: function(text, yesMethod, noMethod = null) {
            if (noMethod === null) noMethod = () => this.resetActionButtons();

            this.changeActionTitle(text);
            debugger;
            document.getElementById('generalactions').innerHTML = '';
            this.addActionButton('confirmYes', _('Yes'), () => yesMethod());
            this.addActionButton('confirmNo', _('No'), () => noMethod());
        },
        changeActionTitle: function(text, notTranslated = '') {
            document.getElementById('pagemaintitletext').innerHTML = _(text) + notTranslated;
        },
        resetActionButtons: function() {
            this.changeActionTitle('Please choose an action: ');
            document.getElementById('generalactions').innerHTML = '';
            this.onUpdateActionButtons(this.gamedatas.gamestate.name, {args: {}});
        },

        onActExplore: function() {
            let tileId = null;
            if (this.map.people.explore.length === 1) {
                tileId = this.map.people.explore[0];
                this.map.highlightTiles([tileId]);
                this.confirmAction(
                    'You will explore this new tile',
                    () => this.ajaxCallWrapper('explore', {tileId: tileId}),
                    () => {
                        this.map.unHighlightAllTiles();
                        this.resetActionButtons();
                    }
                );
            } else if (this.map.people.explore.length > 1) {
                this.changeActionTitle('Please, select tile to explore...');


            }

            // this.ajaxCallWrapper(action);
        },

        onActPTurnPass: function(action) {
            this.ajaxCallWrapper(action);
        },

        onActResourceHarvest: function(action, status) {
            console.log(action, status);

            //this.ajaxCallWrapper(action, {}, (response) => {}, (isError) => {});
        },

        onHarvestResource: function(evt) {
            dojo.stopEvent(evt);

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
                dom.classList.add('used');
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
                this.ajaxCallWrapper('selectLineage', {lineage: this.selectedCards[0]});
            }
        },

        onUnselectLineage: function(evt) {
            dojo.stopEvent(evt);

            this.selectedCards = [];
            dojo.query('#floating-cards .card').forEach((thisCard) => {
                thisCard.classList.remove('selected');
            });

            this.changeActionTitle('Please, select your lineage');
            document.getElementById('chooseLineage').classList.add('hidden');
            document.getElementById('cancelChooseLineage').classList.add('hidden');
        },

        onSelectSpell: function(evt)  {
            dojo.stopEvent(evt);

            if (this.selectedCards[0] !== 'undefined' && this.checkAction('masterSpell') && !this.status.state.spellToMasterChosen) {
                this.status.state.spellToMasterChosen = true;
                this.ajaxCallWrapper('masterSpell', {spell: this.selectedCards[0]});
            }
        },

        onUnselectSpell: function(evt) {
            dojo.stopEvent(evt);

            this.selectedCards = [];
            dojo.query('#spell-onTable .card').forEach((thisCard) => {
                thisCard.classList.remove('selected');
            });

            this.changeActionTitle('Please, select spell you will master');
            document.getElementById('chooseSpell').classList.add('hidden');
            document.getElementById('cancelChooseSpell').classList.add('hidden');
        },

        onPass: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction('pTurnPass')) return;

            this.ajaxCallWrapper('pTurnPass');
        },

        onScienceBonusPass: function (evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction('shBonusPass')) return;

            this.ajaxCallWrapper('shBonusPass');
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
            dojo.subscribe('playerChooseLineage', this, 'onNotification');
            this.notifqueue.setSynchronous('playerChooseLineage', 1500);

            dojo.subscribe('playerDrawObjective', this, 'onObjectiveDrawn');
            this.notifqueue.setSynchronous('playerDrawObjective', 1500);

            dojo.subscribe('ntfyEndTurn', this, 'onNotification');
            this.notifqueue.setSynchronous('ntfyEndTurn', 1000);

            dojo.subscribe('ntfyScienceHarvest', this, 'onNotification');
            dojo.subscribe('ntfyFoodHarvest', this, 'onNotification');
            this.notifqueue.setSynchronous('ntfyFoodHarvest', 1000);

            dojo.subscribe('ntfyDisabledCards', this, 'onDisabledCards');
            this.notifqueue.setSynchronous('ntfyDisabledCards', 1000);

            dojo.subscribe('ntfyDiedPeople', this, 'onNotification');
            this.notifqueue.setSynchronous('ntfyDiedPeople', 1000);

            dojo.subscribe('ntfyFoodStock', this, 'onNotification');
            this.notifqueue.setSynchronous('ntfyFoodStock', 1000);

            dojo.subscribe('ntfyStartTurn', this, 'onNotification');
            this.notifqueue.setSynchronous('ntfyStartTurn', 1000);

            dojo.subscribe('ntfyResourceHarvested', this, 'onNotification');
            dojo.subscribe('ntfyRenewResources', this, 'onNotification');
            dojo.subscribe('ntfyPeopleFree', this, 'onNotification');
            dojo.subscribe('ntfyAllDied', this, 'onNotification');
            dojo.subscribe('ntfySpellCardsRevealed', this, 'onNotification');
            dojo.subscribe('ntfySpellMastered', this, 'onNotification');

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
                    log = Utility.replaceIconsInString(log);
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

        playerChooseLineage: function (notif)
        {
            const lineage = this.indexed[notif?.args?.lineageId] || null;
            if (lineage) {
                lineage.location_arg = parseInt(notif.args.playerId);

                // Move card to player board
                this.slideToObjectAndDestroy('flip-' + lineage.id, 'overall_player_board_' + lineage.location_arg, 1000, 0);

                // Create lineage cartridge using chosen lineage
                window.setTimeout(() => {
                    this.initPlayerLineage(lineage);
                    this.map.initEvents();
                }, 1000);
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

        endBlockingState: function () {
            this.resetActionButtons();
        },

        updateActions: function (actions) {
            this.actions = actions;
            this.gamedatas.gamestate.args.actions = actions;

            if (this.actions?.move?.moves !== undefined) {
                this.map.people.updateMoves(this.actions.move.moves);
            }

            if (this.actions?.explore?.tiles !== undefined) {
                this.map.people.updateExplore(this.actions.explore.tiles);
            }
        },

        onNotification: function (notif) {
            // Auto update
            console.log('onNotification', notif);
            if (notif?.args?.actions !== undefined) {this.updateActions(notif.args.actions);}

            if (notif?.args?.units !== undefined) {this.map.people.updateUnits(notif.args.units);}
            if (notif?.args?.unit !== undefined) {this.map.people.updateUnit(notif.args.unit);}

            notif?.args?.map && this.map.update(notif.args.map);
            notif?.args?.cartridge && this.cartridge.update(notif.args.cartridge, true);
            notif?.args?.cards && this.cardManager.update(notif.args.cards);
            notif?.args?.fullscreen && this.displayFullScreenMessage(notif.args.fullscreen);

            if (notif?.args?.animations !== undefined) {
                notif?.args?.animations.forEach((animation) => {
                    this.animator.addAnimation(
                        new Animation(
                            animation.type,
                            animation?.subject,
                            animation?.target,
                            animation?.duration || 0
                        )
                    );
                });
            }

            // Call notification specific method if exists
            if (this[notif.type] !== undefined) {
                this[notif.type](notif);
            }
        },

        ntfyResourceHarvested: function() {
            this.endBlockingState();
        },

        ntfySpellMastered: function () {
            this.status.state.spellToMasterChosen = true;
            this.endBlockingState();
        },

        ntfySpellCardsRevealed: function () {
            this.initMasterSpell();
        },

        ntfyDiedPeople: function(notif) {
            // Remove units
            const unitIds = notif.args.died;
            const _self = this;
            unitIds.forEach((unitId) => {
                console.log(unitId);
            });
        },

        ntfyScienceHarvest: function(notif) {
            // Animate science from map to Cartridge
            // 'savantHarvesters', 'scienceMultiplier', 'populationBonus', 'lineageBonus'

        },
        onDisabledCards: function (notif) {
            console.log('Disabled cards:');
            console.log(notif.args.disabled);
        },

        ntfyFoodHarvest: function (notif) {
            // Animate food from lineage


            // Animate food from map to Cartridge

        }
    });
});
