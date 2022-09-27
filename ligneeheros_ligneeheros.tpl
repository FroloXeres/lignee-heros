{OVERALL_GAME_HEADER}

<div id="cartridge">
    <h2 id="turn" data-turn="1">Turn 0</h2>
    <div class="people">
        <div id="people-title"></div>
        <div id="people-people" data-count="0"></div>
        <div id="people-worker" data-count="0"></div>
        <div id="people-warrior" data-count="0"></div>
        <div id="people-savant" data-count="0"></div>
        <div id="people-mage" data-count="0"></div>
    </div>
    <div class="harvest">
        <div id="harvest-title"></div>
        <div id="harvest-food" data-count="0"></div>
        <div id="harvest-science" data-count="0"></div>
    </div>
    <div class="stock">
        <div id="stock-title"></div>
        <div id="stock-food" data-count="0"></div>
        <div id="stock-science" data-count="0"></div>
        <div class="stock-resources">
            <div class="group">
                <div id="stock-wood" data-count="0"></div>
                <div id="stock-animal" data-count="0"></div>
                <div id="stock-stone" data-count="0"></div>
                <div id="stock-metal" data-count="0"></div>
            </div>
            <div class="group">
                <div id="stock-clay" data-count="0"></div>
                <div id="stock-paper" data-count="0"></div>
                <div id="stock-medic" data-count="0"></div>
                <div id="stock-gem" data-count="0"></div>
            </div>
        </div>
    </div>
</div>
<div id="map-zone">
    <ul class="map-hex-grid">
        <!-- BEGIN MAP_TILES -->
            <li id="tile-{ID}" data-coord="{COORD}" class="map-hex-item">
                <div class="map-hex-content {CLASS}">
                    <div class="map-dist-bg"><label data-dist="{HOW_FAR}"></label></div>
                    <div class="map-content" id="tile-content-{ID}"></div>
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
    var jstpl_tile =
       '<div class="resources resources_${count}">\
            <div class="resource resource-1 ${resource1}"></div>\
            <div class="resource resource-2 ${resource2}"></div>\
            <div class="resource resource-3 ${resource3}"></div>\
        </div>\
        <div class="name">${name}</div>\
        <div class="harvest">\
            <div class="resource food ${food}" data-count="${foodCount}"></div>\
            <div class="resource science ${science}"></div>\
        </div>'
    ;

    var jstpl_card_verso =
       '<div class="card verso thickness ${large} ${canDraw} ${type}" title="${name}">\
            <div class="content">\
                <div class="inner"></div>\
            </div>\
            <div class="counter">${count}</div>\
       </div>'
    ;

    var jstpl_card_recto =
       '<div class="card recto ${deck} ${id}">\
            <div class="content">\
                <div class="inner">\
                    <div class="header">\
                        <div class="card-icon"><div class="icon cube ${icon}"></div></div>\
                        <div class="text cost middle">${cost}</div>\
                        <div class="text title middle">${name}</div>\
                    </div>\
                    <div class="type ${typeIcon}">${type}</div>\
                    <div class="needs text">${need1}${need2}</div>\
                    <div class="text gain">${gain}</div>\
                    <div class="ldh_meeple">\
                        <div class="text power">${meeplePower}</div>\
                        <div class="text objective">${objective}</div>\
                        <div class="text bonus">${objectiveBonus}</div>\
                    </div>\
                    <div class="text leading ${leadType}"><span></span>${leadPower}</div>\
                    <div class="text info">\
                        <div class="bold">${textBold}</div>\
                        <div>${text}</div>\
                    </div>\
                    <div class="text graph"></div>\
                    <div class="footer">${artist}</div>\
                </div>\
            </div>\
        </div>'
    ;
</script>

{OVERALL_GAME_FOOTER}