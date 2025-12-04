"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var policyActivity = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.61", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.61"),
        {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("identifier"),
                extension: leafLevel.inputProperty("extension")
            },
            dataKey: 'policy.identifiers',
            existsWhen: condition.keyExists('identifier'),
            required: true
        },
        {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "policy.code"
        },
        // Add text element for policy description
        {
            key: "text",
            text: leafLevel.input,
            dataKey: "policy.description",
            existsWhen: condition.keyExists('policy.description')
        },
        fieldLevel.statusCodeCompleted,
        // Add effectiveTime for the policy period
        {
            key: "effectiveTime",
            content: [
                {
                    key: "low",
                    attributes: {
                        value: leafLevel.inputProperty("low")
                    }
                },
                {
                    key: "high",
                    attributes: {
                        value: leafLevel.inputProperty("high")
                    }
                }
            ],
            dataKey: "policy.effectiveTime",
            existsWhen: condition.keyExists('policy.effectiveTime')
        },
        {
            key: "performer",
            attributes: {
                typeCode: "PRF"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.87"),
                fieldLevel.assignedEntity
            ],
            dataKey: "policy.insurance.performer"
        },
        {
            key: "performer",
            attributes: {
                typeCode: "PRF"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.88"),
                [fieldLevel.effectiveTime, key("time")],
                fieldLevel.assignedEntity
            ],
            dataKey: "guarantor"
        },
        {
            key: "participant",
            attributes: {
                typeCode: "COV"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.89"),
                [fieldLevel.effectiveTime, key("time")],
                {
                    key: "participantRole",
                    attributes: {
                        classCode: "PAT"
                    },
                    content: [
                        fieldLevel.id,
                        {
                            key: "code",
                            attributes: leafLevel.code,
                            dataKey: "code"
                        },
                        fieldLevel.usRealmAddress,
                        fieldLevel.telecom,
                        {
                            key: "playingEntity",
                            content: [
                                fieldLevel.usRealmName,
                                {
                                    key: "sdtc:birthTime",
                                    attributes: {
                                        value: leafLevel.inputProperty("birthTime")
                                    },
                                }
                            ]
                        }
                    ]
                }
            ],
            dataKey: "participant",
            dataTransform: function (input) {
                if (input.performer) {
                    input.identifiers = input.performer.identifiers;
                    input.address = input.performer.address;
                    input.phone = input.performer.phone;
                }
                return input;
            }
        },
        {
            key: "participant",
            attributes: {
                typeCode: "HLD"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.90"),
                {
                    key: "participantRole",
                    content: [
                        fieldLevel.id,
                        fieldLevel.usRealmAddress,
                        // Add playingEntity for policy holder details
                        {
                            key: "playingEntity",
                            content: [
                                fieldLevel.usRealmName
                            ],
                            dataKey: "name",
                            existsWhen: condition.keyExists('name')
                        }
                    ],
                    dataKey: "performer"
                }
            ],
            dataKey: "policy_holder"
        },
        // Authorization/Plan entryRelationship with proper code
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: {
                key: "act",
                attributes: {
                    classCode: "ACT",
                    moodCode: "DEF"
                },
                content: [
                    fieldLevel.id,
                    {
                        key: "code",
                        attributes: {
                            code: leafLevel.inputProperty("authorization_code"),
                            displayName: "Health Insurance Plan Policy",
                            codeSystem: "2.16.840.1.113883.3.221.5",
                            codeSystemName: "Source of Payment Typology"
                        },
                        // Fallback to nullFlavor if no code provided (but preferably always provide code)
                        existsWhen: condition.keyExists('authorization_code')
                    },
                    {
                        key: "text",
                        text: leafLevel.input,
                        dataKey: "plan_name"
                    }
                ]
            },
            dataKey: "authorization"
        }
    ]
};

exports.coverageActivity = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.60", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.60"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("CoverageActivity"),
        fieldLevel.statusCodeCompleted, {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: [
                [policyActivity, required]
            ],
            required: true
        }
    ]
};
