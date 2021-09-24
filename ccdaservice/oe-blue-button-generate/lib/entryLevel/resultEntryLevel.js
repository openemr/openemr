"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

var resultObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.2", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.2"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "result",
            required: true
        },
        fieldLevel.text(leafLevel.nextReference("result")),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required],
        {
            key: "value",
            attributes: {
                "xsi:type": "PQ",
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            existsWhen: condition.propertyEquals("type", "PQ"),
            //required: true
        }, {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("value"),
            existsWhen: condition.propertyEquals("type", "ST"),
        }, {
            key: "value",
            attributes: {
                "xsi:type": "CO",
                "code": "260385009",
                "codeSystemName": "SNOMED-CT",
                "displayName": "Negative",
                "codeSystem": "2.16.840.1.113883.6.96"
            },
            existsWhen: condition.propertyEquals("type", "CO"),
        }, {
            key: "interpretationCode",
            attributes: {
                code: function (input) {
                    if (Object.prototype.toString.call(input) === "[object String]")
                        return input.substring(0, 1);
                    else return input.code.substring(0, 1);
                },
                codeSystem: "2.16.840.1.113883.5.83",
                displayName: leafLevel.input,
                codeSystemName: "ObservationInterpretation",
            },
            dataKey: "interpretations"
        }, {
            key: "referenceRange",
            content: {
                key: "observationRange",
                content: [{
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "range"
                }, {
                    key: "value",
                    attributes: {
                        "xsi:type": "IVL_PQ"
                    },
                    content: [{
                        key: "low",
                        attributes: {
                            value: leafLevel.inputProperty("low"),
                            unit: leafLevel.inputProperty("unit")
                        },
                        existsWhen: condition.propertyNotEmpty("low"),
                    }, {
                        key: "high",
                        attributes: {
                            value: leafLevel.inputProperty("high"),
                            unit: leafLevel.inputProperty("unit")
                        },
                        existsWhen: condition.propertyNotEmpty("high")
                    }],
                    existsWhen: function (input) {
                        return (input && input['unit'] && (input['range_type'] !== "CO"));
                    }
                }, {
                    key: "value",
                    attributes: {
                        "xsi:type": "IVL_PQ"
                    },
                    content: [{
                        key: "low",
                        attributes: {
                            value: leafLevel.inputProperty("low"),
                        },
                        existsWhen: condition.propertyNotEmpty("low"),
                    }, {
                        key: "high",
                        attributes: {
                            value: leafLevel.inputProperty("high"),
                        },
                        existsWhen: condition.propertyNotEmpty("high")
                    }],
                    existsWhen: function (input) {
                        return (input && !input['unit'] && (input['range_type'] !== "CO"));
                    }
                }, {
                    key: "value",
                    attributes: {
                        "xsi:type": "CO",
                        "code": "260385009",
                        "codeSystemName": "SNOMED-CT",
                        "displayName": "Negative",
                        "codeSystem": "2.16.840.1.113883.6.96"
                    },
                    existsWhen: condition.propertyEquals("range_type", "CO"),
                },
                ],
                required: true
            },
            dataKey: "reference_range"
        }
    ],
    notIplemented: [
        "variable statusCode",
        "methodCode",
        "targetSiteCode",
        "author"
    ]
};

exports.resultOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "BATTERY",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.1", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.1"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            },
            dataKey: "result_set",
            required: true
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.author,
        {
            key: "component",
            content: [
                [resultObservation, required]
            ],
            dataKey: "results",
            required: true
        }
    ],
    notIplemented: [
        "variable @classCode",
        "variable statusCode"
    ]
};
