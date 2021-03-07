"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

var vitalSignObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.27"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("vital")
                    }
                }
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "vital",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: {
                "xsi:type": "PQ",
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            existsWhen: condition.keyExists("value"),
            required: true
        }, {
            key: "interpretationCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.5.83"),
            dataKey: "interpretations"
        }
    ],
    notImplemented: [
        "constant statusCode",
        "methodCode",
        "targetSiteCode",
        "author"
    ]
};

exports.vitalSignsOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "CLUSTER",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.26"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("VitalSignsOrganizer"), {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required], {
            key: "component",
            content: vitalSignObservation,
            required: true
        }
    ],
    notImplemented: [
        "constant statusCode"
    ]
};
