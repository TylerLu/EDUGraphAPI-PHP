/*   
 *   * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.  
 *   * See LICENSE in the project root for license information.  
 */
$(document).ready(function () {
    iniTiles();
    iniControl();
    formatDateTime();
    loadImages();
    iniTableSort();
    var tabname = '';
    if ($(".nav-tabs li.active").length > 0) {
        tabname = $(".nav-tabs li.active").find("a").attr("href");
    }
    showDemoHelper(tabname);
    $('.nav-tabs li').click(function (e) {
        tabname = $(this).find("a").attr("href");
        showDemoHelper(tabname);
    });
});

function iniTiles(){
    $(".deskcontainer:not([position='0']").each(function () {
        var position = $(this).attr("position");
        var tile = $(".desktile[position='" + position + "']")
        $(this).appendTo(tile);
    });
}

function iniControl() {
    $("#imgedit").click(function () {
        $(this).hide();
        $("#imgsave").show();
        $("#imgcancel").show();
        $(".deskclose").show();
        // $("#graybg").find(".deskclose").show();
        enableDragAndDrop();
    });
    $("#imgcancel").click(function () {
        exitEdit();
        cancelEditDesk();
    });
    $("#imgsave").click(function () {
        exitEdit();
        saveEditDesk();
    });
    $(".students #studoc tbody tr, .students #conversations tbody tr").click(function () {
        $(this).addClass("selected").siblings().removeClass("selected");
    });

    var tabToActivate = $.urlParam("tab");
    if (tabToActivate) {
        $('.nav-tabs li:eq(' + tabToActivate + ') a').tab('show');
    }

    function exitEdit() {
        $("#imgsave").hide();
        $("#imgcancel").hide();
        $("#imgedit").show();
        $(".deskclose").hide();
        disableDragAndDrop();
    }
}

function formatDateTime() {
    $(".coursedetail #termdate").each(function (i, e) {
        var $e = $(e);
        var dateStr = $e.text();
        if (dateStr) {
            $e.text(moment.utc(dateStr).local().format('MMMM D YYYY'));
        }
    });
    $("#studoc tbody .tr-content td:nth-child(4)").each(function (i, e) {
        var $e = $(e);
        var dateStr = $e.text();
        if (dateStr) {
            $e.text(moment.utc(dateStr).local().format('MM/DD/YYYY hh: mm: ss A'));
        }
    });
}

function loadImages() {
    $("img[realheader]").each(function (i, e) {
        var $e = $(e);
        $e.attr("src", $e.attr("realheader"));
    });
}

function iniTableSort() {
    $("#studentsTable").tablesorter({ sortList: [[0, 0]] });

    $("#studoc").tablesorter({ sortList: [[2, 0]] });
}

function enableDragAndDrop() {
    var lstProducts = $('#lstproducts li');
    //Set Drag on Each 'li' in the list 
    $.each(lstProducts, function (idx, val) {
        var id = $(this).attr("id");
        var position = $(".deskcontainer[userid='" + id + "']").attr("position");
        if (position == '0') {
            enableDragOnLeft(this, true);
        } else {
            enableDragOnLeft($(this), false).find(".seated").removeClass("hideitem");
        }

    });
    $(".deskcontainer").on('dragstart', function (evt) {
        var id = $(this).attr("userid");
        evt.originalEvent.dataTransfer.setData("text", "userid:" + id);
        $("#" + id).addClass("greenlist");
        var prevPosition = $(this).attr("prev-position");
        if (!prevPosition) {
            $(this).attr("prev-position", $(this).attr("position"));
        }
    });

    $(".desktile").on('drop', function (evt) {
        evt.preventDefault();
        var idData = evt.originalEvent.dataTransfer.getData("text");
        var prefix = "userid:";
        var id = "";
        if (typeof (idData) === "string" && idData.indexOf(prefix) == 0) {
            id = idData.substr(prefix.length);
        }
        var position = $(this).attr("position");
        var container = $(this).find(".deskcontainer");
        if (container.length > 0 || id.length === 0) {
            if (container.attr("prev-position") === position) {
                container.removeAttr("prev-position");
            }
            return;
        }
        $(".greenTileTooltip").remove();
        enableDragOnLeft($("#" + id), false).removeClass("greenlist").find(".seated").removeClass("hideitem");
        $(".deskcontainer[userid='" + id + "']").addClass("white").appendTo($(this));
        container = $(this).find(".deskcontainer");
        container.attr("position", position);
        if (container.attr("prev-position") === position) {
            container.removeAttr("prev-position");
        }
    });

    $(".desktile").on('dragenter', function (evt) {
        evt.preventDefault();
        if ($(this).find(".deskcontainer").length == 0 && $('#lstproducts li.greenlist').length > 0) {
            var tooltip = $(".desktile .greenTileTooltip");
            if (tooltip.length == 0) {
                tooltip = $("<div class='greenTileTooltip'>Place student here</div>")
            }
            tooltip.appendTo($(this));
        }
    }).on("dragend", function (evt) {
        evt.preventDefault();
        $(".greenTileTooltip").remove();
        $(".greenlist").removeClass("greenlist");
    });
    
    //The dragover
    $("#dvright").on('dragover', function (evt) {
        evt.preventDefault();
    });

    $(".deskclose").click(function (evt) {
        var parent = $(this).closest(".deskcontainer");
        var id = parent.attr("userid");
        var user = $("#" + id);
        user.find(".seated").addClass("hideitem");
        enableDragOnLeft(user, true);
        var position = parent.attr("position");
        parent.attr({"prev-position": position, "position": 0});
        parent.appendTo($("#hidtiles"));
    });

    function enableDragOnLeft(item, enable) {
        item = $(item);
        if (typeof (enable) === undefined || enable == true) {
            item.on('dragstart', function (evt) {
                $(this).addClass("greenlist");
                var id = $(this).attr("id");
                evt.originalEvent.dataTransfer.setData("text", "userid:" + id);
            }).on('dragend', function () {
                $(this).removeClass("greenlist");
                $(".greenTileTooltip").remove();
            });
        }
        else {
            item.off("dragstart dragend");
        }
        return item;
    }
}

function disableDragAndDrop() {
    $('#lstproducts li, .deskcontainer').off('dragstart');
    $(".desktile").off('dragenter drop dragend');
    $("#dvright").off('dragover');
    $(".deskclose").off('click');
}

function saveEditDesk() {
    var classroomSeatingArrangements = [];
    var classId = $("#hidSectionid").val();
    $(".desktile .deskcontainer.unsaved, #hidtiles .deskcontainer:not(.unsaved), .desktile .deskcontainer[prev-position]").each(function () {
        var userid = $(this).attr("userid");
        if (userid) {
            var position = $(this).attr("position");
            classroomSeatingArrangements.push({
                o365UserId: userid,
                position: position,
                classId: classId
            });
        }
    });

    $.ajax({
        type: 'POST',
        url: "/saveSeatingArrangements",
        dataType: 'json',
        data: JSON.stringify(classroomSeatingArrangements),
        contentType: "application/json; charset=utf-8",
        success: function (responseData) {
            $(".desktile .deskcontainer.unsaved").removeClass("unsaved");
            $(".desktile .deskcontainer[prev-position]").removeAttr("prev-position");
            $('<div id="saveResult"><div>Seating map changes saved.</div></div>').insertBefore($('#dvleft'))
           .fadeIn("slow", function () { $(this).delay(3000).fadeOut("slow"); });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            
        }
    });
}

function cancelEditDesk() {
    $(".desktile .deskcontainer.unsaved").appendTo($("#hidtiles")).attr("position", 0).each(function (i, e) {
        $("#" + $(e).attr("userid")).find(".seated").addClass("hideitem");
    });
    $("#hidtiles .deskcontainer:not(.unsaved)").each(function (i, e) {
        var $e = $(e);
        var position = $e.attr("prev-position");
        $e.attr("position", position).removeAttr("prev-position").appendTo($(".desktile[position=" + position + "]"));
        $("#" + $e.attr("userid")).find(".seated").removeClass("hideitem");
    });
    $(".desktile .deskcontainer[prev-position]").each(function (i, e) {
        var $e = $(e);
        var prevPosition = $e.attr("prev-position");
        $e.attr("position", prevPosition).removeAttr("prev-position").appendTo($(".desktile[position=" + prevPosition + "]"));
    })
}

$.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
        return null;
    }
    else {
        return results[1] || 0;
    }
}
