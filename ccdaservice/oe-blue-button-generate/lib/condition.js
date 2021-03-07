"use strict";

exports.keyExists = function (key) {
    return function (input) {
        return input.hasOwnProperty(key);
    };
};

exports.keyDoesntExist = function (key) {
    return function (input) {
        return !input.hasOwnProperty(key);
    };
};

exports.eitherKeyExists = function (key0, key1, key2, key3) {
    return function (input) {
        return input.hasOwnProperty(key0) || input.hasOwnProperty(key1) || input.hasOwnProperty(key2) || input.hasOwnProperty(key3);
    };
};

exports.codeOrDisplayname = function (input) {
    return input.code || input.name;
};

exports.propertyEquals = function (property, value) {
    return function (input) {
        return input && (input[property] === value);
    };
};
