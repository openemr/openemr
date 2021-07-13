"use strict";

var bbu = require("../../oe-blue-button-util");

var fieldLevel = require("./fieldLevel");
var entryLevel = require("./entryLevel");
var leafLevel = require('./leafLevel');
var contentModifier = require("./contentModifier");

var required = contentModifier.required;
var bbud = bbu.datetime;
var bbuo = bbu.object;

var nda = "No Data Available";

var condition = require('./condition');

var getText = function (topArrayKey, headers, values) {
    var result = {
        key: "text",
        existsWhen: condition.keyExists(topArrayKey),

        content: [{
            key: "table",
            attributes: {
                border: "1",
                width: "100%"
            },
            content: [{
                key: "thead",
                content: [{
                    key: "tr",
                    content: []
                }]
            }, {
                key: "tbody",
                content: [{
                    key: "tr",
                    content: [],
                    dataKey: topArrayKey
                }]
            }]
        }]
    };
    var headerTarget = result.content[0].content[0].content[0].content;
    headers.forEach(function (header) {
        var element = {
            key: "th",
            text: header
        };
        headerTarget.push(element);
    });
    var valueTarget = result.content[0].content[1].content[0].content;
    values.forEach(function (value) {
        var data;
        if (typeof value !== 'function') {
            data = leafLevel.deepInputProperty(value, "");
        } else {
            data = value;
        }

        var element = {
            key: "td",
            text: data
        };
        valueTarget.push(element);
    });
    return result;
};

exports.allergiesSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.6.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6.1"),
                fieldLevel.templateCode("AllergiesSection"),
                fieldLevel.templateTitle("AllergiesSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("allergies")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.allergyProblemAct, required]
                    ],
                    dataKey: "allergies",
                    required: true
                }
            ]
        }]
    };
};

exports.medicationsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.1.1", "2014-06-09"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1.1"),
                fieldLevel.templateCode("MedicationsSection"),
                fieldLevel.templateTitle("MedicationsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("medications")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.medicationActivity, required]
                    ],
                    dataKey: "medications",
                    required: true
                }
            ]
        }]
    };
};

exports.problemsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.5.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5.1"),
                fieldLevel.templateCode("ProblemSection"),
                fieldLevel.templateTitle("ProblemSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("problems")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.problemConcernAct, required]
                    ],
                    dataKey: "problems",
                    required: true
                }, {
                    key: "entry",
                    existsWhen: condition.keyExists("problems_comment"),
                    content: {
                        key: "act",
                        attributes: {
                            classCode: "ACT",
                            moodCode: "EVN"
                        },
                        content: [
                            fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.64"),
                            fieldLevel.templateCode("CommentActivity"), {
                                key: "text",
                                text: leafLevel.deepInputProperty("problems_comment")
                            },
                        ]

                    },
                    dataKey: "demographics.meta"
                }
            ]
        }]
    };
};

exports.proceduresSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7.1"),
                fieldLevel.templateCode("ProceduresSection"),
                fieldLevel.templateTitle("ProceduresSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("procedures")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": function (input) {
                            return input.procedure_type === "procedure" ? "DRIV" : null;
                        }
                    },
                    content: [
                        entryLevel.procedureActivityAct,
                        entryLevel.procedureActivityProcedure,
                        entryLevel.procedureActivityObservation
                    ],
                    dataKey: "procedures"
                }
            ]
        }],
        notImplemented: [
            "entry required"
        ]
    };
};

exports.resultsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.3.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3.1"),
                fieldLevel.templateCode("ResultsSection"),
                fieldLevel.templateTitle("ResultsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("results")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        [entryLevel.resultOrganizer, required]
                    ],
                    dataKey: "results",
                    required: true
                }
            ]
        }]
    };
};

exports.encountersSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.22.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22.1"),
                fieldLevel.templateCode("EncountersSection"),
                fieldLevel.templateTitle("EncountersSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("encounters")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.encounterActivities, required]
                    ],
                    dataKey: "encounters"
                }
            ]
        }]
    };
};

exports.immunizationsSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2.1"),
                fieldLevel.templateCode("ImmunizationsSection"),
                fieldLevel.templateTitle("ImmunizationsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("immunizations")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.immunizationActivity, required]
                    ],
                    dataKey: "immunizations"
                }
            ]
        }]
    };
};

exports.payersSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.18"),
                fieldLevel.templateCode("PayersSection"),
                fieldLevel.templateTitle("PayersSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("payers")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        [entryLevel.coverageActivity, required]
                    ],
                    dataKey: "payers"
                }
            ]
        }]
    };
};

exports.assessmentSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.8"),
                fieldLevel.templateCode("AssessmentSection"),
                fieldLevel.templateTitle("AssessmentSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("assessments")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "description"
                }
            ],
            dataKey: "assessments"
        }]
    }
};

exports.planOfCareSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.10"),
                fieldLevel.templateCode("PlanOfCareSection"),
                fieldLevel.templateTitle("PlanOfCareSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("plan_of_care")
                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": function (input) {
                            return input.type === "observation" ? "DRIV" : null;
                        }
                    },
                    content: [
                        entryLevel.planOfCareActivityAct,
                        entryLevel.planOfCareActivityObservation,
                        entryLevel.planOfCareActivityProcedure,
                        entryLevel.planOfCareActivityEncounter,
                        entryLevel.planOfCareActivitySubstanceAdministration,
                        entryLevel.planOfCareActivitySupply,
                        entryLevel.planOfCareActivityInstructions
                    ],
                    dataKey: "plan_of_care"
                }
            ]
        }]
    };
};

exports.goalSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.60"),
                fieldLevel.templateCode("GoalSection"),
                fieldLevel.templateTitle("GoalSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("goals")
                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": function (input) {
                            return input.type === "observation" ? "DRIV" : null;
                        }
                    },
                    content: [
                        entryLevel.goalActivityObservation
                    ],
                    dataKey: "goals"
                }
            ]
        }]
    };
};

exports.socialHistorySection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.17", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.17"),
                fieldLevel.templateCode("SocialHistorySection"),
                fieldLevel.templateTitle("SocialHistorySection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("social_history")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [entryLevel.smokingStatusObservation],
                    dataKey: "social_history"
                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [entryLevel.genderStatusObservation],
                    dataKey: "social_history"
                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [entryLevel.socialHistoryObservation],
                    existsWhen: function (input) {
                        return (!input.value) || input.value.indexOf("smoke") < 0;
                    },
                    dataKey: "social_history"
                }
            ]
        }],
        notImplemented: [
            "pregnancyObservation",
            "tobaccoUse"
        ]
    };
};

exports.vitalSignsSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.4.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4.1"),
                fieldLevel.templateCode("VitalSignsSection"),
                fieldLevel.templateTitle("VitalSignsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("vitals")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.vitalSignsOrganizer, required]
                    ],
                    dataKey: "vitals"
                }
            ]
        }]
    };
};

exports.medicalEquipmentSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.23", "2014-06-09"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.23"),
                fieldLevel.templateCode("MedicalEquipmentSection"),
                fieldLevel.templateTitle("MedicalEquipmentSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("medical_devices")

                },
                htmlHeader, {
                    key: "entry",
                    content: [
                        entryLevel.medicalDeviceActivityProcedure,
                    ],
                    dataKey: "medical_devices"
                }
            ]
        }],
        notImplemented: [
            "entry required"
        ]
    };
};

exports.functionalStatusSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.14", "2014-06-09"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.14"),
                fieldLevel.templateCode("FunctionalStatusSection"),
                fieldLevel.templateTitle("FunctionalStatusSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("functional_status")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        entryLevel.functionalStatusOrganizer
                    ],
                    dataKey: "functional_status",
                    required: true
                }
            ]
        }]
    };
};

exports.mentalStatusSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.56", "2015-08-01"),
                fieldLevel.templateCode("MentalStatusSection"),
                fieldLevel.templateTitle("MentalStatusSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("mental_status")
                }, {// mental status does not use a header table.
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "mental_status.note"
                }, {
                    key: "entry",
                    content: [
                        entryLevel.mentalStatusObservation
                    ],
                    dataKey: "mental_status"
                }
            ]
        }]
    };
};

exports.reasonForReferralSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("1.3.6.1.4.1.19376.1.5.3.1.3.1", "2014-06-09"),
                fieldLevel.templateId("1.3.6.1.4.1.19376.1.5.3.1.3.1"),
                fieldLevel.templateCode("ReasonForReferralSection"),
                fieldLevel.templateTitle("ReasonForReferralSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("referral_reason")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "reason"
                }
            ],
            dataKey: "referral_reason"
        }]
    }
};

exports.healthConcernSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.58", "2015-08-01"),
                fieldLevel.templateCode("HealthConcernSection"),
                fieldLevel.templateTitle("HealthConcernSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("health_concerns")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "health_concerns.text"
                }, {
                    key: "entry",
                    content: [
                        entryLevel.healthConcernObservation
                    ],
                    dataKey: "health_concerns"
                }, {
                    key: "entry",
                    content: [
                        [entryLevel.healthConcernActivityAct]
                    ],
                    dataKey: "health_concerns"
                }
            ]
        }]
    }
};
