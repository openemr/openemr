"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.goalActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "GOL"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.121"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "goal_code",
            existsWhen: condition.propertyNotEmpty("code"),
        }, {
            key: "code",
            attributes: {
                nullFlavor: "UNK"
            },
            dataKey: "goal_code",
            existsWhen: condition.propertyEmpty("code"),
        },
        fieldLevel.statusCodeActive, // always for goals
        fieldLevel.effectiveTime,
        {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("name")
        },
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "observation";
    }
};

var goal = {
    key: "code",
    attributes: {
        "code": leafLevel.deepInputProperty("code"),
        "displayName": "Goal"
    },
    content: [{
        key: "originalText",
        text: leafLevel.deepInputProperty("name")
    }],
    dataKey: "goals"
};
