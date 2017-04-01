/*
 *   * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *   * See LICENSE in the project root for license information.
 */
$(document).ready(function () {
    loadImages();

    $(".teacher-student .filterlink-container .filterlink").click(function () {
        var element = $(this);
        element.addClass("selected").siblings("a").removeClass("selected");
        var filterType = element.data("type");
        var tilesContainer = $(".teacher-student .tiles-root-container");
        tilesContainer.removeClass(tilesContainer.attr("class").replace("tiles-root-container", "")).addClass(filterType + "-container");
    });

    $(".teacher-student .tiles-root-container .pagination .prev, .teacher-student .tiles-root-container .pagination .next").click(function() {
        var element = $(this);
        if (element.hasClass("current") || element.hasClass("disabled")) {
            return;
        }

        var curPageElement = element.siblings("#curpage");
        var curPage = parseInt(curPageElement.val());
        var isBackward = element.hasClass("prev");
        var targetPageNum = curPage + (isBackward ? -1 : 1);
        var nextElement = isBackward ? element.siblings(".next") : element;
        var prevElement = isBackward ? element : element.siblings(".prev");
        var skipTokenElement = element.siblings("#skipToken");
        var skipToken = skipTokenElement.val();
        var hasSkipToken = typeof (skipToken) == "string" && skipToken.length > 0;
        if (isBackward) {
            nextElement = element.siblings(".next");
            prevElement = element;
        }
        var container = element.closest(".tiles-secondary-container");
        var content = container.find(".content");
        var startItemNum = (targetPageNum - 1) * 12;
        if (startItemNum < content.children().length) {
            showPage(isBackward, targetPageNum, hasSkipToken, prevElement, nextElement, curPageElement, content);
        }
        else if (hasSkipToken) {
            var objectId = $(".teacher-student input#school-objectid").val();
            var action = container.attr("id");
            var url = "/" + action + "/next/" + objectId + "/" + skipToken;

            var prevNext = prevElement.add(nextElement);
            prevNext.addClass("disabled");
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                contentType: "application/json; charset=utf-8",
                beforeSend: function(jqXHR, settings){
                    jqXHR.setRequestHeader("accept", "application/json");
                },
                success: function(data) {
                    var value = data.value;
                    if (!(value instanceof Array) || value.length == 0) {
                        return;
                    }
                    $.each(value, function (i, user) {
                        var userHtml = '<div class="element ' + (user.educationObjectType == "Teacher" ? "teacher-bg" : "student-bg") + '">' +
                            '<div class="userimg">' +
                            '<img src="../public/images/header-default.jpg" realheader="' + '/userPhoto/' + user.o365UserId + '" />' +
                            '</div>' +
                            '<div class="username">' + user.displayName + '</div>' +
                            '</div>';
                        $(userHtml).appendTo(content);
                    });

                    var newSkipToken = data.skipToken;
                    skipTokenElement.val(newSkipToken);
                    hasSkipToken = typeof (newSkipToken) == "string" && newSkipToken.length > 0;
                    showPage(false, targetPageNum, hasSkipToken, prevElement, nextElement, curPageElement, content);
                },
                error: function(jqXHR, textStatus, errorThrown ) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errorCode === 401) {
                        alert("Your current session has expired. Please click OK to refresh the page.");
                        window.location.reload(false);
                    }
                },
                complete: function(XMLHttpRequest, textStatus, errorThrown) {
                    prevNext.removeClass("disabled");
                }
            });
        }
    });

    function loadImages() {
        $("img[realheader]").each(function (i, e) {
            var $e = $(e);
            $e.attr("src", $e.attr("realheader"));
        });
    }

    function showPage(isBackward, targetPageNum, hasNextPage, prevElement, nextElement, curPageElement, content) {
        var start = (targetPageNum - 1) * 12;
        var end = targetPageNum * 12;
        var elements = content.children();
        elements.hide().slice(start, end).fadeIn("slow", function () {
            var img = $(this).find("img[realheader]");
            img.attr("src", img.attr("realheader"));
        });

        nextElement.toggleClass("current", !isBackward && end >= elements.length && !hasNextPage);
        prevElement.toggleClass("current", start === 0);
        curPageElement.val(targetPageNum);
    }
});
