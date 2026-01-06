"use strict";

var headerLevel = require('../headerLevel');
var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var required = contentModifier.required;
var dataKey = contentModifier.dataKey;
var key = contentModifier.key;

// Individual Advance Directive Observation (V3) - used directly in entries
exports.advanceDirectiveObservation = {
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
                codeSystemName: leafLevel.inputProperty("observation_code_system_name"),
                displayName: leafLevel.inputProperty("observation_display")
            },
            content: [
                {
                    key: "translation",
                    attributes: {
                        code: "75320-2",
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        displayName: "Advance directive"
                    }
                }
            ]
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "effectiveTime",
            content: [
                {
                    key: "low",
                    attributes: {
                        value: leafLevel.inputProperty("effective_date")
                    }
                },
                {
                    key: "high",
                    attributes: {
                        nullFlavor: "NA"
                    }
                }
            ]
        },
        {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: leafLevel.inputProperty("observation_value_code"),
                codeSystem: leafLevel.inputProperty("observation_value_code_system"),
                codeSystemName: leafLevel.inputProperty("observation_value_code_system_name"),
                displayName: leafLevel.inputProperty("observation_value_display")
            }
        },
        fieldLevel.author,
        {
            key: "participant",
            attributes: {
                typeCode: "CST"
            },
            content: {
                key: "participantRole",
                attributes: {
                    classCode: "AGNT"
                },
                content: [
                    {
                        key: "addr",
                        attributes: {
                            nullFlavor: "UNK"
                        }
                    },
                    {
                        key: "telecom",
                        attributes: {
                            nullFlavor: "UNK"
                        }
                    },
                    {
                        key: "playingEntity",
                        attributes: {
                            classCode: "PSN"
                        },
                        content: {
                            key: "name",
                            attributes: {
                                nullFlavor: "UNK"
                            }
                        }
                    }
                ]
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
                        key: "code",
                        attributes: {
                            nullFlavor: "UNK"
                        }
                    },
                    {
                        key: "text",
                        attributes: {
                            mediaType: "text/plain"
                        },
                        content: {
                            key: "reference",
                            attributes: {
                                value: leafLevel.inputProperty("location")
                            }
                        }
                    }
                ]
            },
            existsWhen: condition.keyExists("document_reference")
        }
    ]
};
