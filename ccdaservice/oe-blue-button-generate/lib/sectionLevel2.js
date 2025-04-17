"use strict";

const bbu = require("../../oe-blue-button-util");

const fieldLevel = require("./fieldLevel");
const entryLevel = require("./entryLevel");
const leafLevel = require('./leafLevel');
const sharedEntryLevel = require("./entryLevel/sharedEntryLevel");
const contentModifier = require("./contentModifier");

const required = contentModifier.required;
const bbud = bbu.datetime;
const bbuo = bbu.object;

const nda = "No Data Available";

const condition = require('./condition');
const dataKey = contentModifier.dataKey;

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
            attributes: condition.isNullFlavorSection('allergies'),
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.6.1", "2015-08-01"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6.1"),
                fieldLevel.templateCode("AllergiesSection"),
                fieldLevel.templateTitle("AllergiesSection"), {
                    key: "text",
                    text: "No known Allergies and Intolerances",
                    existsWhen: condition.propertyValueNotEmpty('allergies.0.no_know_allergies')
                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        entryLevel.allergyProblemAct,
                        entryLevel.allergyProblemActNKA
                    ],
                    dataKey: "allergies"
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
            attributes: condition.isNullFlavorSection('medications'),
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
                    dataKey: "medications"
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
            attributes: condition.isNullFlavorSection('problems'),
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
                    dataKey: "problems"
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
            attributes: condition.isNullFlavorSection('procedures'),
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
            attributes: condition.isNullFlavorSection('results'),
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
                    dataKey: "results"
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
            attributes: condition.isNullFlavorSection('encounters'),
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
            attributes: condition.isNullFlavorSection('immunizations'),
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
            attributes: condition.isNullFlavorSection('payers'),
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
            attributes: condition.isNullFlavorSection('description'),
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.8"),
                fieldLevel.templateCode("AssessmentSection"),
                fieldLevel.templateTitle("AssessmentSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("description")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "description"
                },
                fieldLevel.author
            ],
            dataKey: "clinicalNoteAssessments"
        }
        ]
    }
};

exports.planOfCareSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            attributes: condition.isNullFlavorSection('plan_of_care'),
            content: [
                // @see http://www.hl7.org/ccdasearch/templates/2.16.840.1.113883.10.20.22.2.10.html
                // They keep renaming this section, but ccda calls this Plan of Treatment Section (V2)
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.10", "2014-06-09"),
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
            attributes: condition.isNullFlavorSection('goals'),
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.60"),
                fieldLevel.templateCode("GoalSection"),
                fieldLevel.templateTitle("GoalSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("goals")
                },
                htmlHeader,
                /*fieldLevel.author,
                dataKey("goals"),*/
                {
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
            attributes: condition.isNullFlavorSection('social_history'),
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
                    existsWhen: condition.propertyNotEmpty("value"),
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
            attributes: condition.isNullFlavorSection('vitals'),
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

exports.careTeamSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            attributes: condition.isNullFlavorSection('care_team'),
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.500", "2019-07-01"),
                fieldLevel.templateCode("CareTeamSection"),
                fieldLevel.templateTitle("CareTeamSection"), {
                    key: "text",
                    text: "A Care Team is not assigned.",
                    existsWhen: condition.keyDoesntExist("care_team")
                },
                htmlHeader, {
                    key: "entry",
                    content: entryLevel.careTeamOrganizer
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
            attributes: condition.isNullFlavorSection('medical_devices'),
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
            attributes: condition.isNullFlavorSection('functional_status'),
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
                    dataKey: "functional_status"
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
            attributes: condition.isNullFlavorSection('mental_status'),
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.56", "2015-08-01"),
                fieldLevel.templateCode("MentalStatusSection"),
                fieldLevel.templateTitle("MentalStatusSection"), {
                    key: "text",
                    text: "Mental Status Not Available",
                    existsWhen: condition.keyDoesntExist("mental_status")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "mental_status.note"
                },
                htmlHeader, {
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
            attributes: condition.isNullFlavorSection('reason'),
            content: [
                fieldLevel.templateIdExt("1.3.6.1.4.1.19376.1.5.3.1.3.1", "2014-06-09"),
                fieldLevel.templateId("1.3.6.1.4.1.19376.1.5.3.1.3.1"),
                fieldLevel.templateCode("ReasonForReferralSection"),
                fieldLevel.templateTitle("ReasonForReferralSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("reason")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "reason"
                },
                fieldLevel.author
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
            attributes: condition.isNullFlavorSection('author'),
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.58", "2015-08-01"),
                fieldLevel.templateCode("HealthConcernSection"),
                fieldLevel.templateTitle("HealthConcernSection"), {
                    key: "text",
                    text: "Health Concerns Not Available",
                    existsWhen: condition.keyDoesntExist("text")
                }, {
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "text"
                },
                fieldLevel.author,
                {
                    key: "entry",
                    content: [
                        entryLevel.healthConcernObservation
                    ],
                    existsWhen: condition.keyExists("text")
                }, {
                    key: "entry",
                    content: [
                        [entryLevel.healthConcernActivityAct]
                    ],
                    existsWhen: condition.keyExists("text")
                }
            ],
            dataKey: "health_concerns"
        }]
    }
};

exports.historyNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("history_physical"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "History and Physical Note"
                    }
                }, {
                    key: "title",
                    text: "History and Physical Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("history_physical"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'history_physical'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "history_physical"
                }
            ]
        }]
    }
};

exports.progressNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("progress_note"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Progress Note"
                    }
                }, {
                    key: "title",
                    text: "Progress Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("progress_note"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'progress_note'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "progress_note"
                }
            ]
        }]
    }
};

exports.consultationNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("consultation_note"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Consultation Note"
                    }
                }, {
                    key: "title",
                    text: "Consultation Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("consultation_note"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'consultation_note'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "consultation_note"
                }
            ]
        }]
    }
};

exports.generalNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("general_note"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "General Note"
                    }
                }, {
                    key: "title",
                    text: "General Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("general_note"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'general_note'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "general_note"
                }
            ]
        }]
    }
};

exports.nurseNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("nurse_note"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Nurse Notes"
                    }
                }, {
                    key: "title",
                    text: "Nurse Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("nurse_note"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'nurse_note'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "nurse_note"
                }
            ]
        }]
    }
};

exports.procedureNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("procedure_note"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Procedure Note"
                    }
                }, {
                    key: "title",
                    text: "Procedure Notes"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("procedure_note"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'procedure_note'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "procedure_note"
                }
            ]
        }]
    }
};

exports.laboratoryReportNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("laboratory_report_narrative"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Laboratory Report Narrative"
                    }
                }, {
                    key: "title",
                    text: "Laboratory Report Narrative"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("laboratory_report_narrative"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'laboratory_report_narrative'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "laboratory_report_narrative"
                }
            ]
        }]
    }
};

exports.imagingNarrativeNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("imaging_narrative"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Imaging Narrative"
                    }
                }, {
                    key: "title",
                    text: "Imaging Narrative"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("imaging_narrative"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'imaging_narrative'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "imaging_narrative"
                }
            ]
        }]
    }
};

exports.dischargeSummaryNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("discharge_summary"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Discharge Summary"
                    }
                }, {
                    key: "title",
                    text: "Discharge Summary"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("discharge_summary"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'discharge_summary'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "discharge_summary"
                }
            ]
        }]
    }
};

exports.pathologyReportNoteSection = function (htmlHeader, noteData) {
    return {
        key: "component",
        existsWhen: condition.keyExists("pathology_report_narrative"),
        content: [{
            key: "section",
            content: [
                fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.2.65", "2016-11-01"),
                {
                    key: "id",
                    attributes: {
                        root: "16C8G888-10D9-23E6-H141-0080055B0002"
                    }
                }, {
                    key: "code",
                    attributes: {
                        codeSystem: "2.16.840.1.113883.6.1",
                        codeSystemName: "LOINC",
                        code: "34117-2",
                        displayName: "Pathology Report Note"
                    }
                }, {
                    key: "title",
                    text: "Pathology Report Note"
                }, {
                    key: "text",
                    existsWhen: condition.keyExists("pathology_report_narrative"),
                    content: [{
                        key: "table",
                        attributes: {
                            width: "100%",
                            border: "1"
                        },
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ["Summary", "Author", "Date"];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    attributes: {
                                        ID: leafLevel.nextTableReference("note")
                                    },
                                    text: leafLevel.deepInputProperty("note", nda),
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputProperty("author.author_full_name", nda)
                                }, {
                                    key: "td",
                                    text: leafLevel.deepInputDate("date_time.point", nda),
                                }
                                ]
                            }],
                            dataKey: 'pathology_report_narrative'
                        }]
                    }]
                }, {
                    key: "entry",
                    content: sharedEntryLevel.notesAct,
                    dataKey: "pathology_report_narrative"
                }
            ]
        }]
    }
};
