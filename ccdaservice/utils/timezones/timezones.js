'use strict';

const DEPTH_LIMIT = 25;
const TIMEZONE_PRECISION = 'tz';

function isObject(node) {
    return node && typeof node === 'object';
}

function isTimezoneDateWithoutOffset(node) {
    return (
        node.precision == TIMEZONE_PRECISION &&
        !Object.prototype.hasOwnProperty.call(node, 'timezoneOffset')
    );
}

// do a recursive descent transformation of the node object, populating the timezone offset value
// if we have a precision property (inside a date) with the value of timezone.
function populateTimezones(node, tzOffset, currentDepth) {
    if (!isObject(node)) {
        return node;
    }

    if (currentDepth > DEPTH_LIMIT) {
        console.error(
            'Max depth traversal reached. Potential infinite loop. Breaking out of loop.'
        );
    } else if (isTimezoneDateWithoutOffset(node)) {
        node.timezoneOffset = tzOffset;
    } else {
        for (const [key, value] of Object.entries(node)) {
            node[key] = populateTimezones(value, tzOffset, currentDepth + 1);
        }
    }
    return node;
}

exports.populateTimezones = populateTimezones;
exports.TIMEZONE_PRECISION = TIMEZONE_PRECISION;
exports.DEPTH_LIMIT = DEPTH_LIMIT;
