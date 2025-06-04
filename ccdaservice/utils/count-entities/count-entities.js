'use strict';

function hasEntityKey(input) {
    return ['npi', 'code', 'extension', 'id', 'date', 'use', 'type'].some(
        (key) => Object.prototype.hasOwnProperty.call(input, key)
    );
}

function countEntities(input) {
    if (input === null || typeof input !== 'object') return 0;
    return hasEntityKey(input) ? 1 : Object.keys(input).length;
}

exports.countEntities = countEntities;
