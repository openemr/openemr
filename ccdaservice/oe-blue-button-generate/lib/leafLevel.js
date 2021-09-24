"use strict";

var bbu = require("../../oe-blue-button-util");

var translate = require('./translate');

var bbuo = bbu.object;
var bbud = bbu.datetime;

exports.input = function (input) {
    return input;
};

exports.inputProperty = function (key) {
    return function (input) {
        return input && input[key];
    };
};

exports.docDateProperty = function (key) {
    return function (input, context) {
        return context && context[key];
    };
};

exports.boolInputProperty = function (key) {
    return function (input) {
        if (input && input.hasOwnProperty(key)) {
            return input[key].toString();
        } else {
            return null;
        }
    };
};

exports.code = translate.code;

exports.codeFromName = translate.codeFromName;

exports.codeOnlyFromName = function (OID, key) {
    var f = translate.codeFromName(OID);
    return function (input) {
        if (input && input[key]) {
            return f(input[key]).code;
        } else {
            return null;
        }
    };
};

exports.time = translate.time;

exports.use = function (key) {
    return function (input) {
        var value = input && input[key];
        if (value) {
            return translate.acronymize(value);
        } else {
            return null;
        }
    };
};

exports.typeCD = {
    "xsi:type": "CD"
};

exports.typeCE = {
    "xsi:type": "CE"
};

// Tables render first, so each table ID will sequence index referenceKey
// of the same referenceKey for template reference. e.g ID="result1" ... ID="severity1"
exports.nextTableReference = function (referenceKey) {
    return function (input, context) {
        return context.nextTableReference(referenceKey);
    };
};

// For our template references to table content ID.
// e.g <text><reference value="#result1"/></text>
exports.nextReference = function (referenceKey) {
    return function (input, context) {
        return context.nextReference(referenceKey);
    };
};

exports.sameReference = function (referenceKey) {
    return function (input, context) {
        return context.sameReference(referenceKey);
    };
};

exports.deepInputProperty = function (deepProperty, defaultValue, plus = "") {
    return function (input) {
        let value = bbuo.deepValue(input, deepProperty);
        value = bbuo.exists(value) ? value : defaultValue;
        if (typeof value !== 'string') {
            value = value.toString();
        }
        if (value === '' || value === 'NaN') {
            return defaultValue;
        }
        if (plus) {
            let valuePlus = bbuo.deepValue(input, plus);
            valuePlus = valuePlus ? valuePlus : defaultValue;
            if (typeof valuePlus !== 'string') {
                valuePlus = valuePlus.toString();
            }
            if (valuePlus === '' || valuePlus === 'NaN') {
                return "";
            }
            value = bbuo.exists(valuePlus) ? (value + ' ' + valuePlus) : value;
        }
        return value;
    };
};

exports.deepInputDate = function (deepProperty, defaultValue) {
    return function (input) {
        var value = bbuo.deepValue(input, deepProperty);
        if (!bbuo.exists(value)) {
            return defaultValue;
        } else {
            value = bbud.modelToDate({
                date: value.date,
                precision: value.precision // workaround a bug in bbud.  Changes precision.
            });
            if (bbuo.exists(value)) {
                return value;
            } else {
                return defaultValue;
            }
        }
    };
};
