"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var required = contentModifier.required;

exports.mentalStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.74", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.74"),
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "373930000",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED-CT",
                displayName: "Cognitive function"
            },
            content: [{
                key: "translation",
                attributes: {
                    code: "75275-8",
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    displayName: "Cognitive function"
                }
            }]
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "value",
            existsWhen: condition.codeOrDisplayname
        }
    ]
};

var functionalStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.67", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.67"),
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "54522-8",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Functional status"
            },
            content: [{
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("functional_status")
                    }
                }
            }]
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "value",
            existsWhen: condition.codeOrDisplayname
        }
    ]
};

exports.functionalStatusOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "CLUSTER",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.66", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.66"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "d5",
                codeSystem: "2.16.840.1.113883.6.254",
                codeSystemName: "ICF",
                displayName: "Self-Care"
            }
        },
        {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [{
            key: "component",
            content: functionalStatusObservation,
            dataKey: "observation",
            required: true
        }]
    ],
};
