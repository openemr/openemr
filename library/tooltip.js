// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// See interface/patient_file/history/encounters.php for an example of
// implementing tooltips with this module.

// Helper functions.
function getX(elem) {
    let x = 0;
    while (elem != null) {
        x += elem.offsetLeft;
        elem = elem.offsetParent;
    }
    return x;
}

function getY(elem) {
    let y = 0;
    while (elem != null) {
        y += elem.offsetTop;
        elem = elem.offsetParent;
    }
    return y;
}

// onmouseover handler.
function ttshow(elem, tttext) {
    const ttobject = document.getElementById('tooltipdiv');
    ttobject.innerHTML = tttext;
    let x = getX(elem);
    const dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
    if (dw && dw < (x + ttobject.offsetWidth)) {
        x = dw - ttobject.offsetWidth;
        if (x < 0) {
            x = 0;
        }
    }
    let y = getY(elem) - ttobject.offsetHeight - 10;
    if (y < 0) {
        y = getY(elem) + elem.offsetHeight + 10;
    }
    ttobject.style.left = x;
    ttobject.style.top = y;
    ttobject.style.visibility = 'visible';
    return false;
}

// onmouseout handler.
function tthide() {
    const ttobject = document.getElementById('tooltipdiv');
    ttobject.style.visibility = 'hidden';
    ttobject.style.left = '-1000px';
}
