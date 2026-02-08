"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");
var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

// Builds one Health Concern Act with nested Problem Observation (REFR)
exports.healthConcernActWithProblemObservation = function (c) {
    // c is the normalized concern from getHealthConcerns()
    const actTpls = (c.act_template_ids || []).map(t =>
        `<templateId root="${t.root}"${t.extension ? ` extension="${t.extension}"` : ''}/>`
    ).join('');

    const obsTpls = (c.obs_template_ids || []).map(t =>
        `<templateId root="${t.root}"${t.extension ? ` extension="${t.extension}"` : ''}/>`
    ).join('');

    const authorXml = c.author ? c.author : ''; // pass-through if your pipeline injects author blocks

    // optional LOINC translation on the observation code
    const translation = c.obs_code && c.obs_code.translation
        ? `<translation code="${c.obs_code.translation.code}"
         codeSystem="${c.obs_code.translation.codeSystem}"
         codeSystemName="${c.obs_code.translation.codeSystemName}"
         displayName="${c.obs_code.translation.displayName}"/>`
        : '';

    const effLow = c.obs_effective_low ? `
    <effectiveTime xsi:type="IVL_TS">
      <low value="${c.obs_effective_low}"/>
    </effectiveTime>` : '';

    return `
  <entry>
    <act classCode="ACT" moodCode="EVN">
      ${actTpls}
      <id root="${c.act_id.root}" extension="${c.act_id.extension}"/>
      <code code="${c.act_code.code}" codeSystem="${c.act_code.codeSystem}" codeSystemName="${c.act_code.codeSystemName}" displayName="${c.act_code.displayName}"/>
      <statusCode code="${c.act_status}"/>
      ${authorXml}
      <entryRelationship typeCode="REFR">
        <observation classCode="OBS" moodCode="EVN">
          ${obsTpls}
          <id root="${c.obs_id.root}" extension="${c.obs_id.extension}"/>
          <code code="${c.obs_code.code}" codeSystem="${c.obs_code.codeSystem}" codeSystemName="${c.obs_code.codeSystemName}" displayName="${c.obs_code.displayName}">
            ${translation}
          </code>
          <statusCode code="${c.obs_status}"/>
          ${effLow}
          <value xsi:type="CD" code="${c.value.code}"
                 displayName="${c.value.displayName}"
                 codeSystem="${c.value.codeSystem}"
                 codeSystemName="${c.value.codeSystemName}"/>
        </observation>
      </entryRelationship>
    </act>
  </entry>`;
};

exports.healthConcernObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.5", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.5"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: {
                code: "11323-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Health Status"
            },
        },
        fieldLevel.statusCodeCompleted,
        {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "value",
            existsWhen: condition.codeOrDisplayname
        },
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],

}

exports.healthConcernActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    existsWhen: condition.keyExists("value"),
    content: [
        // Health Concern Act (V2)
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.132"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.132", "2015-08-01"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.132", "2022-06-01"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        {
            key: "code",
            attributes: {
                code: "75310-3",
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                displayName: "Health Concern"
            }
        },
        fieldLevel.statusCodeActive,
        // Optional effectiveTime.low (when concern began)
        fieldLevel.effectiveTime, // consume input.effective_time.{low,high} if present
        fieldLevel.author,
        {
            key: "entryRelationship",
            attributes: { typeCode: "REFR" },
            content: [{
                key: "observation",
                attributes: { classCode: "OBS", moodCode: "EVN" },
                content: [
                    // Problem Observation (V3)
                    fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.4"),
                    fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.4","2015-08-01"),
                    fieldLevel.uniqueId,
                    fieldLevel.id,
                    // code = “Clinical finding” with LOINC translation as in ONC sample
                    {
                        key: "code",
                        attributes: {
                            code: "404684003",
                            displayName: "Clinical finding (finding)",
                            codeSystem: "2.16.840.1.113883.6.96",
                            codeSystemName: "SNOMED CT"
                        },
                        content: [{
                            key: "translation",
                            attributes: {
                                code: "75321-0",
                                displayName: "Clinical Finding",
                                codeSystem: "2.16.840.1.113883.6.1",
                                codeSystemName: "LOINC"
                            }
                        }]
                    },
                    fieldLevel.statusCodeCompleted,
                    fieldLevel.effectiveTime,
                    {
                        key: "value",
                        attributes: [leafLevel.typeCD, leafLevel.code],
                        dataKey: "value",
                        existsWhen: require("../condition").codeOrDisplayname
                    },
                    fieldLevel.author
                ]
            }],
            // NEW: ensure 'value' and effective_time.low exist for the nested observation
            dataTransform: function (input) {
                // map code system name -> OID
                function oid(sysName) {
                    const n = (sysName || '').toLowerCase().replace(/\s+/g, '');
                    if (n.includes('snomed')) return '2.16.840.1.113883.6.96';
                    if (n.includes('loinc'))  return '2.16.840.1.113883.6.1';
                    if (n.includes('icd10'))  return '2.16.840.1.113883.6.90';
                    return input.value && input.value.code_system || ''; // leave as-is if pre-set
                }
                // normalize "SNOMED-CT" -> "SNOMED CT"
                function normName(sysName) {
                    if (!sysName) return '';
                    return sysName.replace(/-/g, ' ').replace(/\s+/g, ' ').trim();
                }

                // synthesize <value> if missing
                const v = Object.assign({}, input.value || {});
                if (!v.code && input.code) v.code = (''+input.code).trim();
                if (!v.name && input.code_text) v.name = input.code_text;
                if (!v.code_system_name && input.code_type) v.code_system_name = normName(input.code_type);
                if (!v.code_system && (v.code_system_name || input.code_type)) v.code_system = oid(v.code_system_name || input.code_type);

                input.value = v;

                return input;

            }
        }
    ],
};


exports.planOfCareActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.39"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "act";
    }
};

exports.planOfCareActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: leafLevel.inputProperty("mood_code")
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.44"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("name")
        },
        fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.type === "observation";
    }
};

exports.planOfCarePlannedProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.41"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.41", "2014-06-09"),
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.41", "2022-06-01"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "planned_procedure";
    }
};

exports.planOfCareActivityProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.41"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author,
    ],
    existsWhen: function (input) {
        return input.type === "procedure";
    }
};

exports.planOfCareActivityEncounter = {
    key: "encounter",
    attributes: {
        classCode: "ENC",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.40"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        [fieldLevel.performer, dataKey("performers")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "RSON"
            },
            content: [
                [sharedEntryLevel.indication, required]
            ],
            dataKey: "findings",
            dataTransform: function (input) {
                input = input.map(function (e) {
                    e.code = {
                        code: "282291009",
                        name: "Diagnosis",
                        code_system: "2.16.840.1.113883.6.96",
                        code_system_name: "SNOMED CT"
                    };
                    return e;
                });
                return input;
            }
        }
    ],
    existsWhen: function (input) {
        return input.type === "encounter";
    }
};

var carePlanMedicationInformation = {
    key: "manufacturedProduct",
    attributes: {
        classCode: "MANU"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.23", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.23"),
        {
            key: "manufacturedMaterial",
            content: [{
                key: "code",
                attributes: leafLevel.code,
            }]
        }
    ]
};

exports.planOfCareActivitySubstanceAdministration = {
    key: "substanceAdministration",
    attributes: {
        classCode: "SBADM",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateIdExt("2.16.840.1.113883.10.20.22.4.42", "2014-06-09"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.42"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "text",
            text: leafLevel.input,
            dataKey: "name"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        {
            key: "consumable",
            content: carePlanMedicationInformation,
            dataKey: "plan"
        },
    ],
    existsWhen: function (input) {
        return input.type === "substanceAdministration";
    }
};

exports.planOfCareActivitySupply = {
    key: "supply",
    attributes: {
        classCode: "SPLY",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.43"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive,
        fieldLevel.effectiveTime,
        fieldLevel.author
    ],
    existsWhen: function (input) {
        return input.type === "supply";
    }
};

var goal = {
    key: "code",
    attributes: {
        "code": leafLevel.deepInputProperty("code"),
        "displayName": "Goal"
    },
    content: [{
        key: "originalText",
        text: leafLevel.deepInputProperty("name")
    }],
    dataKey: "goal"
};

var intervention = {
    key: "code",
    attributes: {
        "code": leafLevel.deepInputProperty("code"),
        "displayName": "Intervention"
    },
    content: [{
        key: "originalText",
        text: leafLevel.deepInputProperty("name")
    }],
    dataKey: "intervention"
};

exports.planOfCareActivityInstructions = {
    key: "instructions",
    attributes: {
        classCode: "ACT",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.20"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeActive, {
            key: "priorityCode",
            attributes: {
                "code": leafLevel.deepInputProperty("code"),
                "displayName": "Severity Code"
            },
            content: [{
                key: "originalText",
                text: leafLevel.deepInputProperty("name")
            }],
            dataKey: "severity"
        },
        fieldLevel.effectiveTime,
        fieldLevel.author,
        {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: [{
                key: "observation",
                attributes: {
                    classCode: "OBS",
                    moodCode: "GOL"
                },
                content: [fieldLevel.effectiveTime, goal, {
                    key: "act",
                    attributes: {
                        classCode: "ACT",
                        moodCode: "INT"
                    },

                    content: [{
                        key: "entryRelationship",
                        attributes: {
                            typeCode: "REFR"
                        },
                        content: [intervention],
                        dataKey: "interventions"
                    }]
                }],
                dataKey: "goals"

            }],
            required: true
        }
    ],
    existsWhen: function (input) {
        return input.type === "instructions";
    }
};
