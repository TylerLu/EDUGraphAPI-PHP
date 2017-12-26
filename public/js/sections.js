/*   
 *   * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.  
 *   * See LICENSE in the project root for license information.  
 */
$(document).ready(function () {
    function bindShowDetail(tiles) {
        tiles.hover(function inFn(e) {
            $(this).children().last().show();
        }, function outFn(e) {
            $(this).children().last().hide();
        }).find(".detail #termdate").each(function (i, e) {
            var $e = $(e);
            var dateStr = $e.text();
            if (dateStr) {
                $e.text(moment.utc(dateStr).local().format('MMMM D YYYY'));
            }
        });
    };

    bindShowDetail($(".section-tiles .tile-container"));
    var tabname = '';
    if ($(".sections .filterlink-container .selected").length > 0) {
        tabname = $(".sections .filterlink-container .selected").attr("id");
    }
    showDemoHelper(tabname);
    $(".sections .filterlink-container .filterlink").click(function () {
        tabname = $(this).attr("id");
        showDemoHelper(tabname);
        var element = $(this);
        element.addClass("selected").siblings("a").removeClass("selected");
        var filterType = element.data("type");
        var tilesContainer = $(".sections .tiles-root-container");
        tilesContainer.removeClass(tilesContainer.attr("class").replace("tiles-root-container", "")).addClass(filterType + "-container");
    });

    $("#see-more span").click(function () {
        var element = $(this);
        if (element.hasClass("disabled") || element.hasClass("nomore")) {
            return;
        }

        var schoolId = element.siblings("input#schoolid").val();
        var skipTokenElement = element.siblings("input#skiptoken");
        var url = "/classes/next/"+schoolId+"/"+ skipTokenElement.val();

        element.addClass("disabled");
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            contentType: "application/json; charset=utf-8",
            beforeSend: function(jqXHR, settings){
                jqXHR.setRequestHeader("accept", "application/json");
            },
            success: function(data) {
                var tiles = element.parent().prev(".content");
                var newTiles = $();
                $.each(data.value, function (i, s) {
                    var isMine = s.isMySection;
                    var newTile = $('<div class="tile-container"></div>');
                    var tileContainer = newTile;
                    if (isMine) {
                        tileContainer = $('<a class="mysectionlink" href="/class/' + schoolId + '/' + s.id + '"></a>').appendTo(newTile);
                    }
                    var tile = $('<div class="tile"><h5>' + s.displayName + '</h5><h2>' + s.classCode + '</h2></div>');
                    tile.appendTo(tileContainer);
                    var tileDetail = $('<div class="detail" style="display: none;">' +
                                            '<h5>Class Number:</h5>' +
                                            '<h6>' + s.classCode + '</h6>' +
                                            '<h5>Teachers:</h5>' +
                                            ((s.members instanceof Array) ?
                                            s.members.reduce(function (accu, cur) {
                                                if (cur.primaryRole == 'teacher') {
                                                    accu += '<h6>' + cur.displayName + '</h6>';
                                                }
                                                return accu;
                                            }, '') : '') +

                                            '<h5>Term Name:</h5>' +
                                            '<h6>' + s.term["displayName"] + '</h6>' +
                                            '<h5>Start/Finish Date:</h5>' +
                                            ((s.term["startDate"] || s.term["endDate"]) ?
                                            ('<h6><span id="termdate">' + s.term["startDate"] + '</span>' +
                                            '<span> - </span>' +
                                            '<span id="termdate">' + s.term["endDate"] + '</span>' +
                                            '</h6>') : '') +
                                        '</div>');
                    tileDetail.appendTo(newTile);
                    newTiles = newTiles.add(newTile);
                });
                newTiles.appendTo(tiles).hide().fadeIn("slow");
                bindShowDetail(newTiles);

                var skipToken = data.skipToken;
                skipTokenElement.val(skipToken);
                if (typeof (skipToken) != "string" || skipToken.length == 0) {
                    element.addClass("nomore");
                }
                $(window).scrollTop($(document).height() - $(window).height())
            },
            error: function(jqXHR, textStatus, errorThrown ) {
                if (jqXHR.responseJSON && jqXHR.responseJSON.errorCode === 401) {
                    alert("Your current session has expired. Please click OK to refresh the page.");
                    window.location.reload(false);
                }
            },
            complete: function() {
                element.removeClass("disabled");
            }
        });
    });


});
