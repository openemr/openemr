"use strict";

var headerLevel = require('./headerLevel');
var fieldLevel = require('./fieldLevel');
var leafLevel = require('./leafLevel');
var contentModifier = require("./contentModifier");
var condition = require("./condition");

var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var sectionLevel2 = require('./sectionLevel2');

exports.ccd2 = function (html_renderer) {
    return {
        key: "ClinicalDocument",
        attributes: {
            "xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns": "urn:hl7-org:v3",
            "xmlns:voc": "urn:hl7-org:v3/voc",
            "xmlns:sdtc": "urn:hl7-org:sdtc"
        },
        content: [{
            key: "realmCode",
            attributes: {
                code: "US"
            }
        }, {
            key: "typeId",
            attributes: {
                root: "2.16.840.1.113883.1.3",
                extension: "POCD_HD000040"
            }
        },
            fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.1.1", "2015-08-01"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.1"),
            {
                key: "templateId",
                attributes: {
                    "root": leafLevel.inputProperty("root"),
                    "extension": leafLevel.inputProperty("extension")
                },
                dataKey: 'meta.ccda_header.template',
            },
            {
                key: "templateId",
                attributes: {
                    "root": leafLevel.inputProperty("root")
                },
                dataKey: 'meta.ccda_header.template',
            },
            [fieldLevel.id, dataKey("meta.identifiers")],
            {
                key: "code",
                attributes: {
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    code: leafLevel.inputProperty("code"),
                    displayName: leafLevel.inputProperty("name")
                },
                dataKey: 'meta.ccda_header.code',
            }, {
                key: "title",
                text: leafLevel.inputProperty("title"),
                dataKey: "meta.ccda_header"
            },
            [fieldLevel.effectiveTime, required, dataKey("meta.ccda_header.date_time")], {
                key: "confidentialityCode",
                attributes: leafLevel.codeFromName("2.16.840.1.113883.5.25"),
                dataKey: "meta.confidentiality"
            }, {
                key: "languageCode",
                attributes: {
                    code: "en-US"
                }
            }, {
                key: "setId",
                attributes: {
                    root: leafLevel.inputProperty("identifier"),
                    extension: leafLevel.inputProperty("extension")
                },
                dataKey: 'meta.set_id',
                existsWhen: condition.keyExists('identifier')
            }, {
                key: "versionNumber",
                attributes: {
                    value: "1"
                }
            },
            headerLevel.recordTarget,
            headerLevel.headerAuthor,
            headerLevel.headerInformant,
            headerLevel.headerCustodian,
            headerLevel.headerInformationRecipient,
            headerLevel.participant,
            headerLevel.providers,
            headerLevel.headerComponentOf, {
                key: "component",
                content: {
                    key: "structuredBody",
                    content: [
                        sectionLevel2.careTeamSection(html_renderer.careTeamSectionHtmlHeader, html_renderer.careTeamSectionHtmlHeaderNA),
                        [sectionLevel2.allergiesSectionEntriesRequired(html_renderer.allergiesSectionEntriesRequiredHtmlHeader, html_renderer.allergiesSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.medicationsSectionEntriesRequired(html_renderer.medicationsSectionEntriesRequiredHtmlHeader, html_renderer.medicationsSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.problemsSectionEntriesRequired(html_renderer.problemsSectionEntriesRequiredHtmlHeader, html_renderer.problemsSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.proceduresSectionEntriesRequired(html_renderer.proceduresSectionEntriesRequiredHtmlHeader, html_renderer.proceduresSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.resultsSectionEntriesRequired(html_renderer.resultsSectionEntriesRequiredHtmlHeader, html_renderer.resultsSectionEntriesRequiredHtmlHeaderNA), required],
                        sectionLevel2.functionalStatusSection(html_renderer.functionalStatusSectionHtmlHeader, html_renderer.functionalStatusSectionHtmlHeaderNA),
                        sectionLevel2.historyNoteSection('', ''),
                        sectionLevel2.progressNoteSection('', ''),
                        sectionLevel2.procedureNoteSection('', ''),
                        sectionLevel2.nurseNoteSection('', ''),
                        sectionLevel2.generalNoteSection('', ''),
                        sectionLevel2.consultationNoteSection('', ''),
                        sectionLevel2.dischargeSummaryNoteSection('', ''),
                        sectionLevel2.laboratoryReportNoteSection('', ''),
                        sectionLevel2.imagingNarrativeNoteSection('', ''),
                        sectionLevel2.pathologyReportNoteSection('', ''),
                        sectionLevel2.encountersSectionEntriesOptional(html_renderer.encountersSectionEntriesOptionalHtmlHeader, html_renderer.encountersSectionEntriesOptionalHtmlHeaderNA),
                        sectionLevel2.immunizationsSectionEntriesOptional(html_renderer.immunizationsSectionEntriesOptionalHtmlHeader, html_renderer.immunizationsSectionEntriesOptionalHtmlHeaderNA),
                        //sectionLevel2.payersSection(html_renderer.payersSectionHtmlHeader, html_renderer.payersSectionHtmlHeaderNA),
                        sectionLevel2.assessmentSection(html_renderer.assessmentSectionHtmlHeader, html_renderer.assessmentSectionHtmlHeaderNA),
                        sectionLevel2.planOfCareSection(html_renderer.planOfCareSectionHtmlHeader, html_renderer.planOfCareSectionHtmlHeaderNA),
                        sectionLevel2.goalSection(html_renderer.goalSectionHtmlHeader, html_renderer.goalSectionHtmlHeaderNA),
                        sectionLevel2.healthConcernSection('', ''),
                        sectionLevel2.reasonForReferralSection('', html_renderer.goalSectionHtmlHeaderNA),
                        sectionLevel2.mentalStatusSection('', ''),
                        sectionLevel2.socialHistorySection(html_renderer.socialHistorySectionHtmlHeader, html_renderer.socialHistorySectionHtmlHeaderNA),
                        sectionLevel2.vitalSignsSectionEntriesOptional(html_renderer.vitalSignsSectionEntriesOptionalHtmlHeader, html_renderer.vitalSignsSectionEntriesOptionalHtmlHeaderNA),
                        sectionLevel2.medicalEquipmentSectionEntriesOptional(html_renderer.medicalEquipmentSectionEntriesOptionalHtmlHeader, html_renderer.medicalEquipmentSectionEntriesOptionalHtmlHeaderNA)
                    ]
                },
                dataKey: 'data'
            }
        ]
    };
};

exports.unstructured = function () {
    return {
        key: "ClinicalDocument",
        attributes: {
            "xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns": "urn:hl7-org:v3",
            "xmlns:voc": "urn:hl7-org:v3/voc",
            "xmlns:sdtc": "urn:hl7-org:sdtc"
        },
        content: [{
            key: "realmCode",
            attributes: {
                code: "US"
            }
        }, {
            key: "typeId",
            attributes: {
                root: "2.16.840.1.113883.1.3",
                extension: "POCD_HD000040"
            }
        },
            fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.1.1", "2015-08-01"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.1"),
            {
                key: "templateId",
                attributes: {
                    "root": leafLevel.inputProperty("root"),
                    "extension": leafLevel.inputProperty("extension")
                },
                dataKey: 'meta.ccda_header.template',
            },
            {
                key: "templateId",
                attributes: {
                    "root": leafLevel.inputProperty("root")
                },
                dataKey: 'meta.ccda_header.template',
            },
            [fieldLevel.id, dataKey("meta.identifiers")],
            {
                key: "code",
                attributes: {
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    code: leafLevel.inputProperty("code"),
                    displayName: leafLevel.inputProperty("name")
                },
                dataKey: 'meta.ccda_header.code',
            }, {
                key: "title",
                text: leafLevel.inputProperty("title"),
                dataKey: "meta.ccda_header"
            },
            [fieldLevel.effectiveTime, required, dataKey("meta.ccda_header.date_time")], {
                key: "confidentialityCode",
                attributes: leafLevel.codeFromName("2.16.840.1.113883.5.25"),
                dataKey: "meta.confidentiality"
            }, {
                key: "languageCode",
                attributes: {
                    code: "en-US"
                }
            }, {
                key: "setId",
                attributes: {
                    root: leafLevel.inputProperty("identifier"),
                    extension: leafLevel.inputProperty("extension")
                },
                dataKey: 'meta.set_id',
                existsWhen: condition.keyExists('identifier')
            }, {
                key: "versionNumber",
                attributes: {
                    value: "1"
                }
            },
            headerLevel.recordTarget,
            headerLevel.headerAuthor,
            headerLevel.headerInformant,
            headerLevel.headerCustodian,
            headerLevel.headerInformationRecipient,
            headerLevel.participant,
            headerLevel.providers,
            headerLevel.headerComponentOf, {}
        ]
    };
};
