// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 IntegralEMR LLC <kevin.y@integralemr.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Kevin Yeh <kevin.y@integralemr.com>
// Author:   Jerry Padgett <sjpadgett@gmail.com>
//
// +------------------------------------------------------------------------------+
function updateApptTime(marker, index, y, date, provider) {
    let row = $("#times table tr").eq(index);
    let timeSlot = row.find("td a");
    if (typeof timeSlot.attr("href") !== 'undefined') {
        if (index && y) {
            let params = timeSlot.attr("href").split("(")[1].split(",");
            let newEvtParam = params[0] + "," + params[1] + "," + params[2] + "," + date + "," + provider + "," + "0";
            let onClk = "javascript:newEvt(" + newEvtParam + ")";
            marker.html(timeSlot.html());
            marker.attr("href", onClk);
        }
    }
}

function displayApptTime(evt) {
    let marker = $(this).find("a.apptMarker");
    if (marker.length == 0) {
        // Do not add class "event" — that class is bound to EditEvent dblclick
        // and would intercept double-clicks meant for IN/OUT/LUNCH blocks.
        style = "style='height:" + tsHeight + ";'";
        $(this).find("div.calendar_day").append("<a class='apptMarker event_appointment'" + style + "></a>");
        marker = $(this).find("a.apptMarker");
        // Stay under real schedule events (OUT/LUNCH/appointments) so they remain clickable.
        marker.css("z-index", 0);
    }
    y = evt.pageY - $(this).offset().top;
    rem = y % tsHeightNum;
    y = y - rem;
    let ph = $(this).find("div.providerheader");
    let index = y / tsHeightNum;
    if (ph.length == 1) {
        y = y - ph.height();
        if (index == 0) {
            marker.hide();
            return;
        }
    }

    // If the cursor is over a real calendar event (IN label, OUT, LUNCH,
    // appointment), hide the create-slot marker so click/dblclick reach it.
    // Temporarily disable marker hit-testing so elementFromPoint sees below.
    marker.css("pointer-events", "none");
    let under = document.elementFromPoint(evt.clientX, evt.clientY);
    marker.css("pointer-events", "");
    let $under = $(under);
    let $realEvent = $under.closest("div.event").not(".apptMarker");
    if ($realEvent.length) {
        // IN body fill is pointer-events:none; only the in_start label is editable.
        // Over bare IN fill, keep the marker so empty office hours stay creatable.
        if ($realEvent.hasClass("event_in") && !$realEvent.hasClass("in_start") && !$under.closest(".in_start").length) {
            // fall through — show marker for new appointment in open IN hours
        } else {
            marker.hide();
            return;
        }
    }

    marker.css("top", y);
    date = $(this).attr("date");
    updateApptTime(marker, index, y, date, $(this).attr("provider"));
    marker.show();
}

function hideApptTime(evt) {
    let marker = $(this).find("a.apptMarker");
    marker.hide();
}

function setupDirectTime() {
    $("td.schedule").mousemove(displayApptTime);
    $("td.schedule").mouseleave(hideApptTime);
}
