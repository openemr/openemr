"use strict";

var headerLevel = require('../headerLevel');
var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var required = contentModifier.required;
var dataKey = contentModifier.dataKey;
var key = contentModifier.key;

var careTeamProviderAct = {
    key: "act",
    attributes: {
        classCode: "PCPR",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.500.1", "2019-07-01"),
        fieldLevel.uniqueIdRoot,
        fieldLevel.templateCode("CareTeamAct"),
        {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        }, [fieldLevel.effectiveTimeIVL_TS, required], {
            key: "performer",
            attributes: {
                typeCode: "PRF"
            },
            content: [{
                key: "functionCode",
                attributes: leafLevel.code,
                dataKey: "function_code",
                existsWhen: condition.propertyNotEmpty('function_code'),
                content: [{
                    key: "originalText",
                    attributes: {
                        "xmlns": "urn:hl7-org:v3"
                    },
                    content: {
                        key: "reference",
                        attributes: {
                            "value": leafLevel.nextReference("teamMember")
                        }
                    }
                }]
            },
                fieldLevel.assignedEntity
            ]
        }
    ]
};

exports.careTeamOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "CLUSTER",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.500", "2019-07-01"),
        fieldLevel.uniqueIdRoot,
        {
            key: "code",
            attributes: {
                code: "86744-0",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Care Team Information"
            }
        },
        {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required],
        fieldLevel.author,
        [{
            key: "component",
            content: careTeamProviderAct
        },
            dataKey("providers.provider")
        ],
    ],
    dataKey: "care_team"
};
