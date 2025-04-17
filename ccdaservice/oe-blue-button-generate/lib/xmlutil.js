"use strict";

var libxmljs = require("libxmljs2");

exports.newDocument = function () {
    return new libxmljs.Document();
};

exports.newNode = function (xmlDoc, name, text) {
    if ((text === undefined) || (text === null)) {
        return xmlDoc.node(name);
    } else {
        return xmlDoc.node(name, text);
    }
};

exports.nodeAttr = function (node, attr) {
    node.attr(attr);
};

exports.serializeToString = function (xmlDoc) {
    return xmlDoc.toString();
};
