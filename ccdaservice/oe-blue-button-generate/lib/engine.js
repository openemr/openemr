"use strict";

var xmlutil = require('./xmlutil');

var expandText = function (input, template) {
    var text = template.text;
    if (text) {
        if (typeof text === 'function') {
            text = text(input);
        }
        if ((text !== null) && (text !== undefined)) {
            return text;
        }
    }
    return null;
};

var expandAttributes = function expandAttributes(input, context, attrObj, attrs) {
    if (Array.isArray(attrObj)) {
        attrObj.forEach(function (attrObjElem) {
            expandAttributes(input, context, attrObjElem, attrs);
        });
    } else if (typeof attrObj === 'function') {
        expandAttributes(input, context, attrObj(input, context), attrs);
    } else {
        Object.keys(attrObj).forEach(function (attrKey) {
            var attrVal = attrObj[attrKey];
            if (typeof attrVal === 'function') {
                attrVal = attrVal(input, context);
            }
            if ((attrVal !== null) && (attrVal !== undefined)) {
                attrs[attrKey] = attrVal;
            }
        });
    }
};

var fillAttributes = function (node, input, context, template) {
    var attrObj = template.attributes;
    if (attrObj) {
        var inputAttrKey = template.attributeKey;
        if (inputAttrKey) {
            input = input[inputAttrKey];
        }
        if (input) {
            var attrs = {};
            expandAttributes(input, context, attrObj, attrs);
            xmlutil.nodeAttr(node, attrs);
        }
    }
};

var update;

var fillContent = function (node, input, context, template) {
    var content = template.content;
    if (content) {
        if (!Array.isArray(content)) {
            content = [content];
        }
        content.forEach(function (element) {
            if (Array.isArray(element)) {
                var actualElement = Object.create(element[0]);
                for (var i = 1; i < element.length; ++i) {
                    element[i](actualElement);
                }
                update(node, input, context, actualElement);
            } else {
                update(node, input, context, element);
            }
        });
    }
};

var updateUsingTemplate = function updateUsingTemplate(xmlDoc, input, context, template) {
    var condition = template.existsWhen;
    if ((!condition) || condition(input, context)) {
        var name = template.key;
        var text = expandText(input, template);
        if (((text !== null) && (text !== undefined)) || template.content || template.attributes) {
            var node = xmlutil.newNode(xmlDoc, name, text);

            fillAttributes(node, input, context, template);
            fillContent(node, input, context, template);
            return true;
        }
    }
    return false;
};

var transformInput = function (input, template) {
    var inputKey = template.dataKey;
    if (inputKey) {
        var pieces = inputKey.split('.');
        pieces.forEach(function (piece) {
            if (Array.isArray(input) && (piece !== "0")) {
                var nextInputs = [];
                input.forEach(function (inputElement) {
                    var nextInput = inputElement[piece];
                    if (nextInput) {
                        if (Array.isArray(nextInput)) {
                            nextInput.forEach(function (nextInputElement) {
                                if (nextInputElement) {
                                    nextInputs.push(nextInputElement);
                                }
                            });
                        } else {
                            nextInputs.push(nextInput);
                        }
                    }
                });
                if (nextInputs.length === 0) {
                    input = null;
                } else {
                    input = nextInputs;
                }
            } else {
                input = input && input[piece];
            }
        });
    }
    if (input) {
        var transform = template.dataTransform;
        if (transform) {
            input = transform(input);
        }
    }
    return input;
};

update = exports.update = function (xmlDoc, input, context, template) {
    var filled = false;
    if (input) {
        input = transformInput(input, template);
        if (input) {
            if (Array.isArray(input)) {
                input.forEach(function (element) {
                    filled = updateUsingTemplate(xmlDoc, element, context, template) || filled;
                });
            } else {
                filled = updateUsingTemplate(xmlDoc, input, context, template);
            }
        }
    }
    if ((!filled) && template.required && !context.preventNullFlavor) {
        var node = xmlutil.newNode(xmlDoc, template.key);
        xmlutil.nodeAttr(node, {
            nullFlavor: 'UNK'
        });
    }
};

exports.create = function (template, input, context) {
    var doc = new xmlutil.newDocument();
    update(doc, input, context, template);
    var result = xmlutil.serializeToString(doc);
    return result;
};
