"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
    ],
    existsWhen: function (input) {
        return input.type === "act";
    }
};

exports.planOfCareActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.44"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
    ],
    existsWhen: function (input) {
        return input.type === "encounter";
    }
};

exports.planOfCareActivitySubstanceAdministration = {
    key: "substanceAdministration",
    attributes: {
        classCode: "SBADM",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.42"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew, {
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
        fieldLevel.effectiveTime, {
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
