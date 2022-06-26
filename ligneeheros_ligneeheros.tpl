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
    <ul class="card_list">
        <!-- BEGIN CARDS -->
        <li data-id="{ID}" data-sub-id="{SUB_ID}" class="card {DECK}">
            <label>{NAME}</label>
            <p>{DESCRIPTION}</p>
        </li>
        <!-- END CARDS -->
    </ul>
    <!-- BEGIN DECKS -->
    <div class="deck {LARGE} {CAN_DRAW} {TYPE}" title="{NAME}">
        <div class="counter">{COUNT}</div>
    </div>
    <!-- END DECKS -->
</div>
<div id="map-zone">
    <ul class="map-hex-grid">
        <!-- BEGIN MAP_TILES -->
            <li data-id="{ID}" data-coord="{COORD}" class="map-hex-item">
                <div class="map-hex-content {CLASS}">
                    <div class="map-dist-bg"><label data-dist="{HOW_FAR}"></label></div>
                </div>
            </li>
        <!-- END MAP_TILES -->
    </ul>
</div>
<div id="city-zone">
    <div>
        <div class="icon cube small warrior"></div>
        <div class="icon cube small mage"></div>
        <div class="icon cube small worker"></div>
        <div class="icon cube small savant"></div>
        <div class="icon cube small all"></div>
        <div class="icon cube small monster"></div>
        <div class="icon cube small city"></div>
        <div class="icon cube small explore"></div>
        <div class="icon cube small objective"></div>
        <div class="icon cube small spell"></div>
    </div>
    <div>
        <div class="icon cube warrior"></div>
        <div class="icon cube mage"></div>
        <div class="icon cube worker"></div>
        <div class="icon cube savant"></div>
        <div class="icon cube all"></div>
        <div class="icon cube monster"></div>
        <div class="icon cube city"></div>
        <div class="icon cube explore"></div>
        <div class="icon cube objective"></div>
        <div class="icon cube spell"></div>
    </div>
    <div>
        <div class="icon cube large warrior"></div>
        <div class="icon cube large mage"></div>
        <div class="icon cube large worker"></div>
        <div class="icon cube large savant"></div>
        <div class="icon cube large all"></div>
        <div class="icon cube large monster"></div>
        <div class="icon cube large city"></div>
        <div class="icon cube large explore"></div>
        <div class="icon cube large objective"></div>
        <div class="icon cube large spell"></div>
    </div>
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
