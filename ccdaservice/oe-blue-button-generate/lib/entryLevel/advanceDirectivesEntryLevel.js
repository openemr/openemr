
"use strict";

var headerLevel = require('../headerLevel');
var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var required = contentModifier.required;
var dataKey = contentModifier.dataKey;
var key = contentModifier.key;

exports.advanceDirectiveOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "CLUSTER",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.222", "2016-11-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.222"),
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "42348-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Advance directives"
            }
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime,
        fieldLevel.author,
        {
            key: "component",
            dataKey: "directives", // Move this here
            content: {
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.48", "2015-08-01"),
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.48"),
                    fieldLevel.id,
                    {
                        key: "code",
                        attributes: {
                            code: leafLevel.inputProperty("observation_code"),
                            codeSystem: leafLevel.inputProperty("observation_code_system"),
                            codeSystemName: "LOINC",
                            displayName: leafLevel.inputProperty("observation_display")
                        }
                    },
                    fieldLevel.statusCodeCompleted,
                    {
                        key: "effectiveTime",
                        attributes: {
                            value: leafLevel.inputProperty("effective_date")
                        }
                    },
                    {
                        key: "value",
                        attributes: {
                            "xsi:type": "CD",
                            code: leafLevel.inputProperty("observation_value_code"),
                            codeSystem: "2.16.840.1.113883.6.1",
                            codeSystemName: "LOINC",
                            displayName: leafLevel.inputProperty("observation_value_display")
                        }
                    },
                    {
                        key: "reference",
                        attributes: {
                            typeCode: "REFR"
                        },
                        content: {
                            key: "externalDocument",
                            attributes: {
                                classCode: "DOC",
                                moodCode: "EVN"
                            },
                            content: [
                                {
                                    key: "id",
                                    attributes: {
                                        root: leafLevel.inputProperty("document_reference")
                                    }
                                },
                                {
                                    key: "text",
                                    text: leafLevel.inputProperty("location")
                                }
                            ]
                        },
                        existsWhen: condition.keyExists("document_reference")
                    }
                ]
            }
        }
    ]
};
