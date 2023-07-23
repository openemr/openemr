"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

exports.socialHistoryObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                text: leafLevel.inputProperty("unencoded_name"),
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("social")
                    }
                }
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "code",
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("value")
        }
    ],
    existsWhen: function (input) {
        return (!input.value) || input.value.indexOf("smoke")  > -1;
    }
};

exports.smokingStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.78"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("SmokingStatusObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.codeFromName("2.16.840.1.113883.11.20.9.38")
            ],
            required: true,
            dataKey: "value"
        }
        ,fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.value && input.value.indexOf("smoke") > -1;
    }
};

exports.genderStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.200", "2016-06-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.200"),
        fieldLevel.templateCode("GenderStatusObservation"),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: [{
                "xsi:type": "CD"
            },
                leafLevel.codeFromName("2.16.840.1.113883.5.1")
            ],
            required: true,
            dataKey: "gender"
        }
        ,[fieldLevel.author, contentModifier.dataKey("gender_author")]
    ],
    existsWhen: function (input) {
        return input && input.gender;
    }
};
