"use strict";

var bbu = require("../../oe-blue-button-util");

var bbuo = bbu.object;

exports.isNullFlavorSection = function (key) {
    return function (input) {
        let value = input && input[key];
        if (!value) {
            return {
                nullFlavor: "NI"
            };
        } else {
            return {};
        }
    };
};

exports.keyExists = function (key) {
    let bu = key;
    return function (input) {
        return Object.prototype.hasOwnProperty.call(input, key);
    };
};

exports.keyDoesntExist = function (key) {
    return function (input) {
        return !Object.prototype.hasOwnProperty.call(input, key);
    };
};

exports.eitherKeyExists = function (key0, key1, key2, key3) {
    return function (input) {
        return (
            Object.prototype.hasOwnProperty.call(input, key0) ||
            Object.prototype.hasOwnProperty.call(input, key1) ||
            Object.prototype.hasOwnProperty.call(input, key2) ||
            Object.prototype.hasOwnProperty.call(input, key3)
        );
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

exports.propertyNotEmpty = function (property) {
    return function (input) {
        return input && (input[property] !== "");
    };
};

exports.propertyEmpty = function (property) {
    return function (input) {
        return input && (input[property] === "");
    };
};

exports.propertyValueEmpty = function (deepProperty, defaultValue = "") {
    return function (input) {
        let value = bbuo.deepValue(input, deepProperty);
        value = bbuo.exists(value) ? value : defaultValue;
        if (typeof value !== 'string') {
            value = value.toString();
        }

        return input && (value === '' || value === 'NaN')
    }
};

exports.propertyValueNotEmpty = function (deepProperty, defaultValue = "") {
    return function (input) {
        let value = bbuo.deepValue(input, deepProperty);
        value = bbuo.exists(value) ? value : defaultValue;
        if (typeof value !== 'string') {
            value = value.toString();
        }

        return input && !(value === '' || value === 'NaN')
    }
};
