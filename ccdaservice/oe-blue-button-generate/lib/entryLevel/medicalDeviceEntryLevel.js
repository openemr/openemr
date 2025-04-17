"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.medicalDeviceActivityProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.14", "2014-06-09"),
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
                        "value": leafLevel.nextReference("device")
                    }
                }]
            }]
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            dataKey: 'device'
        },
        fieldLevel.effectiveTime,
        fieldLevel.author,
        /*{
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        },*/ {
            key: "participant",
            attributes: {
                "typeCode": "DEV"
            },
            content: [{
                key: "participantRole",
                attributes: {
                    "classCode": "MANU"
                },
                content: [
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.37"),
                    fieldLevel.id, {
                        key: "playingDevice",
                        content: [{
                            key: "code",
                            attributes: leafLevel.code,
                            /*content: [{
                                key: "originalText",
                                content: [{
                                    key: "reference",
                                    attributes: {
                                        "value": leafLevel.sameReference("name")
                                    }
                                }]
                            }],*/
                        }]
                    }, {
                        key: "scopingEntity",
                        content: [{
                            key: "id",
                            attributes: {
                                "root": "2.16.840.1.113883.3.3719"
                            }
                        }]
                    }
                ]
            }],
            dataKey: 'device'
        }],
    existsWhen: condition.propertyEquals("device_type", "UDI")
};
