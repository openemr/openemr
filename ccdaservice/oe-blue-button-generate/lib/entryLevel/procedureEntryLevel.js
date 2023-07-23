"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.procedureActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "INT" // not constant in the specification
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.12"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        },
        [fieldLevel.performer, dataKey("performer")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "act"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "entryRelationship:encounter",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};

exports.procedureActivityProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.14"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        }, {
            key: "specimen",
            attributes: {
                typeCode: "SPC"
            },
            content: {
                key: "specimenRole",
                attributes: {
                    classCode: "SPEC"
                },
                content: [
                    fieldLevel.id, {
                        key: "specimenPlayingEntity",
                        content: {
                            key: "code",
                            attributes: leafLevel.code,
                            dataKey: "code"
                        },
                        existsWhen: condition.keyExists("code")
                    }
                ],
                required: true
            },
            dataKey: "specimen"
        },
        [fieldLevel.performer, dataKey("performer")]
        ,fieldLevel.author
        , {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "procedure"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "methodCode",
        "participant:productInstance",
        "entryRelationship:encounter",
        "entryRelationship:instructions",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};

exports.procedureActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN" // not constant in the specification
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.13"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "value",
            attributes: {
                "xsi:type": "CD"
            }
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        },
        [fieldLevel.performer, dataKey("performers")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "observation"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "entryRelationship:encounter",
        "entryRelationship:instructions",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};
