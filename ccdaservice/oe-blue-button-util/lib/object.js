"use strict";

var lodash = require('lodash');

exports.isObject = lodash.isObject; // remove 1.5, leaving now just to be safe

var exists = exports.exists = function (obj) {
    return (obj !== undefined) && (obj !== null);
};

exports.deepValue = function (obj, deepProperty) {
    var propertyPieces = deepProperty.split('.');
    var n = propertyPieces.length;
    for (var i = 0; i < n; ++i) {
        if ((!exists(obj)) || (typeof obj !== 'object')) {
            return null;
        }
        var property = propertyPieces[i];
        obj = obj[property];
    }
    return (obj === undefined) ? null : obj;
};
