'use strict';

const NULL_FLAVOR = require('../constants').NULL_FLAVOR;

function cleanCode(code) {
    return typeof code === 'undefined' || code.length < 1
        ? NULL_FLAVOR
        : code.replace(/[.#]/, '');
}

exports.cleanCode = cleanCode;
