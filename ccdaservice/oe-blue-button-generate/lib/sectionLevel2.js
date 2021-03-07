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

var alllergiesTextHeaders = ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
var allergiesTextRow = [
    leafLevel.deepInputProperty("observation.allergen.name", ""),
    leafLevel.deepInputProperty("observation.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.reaction.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.status.name", "")
];

exports.allergiesSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6"),
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

var medicationsTextHeaders = ["Medication Class", "# fills", "Last fill date"];
var medicationsTextRow = [ // Name, did not find class in the medication blue-button-data
    function (input) {
        var value = bbuo.deepValue(input, 'product.product.name');
        if (!bbuo.exists(value)) {
            value = bbuo.deepValue(input, 'product.unencoded_name');
        }
        if (!bbuo.exists(value)) {
            return "";
        } else {
            return value;
        }
    },
    leafLevel.deepInputProperty("supply.repeatNumber", ""),
    leafLevel.deepInputDate("supply.date_time.point", "")
];

exports.medicationsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1"),
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
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5"),
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
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3"),
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
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22"),
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

                }, {
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

exports.socialHistorySection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.17"),
                fieldLevel.templateCode("SocialHistorySection"),
                fieldLevel.templateTitle("SocialHistorySection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("social_history")

                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        entryLevel.smokingStatusObservation,
                        entryLevel.socialHistoryObservation
                    ],
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
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4.1"),
                fieldLevel.templateCode("VitalSignsSection"),
                fieldLevel.templateTitle("VitalSignsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("vitals")

                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
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
