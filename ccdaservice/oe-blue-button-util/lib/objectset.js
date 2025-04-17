"use strict";

var lodash = require('lodash');

var object = require('./object');

exports.compact = function compact(obj) {
    if (typeof obj === 'object') {
        Object.keys(obj).forEach(function (key) {
            if (object.exists(obj[key])) {
                compact(obj[key]);
            } else {
                delete obj[key];
            }
        });
    }
};

exports.merge = lodash.merge; // remove 1.5, leaving now just to be safe

exports.deepValue = function (obj, deepProperty, value) {
    if ((!object.exists(obj)) || (typeof obj !== 'object')) {
        return null;
    }
    var currentObj = obj;
    var propertyPieces = deepProperty.split('.');
    var lastIndex = propertyPieces.length - 1;
    for (var i = 0; i < lastIndex; ++i) {
        var propertyPiece = propertyPieces[i];
        var nextObj = currentObj[propertyPiece];
        if ((!object.exists(nextObj)) || (typeof nextObj !== 'object')) {
            currentObj[propertyPiece] = nextObj = {};
        }
        currentObj = nextObj;
    }
    currentObj[propertyPieces[lastIndex]] = value;
    return obj;
};
