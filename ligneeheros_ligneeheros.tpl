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
        <li><div class="card recto {DECK} {ID}">
                <div class="content">
                    <div class="inner">
                        <div class="text icon {ICON}"></div>
                        <div class="text cost">{COST}</div>
                        <div class="text title">{NAME}</div>
                        <div class="sub {TYPE_ICON}">{TYPE}</div>
                        <div class="text cost">
                            <div>{NEED_1}</div>
                            <div>{NEED_2}</div>
                        </div>
                        <div class="text gain">{GAIN}</div>
                        <div class="text info">
                            <div class="bold">{TEXT_BOLD}</div>
                            <div>{TEXT}</div>
                        </div>
                        <div class="text graph"></div>
                        <div class="status">{ARTIST}</div>
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
