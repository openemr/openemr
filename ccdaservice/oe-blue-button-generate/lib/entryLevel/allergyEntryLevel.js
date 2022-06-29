"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require('../condition');
var contentModifier = require("../contentModifier");

var sel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var allergyStatusObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.28"),
        fieldLevel.templateCode("AllergyStatusObservation"),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: [
                leafLevel.typeCE,
                leafLevel.code
            ],
            existsWhen: condition.codeOrDisplayname,
            required: true
        }
    ],
    dataKey: "status"
};

var allergyIntoleranceObservationNKA = exports.allergyIntoleranceObservationNKA = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN",
        "negationInd": "true"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.7", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.7"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("AllergyObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                {
                    "code": "419199007",
                    "codeSystem": "2.16.840.1.113883.6.96",
                    "codeSystemName": "SNOMED-CT",
                    "displayName": "Allergy to substance (disorder)",
                }],
            content: {
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("reaction")
                    }
                }
            },
            required: true
        }, {
            key: "participant",
            attributes: {
                "typeCode": "CSM"
            },
            content: [{
                key: "participantRole",
                attributes: {
                    "classCode": "MANU"
                },
                content: [{
                    key: "playingEntity",
                    attributes: {
                        classCode: "MMAT"
                    },
                    content: [{
                        key: "code",
                        attributes: {
                            nullFlavor: "NA"
                        }
                    }]
                }],
                required: true
            }]
        }]
}

var allergyProblemActNKA = exports.allergyProblemActNKA = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.30", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.30"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("AllergyConcernAct"),
        fieldLevel.statusCodeActive, [fieldLevel.effectiveTime, required], {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [allergyIntoleranceObservationNKA, required],
            existsWhen: condition.keyExists('no_know_allergies'),
            required: true
        }
    ],
    existsWhen: condition.keyExists("no_know_allergies"),
};

var allergyIntoleranceObservation = exports.allergyIntoleranceObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN",
        "negationInd": leafLevel.boolInputProperty("negation_indicator")
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.7", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.7"),
        fieldLevel.id,
        fieldLevel.templateCode("AllergyObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            content: {
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("reaction")
                    }
                }
            },
            dataKey: 'intolerance',
            existsWhen: condition.codeOrDisplayname,
            required: true
        },
        fieldLevel.author,
        {
            key: "participant",
            attributes: {
                "typeCode": "CSM"
            },
            content: [{
                key: "participantRole",
                attributes: {
                    "classCode": "MANU"
                },
                content: [{
                    key: "playingEntity",
                    attributes: {
                        classCode: "MMAT"
                    },
                    content: [{
                        key: "code",
                        attributes: leafLevel.code,
                        content: [{
                            key: "originalText",
                            content: [{
                                key: "reference",
                                attributes: {
                                    "value": leafLevel.sameReference("reaction")
                                }
                            }]
                        }, {
                            key: "translation",
                            attributes: leafLevel.code,
                            dataKey: "translations"
                        }],
                        require: true
                    }]
                }],
                required: true
            }],
            dataKey: 'allergen'
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [allergyStatusObservation, required]
            ],
            existsWhen: condition.keyExists("status")
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "MFST",
                "inversionInd": "true"
            },
            content: [
                [sel.reactionObservation, required]
            ],
            dataKey: 'reactions',
            existsWhen: condition.keyExists('reaction')
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [sel.severityObservation, required]
            ],
            existsWhen: condition.keyExists('severity')
        }
    ],
    dataKey: "observation",
    warning: [
        "negationInd attribute is not specified in specification"
    ]
};

var allergyProblemAct = exports.allergyProblemAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.30", "2015-08-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.30"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("AllergyProblemAct"),
        fieldLevel.statusCodeActive, [fieldLevel.effectiveTime, required],
        fieldLevel.author, {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [allergyIntoleranceObservation, required],
            existsWhen: condition.keyExists('observation'),
            required: true,
            warning: "inversionInd is not in spec"
        }
    ],
    existsWhen: condition.keyDoesntExist("no_know_allergies"),
    warning: "statusCode is not constant in spec"
};
