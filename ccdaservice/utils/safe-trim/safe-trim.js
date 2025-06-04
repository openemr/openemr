'use strict';

function safeTrim(input) {
    return typeof input === 'string'
        ? input.trim()
        : input;
}

exports.safeTrim = safeTrim;
