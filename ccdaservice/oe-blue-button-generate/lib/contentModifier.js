"use strict";

exports.key = function (overrideKeyValue) {
    return function (template) {
        template.key = overrideKeyValue;
    };
};

exports.required = function (template) {
    template.required = true;
};

exports.dataKey = function (overrideKeyValue) {
    return function (template) {
        template.dataKey = overrideKeyValue;
    };
};
