"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

exports.socialHistoryObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                text: leafLevel.inputProperty("unencoded_name"),
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("social")
                    }
                }
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "code",
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("value")
        }
    ],
    existsWhen: function (input) {
        return (!input.value) || input.value.indexOf("smoke") > -1;
    }
};

exports.smokingStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.78"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("SmokingStatusObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [{
                "xsi:type": "CD"
            },
                leafLevel.codeFromName("2.16.840.1.113883.11.20.9.38")
            ],
            required: true,
            dataKey: "value"
        }, //fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.value && input.value.indexOf("smoke") > -1;
    }
};

exports.genderStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.200", "2016-06-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.200"),
        fieldLevel.templateCode("GenderStatusObservation"),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: [{
                "xsi:type": "CD"
            },
                leafLevel.codeFromName("2.16.840.1.113883.5.1")
            ],
            required: true,
            dataKey: "gender"
        }, //[fieldLevel.author, contentModifier.dataKey("gender_author")]
    ],
    existsWhen: function (input) {
        return input && input.gender;
    }
};

exports.tribalAffiliationObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.506", "2023-05-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.506"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "95370-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Tribal affiliation",
            }
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, contentModifier.dataKey("effective_date")],
        {
            key: "value",
            attributes: [{
                "xsi:type": "CD",
            }, {
                code: leafLevel.inputProperty("tribal_code"),
                codeSystem: "2.16.840.1.113883.5.140",
                codeSystemName: "Tribal TribalEntityUS",
                displayName: leafLevel.inputProperty("tribal_title")
            }],
            dataKey: "tribal_affiliation"
        },
        //fieldLevel.author
    ],
    existsWhen: function (input) {
        return input && input.tribal_affiliation.tribal_code;
    }
};

exports.pregnancyStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.15.3.8", "2023-05-01"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.15.3.8"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "ASSERTION",
                codeSystem: "2.16.840.1.113883.5.4",
                //codeSystemName: "LOINC",
                //displayName: "",
            }
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, contentModifier.dataKey("effective_date")],
        {
            key: "value",
            attributes: [{
                "xsi:type": "CD",
            }, {
                code: leafLevel.inputProperty("pregnancy_code"),
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED-CT",
                displayName: leafLevel.inputProperty("pregnancy_title")
            }],
            dataKey: "pregnancy_status"
        },
        //fieldLevel.author
    ],
    existsWhen: function (input) {
        return input && input.pregnancy_status.pregnancy_code;
    }
};

exports.hungerVitalSignsObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.38", "2015-08-01"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.38", "2022-06-01"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "160476009",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED CT",
                displayName: "Social / personal history observable"
            },
            content: [{
                key: "translation",
                attributes: {
                    code: "8689-2",
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    displayName: "History of Social function"
                }
            }]
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, contentModifier.dataKey("effective_date")],
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "SPRT"
            },
            content: [{
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.69", "2022-06-01"),
                    fieldLevel.uniqueId,
                    fieldLevel.id,
                    {
                        key: "code",
                        attributes: {
                            code: "88121-9",
                            displayName: "Hunger Vital Signs",
                            codeSystem: "2.16.840.1.113883.6.1",
                            codeSystemName: "LOINC"
                        }
                    },
                    {
                        key: "derivationExpr",
                        text: "Sum of hunger screening responses"
                    },
                    fieldLevel.statusCodeCompleted,
                    {
                        key: "effectiveTime",
                        attributes: {
                            value: leafLevel.inputProperty("assessment_date")
                        }
                    },
                    {
                        key: "value",
                        attributes: {
                            "xsi:type": "INT",
                            value: leafLevel.inputProperty("score")
                        }
                    },
                    // Question 1
                    {
                        key: "entryRelationship",
                        attributes: {
                            typeCode: "COMP"
                        },
                        content: [{
                            key: "observation",
                            attributes: {
                                classCode: "OBS",
                                moodCode: "EVN"
                            },
                            content: [
                                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.86"),
                                fieldLevel.uniqueId,
                                fieldLevel.id,
                                {
                                    key: "code",
                                    attributes: {
                                        code: leafLevel.inputProperty("code"),
                                        displayName: leafLevel.inputProperty("display"),
                                        codeSystem: leafLevel.inputProperty("code_system"),
                                        codeSystemName: "LOINC"
                                    }
                                },
                                fieldLevel.statusCodeCompleted,
                                {
                                    key: "effectiveTime",
                                    attributes: {
                                        value: leafLevel.inputProperty("effective_date")
                                    }
                                },
                                {
                                    key: "value",
                                    attributes: {
                                        "xsi:type": "CD",
                                        code: leafLevel.inputProperty("answer_code"),
                                        displayName: leafLevel.inputProperty("answer_display"),
                                        codeSystem: "2.16.840.1.113883.6.1"
                                    }
                                }
                            ]
                        }],
                        dataKey: "question1",
                        existsWhen: function (input) {
                            return input && input.answer_code;
                        }
                    },
                    // Question 2
                    {
                        key: "entryRelationship",
                        attributes: {
                            typeCode: "COMP"
                        },
                        content: [{
                            key: "observation",
                            attributes: {
                                classCode: "OBS",
                                moodCode: "EVN"
                            },
                            content: [
                                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.86"),
                                fieldLevel.uniqueId,
                                fieldLevel.id,
                                {
                                    key: "code",
                                    attributes: {
                                        code: leafLevel.inputProperty("code"),
                                        displayName: leafLevel.inputProperty("display"),
                                        codeSystem: leafLevel.inputProperty("code_system"),
                                        codeSystemName: "LOINC"
                                    }
                                },
                                fieldLevel.statusCodeCompleted,
                                {
                                    key: "effectiveTime",
                                    attributes: {
                                        value: leafLevel.inputProperty("effective_date")
                                    }
                                },
                                {
                                    key: "value",
                                    attributes: {
                                        "xsi:type": "CD",
                                        code: leafLevel.inputProperty("answer_code"),
                                        displayName: leafLevel.inputProperty("answer_display"),
                                        codeSystem: "2.16.840.1.113883.6.1"
                                    }
                                }
                            ]
                        }],
                        dataKey: "question2",
                        existsWhen: function (input) {
                            return input && input.answer_code;
                        }
                    }
                ]
            }],
            dataKey: "hunger_vital_signs"
        }
    ],
    existsWhen: function (input) {
        return input && input.hunger_vital_signs &&
            (input.hunger_vital_signs.question1 || input.hunger_vital_signs.question2);
    }
};

// Add disability assessment observation template
exports.disabilityAssessmentObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.uniqueId,
        {
            key: "code",
            attributes: {
                code: "89571-4",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Overall disability status CUBS"
            }
        },
        fieldLevel.statusCodeCompleted,
        [fieldLevel.effectiveTime, contentModifier.dataKey("effective_date")],
        {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: leafLevel.inputProperty("disability_status.answer_code"),
                displayName: leafLevel.inputProperty("disability_status.answer_display"),
                codeSystem: "2.16.840.1.113883.6.1"
            },
            dataKey: "disability_status",
            existsWhen: function (input) {
                return input && input.answer_code;
            }
        },
        // Add disability questions as component observations
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: [{
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.86"),
                    fieldLevel.uniqueId,
                    {
                        key: "code",
                        attributes: {
                            code: leafLevel.inputProperty("code"),
                            displayName: leafLevel.inputProperty("display"),
                            codeSystem: leafLevel.inputProperty("code_system"),
                            codeSystemName: "LOINC"
                        }
                    },
                    fieldLevel.statusCodeCompleted,
                    {
                        key: "value",
                        attributes: {
                            "xsi:type": "CD",
                            code: leafLevel.inputProperty("answer_code"),
                            displayName: leafLevel.inputProperty("answer_display"),
                            codeSystem: "2.16.840.1.113883.6.1"
                        }
                    }
                ]
            }],
            dataKey: "disability_questions",
            multiple: true
        }
    ],
    existsWhen: function (input) {
        return input && (input.disability_status ||
            (input.disability_questions && input.disability_questions.length > 0));
    }
};

exports.occupationObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.503", "2023-05-01"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "11341-5",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "History of occupation"
            }
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "effectiveTime",
            content: [{
                key: "low",
                attributes: {
                    value: leafLevel.inputProperty("start_date")
                }
            }, {
                key: "high",
                attributes: {
                    value: leafLevel.inputProperty("end_date")
                },
                existsWhen: function (input) {
                    return input && input.end_date;
                }
            }]
        },
        {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: leafLevel.inputProperty("occupation_code"),
                displayName: leafLevel.inputProperty("occupation_title"),
                codeSystem: "2.16.840.1.114222.4.5.327",
                codeSystemName: "Occupational Data for Health (ODH)"
            }
        },
        // Industry observation (nested)
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [{
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.504", "2023-05-01"),
                    fieldLevel.uniqueId,
                    fieldLevel.id,
                    {
                        key: "code",
                        attributes: {
                            code: "86188-0",
                            codeSystem: "2.16.840.1.113883.6.1",
                            codeSystemName: "LOINC",
                            displayName: "History of occupation industry"
                        }
                    },
                    fieldLevel.statusCodeCompleted,
                    {
                        key: "effectiveTime",
                        content: [{
                            key: "low",
                            attributes: {
                                value: leafLevel.inputProperty("industry_start_date")
                            }
                        }, {
                            key: "high",
                            attributes: {
                                value: leafLevel.inputProperty("industry_end_date")
                            },
                            existsWhen: function (input) {
                                return input && input.industry_end_date;
                            }
                        }]
                    },
                    {
                        key: "value",
                        attributes: {
                            "xsi:type": "CD",
                            code: leafLevel.inputProperty("industry_code"),
                            displayName: leafLevel.inputProperty("industry_title"),
                            codeSystem: "2.16.840.1.114222.4.5.327",
                            codeSystemName: "Occupational Data for Health (ODH)"
                        }
                    }
                ],
                dataKey: "industry",
            }],
        }
    ],
    dataKey: "occupation",
};

exports.sexualOrientationObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.501", "2022-06-01"),
        fieldLevel.uniqueIdRoot,
        {
            key: "code",
            attributes: {
                code: "76690-7",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Sexual Orientation"
            }
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "effectiveTime",
            attributes: {
                nullFlavor: "NI"
            }
        },
        {
            key: "value",
            attributes: function (input) {
                const attrs = {
                    "xsi:type": "CD"
                };
                // If we have actual data, include it; otherwise use nullFlavor
                if (input && input.code) {
                    attrs.code = input.code;
                    attrs.displayName = input.display;
                    attrs.codeSystem = input.code_system || "2.16.840.1.113883.6.1";
                    attrs.codeSystemName = input.code_system_name || "LOINC";
                } else {
                    attrs.nullFlavor = "UNK";
                }
                return attrs;
            },
            dataKey: "sexual_orientation"
        }
    ],
    existsWhen: function (input) {
        // Always include this observation if the social_history section exists
        return true;
    }
};

exports.genderIdentityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.34.3.45", "2022-06-01"),
        fieldLevel.uniqueIdRoot,
        {
            key: "code",
            attributes: {
                code: "76691-5",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Gender Identity"
            }
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "effectiveTime",
            attributes: {
                nullFlavor: "NI"
            }
        },
        {
            key: "value",
            attributes: function (input) {
                const attrs = {
                    "xsi:type": "CD"
                };
                // If we have actual data, include it; otherwise use nullFlavor
                if (input && input.code) {
                    attrs.code = input.code;
                    attrs.displayName = input.display;
                    attrs.codeSystem = input.code_system || "2.16.840.1.113883.6.1";
                    attrs.codeSystemName = input.code_system_name || "LOINC";
                } else {
                    attrs.nullFlavor = "ASKU";
                }
                return attrs;
            },
            dataKey: "gender_identity"
        }
    ],
    existsWhen: function (input) {
        // Always include this observation if the social_history section exists
        return true;
    }
};

function resolveAdministrativeSexFromOptionId(optionIdRaw) {
    if (!optionIdRaw) return null;
    const optionId = String(optionIdRaw).trim().toLowerCase();

    switch (optionId) {
        case 'male':
            return {code: '248153007', system: '2.16.840.1.113883.6.96', display: 'Male'}; // SNOMED
        case 'female':
            return {code: '248152002', system: '2.16.840.1.113883.6.96', display: 'Female'}; // SNOMED
        case 'nonbinary':
            return {code: '33791000087105', system: '2.16.840.1.113883.6.96', display: 'Identifies as nonbinary gender (finding)'}; // SNOMED
        case 'asked-declined':
            return { code: 'asked-declined', system: '2.16.840.1.113883.4.642.4.1048', display: 'Asked But Declined' };
        case 'unk':
        case 'unknown':
            return { code: 'unknown', system: '2.16.840.1.113883.4.642.4.1048', display: 'Unknown' };
        default:
            return null;
    }
}

function parseCodeSpec(spec) {
    if (!spec || typeof spec !== 'string') return null;
    const [sys, code] = spec.split(':').map(s => (s || '').trim());
    if (!sys || !code) return null;
    const SYSTEMS = {
        'SNOMED-CT': '2.16.840.1.113883.6.96',
        'SNOMED': '2.16.840.1.113883.6.96',
        'DataAbsentReason': '2.16.840.1.113883.4.642.4.1048'
    };
    return {systemUri: SYSTEMS[sys] || sys, code};
}

function displayForSexCode(systemUri, code) {
    if (systemUri === '2.16.840.1.113883.6.96') {
        if (code === '248153007') return 'Male';
        if (code === '248152002') return 'Female';
        if (code === '33791000087105') return 'Identifies as nonbinary gender (finding)';
    }
    return '';
}

exports.sexObservation = {
    key: "observation",
    attributes: {classCode: "OBS", moodCode: "EVN"},
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.507", "2023-06-28"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.507"),
        fieldLevel.uniqueIdRoot,
        {
            key: "code",
            attributes: {
                code: "46098-0",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Sex"
            }
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "effectiveTime",
            attributes: {
                nullFlavor: "NI"
            }
        }, {
            key: "value",
            attributes: function (input) {
                const so = (input && input.sex_observation) || {};
                const attrs = {"xsi:type": "CD"};

                const optionId = so.gender;
                const resolved = resolveAdministrativeSexFromOptionId(optionId);
                if (resolved) {
                    attrs.code = resolved.code;
                    attrs.codeSystem = resolved.system;
                    if (resolved.display) attrs.displayName = resolved.display;
                    return attrs;
                }

                const fromSpec = parseCodeSpec(so.code_spec);
                if (fromSpec) {
                    attrs.code = fromSpec.code;
                    attrs.codeSystem = fromSpec.systemUri;
                    attrs.displayName =
                        displayForSexCode(fromSpec.systemUri, fromSpec.code) ||
                        (fromSpec.systemUri === "2.16.840.1.113883.4.642.4.1048"
                            ? (fromSpec.code === "asked-declined" ? "Asked But Declined"
                                : fromSpec.code === "unknown" ? "Unknown" : undefined)
                            : undefined);
                    return attrs;
                }

                if (so.code && (so.code_system || so.codeSystem)) {
                    const sys = so.code_system || so.codeSystem;
                    attrs.code = String(so.code);
                    attrs.codeSystem = String(sys);
                    attrs.displayName =
                        so.display || displayForSexCode(sys, so.code) ||
                        (sys === "2.16.840.1.113883.4.642.4.1048"
                            ? (so.code === "asked-declined" ? "Asked But Declined"
                                : so.code === "unknown" ? "Unknown" : undefined)
                            : undefined);
                    return attrs;
                }

                const g = (input && input.gender) ? String(input.gender).toLowerCase() : null;
                if (g === 'm' || g === 'male') {
                    attrs.code = '248153007';
                    attrs.codeSystem = '2.16.840.1.113883.6.96';
                    attrs.displayName = 'Male';
                    return attrs;
                }
                if (g === 'f' || g === 'female') {
                    attrs.code = '248152002';
                    attrs.codeSystem = '2.16.840.1.113883.6.96';
                    attrs.displayName = 'Female';
                    return attrs;
                }
                if (g === 'nonbinary') {
                    attrs.code = '33791000087105';
                    attrs.codeSystem = '2.16.840.1.113883.6.96';
                    attrs.displayName = 'Identifies as nonbinary gender (finding)';
                    return attrs;
                }
                if (g === 'asked-declined') {
                    attrs.code = 'asked-declined';
                    attrs.codeSystem = '2.16.840.1.113883.4.642.4.1048';
                    attrs.displayName = 'Asked But Declined';
                    return attrs;
                }
                if (g === 'unk' || g === 'unknown') {
                    attrs.code = 'unknown';
                    attrs.codeSystem = '2.16.840.1.113883.4.642.4.1048';
                    attrs.displayName = 'Unknown';
                    return attrs;
                }

                // 4) Last resort
                attrs.code = 'unknown';
                attrs.codeSystem = '2.16.840.1.113883.4.642.4.1048';
                attrs.displayName = 'Unknown';
                return attrs;
            },
            dataKey: "sex_observation"
        }
    ],
    existsWhen: function (input) {
        // Emit when we have an option_id, a code, a code_spec, or gender
        const so = input && input.sex_observation;
        return !!(
            (typeof so === 'string' && so) ||
            (so && (so.gender || so.code_spec || so.code)) ||
            (input && input.gender)
        );
    }
};
