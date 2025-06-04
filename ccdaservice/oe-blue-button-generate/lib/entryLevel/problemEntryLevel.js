"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var problemStatus = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.6"),
        fieldLevel.id,
        fieldLevel.templateCode("ProblemStatus"),
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.codeFromName("2.16.840.1.113883.3.88.12.80.68")
            ],
            dataKey: "name",
            required: true
        }
    ],
    warning: "effectiveTime does not exist in the specification"
};

var healthStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.5"),
        fieldLevel.templateCode("HealthStatusObservation"),
        fieldLevel.text(leafLevel.nextReference("healthStatus")),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: "81323004",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED CT",
                displayName: leafLevel.inputProperty("patient_status")
            },
            required: true,
            toDo: "The attribute should not be constant"
        }
    ]
};

var problemObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN",
        negationInd: leafLevel.boolInputProperty("negation_indicator")
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.4", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.4"),
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "64572001", codeSystem: "2.16.840.1.113883.6.96", displayName: "Condition"
            },
            content: [{
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
        },
        fieldLevel.text(leafLevel.nextReference("problem")),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, dataKey("problem.date_time")], {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.code
            ],
            content: [{
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "problem.code",
            existsWhen: condition.codeOrDisplayname,
            required: true
        },
        fieldLevel.author,
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [problemStatus, required]
            ],
            dataTransform: function (input) {
                if (input && input.status) {
                    var result = input.status;
                    result.identifiers = input.identifiers;
                    return result;
                }
                return null;
            }
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [
                [sharedEntryLevel.ageObservation, required]
            ],
            existsWhen: condition.keyExists("onset_age")
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [healthStatusObservation, required]
            ],
            existsWhen: condition.keyExists("patient_status")
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [sharedEntryLevel.severityObservation]
            ],
            dataKey: "problem",
            existsWhen: condition.keyExists("severity")
        }
    ],
    notImplemented: [
        "code"
    ]
};

exports.problemConcernAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.3", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.3"),
        fieldLevel.uniqueId, {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("identifier"),
                extension: leafLevel.inputProperty("extension")
            },
            dataKey: 'source_list_identifiers',
            existsWhen: condition.keyExists('identifier'),
            required: true
        },
        fieldLevel.templateCode("ProblemConcernAct"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required],
        fieldLevel.author,
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ"
            },
            content: [
                [problemObservation, required]
            ],
            required: true
        }
    ]
};
