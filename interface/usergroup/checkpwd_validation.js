//-------------------------------------------------------------------
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//Author:- Author:- ViCarePlus Team, Visolve
//Email ID:- vicareplus_engg@visolve.com
//-------------------------------------------------------------------


/* 
 *  Fuction to check that the password contains at least 3 of the following:
 *  - an integer one integer
 *  - a lower case letter
 *  - an upper case letter
 *  - a special character
 *  Also, the password should be at least 8 characters.
 */
function passwordvalidate(password_string) {
    var pwd = password_string;
    var items = 0;
    if (pwd.length < 8) {
        return false;
    }
    var rgx = /[a-z]+/;
    if (rgx.test(pwd)) {
        items += 1;
    }
    rgx = /[A-Z]+/;
    if (rgx.test(pwd)) {
        items += 1;
    }
    rgx = /\d+/;
    if (rgx.test(pwd)) {
        items += 1;
    }
    rgx = /[\W_]+/;
    if (rgx.test(pwd)) {
        items += 1;
    }
    if (items < 3) {
        return false;
    }
    return true;
}

// Removes leading whitespaces
function LTrim(value) {
    var re = /\s*((\S+\s*)*)/;
    return value.replace(re, "$1");
}

// Removes ending whitespaces
function RTrim(value) {
    var re = /((\s*\S+)*)\s*/;
    return value.replace(re, "$1");
}

// Removes leading and ending whitespaces
function trim(value) {
    return LTrim(RTrim(value));
}
