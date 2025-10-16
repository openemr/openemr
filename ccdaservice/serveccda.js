/**
 * @package   OpenEMR CCDAServer
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Refactored CCDA server using async xml2js instead of xmljson */

"use strict";

const enableDebug = true;

const net = require("net");
const server = net.createServer();
const xml2js = require("xml2js");
const xmlParser = new xml2js.Parser({explicitArray: false, mergeAttrs: true});
const bbg = require(__dirname + "/oe-blue-button-generate");
const fs = require("fs");
const {DataStack} = require("./data-stack/data-stack");
const {cleanCode} = require("./utils/clean-code/clean-code");
const {safeTrim} = require("./utils/safe-trim/safe-trim");
const {headReplace} = require("./utils/head-replace/head-replace");
const {fDate, templateDate} = require("./utils/date/date");
const {countEntities} = require("./utils/count-entities/count-entities");
const {populateTimezones} = require("./utils/timezones/timezones");
const {
    getNpiFacility,
    populateDemographics,
} = require("./utils/demographics/populate-demographics");
const {populateProvider} = require("./utils/providers/providers");

let conn = "";
let oidFacility = "";
let all = "";
let npiProvider = "";
let npiFacility = "";
let webRoot = "";
let authorDateTime = "";
let documentLocation = "";

// Helper function for safe property access
function safeGet(obj, path, defaultValue = "") {
    return path.split('.').reduce((current, key) => {
        return (current && current[key] !== undefined) ? current[key] : defaultValue;
    }, obj);
}

function populateProviders(all) {
    if (!all) return {providers: {provider: []}};

    let providerArray = [];
    let provider = {};

    // primary provider
    if (all.primary_care_provider?.provider) {
        provider = populateProvider(all.primary_care_provider.provider, all);
        providerArray.push(provider);
    }

    const careTeam = all.care_team || {};
    let count = countEntities(careTeam.provider);

    if (count === 1) {
        provider = populateProvider(careTeam.provider, all);
        providerArray.push(provider);
    } else if (count > 1) {
        for (let i in careTeam.provider) {
            provider = populateProvider(careTeam.provider[i], all);
            providerArray.push(provider);
        }
    }

    const primaryDiagnosis = all.primary_diagnosis || {};

    return {
        "providers": {
            "date_time": {
                "low": {
                    "date": fDate(all.time_start) || fDate(""),
                    "precision": "tz"
                },
                "high": {
                    "date": fDate(all.time_end) || fDate(""),
                    "precision": "tz"
                }
            },
            "code": {
                "name": primaryDiagnosis.text || "",
                "code": cleanCode(primaryDiagnosis.code || ""),
                "code_system_name": primaryDiagnosis.code_type || ""
            },
            "provider": providerArray,
        }
    }
}

function populateCareTeamMember(provider) {
    if (!provider) return {};

    const encounterProvider = all?.encounter_provider || {};

    return {
        "function_code": {
            "xmlns": "urn:hl7-org:sdtc",
            "name": provider.role_display || "",
            "code": cleanCode(provider.role_code) || "",
            "code_system": "2.16.840.1.113883.6.101",
            "code_system_name": "SNOMED CT"
        },
        "status": provider.status || "active",
        "date_time": {
            "low": {
                "date": fDate(provider.provider_since) || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": provider.npi ? "2.16.840.1.113883.4.6" : oidFacility,
                "extension": provider.npi || provider.table_id || ""
            }
        ],
        "full_name": (provider.fname || "") + " " + (provider.lname || ""),
        "name": {
            "last": provider.lname || "",
            "first": provider.fname || ""
        },
        "address": {
            "street_lines": [
                provider.street || ""
            ],
            "city": provider.city || "",
            "state": provider.state || "",
            "zip": provider.zip || "",
            "country": encounterProvider.facility_country_code || "US"
        },
        "phone": [
            {
                "number": provider.telecom || "",
                "type": "work place"
            }
        ]
    }
}

function populateAuthorFromAuthorContainer(pd) {
    if (!pd) return {};

    let author = pd.author || {};
    return {
        "code": {
            "name": author.physician_type || '',
            "code": author.physician_type_code || '',
            "code_system": author.physician_type_system || "",
            "code_system_name": author.physician_type_system_name || ""
        },
        "date_time": {
            "point": {
                "date": fDate(author.time) || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                "extension": author.npi ? author.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": author.lname || "",
                "first": author.fname || ""
            }
        ],
        "organization": [
            {
                "identity": [
                    {
                        "root": author.facility_oid || "2.16.840.1.113883.4.6",
                        "extension": author.facility_npi || "NI"
                    }
                ],
                "name": [
                    author.facility_name || ""
                ]
            }
        ]
    };
}

function populateCareTeamMembers(pd) {
    if (!pd) return {providers: {provider: []}};

    const providerArray = [];
    let providerSince = "";

    // Process additional care team members (providers, case managers, etc.)
    const teamMembers = pd.care_team?.provider || [];

    if (Array.isArray(teamMembers)) {
        for (const member of teamMembers) {
            const entry = populateCareTeamMember(member);
            providerArray.push(entry);
            if (!providerSince && member.provider_since) {
                providerSince = fDate(member.provider_since);
            }
        }
    } else if (typeof teamMembers === 'object' && teamMembers) {
        // Single care team member object (not an array)
        const entry = populateCareTeamMember(teamMembers);
        providerArray.push(entry);
        if (!providerSince && teamMembers.provider_since) {
            providerSince = fDate(teamMembers.provider_since);
        }
    }

    return {
        providers: {
            provider: providerArray
        },
        status: "active",
        date_time: {
            low: {
                date: providerSince || fDate(""),
                precision: "tz"
            }
        },
        author: populateAuthorFromAuthorContainer(pd.care_team || {})
    };
}

function populateMedication(pd) {
    if (!pd) return {};

    pd.status = 'Completed'; //@todo invoke prescribed

    const author = pd.author || {};
    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};

    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date) || fDate(""),
                "precision": "day"
            },
            "high": {
                "date": fDate(pd.end_date),
                "precision": "day"
            }
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "status": pd.status,
        "sig": pd.direction || "",
        "product": {
            "identifiers": [{
                "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": (pd.extension ? pd.extension + 1 : "") || ""
            }],
            "unencoded_name": pd.drug || "",
            "product": {
                "name": pd.drug || "",
                "code": cleanCode(pd.rxnorm) || "",
                "code_system_name": "RXNORM"
            },
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        },
        "supply": {
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date) || fDate(""),
                    "precision": "day"
                },
                "high": {
                    "date": fDate(pd.end_date) || fDate(""),
                    "precision": "day"
                }
            },
            "repeatNumber": "0",
            "quantity": "0",
            "product": {
                "identifiers": [{
                    "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                    "extension": (pd.extension ? pd.extension + 1 : "") || ""
                }],
                "unencoded_name": pd.drug || "",
                "product": {
                    "name": pd.drug || "",
                    "code": cleanCode(pd.rxnorm) || "",
                    "code_system_name": "RXNORM"
                },
            },
            "author": {
                "code": {
                    "name": allAuthor.physician_type || '',
                    "code": allAuthor.physician_type_code || '',
                    "code_system": allAuthor.physician_type_system || "",
                    "code_system_name": allAuthor.physician_type_system_name || ""
                },
                "date_time": {
                    "point": {
                        "date": authorDateTime || fDate(""),
                        "precision": "tz"
                    }
                },
                "identifiers": [
                    {
                        "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                        "extension": allAuthor.npi ? allAuthor.npi : 'NI'
                    }
                ],
                "name": [
                    {
                        "last": allAuthor.lname || "",
                        "first": allAuthor.fname || ""
                    }
                ],
                "organization": [
                    {
                        "identity": [
                            {
                                "root": oidFacility || "2.16.840.1.113883.4.6",
                                "extension": npiFacility || ""
                            }
                        ],
                        "name": [
                            encounterProvider.facility_name || ""
                        ]
                    }
                ]
            },
            "instructions": {
                "code": {
                    "name": "instruction",
                    "code": "409073007",
                    "code_system_name": "SNOMED CT"
                },
                "free_text": pd.instructions || "No Instructions"
            },
        },
        "administration": {
            "route": {
                "name": pd.route || "",
                "code": cleanCode(pd.route_code) || "",
                "code_system_name": "Medication Route FDA"
            },
            "form": {
                "name": pd.form || "",
                "code": cleanCode(pd.form_code) || "",
                "code_system_name": "Medication Route FDA"
            },
            "dose": {
                "value": parseFloat(pd.size || '') || null,
                "unit": pd.unit || "",
            },
            /*"rate": {
                "value": parseFloat(pd.dosage),
                "unit": ""
            },*/
            "interval": {
                "period": {
                    "value": parseFloat(pd.dosage) || null,
                    "unit": pd.interval || null
                },
                "frequency": true
            }
        },
        "performer": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "organization": [{
                "identifiers": [{
                    "identifier": pd.sha_extension || "",
                    "extension": pd.extension || ""
                }],
                "name": [pd.performer_name || ""]
            }]
        },
        "drug_vehicle": {
            "name": pd.form,
            "code": cleanCode(pd.form_code),
            "code_system_name": "RXNORM"
        },
        /*"precondition": {
            "code": {
                "code": "ASSERTION",
                "code_system_name": "ActCode"
            },
            "value": {
                "name": "none",
                "code": "none",
                "code_system_name": "SNOMED CT"
            }
        },
        "indication": {
            "identifiers": [{
                "identifier": "db734647-fc99-424c-a864-7e3cda82e703",
                "extension": "45665"
            }],
            "code": {
                "name": "Finding",
                "code": "404684003",
                "code_system_name": "SNOMED CT"
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date),
                    "precision": "day"
                }
            },
            "value": {
                "name": pd.indications,
                "code": pd.indications_code,
                "code_system_name": "SNOMED CT"
            }
        },
        "dispense": {
            "identifiers": [{
                "identifier": "1.2.3.4.56789.1",
                "extension": "cb734647-fc99-424c-a864-7e3cda82e704"
            }],
            "performer": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.19.5.9999.456",
                    "extension": "2981823"
                }],
                "address": [{
                    "street_lines": [pd.address],
                    "city": pd.city,
                    "state": pd.state,
                    "zip": pd.zip,
                    "country": "US"
                }],
                "organization": [{
                    "identifiers": [{
                        "identifier": "2.16.840.1.113883.19.5.9999.1393"
                    }],
                    "name": [pd.performer_name]
                }]
            },
            "product": {
                "identifiers": [{
                    "identifier": "2a620155-9d11-439e-92b3-5d9815ff4ee8"
                }],
                "unencoded_name": pd.drug,
                "product": {
                    "name": pd.drug,
                    "code": pd.rxnorm,
                    "translations": [{
                        "name": pd.drug,
                        "code": pd.rxnorm,
                        "code_system_name": "RXNORM"
                    }],
            "code_system_name": "RXNORM"
                },
                "manufacturer": ""
        }
        }*/
    };
}

function getFinding(pd, problem) {
    if (!pd || !problem) return {};

    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};

    const finding = {
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": problem.extension || ""
        }],
        "value": {
            "name": problem.text || "",
            "code": cleanCode(problem.code) || "",
            "code_system_name": problem.code_type || ""
        },
        "date_time": {
            "low": {
                "date": fDate(problem.date) || fDate(""),
                "precision": "day"
            }
        },
        "status": problem.status || "",
        "reason": pd.encounter_reason || "",
        "author": {
            "code": {
                "name": allAuthor.physician_type || '',
                "code": allAuthor.physician_type_code || '',
                "code_system": allAuthor.physician_type_system || "",
                "code_system_name": allAuthor.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": authorDateTime || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                    "extension": allAuthor.npi ? allAuthor.npi : 'UNK'
                }
            ],
            "name": [
                {
                    "last": allAuthor.lname || "",
                    "first": allAuthor.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": oidFacility || "2.16.840.1.113883.4.6",
                            "extension": npiFacility || ""
                        }
                    ],
                    "name": [
                        encounterProvider.facility_name || ""
                    ]
                }
            ]
        },
    };

    return finding;
}

function populateEncounter(pd) {
    if (!pd) return {};

    // just to get diagnosis. for findings..
    let findingObj = [];
    let theone = {};
    let count = 0;

    try {
        count = countEntities(pd.encounter_problems?.problem);
    } catch (e) {
        count = 0;
    }

    if (count > 1) {
        for (let i in pd.encounter_problems.problem) {
            theone[i] = getFinding(pd, pd.encounter_problems.problem[i]);
            findingObj.push(theone[i]);
        }
    } else if (count !== 0 && pd.encounter_problems?.problem?.code) {
        let finding = getFinding(pd, pd.encounter_problems.problem);
        findingObj.push(finding);
    }

    const encounterProcedures = pd.encounter_procedures?.procedures || {};

    return {
        "encounter": {
            "name": pd.visit_category ? (pd.visit_category + " | " + (pd.encounter_reason || "")) : (pd.code_description || ""),
            "code": encounterProcedures.code || "185347001",
            "code_system": encounterProcedures.code_type || "2.16.840.1.113883.6.96",
            "code_system_name": encounterProcedures.code_type_name || "SNOMED CT",
            "translations": [{
                "name": "Ambulatory",
                "code": "AMB",
                "code_system_name": "ActCode"
            }]
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.date) || fDate(""),
                "precision": "tz"
            }
        },
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "code": [{
                "name": pd.physician_type || "",
                "code": cleanCode(pd.physician_type_code) || "",
                "code_system_name": pd.physician_code_type || ""
            }],
            "name": [
                {
                    "last": pd.lname || "",
                    "first": pd.fname || ""
                }
            ],
            "phone": [
                {
                    "number": pd.work_phone || "",
                    "type": "work place"
                }
            ]
        }],
        "locations": [{
            "name": pd.location || "",
            "location_type": {
                "name": pd.location_details || "",
                "code": "1160-1",
                "code_system_name": "HealthcareServiceLocation"
            },
            "address": [{
                "street_lines": [pd.facility_address || ""],
                "city": pd.facility_city || "",
                "state": pd.facility_state || "",
                "zip": pd.facility_zip || "",
                "country": pd.facility_country || "US"
            }],
            "phone": [
                {
                    "number": pd.facility_phone || "",
                    "type": "work place"
                }
            ]
        }],
        "findings": findingObj
    };
}

function populateAllergy(pd) {
    if (!pd) {
        return {
            "no_know_allergies": "No Known Allergies",
            "date_time": {
                "low": templateDate("", "day"),
            }
        }
    }

    const author = pd.author || {};
    let allergyAuthor = {
        "code": {
            "name": author.physician_type || '',
            "code": author.physician_type_code || '',
            "code_system": author.physician_type_system || "",
            "code_system_name": author.physician_type_system_name || ""
        },
        "date_time": {
            "point": {
                "date": fDate(author.time) || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                "extension": author.npi ? author.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": author.lname || "",
                "first": author.fname || ""
            }
        ],
        "organization": [
            {
                "identity": [
                    {
                        "root": author.facility_oid || "2.16.840.1.113883.4.6",
                        "extension": author.facility_npi || "NI"
                    }
                ],
                "name": [
                    author.facility_name || ""
                ]
            }
        ]
    };

    return {
        "identifiers": [{
            "identifier": pd.sha_id || "",
            "extension": pd.id || ""
        }],
        "date_time": {
            "low": templateDate(pd.startdate, "day"),
        },
        "author": allergyAuthor,
        "observation": {
            "identifiers": [{
                "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": (pd.id ? pd.id + 1 : "") || ""
            }],
            "author": allergyAuthor,
            "allergen": {
                "name": pd.title || "",
                "code": pd.rxnorm_code_text ? cleanCode(pd.rxnorm_code) : pd.snomed_code_text ? cleanCode(pd.snomed_code) : cleanCode(""),
                "code_system_name": pd.rxnorm_code_text ? "RXNORM" : pd.snomed_code_text ? "SNOMED CT" : ""
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.startdate) || fDate(""),
                    "precision": "day"
                }
            },
            "intolerance": {
                "name": "Propensity to adverse reactions to drug",
                "code": "420134006",
                "code_system_name": "SNOMED CT"
            },
            "severity": {
                "code": {
                    "name": pd.outcome || "",
                    "code": cleanCode(pd.outcome_code) || "",
                    "code_system_name": "SNOMED CT"
                }
            },
            "status": {
                "name": pd.status_table || "",
                "code": cleanCode(pd.status_code) || "",
                "code_system_name": "SNOMED CT"
            },
            "reactions": [{
                "identifiers": [{
                    "identifier": "4adc1020-7b14-11db-9fe1-0800200c9a64"
                }],
                "date_time": {
                    "low": templateDate(pd.startdate, "day"),
                    "high": templateDate(pd.enddate, "day")
                },
                "reaction": {
                    "name": pd.reaction_text || "",
                    "code": cleanCode(pd.reaction_code) || "",
                    "code_system_name": pd.reaction_code_type || "SNOMED CT"
                },
                "severity": {
                    "code": {
                        "name": pd.outcome || "",
                        "code": cleanCode(pd.outcome_code) || "",
                        "code_system_name": "SNOMED CT"
                    }
                }
            }]
        }
    }
}

function populateProblem(pd) {
    if (!pd) return {};

    let primary_care_provider = all?.primary_care_provider || {provider: {}};
    const author = pd.author || {};

    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date_table) || fDate(""),
                "precision": "day"
            }
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "translations": [{
            "name": "Condition",
            "code": "75323-6",
            "code_system_name": "LOINC"
        }],
        "problem": {
            "code": {
                "name": safeTrim(pd.title) || "",
                "code": cleanCode(pd.code) || "",
                "code_system_name": safeTrim(pd.code_type) || ""
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date) || fDate(""),
                    "precision": "day"
                }
            }
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        },
        "performer": [
            {
                "identifiers": [
                    {
                        "identifier": "2.16.840.1.113883.4.6",
                        "extension": primary_care_provider.provider?.npi || ""
                    }
                ],
                "name": [
                    {
                        "last": primary_care_provider.provider?.lname || "",
                        "first": primary_care_provider.provider?.fname || ""
                    }
                ]
            }],
        "onset_age": pd.age || "",
        "onset_age_unit": "Year",
        "status": {
            "name": pd.status_table || "",
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date) || fDate(""),
                    "precision": "day"
                }
            }
        },
        "patient_status": pd.observation || "",
        "source_list_identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }]
    };
}

function populateProcedure(pd) {
    if (!pd) return {};

    return {
        "procedure": {
            "name": pd.description || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || ""
        },
        "identifiers": [{
            "identifier": "d68b7e32-7810-4f5b-9cc2-acd54b0fd85d",
            "extension": pd.extension || ""
        }],
        "status": "completed",
        "date_time": {
            "point": {
                "date": fDate(pd.date) || fDate(""),
                "precision": "day"
            }
        },
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "address": [{
                "street_lines": [pd.address || ""],
                "city": pd.city || "",
                "state": pd.state || "",
                "zip": pd.zip || "",
                "country": "US"
            }],
            "phone": [{
                "number": pd.work_phone || "",
                "type": "work place"
            }],
            "organization": [{
                "identifiers": [{
                    "identifier": pd.facility_sha_extension || "",
                    "extension": pd.facility_extension || ""
                }],
                "name": [pd.facility_name || ""],
                "address": [{
                    "street_lines": [pd.facility_address || ""],
                    "city": pd.facility_city || "",
                    "state": pd.facility_state || "",
                    "zip": pd.facility_zip || "",
                    "country": pd.facility_country || "US"
                }],
                "phone": [{
                    "number": pd.facility_phone || "",
                    "type": "work place"
                }]
            }]
        }],
        "author": populateAuthorFromAuthorContainer(pd),
        "procedure_type": "procedure"
    };
}

function populateMedicalDevice(pd) {
    if (!pd) return {};

    const author = pd.author || {};

    return {
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "date_time": {
            "low": {
                "date": fDate(pd.start_date) || fDate(""),
                "precision": "day"
            }
        },
        "device_type": "UDI",
        "device": {
            "name": pd.code_text || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": "SNOMED CT",
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.3719",
                "extension": pd.udi || ""
            }],
            "status": "completed",
            "body_sites": [{
                "name": "",
                "code": "",
                "code_system_name": ""
            }],
            "udi": pd.udi || ""
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        }
    }
}

function populateResult(pd) {
    if (!pd || !pd.subtest) return {};

    let icode = pd.subtest.abnormal_flag || "";
    let value = parseFloat(pd.subtest.result_value) || pd.subtest.result_value || "";
    let type = isNaN(value) ? "ST" : "PQ";
    type = !pd.subtest.unit ? "ST" : type;
    value += "";
    let range_type = (pd.subtest.range || "").toUpperCase() == "NEGATIVE" ? "CO" : type;
    type = value.toUpperCase() == "NEGATIVE" ? "CO" : type;

    switch ((pd.subtest.abnormal_flag || "").toUpperCase()) {
        case "NO":
            icode = "Normal";
            break;
        case "YES":
            icode = "Abnormal";
            break;
        case "":
            icode = "";
            break;
    }
    let result = {
        "identifiers": [{
            "identifier": pd.subtest.root || "",
            "extension": pd.subtest.extension || ""
        }],
        "result": {
            "name": pd.title || "",
            "code": cleanCode(pd.subtest.result_code) || "",
            "code_system_name": "LOINC"
        },
        "date_time": {
            "point": {
                "date": fDate(pd.date_ordered) || fDate(""),
                "precision": "day"
            }
        },
        "status": pd.order_status || "",
        "reference_range": {
            "low": pd.subtest.low || "",
            "high": pd.subtest.high || "",
            "unit": pd.subtest.unit || "",
            "type": type,
            "range_type": range_type
        },
        "value": value + "",
        "unit": pd.subtest.unit || "",
        "type": type,
        "range": pd.subtest.range || "",
        "range_type": range_type
    };
    // interpretation cannot be an empty value so we skip it if it is
    // empty as Observation.interpretationCode is [0..*]
    if (icode !== "") {
        result["interpretations"] = [icode];
    }
    return result;
}

function getResultSet(results) {
    if (!results || !results.result) return '';

    // not sure if the result set should be grouped better on the backend as the author information needs to be more nuanced here
    let tResult = results.result[0] || results.result;
    if (!tResult) return '';

    let resultSet = {
        "identifiers": [{
            "identifier": tResult.root || "",
            "extension": tResult.extension || ""
        }],
        "author": populateAuthorFromAuthorContainer(tResult),
        "result_set": {
            "name": tResult.test_name || "",
            "code": cleanCode(tResult.test_code) || "",
            "code_system_name": "LOINC"
        }
    };
    let rs = [];
    let many = [];
    let theone = {};
    let count = 0;
    many.results = [];
    try {
        count = countEntities(results.result);
    } catch (e) {
        count = 0;
    }
    if (count > 1) {
        for (let i in results.result) {
            theone[i] = populateResult(results.result[i]);
            many.results.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = populateResult(results.result);
        many.results.push(theone);
    }
    rs.results = Object.assign(resultSet);
    rs.results.results = Object.assign(many.results);
    return rs;
}

function getPlanOfCare(pd) {
    if (!pd) return false;

    let name = '';
    let code = '';
    let code_system_name = "";
    let status = "Active";
    let one = true;
    let encounter;

    let planType = "observation";
    switch (pd.care_plan_type) {
        case 'plan_of_care':
            planType = "observation"; // mood code INT. sets code in template
            break;
        case 'test_or_order':
            planType = "observation"; // mood code RQO
            break;
        case 'procedure':
            planType = "procedure";
            break;
        case 'planned_procedure':
            planType = "planned_procedure";
            break;
        case 'appointments':
            planType = "encounter";
            break;
        case 'instructions':
            planType = "instructions";
            break;
        case 'referral':
            planType = ""; // for now exclude. unsure how to template.
            break;
        default:
            planType = "observation";
    }
    if (pd.code_type === 'RXCUI') {
        pd.code_type = 'RXNORM';
    }
    if (pd.code_type === 'RXNORM') {
        planType = "substanceAdministration";
    }
    if (planType === "") {
        return false;
    }

    const encounterList = all?.encounter_list?.encounter || {};
    for (let key in encounterList) {
        // skip loop if the property is from prototype
        if (!Object.prototype.hasOwnProperty.call(encounterList, key)) {
            continue;
        }
        encounter = encounterList[key];
        if (pd.encounter == encounter.encounter_id) {
            one = false;
            const encounterDiagnosis = encounter.encounter_diagnosis || {};
            name = encounterDiagnosis.text || "";
            code = cleanCode(encounterDiagnosis.code) || "";
            code_system_name = encounterDiagnosis.code_type || "";
            status = encounterDiagnosis.status || "";
            encounter = encounterList[key]; // to be sure.
            break;
        }
    }
    if (one) {
        let value = "";
        if (all?.encounter_list?.encounter?.encounter_diagnosis) {
            value = all.encounter_list.encounter.encounter_diagnosis;
        }
        name = value.text || "";
        code = cleanCode(value.code) || "";
        code_system_name = value.code_type || "";
        status = value.status || "";
        encounter = all?.encounter_list?.encounter || {};
    }

    return {
        "plan": {
            "name": pd.code_text || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || "SNOMED CT"
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "goal": {
            "code": cleanCode(pd.code) || "",
            "name": safeTrim(pd.description) || ""
        },
        "date_time": {
            "point": {
                "date": pd.proposed_date ? fDate(pd.proposed_date) : fDate(pd.date) || fDate(""),
                "precision": "day"
            }
        },
        "type": planType,
        "status": {
            "code": cleanCode(pd.status) || ""
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": encounter.npi || ""
            }],
            "code": [{
                "name": encounter.physician_type || "",
                "code": cleanCode(encounter.physician_type_code) || "",
                "code_system_name": "SNOMED CT"
            }],
            "name": [
                {
                    "last": encounter.lname || "",
                    "first": encounter.fname || ""
                }
            ],
            "phone": [
                {
                    "number": encounter.work_phone || "",
                    "type": "work place"
                }
            ]
        }],
        "locations": [{
            "name": encounter.location || "",
            "location_type": {
                "name": encounter.location_details || "",
                "code": "1160-1",
                "code_system_name": "HealthcareServiceLocation"
            },
            "address": [{
                "street_lines": [encounter.facility_address || ""],
                "city": encounter.facility_city || "",
                "state": encounter.facility_state || "",
                "zip": encounter.facility_zip || "",
                "country": encounter.facility_country || "US"
            }],
            "phone": [
                {
                    "number": encounter.facility_phone || "",
                    "type": "work place"
                }
            ]
        }],
        "findings": [{
            "identifiers": [{
                "identifier": encounter.sha_extension || "",
                "extension": encounter.extension || ""
            }],
            "value": {
                "name": name,
                "code": code,
                "code_system_name": code_system_name
            },
            "date_time": {
                "low": {
                    "date": fDate(encounter.date) || fDate(""),
                    "precision": "day"
                }
            },
            "status": status,
            "reason": encounter.encounter_reason || ""
        }],
        "name": safeTrim(pd.description) || "",
        "mood_code": pd.moodCode || ""
    };
}

function getGoals(pd) {
    if (!pd) return {};

    pd.description = pd.value_type !== "CD" ? safeTrim(pd.description) : '';
    return {
        "goal_code": {
            "name": pd.code_text !== "NULL" ? (pd.code_text || "") : "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || ""
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || "",
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.date) || fDate(""),
                "precision": "day"
            }
        },
        "sdoh_name": pd.sdoh_code_text || "",
        "sdoh_code": pd.sdoh_code || "",
        "sdoh_code_system": pd.sdoh_code_system || "",
        "sdoh_code_system_name": pd.sdoh_code_type || "",
        "value_type": pd.value_type || "ST",
        "type": "observation",
        "status": {
            "code": "active",
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "name": pd.description || '',
    };
}

function getFunctionalStatus(pd) {
    if (!pd) return {};

    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};

    let functionalStatusAuthor = {
        "code": {
            "name": allAuthor.physician_type || '',
            "code": allAuthor.physician_type_code || '',
            "code_system": allAuthor.physician_type_system || "",
            "code_system_name": allAuthor.physician_type_system_name || ""
        },
        "date_time": {
            "point": {
                "date": authorDateTime || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                "extension": allAuthor.npi ? allAuthor.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": allAuthor.lname || "",
                "first": allAuthor.fname || ""
            }
        ],
        "organization": [
            {
                "identity": [
                    {
                        "root": oidFacility || "2.16.840.1.113883.4.6",
                        "extension": npiFacility || ""
                    }
                ],
                "name": [
                    encounterProvider.facility_name || ""
                ]
            }
        ]
    };
    return {
        "status": "completed",
        "author": functionalStatusAuthor,
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809000",
            "extension": pd.extension || null,
        }],
        "observation": {
            "value": {
                "name": pd.code_text !== "NULL" ? safeTrim(pd.code_text) : "",
                "code": cleanCode(pd.code) || "",
                "code_system_name": pd.code_type || "SNOMED-CT"
            },
            "identifiers": [{
                "identifier": "9a6d1bac-17d3-4195-89a4-1121bc8090ab",
                "extension": pd.extension || null,
            }],
            "date_time": {
                "point": {
                    "date": fDate(pd.date) || fDate(""),
                    "precision": "day"
                }
            },
            "status": "completed",
            "author": functionalStatusAuthor
        }
    }
}

function getDisabilityAssessment(pd) {
    if (!pd) return {};

    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};

    let disabilityAssessmentAuthor = {
        "code": {
            "name": allAuthor.physician_type || '',
            "code": allAuthor.physician_type_code || '',
            "code_system": allAuthor.physician_type_system || "",
            "code_system_name": allAuthor.physician_type_system_name || ""
        },
        "date_time": {
            "point": {
                "date": authorDateTime || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                "extension": allAuthor.npi ? allAuthor.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": allAuthor.lname || "",
                "first": allAuthor.fname || ""
            }
        ],
        "organization": [
            {
                "identity": [
                    {
                        "root": oidFacility || "2.16.840.1.113883.4.6",
                        "extension": npiFacility || ""
                    }
                ],
                "name": [
                    encounterProvider.facility_name || ""
                ]
            }
        ]
    };
    return {
        "status": "completed",
        "author": disabilityAssessmentAuthor,
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809ddd",
            "extension": pd.extension || null,
        }],
        "overall_status": pd.overall_status || "",
        "disability_questions": pd.disability_questions || "",
        "date_time": {
            "point": {
                "date": fDate(pd.date || all?.created_time_timezone) || fDate(""),
                "precision": "day"
            }
        }
    };
}

function getMentalStatus(pd) {
    if (!pd) return {};

    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};

    return {
        "value": {
            "name": pd.code_text !== "NULL" ? (pd.code_text || "") : "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || ""
        },
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809ccc",
            //"extension": pd.extension,
        }],
        "note": safeTrim(pd.description) || "",
        "date_time": {
            "low": templateDate(pd.date, "day")
        },
        "author": {
            "code": {
                "name": allAuthor.physician_type || '',
                "code": allAuthor.physician_type_code || '',
                "code_system": allAuthor.physician_type_system || "",
                "code_system_name": allAuthor.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": authorDateTime || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                    "extension": allAuthor.npi ? allAuthor.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": allAuthor.lname || "",
                    "first": allAuthor.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": oidFacility || "2.16.840.1.113883.4.6",
                            "extension": npiFacility || ""
                        }
                    ],
                    "name": [
                        encounterProvider.facility_name || ""
                    ]
                }
            ]
        }
    };
}

function getAssessments(pd) {
    if (!pd) return {};

    return {
        "description": safeTrim(pd.description) || "",
        "author": populateAuthorFromAuthorContainer(pd)
    };
}

function getHealthConcerns(pd) {
    if (!pd) return {};

    // Build linked problems (issue UUIDs) if present
    let problems = [];
    const addProblem = (uuid) => {
        if (!uuid) return;
        problems.push({"identifiers": [{"identifier": uuid}]});
    };

    const hasMany = countEntities(pd.issues?.issue_uuid) !== 0;
    if (hasMany) {
        for (let key in pd.issues.issue_uuid) {
            if (!Object.prototype.hasOwnProperty.call(pd.issues.issue_uuid, key)) continue;
            addProblem(pd.issues.issue_uuid[key]);
        }
    } else {
        addProblem(pd.issues?.issue_uuid);
    }

    // Normalize incoming concern coding
    let valueName = safeTrim(pd.code_text || pd.text || '');
    let valueCode = cleanCode(pd.code) || "";
    let valueSystemName = (pd.code_type || "").trim();
    if (!valueSystemName) {
        // default SDOH concerns to SNOMED CT if caller didn't set one
        valueSystemName = "SNOMED CT";
    } else if (valueSystemName === "SNOMED-CT") {
        valueSystemName = "SNOMED CT";
    }
    // Optional OID (the templates can work from code_system_name, but include when we can)
    let valueSystemOid = "";
    if (valueSystemName === "SNOMED CT") {
        valueSystemOid = "2.16.840.1.113883.6.96";
    } else if (valueSystemName === "LOINC") {
        valueSystemOid = "2.16.840.1.113883.6.1";
    }
    // Effective time for both the Act and nested Observation
    const lowDate = fDate(pd.date) || fDate(pd.date_formatted) || fDate(pd.effective_time?.low?.date) || fDate("");
    const date_time = {
        low: {
            date: lowDate,
            precision: "day"
        }
        // high optional — omit unless track resolution
    };

    // Compose the concern entry for section → entry → healthConcernActivityAct
    return {
        type: "act",                       // REQUIRED by planOfCareEntryLevel.healthConcernActivityAct
        text: safeTrim(pd.text || valueName),
        date_time,                    // drives <effectiveTime> in both Act and nested Observation
        value: {
            // used by nested Problem Observation's <value xsi:type="CD"...>
            name: valueName,
            code: valueCode,
            code_system: valueSystemOid,   // optional; generator can also use code_system_name
            code_system_name: valueSystemName
        },
        author: populateAuthorFromAuthorContainer(pd),
        identifiers: [{
            identifier: pd.sha_extension || "",
            extension: pd.extension || ""
        }],
        category: pd.category || undefined,
        assessment: pd.assessment || '',
        problems
    };
}

function getReferralReason(pd) {
    if (!pd) return {};

    return {
        "reason": safeTrim(pd.text) || "",
        "author": populateAuthorFromAuthorContainer(pd)
    };
}

function populateVital(pd) {
    if (!pd) return {};

    return {
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "status": "completed",
        "date_time": {
            "point": {
                "date": fDate(pd.effectivetime) || fDate(""),
                "precision": "day"
            }
        },
        // our list of vitals per organizer.
        "vital_list": [{
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_bps || ""
            }],
            "vital": {
                "name": "Blood Pressure Systolic",
                "code": "8480-6",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.bps) || pd.bps || "",
            "unit": "mm[Hg]",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_bpd || ""
            }],
            "vital": {
                "name": "Blood Pressure Diastolic",
                "code": "8462-4",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.bpd) || pd.bpd || "",
            "unit": "mm[Hg]",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_height || ""
            }],
            "vital": {
                "name": "Height",
                "code": "8302-2",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.height) || pd.height || "",
            "unit": pd.unit_height || "",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_weight || ""
            }],
            "vital": {
                "name": "Weight Measured",
                "code": "29463-7",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.weight) || "",
            "unit": pd.unit_weight || "",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_BMI || ""
            }],
            "vital": {
                "name": "BMI (Body Mass Index)",
                "code": "39156-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": [pd.BMI_status == 'Overweight' ? 'High' : pd.BMI_status == 'Underweight' ? 'Low' : 'Normal'],
            "value": parseFloat(pd.BMI) || "",
            "unit": "kg/m2",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_pulse || ""
            }],
            "vital": {
                "name": "Heart Rate",
                "code": "8867-4",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.pulse) || "",
            "unit": "/min",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.2",
                "extension": pd.extension_breath || ""
            }],
            "vital": {
                "name": "Respiratory Rate",
                "code": "9279-1",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.breath) || "",
            "unit": "/min",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.3",
                "extension": pd.extension_temperature || ""
            }],
            "vital": {
                "name": "Body Temperature",
                "code": "8310-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": Math.ceil(parseFloat(pd.temperature)) || "",
            "unit": pd.unit_temperature || "",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_oxygen_saturation || ""
            }],
            "vital": {
                "name": "O2 % BldC Oximetry",
                "code": "59408-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.oxygen_saturation) || "",
            "unit": "%",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_ped_weight_height || ""
            }],
            "vital": {
                "name": "Weight for Height Percentile",
                "code": "77606-2",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_weight_height) || "",
            "unit": "%",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_inhaled_oxygen_concentration || ""
            }],
            "vital": {
                "name": "Inhaled Oxygen Concentration",
                "code": "3150-0",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.inhaled_oxygen_concentration) || "",
            "unit": "%",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_ped_bmi || ""
            }],
            "vital": {
                "name": "BMI Percentile",
                "code": "59576-9",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_bmi) || "",
            "unit": "%",
            "author": populateAuthorFromAuthorContainer(pd),
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension || "",
                "extension": pd.extension_ped_head_circ || ""
            }],
            "vital": {
                "name": "Head Occipital-frontal Circumference Percentile",
                "code": "8289-1",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime) || fDate(""),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_head_circ) || "",
            "unit": "%",
            "author": populateAuthorFromAuthorContainer(pd),
        }
        ]
    }
}

function populateSocialHistory(pd) {
    if (!pd) return {};

    let food = all?.social_history_sdoh?.hunger_vital_signs || {};
    let occupation = all?.occupation || {};
    const patient = all?.patient || {};
    const sdohData = all?.sdoh_data || {};
    const author = pd.author || {};

    return {
        "date_time": {
            "low": templateDate(pd.date, "day")
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "code": {
            "name": pd.code || ""
        },
        "element": pd.element || "",
        "value": pd.description || "",
        "gender": patient.gender || "",
        "effective_date": {
            "point": {
                "date": fDate(pd.date) || fDate(""),
                "precision": "day"
            }
        },
        "occupation": {
            "occupation_code": occupation.occupation_code || "",
            "occupation_title": occupation.occupation_title || "",
            "start_date": fDate(occupation.start_date, true) || "",
            "industry": {
                "industry_code": occupation.industry_code || "",
                "industry_title": occupation.industry_title || "",
                "industry_start_date": fDate(occupation.industry_start_date, true) || "",
            }
        },
        "tribal_affiliation": {
            "tribal": patient.tribal || "",
            "tribal_code": patient.tribal_code || "",
            "tribal_title": patient.tribal_title || "",
        },
        "pregnancy_status": {
            "pregnancy": sdohData.pregnancy || "",
            "pregnancy_code": sdohData.pregnancy_code || "",
            "pregnancy_title": sdohData.pregnancy_title || ""
        },
        "hunger_vital_signs": {
            "assessment_date": food.assessment_date || "",
            "score": food.score || "",
            "risk_status": {
                "code": food.risk_status?.code || "",
                "code_system": food.risk_status?.code_system || "",
                "display": food.risk_status?.answer_display || "",
                "answer_code": food.risk_status?.answer_code || "",
                "answer_display": food.risk_status?.answer_display || "",
            },
            "question1": {
                "effective_date": food.assessment_date || "",
                "code": food.question1?.code || "",
                "code_system": food.question1?.code_system || "",
                "display": food.question1?.display || "",
                "answer_code": food.question1?.answer_code || "",
                "answer_display": food.question1?.answer_display || "",
            },
            "question2": {
                "effective_date": food.assessment_date || "",
                "code": food.question2?.code || "",
                "code_system": food.question2?.code_system || "",
                "display": food.question2?.display || "",
                "answer_code": food.question2?.answer_code || "",
                "answer_display": food.question2?.answer_display || "",
            },
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        },
        "gender_author": {
            "code": {
                "name": patient.author?.physician_type || '',
                "code": patient.author?.physician_type_code || '',
                "code_system": patient.author?.physician_type_system || "",
                "code_system_name": patient.author?.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(patient.author?.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": patient.author?.npi ? "2.16.840.1.113883.4.6" : (patient.author?.id || ""),
                    "extension": patient.author?.npi ? patient.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": patient.author?.lname || "",
                    "first": patient.author?.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": patient.author?.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": patient.author?.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        patient.author?.facility_name || ""
                    ]
                }
            ]
        }
    };
}

function populateImmunization(pd) {
    if (!pd) return {};

    const author = pd.author || {};

    return {
        "date_time": {
            "low": {
                "date": fDate(pd.administered_on) || fDate(""),
                "precision": "day"
            }
        },
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "status": "complete",
        "product": {
            "product": {
                "name": pd.code_text || "",
                "code": cleanCode(pd.cvx_code) || "",
                "code_system_name": "CVX"
            },
            "lot_number": "",
            "manufacturer": ""
        },
        "administration": {
            "route": {
                "name": pd.route_of_administration || "",
                "code": cleanCode(pd.route_code) || "",
                "code_system_name": "Medication Route FDA"
            }
        },
        "performer": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "name": [{
                "last": pd.lname || "",
                "first": pd.fname || ""
            }],
            "address": [{
                "street_lines": [pd.address || ""],
                "city": pd.city || "",
                "state": pd.state || "",
                "zip": pd.zip || "",
                "country": "US"
            }],
            "organization": [{
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": npiFacility || ""
                }],
                "name": [pd.facility_name || ""]
            }]
        },
        "instructions": {
            "code": {
                "name": "immunization education",
                "code": "171044003",
                "code_system_name": "SNOMED CT"
            },
            "free_text": "Needs Attention for more data."
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        }
    };
}

function populatePayer(pd) {
    if (!pd || !pd.payer) return [];

    const safeArray = (v) => Array.isArray(v) ? v : [v];
    return safeArray(pd.payer).map((payer) => {
        if (!payer) return {};

        return {
            identifiers: [{
                identifier: payer.identifiers?.identifier || ""
            }],
            policy: {
                identifiers: [{
                    identifier: payer.policy?.identifiers?.identifier || "",
                    extension: payer.policy?.identifiers?.extension || ""
                }],
                code: {
                    code: payer.policy?.code?.code || "72",
                    code_system: payer.policy?.code?.code_system || "",
                    code_system_name: payer.policy?.code?.code_system_name || "",
                    name: payer.policy?.code?.name || "Self"
                },
                insurance: {
                    code: {
                        code: payer.policy?.insurance?.code?.code || "PAYOR",
                        code_system: payer.policy?.insurance?.code?.code_system || "2.16.840.1.113883.5.110",
                        code_system_name: payer.policy?.insurance?.code?.code_system_name || "HL7 RoleCode",
                        name: payer.policy?.insurance?.code?.name || "Payor"
                    },
                    performer: {
                        identifiers: [{
                            identifier: payer.policy?.insurance?.performer?.identifiers?.identifier || ""
                        }],
                        address: [{
                            street_lines: payer.policy?.insurance?.performer?.address?.street_lines || "",
                            city: payer.policy?.insurance?.performer?.address?.city || "",
                            state: payer.policy?.insurance?.performer?.address?.state || "",
                            zip: payer.policy?.insurance?.performer?.address?.zip || "",
                            country: payer.policy?.insurance?.performer?.address?.country || "",
                            use: payer.policy?.insurance?.performer?.address?.use || ""
                        }],
                        phone: [{
                            number: payer.policy?.insurance?.performer?.phone?.number || "",
                            type: payer.policy?.insurance?.performer?.phone?.type || ""
                        }],
                        organization: [{
                            name: [payer.policy?.insurance?.performer?.organization?.name || ""],
                            address: [{
                                street_lines: payer.policy?.insurance?.performer?.organization?.address?.street_lines || "",
                                city: payer.policy?.insurance?.performer?.organization?.address?.city || "",
                                state: payer.policy?.insurance?.performer?.organization?.address?.state || "",
                                zip: payer.policy?.insurance?.performer?.organization?.address?.zip || "",
                                country: payer.policy?.insurance?.performer?.organization?.address?.country || "",
                                use: payer.policy?.insurance?.performer?.organization?.address?.use || ""
                            }],
                            phone: [{
                                number: payer.policy?.insurance?.performer?.organization?.phone?.number || "",
                                type: payer.policy?.insurance?.performer?.organization?.phone?.type || ""
                            }]
                        }],
                        code: [{
                            code: payer.policy?.insurance?.code?.code || "PAYOR",
                            code_system: payer.policy?.insurance?.code?.code_system || "2.16.840.1.113883.5.110",
                            code_system_name: payer.policy?.insurance?.code?.code_system_name || "HL7 RoleCode",
                            name: payer.policy?.insurance?.code?.name || "Payor"
                        }]
                    }
                }
            },
            guarantor: {
                code: {
                    code: "GUAR",
                    code_system_name: "HL7 Role"
                },
                identifiers: [{
                    identifier: payer.guarantor?.identifiers?.identifier || ""
                }],
                name: [{
                    prefix: payer.guarantor?.name?.prefix || "",
                    first: payer.guarantor?.name?.first || "",
                    middle: [payer.guarantor?.name?.middle || ""],
                    last: payer.guarantor?.name?.last || ""
                }],
                address: [{
                    street_lines: payer.guarantor?.address?.street_lines || "",
                    city: payer.guarantor?.address?.city || "",
                    state: payer.guarantor?.address?.state || "",
                    zip: payer.guarantor?.address?.zip || "",
                    country: payer.guarantor?.address?.country || "",
                    use: payer.guarantor?.address?.use || ""
                }],
                phone: [{
                    number: payer.guarantor?.phone?.number || "",
                    type: payer.guarantor?.phone?.type || ""
                }]
            },
            participant: {
                "date_time": {
                    "low": {
                        "date": fDate(payer.participant?.time_low, true) || "",
                        "precision": "day"
                    },
                    "high": {
                        "date": fDate(payer.participant?.time_high, true) || "",
                        "precision": "day"
                    }
                },
                code: {
                    name: payer.participant?.code?.name || "Self",
                    code: payer.participant?.code?.code || "SELF",
                    code_system: payer.participant?.code?.code_system || "",
                    code_system_name: payer.participant?.code?.code_system_name || "HL7 Role"
                },
                performer: {
                    identifiers: [{
                        identifier: payer.participant?.performer?.identifiers?.identifier || "",
                        extension: payer.participant?.performer?.identifiers?.extension || ""
                    }],
                    address: [{
                        street_lines: payer.participant?.performer?.address?.street_lines || "",
                        city: payer.participant?.performer?.address?.city || "",
                        state: payer.participant?.performer?.address?.state || "",
                        zip: payer.participant?.performer?.address?.zip || "",
                        country: payer.participant?.performer?.address?.country || "",
                        use: payer.participant?.performer?.address?.use || ""
                    }],
                    code: [{
                        name: "Self",
                        code: "SELF",
                        code_system_name: "HL7 Role"
                    }]
                },
                name: [{
                    prefix: payer.participant?.name?.prefix || "",
                    first: payer.participant?.name?.first || "",
                    middle: [payer.participant?.name?.middle || ""],
                    last: payer.participant?.name?.last || ""
                }],
                birthTime: all?.patient?.dob || ""
            },
            policy_holder: {
                performer: {
                    identifiers: [{
                        identifier: payer.policy_holder?.performer?.identifiers?.identifier || "",
                        extension: payer.policy_holder?.performer?.identifiers?.extension || ""
                    }],
                    address: [{
                        street_lines: payer.policy_holder?.performer?.address?.street_lines || "",
                        city: payer.policy_holder?.performer?.address?.city || "",
                        state: payer.policy_holder?.performer?.address?.state || "",
                        zip: payer.policy_holder?.performer?.address?.zip || "",
                        country: payer.policy_holder?.performer?.address?.country || "",
                        use: payer.policy_holder?.performer?.address?.use || ""
                    }]
                }
            },
            authorization: {
                identifiers: [{
                    identifier: payer.authorization?.identifiers?.identifier || ""
                }],
                authorization_code: payer.authorization?.authorization_code || "72",
                plan_name: payer.policy.plan_name || ""
            }
        };
    });
}

function populateNote(pd) {
    if (!pd) return {};

    return {
        "date_time": {
            "point": {
                "date": fDate(pd.date) || fDate(""),
                "precision": "day"
            }
        },
        "translations": {
            code_system: "2.16.840.1.113883.6.1",
            code_system_name: "LOINC",
            code: cleanCode(pd.code) || "",
            name: pd.code_text || ""
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "note": safeTrim(pd.description) || "",
    };
}

function populateParticipant(participant) {
    if (!participant) return {};

    if (!participant.code) {
        cleanCode(participant.organization_taxonomy);
    }
    return {
        "name": {
            "prefix": participant.prefix || "",
            "suffix": participant.suffix || "",
            "middle": [participant.mname ?? ""],
            "last": participant.lname || "",
            "first": participant.fname || ""
        },
        "typeCode": participant.type || "",
        "classCode": participant.class_code || "ASSIGNED",
        "code": {
            "name": participant.organization_taxonomy_description || participant.code || "",
            "code": participant.organization_taxonomy || participant.code || "",
            "code_system": "2.16.840.1.113883.1.11.19563",
            "code_system_name": "Personal Relationship Role Type Value Set"
        },
        "identifiers": [{
            "identifier": participant.organization_npi ? "2.16.840.1.113883.4.6" : (participant.organization_id || ""),
            "extension": participant.organization_npi ? participant.organization_npi : (participant.organization_ext || 'NI')
        }],
        "date_time": {
            "point": {
                "date": participant.date_time,
                "precision": "tz"
            }
        },
        "phone": [
            {
                "number": participant.phonew1 || "",
                "type": "WP"
            }
        ],
        "address": [
            {
                "street_lines": [
                    participant.street || ""
                ],
                "city": participant.city || "",
                "state": participant.state || "",
                "zip": participant.postalCode || "",
                "country": participant.country || "US",
                "use": participant.address_use || "WP"
            }
        ],
    }
}

function populateAdvanceDirective(pd) {
    if (!pd) return {};

    const author = pd.author || {};

    return {
        "identifiers": [{
            "identifier": pd.sha_extension || "",
            "extension": pd.extension || ""
        }],
        "date_time": {
            "low": {
                "date": fDate(pd.effective_date) || fDate(""),
                "precision": "day"
            }
        },
        "type": pd.type || "Advance Directive",
        "status": pd.status || "active",
        "location": pd.location || "Electronic Health Record",
        "document_reference": pd.uuid || "",
        // Embedded observation data for the organizer component
        "observation": {
            "code": pd.observation_code || "",
            "code_system": pd.observation_code_system || "",
            "display": pd.observation_display || "",
            "value_code": pd.observation_value_code || "LA33-6",
            "value_display": pd.observation_value_display || "Yes",
            "effective_date": fDate(pd.effective_date) || fDate("")
        },
        "author": {
            "code": {
                "name": author.physician_type || '',
                "code": author.physician_type_code || '',
                "code_system": author.physician_type_system || "",
                "code_system_name": author.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": fDate(author.time) || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": author.npi ? "2.16.840.1.113883.4.6" : (author.id || ""),
                    "extension": author.npi ? author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": author.lname || "",
                    "first": author.fname || ""
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        author.facility_name || ""
                    ]
                }
            ]
        }
    };
}

function populateHeader(pd) {
    if (!pd) return {};

    // default doc type ToC CCD
    let name = "Summarization of Episode Note";
    let docCode = "34133-9";
    let docOid = "2.16.840.1.113883.10.20.22.1.2";
    if (pd.doc_type === 'referral') {
        name = "Referral Note";
        docCode = "57133-1";
        docOid = "2.16.840.1.113883.10.20.22.1.14";
    }

    if (pd.doc_type == 'unstructured') {
        name = "Patient Documents";
        docCode = "34133-9";
        docOid = "2.16.840.1.113883.10.20.22.1.10";
    }

    const allAuthor = all?.author || {};
    const encounterProvider = all?.encounter_provider || {};
    const custodian = pd.custodian || {};
    const informationRecipient = pd.information_recipient || {};

    const head = {
        "identifiers": [
            {
                "identifier": oidFacility || "",
                "extension": "123456"
            }
        ],
        "code": {
            "name": name,
            "code": docCode,
            "code_system_name": "LOINC"
        },
        "template": {
            "root": docOid,
            "extension": "2015-08-01"
        },
        "title": name,
        "date_time": {
            "point": {
                "date": fDate(pd.created_time_timezone) || fDate(""),
                "precision": "tz"
            }
        },
        "author": {
            "code": {
                "name": allAuthor.physician_type || '',
                "code": allAuthor.physician_type_code || '',
                "code_system": allAuthor.physician_type_system || "",
                "code_system_name": allAuthor.physician_type_system_name || ""
            },
            "date_time": {
                "point": {
                    "date": authorDateTime || fDate(""),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                    "extension": allAuthor.npi ? allAuthor.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": allAuthor.lname || "",
                    "first": allAuthor.fname || ""
                }
            ],
            "address": [
                {
                    "street_lines": [
                        allAuthor.streetAddressLine || ""
                    ],
                    "city": allAuthor.city || "",
                    "state": allAuthor.state || "",
                    "zip": allAuthor.postalCode || "",
                    "country": allAuthor.country || "US",
                    "use": "work place"
                }
            ],
            "phone": [
                {
                    "number": allAuthor.telecom || "",
                    "type": "WP"
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": oidFacility || "2.16.840.1.113883.4.6",
                            "extension": npiFacility || ""
                        }
                    ],
                    "name": [
                        encounterProvider.facility_name || ""
                    ],
                    "address": [
                        {
                            "street_lines": [
                                encounterProvider.facility_street || ""
                            ],
                            "city": encounterProvider.facility_city || "",
                            "state": encounterProvider.facility_state || "",
                            "zip": encounterProvider.facility_postal_code || "",
                            "country": encounterProvider.facility_country_code || "US",
                            "use": "work place"
                        }
                    ],
                    "phone": [
                        {
                            "number": encounterProvider.facility_phone || "",
                            "type": "work primary"
                        }
                    ]
                }
            ]
        },
        "custodian": {
            "identity": [
                {
                    "root": "2.16.840.1.113883.4.6",
                    "extension": npiFacility || ""
                }
            ],
            "name": [
                custodian.organization || custodian.name || ""
            ],
            "address": [
                {
                    "street_lines": [
                        custodian.streetAddressLine || ""
                    ],
                    "city": custodian.city || "",
                    "state": custodian.state || "",
                    "zip": custodian.postalCode || "",
                    "country": custodian.country || "US"
                }
            ],
            "phone": [
                {
                    "number": custodian.telecom || "",
                    "type": "work primary"
                }
            ]
        },
        "information_recipient": {
            "name": {
                "prefix": informationRecipient.prefix || "",
                "suffix": informationRecipient.suffix || "",
                "middle": [informationRecipient.mname ?? ""],
                "last": informationRecipient.lname || "",
                "first": informationRecipient.fname || ""
            },
            "organization": {
                "name": informationRecipient.organization || "org"
            },
        }
    };

    let participants = [];

// Helper function to safely convert to array
    const safeToArray = (value) => {
        if (!value) return [];
        if (Array.isArray(value)) return value;
        return [value];
    };

// Safely merge participant arrays
    let allParticipants = [
        ...safeToArray(pd.document_participants?.participant),
        ...safeToArray(pd.patient?.related_persons?.participant),
    ];

    let count = 0;
    try {
        count = countEntities(allParticipants);
    } catch (e) {
        count = 0;
    }

    if (count === 1) {
        // Single participant
        participants = [populateParticipant(allParticipants[0])];
    } else if (count > 1) {
        // Multiple participants - handle both array and object structures
        if (Array.isArray(allParticipants)) {
            participants = allParticipants.filter(pcpt => pcpt && pcpt.type).map(pcpt => populateParticipant(pcpt));
        } else {
            // Handle object with numeric keys (xml2js structure)
            participants = Object.values(allParticipants).filter(pcpt => pcpt && pcpt.type).map(pcpt => populateParticipant(pcpt));
        }
    }

    if (participants.length) {
        head.participants = participants;
    }

    if (countEntities(all?.encounter_list?.encounter) === 1) {
        let primary_care_provider = pd.primary_care_provider || {provider: {}};
        const primaryDiagnosis = pd.primary_diagnosis || {};

        head.component_of = {
            "identifiers": [
                {
                    "identifier": oidFacility || "",
                    "extension": "PT-" + (pd.patient?.id || "")
                }
            ],
            "code": {
                "name": primaryDiagnosis.text || "",
                "code": primaryDiagnosis.code || "",
                "code_system_name": primaryDiagnosis.code_type || ""
            },
            "date_time": {
                "low": {
                    "date": primaryDiagnosis.encounter_date || "",
                    "precision": "tz"
                },
                "high": {
                    "date": primaryDiagnosis.encounter_end_date || "",
                    "precision": "tz"
                }
            },
            "responsible_party": {
                "root": oidFacility,
                "name": {
                    "last": pd.author?.lname || "",
                    "first": pd.author?.fname || ""
                },
            },
            "encounter_participant": {
                "root": oidFacility,
                "name": {
                    "last": primary_care_provider.provider?.lname || "",
                    "first": primary_care_provider.provider?.fname || ""
                },
                "address": [
                    {
                        "street_lines": [
                            pd.encounter_provider?.facility_street || ""
                        ],
                        "city": pd.encounter_provider?.facility_city || "",
                        "state": pd.encounter_provider?.facility_state || "",
                        "zip": pd.encounter_provider?.facility_postal_code || "",
                        "country": pd.encounter_provider?.facility_country_code || "US",
                        "use": "work place"
                    }
                ],
                "phone": [
                    {
                        "number": pd.encounter_provider?.facility_phone || "",
                        "type": "work primary"
                    }
                ]
            }
        }
    }

    return head;
}

function getMeta(pd) {
    if (!pd) return {};

    let meta = {};
    meta = {
        "type": pd.doc_type || "",
        "identifiers": [
            {
                "identifier": oidFacility || "NI",
                "extension": "OE-DOC-0001"
            }
        ],
        "confidentiality": "Normal",
        "set_id": {
            "identifier": oidFacility || "NI",
            "extension": "sOE-DOC-0001"
        }
    }
    return meta;
}

/**
 / * function generateCcda
 /* The main document builder
 /* pd array the xml parsed array of data sent from CCM.
 */
function generateCcda(pd) {
    if (!pd) return "";

    let doc = {};
    let data = {};
    let count = 0;
    let many = [];
    let theone = {};
    all = pd;
    let primary_care_provider = all?.primary_care_provider || {};
    npiProvider = primary_care_provider.provider ? primary_care_provider.provider.npi : "NI";
    oidFacility = all?.encounter_provider?.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
    npiFacility = getNpiFacility(pd, false);
    webRoot = all?.serverRoot || "";
    documentLocation = all?.document_location || "";

    authorDateTime = pd.created_time_timezone || "";
    if (pd.author?.time && pd.author.time.length > 7) {
        authorDateTime = pd.author.time;
    } else if (all?.encounter_list?.encounter) {
        if (countEntities(all.encounter_list.encounter) === 1) {
            authorDateTime = all.encounter_list.encounter.date || "";
        } else {
            authorDateTime = all.encounter_list.encounter[0]?.date || "";
        }
    }

    authorDateTime = fDate(authorDateTime);
    // Demographics
    let demographic = populateDemographics(pd, npiFacility);
// This populates documentationOf. We are using providerOrganization also.
    if (pd.care_team) {
        Object.assign(demographic, populateProviders(pd));
    }
    data.demographics = Object.assign(demographic);
// Encounters
    let encs = [];
    let enc = {};
    encs.encounters = [];
    try {
        count = countEntities(pd.encounter_list?.encounter);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.encounter_list.encounter) {
            enc[i] = populateEncounter(pd.encounter_list.encounter[i]);
            encs.encounters.push(enc[i]);
        }
    } else if (count !== 0) {
        enc = populateEncounter(pd.encounter_list.encounter);
        encs.encounters.push(enc);
    }
    if (count !== 0) {
        data.encounters = Object.assign(encs.encounters);
    }
// vitals
    let vitals = [];
    let vital = {};
    vitals.vitals = [];
    try {
        count = countEntities(pd.history_physical?.vitals_list?.vitals);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.history_physical.vitals_list.vitals) {
            vitals[i] = populateVital(pd.history_physical.vitals_list.vitals[i]);
            vitals.vitals.push(vitals[i]);
        }
    } else if (count !== 0) {
        vital = populateVital(pd.history_physical.vitals_list.vitals);
        vitals.vitals.push(vital);
    }
    if (count !== 0) {
        data.vitals = Object.assign(vitals.vitals);
    }
// Medications
    let meds = [];
    let m = {};
    meds.medications = [];
    try {
        count = countEntities(pd.medications?.medication);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.medications.medication) {
            m[i] = populateMedication(pd.medications.medication[i]);
            meds.medications.push(m[i]);
        }
    } else if (count !== 0) {
        m = populateMedication(pd.medications.medication);
        meds.medications.push(m);
    }
    if (count !== 0) {
        data.medications = Object.assign(meds.medications);
    }
// Allergies
    let allergies = [];
    let allergy = {};
    allergies.allergies = [];
    try {
        count = countEntities(pd.allergies?.allergy);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.allergies.allergy) {
            allergy[i] = populateAllergy(pd.allergies.allergy[i]);
            allergies.allergies.push(allergy[i]);
        }
    } else if (count <= 1) {
        allergy = populateAllergy(pd.allergies?.allergy);
        allergies.allergies.push(allergy);
        count = 1;
    }
    if (count !== 0) {
        data.allergies = Object.assign(allergies.allergies);
    }
// Problems
    let problems = [];
    let problem = {};
    problems.problems = [];
    try {
        count = countEntities(pd.problem_lists?.problem);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.problem_lists.problem) {
            problem[i] = populateProblem(pd.problem_lists.problem[i], pd);
            problems.problems.push(problem[i]);
        }
    } else if (count !== 0) {
        problem = populateProblem(pd.problem_lists.problem);
        problems.problems.push(problem);
    }
    if (count !== 0) {
        data.problems = Object.assign(problems.problems);
    }
// Procedures
    many = [];
    theone = {};
    many.procedures = [];
    try {
        count = countEntities(pd.procedures?.procedure);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.procedures.procedure) {
            theone[i] = populateProcedure(pd.procedures.procedure[i]);
            many.procedures.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = populateProcedure(pd.procedures.procedure);
        many.procedures.push(theone);
    }
    if (count !== 0) {
        data.procedures = Object.assign(many.procedures);
    }

// Advance Directives - Single organizer with multiple component observations
    many = [];
    theone = {};
    count = 0;

    try {
        count = countEntities(pd.advance_directives?.directive);
    } catch (e) {
        count = 0;
    }
    if (count !== 0) {
        // Create a single organizer object containing all directives as components
        let organizerData = {
            "identifiers": [{
                "identifier": "advance-directives-organizer",
                "extension": "advance-directives"
            }],
            "date_time": {
                "low": {
                    "date": fDate("") || fDate(""),
                    "precision": "day"
                }
            },
            "author": populateAuthorFromAuthorContainer(pd.advance_directives?.directive?.[0] || pd.advance_directives?.directive || {}),
            "directives": [] // This will hold the component observations
        };

        // Convert individual directives to component observations
        if (count > 1) {
            for (let i in pd.advance_directives.directive) {
                let directive = pd.advance_directives.directive[i];
                organizerData.directives.push({
                    "identifiers": directive.identifiers || [],
                    "document_reference": directive.document_reference || "",
                    "location": directive.location || "",
                    "observation_code": directive.observation?.code || "",
                    "observation_code_system": directive.observation?.code_system || "",
                    "observation_display": directive.observation?.display || "",
                    "observation_value_code": directive.observation?.value_code || "LA33-6",
                    "observation_value_display": directive.observation?.value_display || "Yes",
                    "effective_date": directive.observation?.effective_date || directive?.effective_date || ""
                });
            }
        } else {
            let directive = pd.advance_directives.directive;
            organizerData.directives.push({
                "identifiers": directive.identifiers || [],
                "document_reference": directive.document_reference || "",
                "location": directive.location || "",
                "observation_code": directive.observation?.code || "",
                "observation_code_system": directive.observation?.code_system || "",
                "observation_display": directive.observation?.display || "",
                "observation_value_code": directive.observation?.value_code || "LA33-6",
                "observation_value_display": directive.observation?.value_display || "Yes",
                "effective_date": directive.observation?.effective_date || directive?.effective_date || ""
            });
        }

        // Pass the single organizer object, not an array
        data.advance_directives = organizerData;
    }

// Medical Devices
    many = [];
    theone = {};
    many.medical_devices = [];
    try {
        count = countEntities(pd.medical_devices?.device);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.medical_devices.device) {
            theone[i] = populateMedicalDevice(pd.medical_devices.device[i]);
            many.medical_devices.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = populateMedicalDevice(pd.medical_devices.device);
        many.medical_devices.push(theone);
    }
    if (count !== 0) {
        data.medical_devices = Object.assign(many.medical_devices);
    }
// Results
    if (pd.results) {
        const resultSet = getResultSet(pd.results, pd);
        if (resultSet && resultSet.results) {
            data.results = Object.assign(resultSet.results);
        }
    }

// Referral TODO sjp I'm not happy with this.
    // different referral sources. 1st is dynamic with doc gen from CCM.
    // 2nd is the latest referral from transactions.
    if (pd.referral_reason?.[0]?.text !== "") {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[0], pd));
    } else if (pd.referral_reason?.[1]?.text !== "" && typeof pd.referral_reason[1].text !== 'undefined') {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[1], pd));
    } else {
        data.referral_reason = {}; // leave as empty so we can get our null flavor section.
    }

// Health Concerns
    many = [];
    theone = {};
    count = 0;
    many.health_concerns = [];
    try {
        count = countEntities(pd.health_concerns?.concern);
    } catch (e) {
        count = 0;
    }

    if (count > 1) {
        for (let i in pd.health_concerns.concern) {
            if (!Object.prototype.hasOwnProperty.call(pd.health_concerns.concern, i)) continue;
            theone[i] = getHealthConcerns(pd.health_concerns.concern[i]);
            many.health_concerns.push(theone[i]);
        }
    } else if (count === 1) {
        theone = getHealthConcerns(pd.health_concerns.concern);
        many.health_concerns.push(theone);
    }
    if (many.health_concerns.length) {
        data.health_concerns = {concern: many.health_concerns};
    } else {
        // Leave as an empty section that templates can null-flavor
        data.health_concerns = {type: "act"};
    }

// Immunizations
    many = [];
    theone = {};
    many.immunizations = [];
    try {
        count = countEntities(pd.immunizations?.immunization);
    } catch (e) {
        count = 0;
    }
    if (count > 1) {
        for (let i in pd.immunizations.immunization) {
            theone[i] = populateImmunization(pd.immunizations.immunization[i]);
            many.immunizations.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = populateImmunization(pd.immunizations.immunization);
        many.immunizations.push(theone);
    }
    if (count !== 0) {
        data.immunizations = Object.assign(many.immunizations);
    }
// Plan of Care
    many = [];
    theone = {};
    many.plan_of_care = [];
    try {
        count = countEntities(pd.planofcare?.item);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.planofcare.item) {
            if (cleanCode(pd.planofcare.item[i]?.date) === '') {
                i--;
                continue;
            }
            theone[i] = getPlanOfCare(pd.planofcare.item[i]);
            if (theone[i]) {
                many.plan_of_care.push(theone[i]);
            }
        }
    } else if (count !== 0) {
        theone = getPlanOfCare(pd.planofcare.item);
        if (theone) {
            many.plan_of_care.push(theone);
        }
    }
    if (count !== 0) {
        data.plan_of_care = Object.assign(many.plan_of_care);
    }
// Goals
    many = [];
    theone = {};
    many.goals = [];
    try {
        count = countEntities(pd.goals?.item);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.goals.item) {
            theone[i] = getGoals(pd.goals.item[i]);
            many.goals.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = getGoals(pd.goals.item);
        many.goals.push(theone);
    }
    if (count !== 0) {
        data.goals = Object.assign(many.goals);
    }
// Assessments.
    many = [];
    theone = {};
    many.clinicalNoteAssessments = [];
    try {
        count = countEntities(pd.clinical_notes?.evaluation_note);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.clinical_notes.evaluation_note) {
            theone[i] = getAssessments(pd.clinical_notes.evaluation_note[i]);
            many.clinicalNoteAssessments.push(theone[i]);
            break; // for now only one assessment. @todo concat notes to one.
        }
    } else if (count !== 0) {
        theone = getAssessments(pd.clinical_notes.evaluation_note);
        many.clinicalNoteAssessments.push(theone);
    }
    if (count !== 0) {
        data.clinicalNoteAssessments = Object.assign(many.clinicalNoteAssessments);
    }
// Functional Status.
    many = [];
    theone = {};
    many.functional_status = [];
    try {
        count = countEntities(pd.functional_status?.item);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.functional_status.item) {
            theone[i] = getFunctionalStatus(pd.functional_status.item[i]);
            many.functional_status.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = getFunctionalStatus(pd.functional_status.item);
        many.functional_status.push(theone);
    }
    if (count !== 0) {
        data.functional_status = Object.assign(many.functional_status);
    }

// Add disability status as a separate data key for the section template
    if (all?.sdoh_data?.disability_assessment?.overall_status) {
        const allAuthor = all.author || {};
        const encounterProvider = all.encounter_provider || {};

        data.disability_status = {
            "overall_status": all.sdoh_data.disability_assessment.overall_status || "",
            "disability_questions": all.sdoh_data.disability_assessment.disability_questions || "",
            "date_time": {
                "point": {
                    "date": fDate(all.created_time_timezone) || fDate(""),
                    "precision": "day"
                }
            },
            "author": {
                "code": {
                    "name": allAuthor.physician_type || '',
                    "code": allAuthor.physician_type_code || '',
                    "code_system": allAuthor.physician_type_system || "",
                    "code_system_name": allAuthor.physician_type_system_name || ""
                },
                "date_time": {
                    "point": {
                        "date": authorDateTime || fDate(""),
                        "precision": "tz"
                    }
                },
                "identifiers": [
                    {
                        "identifier": allAuthor.npi ? "2.16.840.1.113883.4.6" : (allAuthor.id || ""),
                        "extension": allAuthor.npi ? allAuthor.npi : 'NI'
                    }
                ],
                "name": [
                    {
                        "last": allAuthor.lname || "",
                        "first": allAuthor.fname || ""
                    }
                ],
                "organization": [
                    {
                        "identity": [
                            {
                                "root": oidFacility || "2.16.840.1.113883.4.6",
                                "extension": npiFacility || ""
                            }
                        ],
                        "name": [
                            encounterProvider.facility_name || ""
                        ]
                    }
                ]
            }
        };
    }
// Mental Status.
    many = [];
    theone = {};
    many.mental_status = [];
    try {
        count = countEntities(pd.mental_status?.item);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.mental_status.item) {
            theone[i] = getMentalStatus(pd.mental_status.item[i]);
            many.mental_status.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = getMentalStatus(pd.mental_status.item);
        many.mental_status.push(theone);
    }
    if (count !== 0) {
        data.mental_status = Object.assign(many.mental_status);
    }

// Social History
    many = [];
    theone = {};
    many.social_history = [];
    try {
        count = countEntities(pd.history_physical?.social_history?.history_element);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.history_physical.social_history.history_element) {
            if (i > 0) break;
            theone[i] = populateSocialHistory(pd.history_physical.social_history.history_element[i]);
            many.social_history.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = populateSocialHistory(pd.history_physical.social_history.history_element);
        many.social_history.push(theone);
    }
    if (count !== 0) {
        data.social_history = Object.assign(many.social_history);
    }

// Notes
    for (let currentNote in pd.clinical_notes || {}) {
        many = [];
        theone = {};
        const currentNoteData = pd.clinical_notes[currentNote];
        if (!currentNoteData) continue;

        switch (currentNoteData.clinical_notes_type) {
            case 'evaluation_note':
                continue;
            case 'progress_note':
                break;
            case 'history_physical':
                currentNoteData.code_text = "History and Physical";
                break;
            case 'nurse_note':
                break;
            case 'general_note':
                break;
            case 'discharge_summary':
                break;
            case 'procedure_note':
                break;
            case 'consultation_note':
                break;
            case 'imaging_narrative':
                break;
            case 'laboratory_report_narrative':
                break;
            case 'pathology_report_narrative':
                break;
            default:
                continue;
        }
        try {
            count = countEntities(currentNoteData);
        } catch (e) {
            count = 0
        }
        if (count > 1) {
            for (let i in currentNoteData) {
                theone[i] = populateNote(currentNoteData);
                many.push(theone[i]);
            }
        } else if (count !== 0) {
            theone = populateNote(currentNoteData);
            many.push(theone);
        }
        if (count !== 0) {
            data[currentNote] = Object.assign(many);
        }
    }
// Care Team and members
    if (pd.care_team?.is_active == 'active') {
        data.care_team = Object.assign(populateCareTeamMembers(pd));
    }
// Payer
    if (pd.payers && typeof pd.payers === 'object' && Object.keys(pd.payers).length > 0) {
        const payers = populatePayer(pd.payers);
        data.payers = Array.isArray(payers) && payers.length > 0 ? payers : [];
    }

// Advance Directives - Single organizer with multiple component observations
    many = [];
    theone = {};
    count = 0;

    try {
        count = countEntities(pd.advance_directives?.directive);
    } catch (e) {
        count = 0;
    }

    if (count !== 0) {
        // Create a single organizer object containing all directives as components
        let organizerData = {
            "identifiers": [{
                "identifier": "advance-directives-organizer",
                "extension": "advance-directives"
            }],
            "date_time": {
                "low": {
                    "date": fDate("") || fDate(""),
                    "precision": "day"
                }
            },
            "author": populateAuthorFromAuthorContainer(pd.advance_directives?.directive?.[0] || pd.advance_directives?.directive || {}),
            "directives": [] // This will hold the component observations
        };

        // Convert individual directives to component observations
        if (count > 1) {
            for (let i in pd.advance_directives.directive) {
                let directive = pd.advance_directives.directive[i];
                organizerData.directives.push({
                    "identifiers": [{
                        "identifier": directive.sha_extension || "",
                        "extension": directive.extension || ""
                    }],
                    "document_reference": directive.document_reference || directive.uuid || "",
                    "location": directive.location || "",
                    "observation_code": directive.observation?.code || "",
                    "observation_code_system": directive.observation?.code_system || "",
                    "observation_display": directive.observation?.display || "",
                    "observation_value_code": directive.observation?.value_code || "LA33-6",
                    "observation_value_display": directive.observation?.value_display || "Yes",
                    "effective_date": fDate(directive.observation?.effective_date || directive?.effective_date) || fDate(""),
                    "type": directive.type || "",
                    "status": directive.status || "active",
                    "author_name": (directive.author?.fname || "") + " " + (directive.author?.lname || "")
                });
            }
        } else {
            let directive = pd.advance_directives.directive;
            organizerData.directives.push({
                "identifiers": [{
                    "identifier": directive.sha_extension || "",
                    "extension": directive.extension || ""
                }],
                "document_reference": directive.document_reference || directive.uuid || "",
                "location": directive.location || "",
                "observation_code": directive.observation?.code || "",
                "observation_code_system": directive.observation?.code_system || "",
                "observation_display": directive.observation?.display || "",
                "observation_value_code": directive.observation?.value_code || "LA33-6",
                "observation_value_display": directive.observation?.value_display || "Yes",
                "effective_date": fDate(directive.observation?.effective_date || directive?.effective_date) || fDate("")
            });
        }

        // Pass the single organizer object, not an array
        data.advance_directives = organizerData;
    }

    // sections data objects
    doc.data = Object.assign(data);
    // document meta data and header objects
    let meta = getMeta(pd);
    let header = populateHeader(pd);
    meta.ccda_header = Object.assign(header);
    doc.meta = Object.assign(meta);

    if (pd.timezone_local_offset) {
        populateTimezones(doc, pd.timezone_local_offset, 0);
    }
    // build to cda
    let xml = bbg.generateCCD(doc);

    /* Debug */
    if (enableDebug === true) {
        let place = documentLocation + "/documents/temp/";
        if (fs.existsSync(place)) {
            fs.writeFile(place + "ccda.json", JSON.stringify(all, null, 4), function (err) {
                if (err) {
                    return console.log(err);
                }
            });
            fs.writeFile(place + "ccda.xml", xml, function (err) {
                if (err) {
                    return console.log(err);
                }
            });
        }
    }

    return xml;
}

let unstructuredTemplate = null;

function generateUnstructured(pd) {
    if (!pd) return "";

    let doc = {};
    let data = {};
    let count = 0;
    let many = [];
    let theone = {};
    // include unstructured document type oid in header
    pd.doc_type = 'unstructured';
    all = pd;
    let primary_care_provider = all?.primary_care_provider || {};
    npiProvider = primary_care_provider.provider ? primary_care_provider.provider.npi : "NI";
    oidFacility = all?.encounter_provider?.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
    npiFacility = getNpiFacility(pd, true);
    webRoot = all?.serverRoot || "";
    documentLocation = all?.document_location || "";
    authorDateTime = pd.created_time_timezone || "";
    if (pd.author?.time && pd.author.time.length > 7) {
        authorDateTime = pd.author.time;
    } else if (all?.encounter_list?.encounter) {
        if (countEntities(all.encounter_list.encounter) === 1) {
            authorDateTime = all.encounter_list.encounter.date || "";
        } else {
            authorDateTime = all.encounter_list.encounter[0]?.date || "";
        }
    }
    authorDateTime = fDate(authorDateTime);
// Demographics is needed in unstructured
    let demographic = populateDemographics(pd, npiFacility);
    data.demographics = Object.assign(demographic);

    if (pd.primary_care_provider) {
        Object.assign(demographic, populateProviders(pd));
    }
    doc.data = Object.assign(data);

    // document meta data and header objects
    let meta = getMeta(pd);
    let header = populateHeader(pd);
    meta.ccda_header = Object.assign(header);
    doc.meta = Object.assign(meta);

    // set TZ offset for moment
    if (pd.timezone_local_offset) {
        populateTimezones(doc, pd.timezone_local_offset, 0);
    }
    // build to cda
    let xml = bbg.generateCCD(doc);
    if (unstructuredTemplate) {
        unstructuredTemplate = unstructuredTemplate.trim();
        xml = xml.replace(/<\/ClinicalDocument>/g, unstructuredTemplate);
        xml += "</ClinicalDocument>" + "\n";
    }

    /* Debug */
    if (enableDebug === true) {
        let place = documentLocation + "/documents/temp/";
        if (fs.existsSync(place)) {
            fs.writeFile(place + "unstructured.xml", xml, function (err) {
                if (err) {
                    return console.log(err);
                }
            });
        }
    }

    return xml;
}

function processConnection(connection) {
    conn = connection; // make it global
    let remoteAddress = conn.remoteAddress + ':' + conn.remotePort;
    conn.setEncoding('utf8');
    //console.log('server remote address ', remoteAddress);
    let xml_complete = "";

    async function eventData(xml) {
        let xml_complete = xml.toString();
        // ensure we have an array start and end
        if (xml_complete.match(/^<CCDA/g) && xml_complete.match(/<\/CCDA>$/g)) {
            let doc = "";
            let xslUrl = "";
            /* eslint-disable-next-line no-control-regex */
            xml_complete = xml_complete.replace(/(\u000b\u001c)/gm, "").trim();
            xml_complete = xml_complete.replace(/\t\s+/g, " ").trim();
            xml_complete = xml_complete.replace(/\n/g, "\r\n");

            // convert xml data set for document to json array
            try {
                const data = await xmlParser.parseStringPromise(xml_complete);
                let unstructured = "";
                let isUnstruturedData = !!(data?.CCDA?.patient_files);
                // extract unstructured documents file component templates. One per file.
                if (isUnstruturedData) {
                    unstructuredTemplate = xml_complete.substring(
                        xml_complete.lastIndexOf("<patient_files>") + 15,
                        xml_complete.lastIndexOf("</patient_files>")
                    );
                }
                // create doc_type document i.e. CCD Referral etc.
                if (data?.CCDA?.doc_type !== "unstructured") {
                    doc = generateCcda(data.CCDA);
                    if (data.CCDA?.xslUrl) {
                        xslUrl = data.CCDA.xslUrl || "";
                    }
                    doc = headReplace(doc, xslUrl);
                } else {
                    unstructured = generateUnstructured(data.CCDA);
                    if (data.CCDA?.xslUrl) {
                        xslUrl = data.CCDA.xslUrl || "";
                    }
                    doc = headReplace(unstructured, xslUrl);
                    // combine the two documents to send back all at once.
                    doc += unstructured;
                }
                // auto build an Unstructured document of supplied embedded files.
                if (
                    data.CCDA?.doc_type !== "unstructured" &&
                    isUnstruturedData
                ) {
                    unstructured = generateUnstructured(data.CCDA);
                    unstructured = headReplace(unstructured, xslUrl);
                    // combine the two documents to send back all at once.
                    doc += unstructured;
                }
                // send results back to eagerly awaiting CCM for disposal.
                doc = doc.toString()
                /* eslint-disable-next-line no-control-regex */
                .replace(/(\u000b\u001c|\r)/gm, "").trim();
                let chunk = "";
                let numChunks = Math.ceil(doc.length / 1024);
                for (let i = 0, o = 0; i < numChunks; ++i, o += 1024) {
                    chunk = doc.substring(o, o + 1024);
                    conn.write(chunk);
                }
                conn.write(String.fromCharCode(28) + "\r\r" + "");
                conn.end();
            } catch (error) {
                console.log("XML parsing error:", error);
                //conn.write("ERROR: " + error.message);
                conn.end();
            }
        }
    }

    function eventCloseConn() {
        //console.log('connection from %s closed', remoteAddress);
    }

    function eventErrorConn(err) {
        console.log('Connection %s error: %s', remoteAddress, err.message);
        console.log(err.stack);
        conn.destroy();
    }

// Connection Events //
    // CCM will send one File Separator characters to mark end of array.
    let received = new DataStack(String.fromCharCode(28));
    conn.on("data", data => {
        received.push(data);
        while (!received.endOfCcda() && data.length > 0) {
            data = "";
            eventData(received.returnData());
        }
    });

    conn.once('close', eventCloseConn);
    conn.on('error', eventErrorConn);
}

function setUp(server) {
    server.on('connection', processConnection);
    server.listen(6661, '127.0.0.1', function () { // never change port!
        //console.log('server listening to ', server.address());
    });
}

// start up listener for requests from CCM or others.
setUp(server);

/* ---------------------------------For future use in header. Do not remove!-------------------------------------------- */
/*"data_enterer": {
    "identifiers": [
        {
            "identifier": "2.16.840.1.113883.4.6",
            "extension": "999999943252"
        }
    ],
    "name": [
        {
            "last": pd.data_enterer?.lname || "",
            "first": pd.data_enterer?.fname || ""
        }
    ],
    "address": [
        {
            "street_lines": [
                pd.data_enterer?.streetAddressLine || ""
            ],
            "city": pd.data_enterer?.city || "",
            "state": pd.data_enterer?.state || "",
            "zip": pd.data_enterer?.postalCode || "",
            "country": pd.data_enterer?.country || ""
        }
    ],
    "phone": [
        {
            "number": pd.data_enterer?.telecom || "",
            "type": "work place"
        }
    ]
},
"informant": {
    "identifiers": [
        {
            "identifier": "2.16.840.1.113883.19.5",
            "extension": "KP00017"
        }
    ],
    "name": [
        {
            "last": pd.informer?.lname || "",
            "first": pd.informer?.fname || ""
        }
    ],
    "address": [
        {
            "street_lines": [
                pd.informer?.streetAddressLine || ""
            ],
            "city": pd.informer?.city || "",
            "state": pd.informer?.state || "",
            "zip": pd.informer?.postalCode || "",
            "country": pd.informer?.country || ""
        }
    ],
    "phone": [
        {
            "number": pd.informer?.telecom || "",
            "type": "work place"
        }
    ]
},*/
/*"service_event": {
    "code": {
        "name": "",
        "code": "",
        "code_system_name": "SNOMED CT"
    },
    "date_time": {
        "low": {
            "date": "2021-03-11",
            "precision": "day"
        },
        "high": {
            "date": pd.created_time || "",
            "precision": "day"
        }
    },
    "performer": [
        {
            "performer": [
                {
                    "identifiers": [
                        {
                            "identifier": "2.16.840.1.113883.4.6",
                            "extension": npiProvider
                        }
                    ],
                    "name": [
                        {
                            "last": pd.information_recipient?.lname || "DAH",
                            "first": pd.information_recipient?.fname || "DAH"
                        }
                    ],
                    "address": [
                        {
                            "street_lines": [
                                pd.information_recipient?.streetAddressLine || ""
                            ],
                            "city": pd.information_recipient?.city || "",
                            "state": pd.information_recipient?.state || "",
                            "zip": pd.information_recipient?.postalCode || "",
                            "country": pd.information_recipient?.country || "US"
                        }
                    ],
                    "phone": [
                        {
                            "number": pd.information_recipient?.telecom || "",
                            "type": "work place"
                        }
                    ],
                    "organization": [
                        {
                            "identifiers": [
                                {
                                    "identifier": "2.16.840.1.113883.19.5.9999.1393"
                                }
                            ],
                            "name": [
                                pd.encounter_provider?.facility_name || ""
                            ],
                            "address": [
                                {
                                    "street_lines": [
                                        pd.encounter_provider?.facility_street || ""
                                    ],
                                    "city": pd.encounter_provider?.facility_city || "",
                                    "state": pd.encounter_provider?.facility_state || "",
                                    "zip": pd.encounter_provider?.facility_postal_code || "",
                                    "country": pd.encounter_provider?.facility_country_code || "US"
                                }
                            ],
                            "phone": [
                                {
                                    "number": pd.encounter_provider?.facility_phone || "",
                                    "type": "primary work"
                                }
                            ]
                        }
                    ],
                    "code": [
                        {
                            "name": "",
                            "code": "",
                            "code_system_name": "Provider Codes"
                        }
                    ]
                }
            ],
            "code": {
                "name": "Primary Performer",
                "code": "PP",
                "code_system_name": "Provider Role"
            }
        }
    ]
}*/
