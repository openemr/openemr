"use strict";

var lodash = require('lodash');

exports.append = function (arr, arrToAppend) {
    Array.prototype.push.apply(arr, arrToAppend);
};

exports.remove = lodash.remove; // remove 1.5, leaving now just to be safe
