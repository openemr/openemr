"use strict";

var object = require('./object');

var exists = object.exists;

var hasProperty = exports.hasProperty = function (deepProperty) {
    var propertyPieces = deepProperty.split('.');
    if (propertyPieces.length > 1) {
        return function (input) {
            if (!exists(input)) {
                return false;
            } else {
                return propertyPieces.every(function (piece) {
                    if (typeof input !== 'object') {
                        return false;
                    }
                    if (!input.hasOwnProperty(piece)) {
                        return false;
                    }
                    input = input[piece];
                    if (!exists(input)) {
                        return false;
                    } else {
                        return true;
                    }
                });
            }
        };
    } else {
        return function (input) {
            if (exists(input) && (typeof input === 'object')) {
                return input.hasOwnProperty(deepProperty);
            } else {
                return false;
            }
        };
    }
};

exports.hasNoProperty = function (deepProperty) {
    var f = hasProperty(deepProperty);
    return function (input) {
        return !f(input);
    };
};

exports.inValueSet = function (valueSet) {
    return function (input) {
        return (valueSet.indexOf(input) >= 0);
    };
};

exports.hasNoProperties = function (deepProperties) {
    var fns = deepProperties.map(function (p) {
        return hasProperty(p);
    });
    return function (input) {
        var some = fns.some(function (fn) {
            return fn(input);
        });
        return !some;
    };
};

exports.propertyValue = function (deepProperty) {
    return function (input) {
        return object.deepValue(input, deepProperty);
    };
};

exports.falsyPropertyValue = function (deepProperty) {
    return function (input) {
        return !object.deepValue(input, deepProperty);
    };
};

exports.and = function (fns) {
    return function (input) {
        return fns.every(function (fn) {
            return fn(input);
        });
    };
};

exports.or = function (fns) {
    return function (input) {
        return fns.some(function (fn) {
            return fn(input);
        });
    };
};

exports.not = function (fn) {
    return function (input) {
        return !fn(input);
    };
};
