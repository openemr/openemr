/**
 * Calendar direct select: in day and week view, a marker follows the mouse over a
 * provider's (or day's) column, one time slot tall, showing that slot's time; clicking
 * it opens a new appointment then.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (C) 2011 IntegralEMR LLC <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* global tsHeight, tsHeightNum */

// The time cell of the given slot (0 = the first time of the day) in the times column.
// Only the cells that hold a time count: day view's column also has a provider icon
// cell above the first time.
function timeCellForSlot(slot) {
    return $("#times td.timeslot").filter(function () {
        return $(this).find("a").length > 0;
    }).eq(slot);
}

function updateApptTime(marker, slot, date, provider) {
    let timeSlot = timeCellForSlot(slot).find("a");
    if (typeof timeSlot.attr("href") === 'undefined') {
        return false;
    }
    let params = timeSlot.attr("href").split("(")[1].split(",");
    let newEvtParam = params[0] + "," + params[1] + "," + params[2] + "," + date + "," + provider + "," + "0";
    marker.html(timeSlot.html());
    marker.attr("href", "javascript:newEvt(" + newEvtParam + ")");
    return true;
}

function displayApptTime(evt) {
    // Holidays / clinic-closed days block new appointments the same way
    // full-column holiday overlays do on the provider schedule.
    if ($(this).hasClass("schedule-holiday") || $(this).attr("data-holiday") === "1") {
        $(this).find("a.apptMarker").hide();
        return;
    }
    let day = $(this).find("div.calendar_day");
    let marker = $(this).find("a.apptMarker");
    if (marker.length == 0) {
        day.append("<a class='apptMarker event event_appointment' style='height:" + tsHeight + ";'></a>");
        marker = $(this).find("a.apptMarker");
        marker.css("z-index", 1);
    }
    // Measured from the top of the appointments, where slot 0 starts, whatever sits
    // above them (day view's provider name bar), so the marker lines up with the times.
    let y = evt.pageY - day.offset().top;
    if (y < 0) {
        marker.hide();
        return;
    }
    let slot = Math.floor(y / tsHeightNum);
    if (!updateApptTime(marker, slot, $(this).attr("date"), $(this).attr("provider"))) {
        marker.hide();
        return;
    }
    marker.css("top", slot * tsHeightNum);
    marker.show();
}

function hideApptTime() {
    let marker = $(this).find("a.apptMarker");
    marker.hide();
}

function setupDirectTime() {
    $("td.schedule").mousemove(displayApptTime);
    $("td.schedule").mouseleave(hideApptTime);
}
