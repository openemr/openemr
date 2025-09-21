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
                codeSystemName: "SNOMED CT",
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
        }, /*{
            key: "text",
            text: leafLevel.input,
            dataKey: "note"
        },*/
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
        , fieldLevel.author
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
        , fieldLevel.author
    ]
};

var functionalStatusSelfCareObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.128"),
        fieldLevel.id, {
            key: "code",
            attributes: {
                nullFlavor: "NA",
            },
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: "371153006",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED CT",
                displayName: "Independent"
            }
        }
        , fieldLevel.author
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
        fieldLevel.author,
        [{
            key: "component",
            content: functionalStatusObservation,
            dataKey: "observation",
            required: true
        }],
        [{
            key: "component",
            content: functionalStatusSelfCareObservation,
            dataKey: "observation",
            required: true
        }]
    ],
};

exports.disabilityStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.505", "2023-05-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.505"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: leafLevel.deepInputProperty("overall_status.code"),
                codeSystem: leafLevel.deepInputProperty("overall_status.code_system"),
                codeSystemName: leafLevel.deepInputProperty("overall_status.code_system_name"),
                displayName: leafLevel.deepInputProperty("overall_status.display")
            }
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, required],
        {
            key: "value",
            attributes: [
                {
                    "xsi:type": "CD"
                }, {
                    code: leafLevel.deepInputProperty("overall_status.answer_code"),
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    displayName: leafLevel.deepInputProperty("overall_status.answer_display")
                }
            ]
        },
        // Add individual disability questions as entryRelationship components
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: {
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.86"),
                    fieldLevel.uniqueId,
                    fieldLevel.id, // Add required id element
                    {
                        key: "code",
                        attributes: {
                            code: leafLevel.inputProperty("code"),
                            codeSystem: leafLevel.inputProperty("code_system"),
                            codeSystemName: leafLevel.inputProperty("code_system_name"),
                            displayName: leafLevel.inputProperty("display")
                        }
                    },
                    fieldLevel.statusCodeCompleted,
                    {
                        key: "value",
                        attributes: [
                            {
                                "xsi:type": "CD"
                            }, {
                                code: leafLevel.inputProperty("answer_code"),
                                codeSystem: leafLevel.inputProperty("answer_code_system") || "2.16.840.1.113883.6.1",
                                codeSystemName: "LOINC",
                                displayName: leafLevel.inputProperty("answer_display")
                            }
                        ]
                    }
                ]
            },
            dataKey: "disability_questions.question"
        }
    ],
    existsWhen: function (input) {
        return input && input.overall_status;
    }
};
