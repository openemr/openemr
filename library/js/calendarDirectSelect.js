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
//
// +------------------------------------------------------------------------------+
function updateApptTime(marker, index, y, date, provider) {
    row = $("#times table tr").eq(index);
    timeSlot = row.find("td a");
    params = timeSlot.attr("href").split("(")[1].split(",");
    newEvtParam = params[0] + "," + params[1] + "," + params[2] + "," + date + "," + provider + "," + "0";
    onClk = "javascript:newEvt(" + newEvtParam + ")";
    marker.html(timeSlot.html());
    marker.attr("href", onClk);
}

function displayApptTime(evt) {
    marker = $(this).find("a.apptMarker");
    if (marker.length == 0) {
        style = "style=\'height:" + tsHeight + ";\'";
        $(this).find("div.calendar_day").append("<a class=\'apptMarker event event_appointment\'" + style + "></a>");
        marker = $(this).find("a.apptMarker");
        marker.css("z-index", 1);
    }
    y = evt.pageY - $(this).offset().top;
    rem = y % tsHeightNum;
    y = y - rem;
    ph = $(this).find("div.providerheader");
    index = y / tsHeightNum;
    if (ph.length == 1) {
        y = y - ph.height();
        if (index == 0) {
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
    marker = $(this).find("a.apptMarker");
    marker.hide();
}

function setupDirectTime() {
    $("td.schedule").mousemove(displayApptTime);
    $("td.schedule").mouseleave(hideApptTime);
}
