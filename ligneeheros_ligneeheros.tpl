{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- ligneeheros implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    ligneeheros_ligneeheros.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="deck-zone">
    <!-- BEGIN CARD -->

    <!-- END CARD -->
    <ul class="card_list">
        <!-- BEGIN CARDS -->
        <li class="{LARGE}"><div class="card recto {DECK} {ID}">
                <div class="content">
                    <div class="inner">
                        <div class="header">
                            <div class="card-icon"><div class="icon cube {ICON}"></div></div>
                            <div class="text cost middle">{COST}</div>
                            <div class="text title middle">{NAME}</div>
                        </div>
                        <div class="type {TYPE_ICON}">{TYPE}</div>
                        <div class="needs text">{NEED_1}{NEED_2}</div>
                        <div class="text gain">{GAIN}</div>
                        <div class="ldh_meeple">
                            <div class="text power">{MEEPLE_POWER}</div>
                            <div class="text objective">{OBJECTIVE}</div>
                            <div class="text bonus">{OBJECTIVE_BONUS}</div>
                        </div>
                        <div class="text leading {LEAD_TYPE}">
                            <span></span>
                            {LEAD_POWER}
                        </div>
                        <div class="text info">
                            <div class="bold">{TEXT_BOLD}</div>
                            <div>{TEXT}</div>
                        </div>
                        <div class="text graph"></div>
                        <div class="footer">{ARTIST}</div>
                    </div>
                </div>
            </div>
        </li>
        <!-- END CARDS -->
    </ul>
    <div class="deck-list">
        <!-- BEGIN DECKS -->
        <div class="card verso thickness {LARGE} {CAN_DRAW} {TYPE}" title="{NAME}">
            <div class="content">
                <div class="inner"></div>
            </div>
            <div class="counter">{COUNT}</div>
        </div>
        <!-- END DECKS -->
    </div>
</div>
<div id="map-zone">
    <ul class="map-hex-grid">
        <!-- BEGIN MAP_TILES -->
            <li data-id="{ID}" data-coord="{COORD}" class="map-hex-item">
                <div class="map-hex-content {CLASS}">
                    <div class="map-dist-bg"><label data-dist="{HOW_FAR}"></label></div>
                    <div class="map-content">
                        <div class="resources resources_{COUNT}">
                            <div class="resource resource-1 {RESOURCE_1}"></div>
                            <div class="resource resource-2 {RESOURCE_2}"></div>
                            <div class="resource resource-3 {RESOURCE_3}"></div>
                        </div>
                        <div class="name">{NAME}</div>
                        <div class="harvest">
                            <div class="resource food {FOOD}" data-count="{FOOD_COUNT}"></div>
                            <div class="resource science {SCIENCE}"></div>
                        </div>
                    </div>
                </div>
            </li>
        <!-- END MAP_TILES -->
    </ul>
</div>
<div id="city-zone">
    <!-- <div class="icon cube warrior"></div> -->
    City
</div>


<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
