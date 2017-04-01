/*
 *   * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *   * See LICENSE in the project root for license information.
 */
$(document).ready(function () {
    $(".bingMapLink").click(function (evnt) {
        cancelBubble(evnt);
        var lat = $(this).attr("latitude");
        var lon = $(this).attr("longitude");
        if (lat && lon) {
            displayPin(lat, lon, $("#bingMapKey").val());
            var offset = $(this).offset();
            $("#myMap").offset({ top: offset.top - 50, left: offset.left + 50 }).css({ width: "200px", height: "200px" }).show();
        }
    });
    $("#myMap").click(function(evnt){
        cancelBubble(evnt);
    });

    $(document).click(function () {
        $("#myMap").offset({ top: 0, left: 0 }).hide();
    });

    function displayPin(latitude, longitude, bingMapKey) {
        if (!bingMapKey || !latitude || !longitude) {
            return;
        }
        var map = new Microsoft.Maps.Map($('#myMap')[0], {
            credentials: bingMapKey,
            center: new Microsoft.Maps.Location(latitude, longitude),
            mapTypeId: Microsoft.Maps.MapTypeId.road,
            showMapTypeSelector: false,
            zoom: 10
        });
        var pushpin = new Microsoft.Maps.Pushpin(map.getCenter(), null);
        map.entities.push(pushpin);
    }

    function cancelBubble(evnt){
        evnt.stopPropagation ? evnt.stopPropagation() : evnt.cancelBubble = true;
    }
});
