"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");
var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.healthConcernObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.5", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.5"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "11323-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Health Status"
            },
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "value",
            existsWhen: condition.codeOrDisplayname
        },
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],

}

exports.healthConcernActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.132", "2015-08-01"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "75310-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Health Concern"
            },
        },
        fieldLevel.statusCodeActive,
        fieldLevel.author,
        [{
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: {
                key: "act",
                attributes: {
                    classCode: "ACT",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.122"),
                    fieldLevel.id, {
                        key: "code",
                        attributes: {
                            nullFlavor: "NP",
                        },
                        datakey: "problems.identifiers",
                    },
                    fieldLevel.statusCodeCompleted
                ]
            },
            dataKey: "problems"
        }],
    ],
    existsWhen: function (input) {
        return input.type === "act";
    }
};

exports.planOfCareActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.39"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "act";
    }
};

exports.planOfCareActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: leafLevel.inputProperty("mood_code")
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.44"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("name")
        },
        fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.type === "observation";
    }
};

exports.planOfCareActivityProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.41"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "procedure";
    }
};

exports.planOfCareActivityEncounter = {
    key: "encounter",
    attributes: {
        classCode: "ENC",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.40"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        [fieldLevel.performer, dataKey("performers")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "RSON"
            },
            content: [
                [sharedEntryLevel.indication, required]
            ],
            dataKey: "findings",
            dataTransform: function (input) {
                input = input.map(function (e) {
                    e.code = {
                        code: "282291009",
                        name: "Diagnosis",
                        code_system: "2.16.840.1.113883.6.96",
                        code_system_name: "SNOMED CT"
                    };
                    return e;
                });
                return input;
            }
        }
    ],
    existsWhen: function (input) {
        return input.type === "encounter";
    }
};

var carePlanMedicationInformation = {
    key: "manufacturedProduct",
    attributes: {
        classCode: "MANU"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.23", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.23"),
        {
            key: "manufacturedMaterial",
            content: [{
                key: "code",
                attributes: leafLevel.code,
            }]
        }
    ]
};
exports.planOfCareActivitySubstanceAdministration = {
    key: "substanceAdministration",
    attributes: {
        classCode: "SBADM",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.42", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.42"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "text",
            text: leafLevel.input,
            dataKey: "name"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        {
            key: "consumable",
            content: carePlanMedicationInformation,
            dataKey: "plan"
        },
    ],
    existsWhen: function (input) {
        return input.type === "substanceAdministration";
    }
};

exports.planOfCareActivitySupply = {
    key: "supply",
    attributes: {
        classCode: "SPLY",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.43"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.type === "supply";
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
    dataKey: "goal"
};

var intervention = {
    key: "code",
    attributes: {
        "code": leafLevel.deepInputProperty("code"),
        "displayName": "Intervention"
    },
    content: [{
        key: "originalText",
        text: leafLevel.deepInputProperty("name")
    }],
    dataKey: "intervention"
};

exports.planOfCareActivityInstructions = {
    key: "instructions",
    attributes: {
        classCode: "ACT",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.20"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive, {
            key: "priorityCode",
            attributes: {
                "code": leafLevel.deepInputProperty("code"),
                "displayName": "Severity Code"
            },
            content: [{
                key: "originalText",
                text: leafLevel.deepInputProperty("name")
            }],
            dataKey: "severity"
        },
        fieldLevel.effectiveTime,
        fieldLevel.author,
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: [{
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "GOL"
                },
                content: [fieldLevel.effectiveTime, goal, {
                    key: "act",
                    attributes: {
                        classCode: "ACT",
                        moodCode: "INT"
                    },

                    content: [{
                        key: "entryRelationship",
                        attributes: {
                            typeCode: "REFR"
                        },
                        content: [intervention],
                        dataKey: "interventions"
                    }]
                }],
                dataKey: "goals"

            }],
            required: true
        }
    ],
    existsWhen: function (input) {
        return input.type === "instructions";
    }
};
