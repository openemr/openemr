/**
 * @package   OpenEMR CCDAServer
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";
const net = require('net');
const server = net.createServer();
const to_json = require('xmljson').to_json;
const bbg = require(__dirname + '/oe-blue-button-generate');
const fs = require('fs');

var conn = ''; // make our connection scope global to script
var oidFacility = "";
var all = "";
var npiProvider = "";
var npiFacility = "";
var webRoot = "";
var authorDate = '';

function trim(s) {
    if (typeof s === 'string') return s.trim();
    return s;
}

function cleanText(s) {
    if (typeof s === 'string') {
        //s = s.replace(new RegExp('\r?\n','g'), '<br />');
        return s.trim();
    }
    return s;
}

function fDate(str, lim8 = false) {
    str = String(str);
    if (lim8) {
        let rtn = str.substring(0, 8);
        return rtn;
    }
    if (Number(str) === 0) {
        return (new Date()).toISOString().slice(0, 10).replace(/-/g, "");
    }
    if (str.length === 1 || str === "0000-00-00") return (new Date()).toISOString().slice(0, 10).replace(/-/g, "");
    if (str.length === 8 || (str.length === 14 && (1 * str.substring(12, 14)) === 0)) {
        return [str.slice(0, 4), str.slice(4, 6), str.slice(6, 8)].join('-')
    } else if (str.length === 10 && (1 * str.substring(0, 2)) <= 12) {
        // case mm/dd/yyyy or mm-dd-yyyy
        return [str.slice(6, 10), str.slice(0, 2), str.slice(3, 5)].join('-')
    } else if (str.length === 14 && (1 * str.substring(12, 14)) > 0) {
        // maybe a real time so parse
    } else {

    }

    return str;
}

function getPrecision(str) {
    str = String(str);
    let pflg = "day";

    if (Number(str) === 0) {
        return "day";
    }
    if (str.length > 8) {
        pflg = "day";
    }
    if (str.length > 12) {
        pflg = "second";
    }

    return pflg;
}

function templateDate(date, precision) {
    return {'date': fDate(date), 'precision': precision}
}

function cleanCode(code) {
    if (typeof code === 'undefined') {
        return "null_flavor";
    }
    if (code.length < 2) {
        code = "null_flavor";
        return code;
    }
    return code.replace(/[.#]/, "");
}

function isOne(who) {
    try {
        if (who !== null && typeof who === 'object') {
            return (who.hasOwnProperty('npi') || who.hasOwnProperty('code') || who.hasOwnProperty('extension') || who.hasOwnProperty('id') || who.hasOwnProperty('date')) ? 1 : Object.keys(who).length;
        }
    } catch (e) {
        return false;
    }
    return 0;
}

function headReplace(content) {
    let xslUrl = "CDA.xsl";
    let r = '<?xml version="1.0" encoding="UTF-8"?>' + "\n" +
        '<?xml-stylesheet type="text/xsl" href="' + xslUrl + '"?>';
    r += "\n" + content.substr(content.search(/<ClinicalDocument/i));
    return r;
}

function populateDemographic(pd, g) {
    let guardian = [{
        "relation": g.relation,
        "addresses": [{
            "street_lines": [g.address],
            "city": g.city,
            "state": g.state,
            "zip": g.postalCode,
            "country": g.country || "US",
            "use": "primary home"
        }],
        "names": [{
            "last": g.display_name, //@todo parse first/last
            "first": g.display_name
        }],
        "phone": [{
            "number": g.telecom,
            "type": "primary home"
        }]
    }];
    let raceCode = pd.race == "White" ? "European" : "African";
    if (pd.race === 'Declined To Specify' || pd.race === '') {
        raceCode = "null_flavor";
        pd.race = "null_flavor";
    }
    if (pd.ethnicity === 'Declined To Specify' || pd.ethnicity === '') {
        pd.ethnicity = "null_flavor";
    }
    return {
        "name": {
            "prefix": pd.prefix,
            "suffix": pd.suffix,
            "middle": [pd.mname] || "",
            "last": pd.lname,
            "first": pd.fname
        },
        "birth_name": {
            "middle": pd.birth_mname || "",
            "last": pd.birth_lname || "",
            "first": pd.birth_fname || ""
        },
        "dob": {
            "point": {
                "date": fDate(pd.dob),
                "precision": "day"
            }
        },
        "gender": pd.gender.toUpperCase() || "null_flavor",
        "identifiers": [{
            "identifier": oidFacility,
            "extension": "PT-" + pd.id
        }],
        "marital_status": pd.status.toUpperCase(),
        "addresses": [{
            "street_lines": [pd.street],
            "city": pd.city,
            "state": pd.state,
            "zip": pd.postalCode,
            "country": pd.country || "US",
            "use": "primary home"
        }],
        "phone": [
            {
                "number": pd.phone_home,
                "type": "primary home"
            }, {
                "number": pd.phone_mobile,
                "type": "primary mobile"
            }, {
                "email": pd.email,
                "type": "contact_email"
            }
        ],
        "ethnicity": pd.ethnicity || "",
        "race": pd.race || "",
        "race_additional": raceCode,
        "languages": [{
            "language": pd.language === 'English' ? "en-US" : pd.language === 'Spanish' ? "sp-US" : 'en-US',
            "preferred": true,
            "mode": "Expressed spoken",
            "proficiency": "Good"
        }],
        //"religion": pd.religion.toUpperCase() || "",
        /*"birthplace":'', {
            "city": "",
            "state": "",
            "zip": "",
            "country": ""
        },*/
        "attributed_provider": {
            "identity": [
                {
                    "root": "2.16.840.1.113883.4.6",
                    "extension": npiFacility || ""
                }
            ],
            "phone": [{
                "number": all.encounter_provider.facility_phone || "",
            }],
            "name": [
                {
                    "full": all.encounter_provider.facility_name || ""
                }
            ],
            "address": [
                {
                    "street_lines": [
                        all.encounter_provider.facility_street
                    ],
                    "city": all.encounter_provider.facility_city,
                    "state": all.encounter_provider.facility_state,
                    "zip": all.encounter_provider.facility_postal_code,
                    "country": all.encounter_provider.facility_country_code || "US",
                    "use": "work place"
                }
            ],
        },
        //"guardians": g.display_name ? guardian : '' //not required
    }
}

function populateProvider(provider) {
    // The provider role is a maybe and will only be provided for physicians as a
    // primary care role. All other team members will id via taxonomy only and if not physicians.
    return {
        "function_code": provider.physician_type ? "PP" : "",
        "identity": [
            {
                "root": provider.npi ? "2.16.840.1.113883.4.6" : oidFacility,
                "extension": provider.npi || provider.table_id || ""
            }
        ],
        "type": [
            {
                "name": provider.taxonomy_description || "",
                "code": cleanCode(provider.taxonomy) || "",
                "code_system": "2.16.840.1.113883.6.101",
                "code_system_name": "NUCC Health Care Provider Taxonomy"
            }
        ],
        "name": [
            {
                "last": provider.lname || "",
                "first": provider.fname || ""
            }
        ],
        "address": [
            {
                "street_lines": [
                    all.encounter_provider.facility_street
                ],
                "city": all.encounter_provider.facility_city,
                "state": all.encounter_provider.facility_state,
                "zip": all.encounter_provider.facility_postal_code,
                "country": all.encounter_provider.facility_country_code || "US"
            }
        ],
        "phone": [
            {
                "value": {
                    "number": all.encounter_provider.facility_phone || "",

                }
            }
        ]
    }
}

function populateProviders() {
    let providerArray = [];
    let provider = populateProvider(all.primary_care_provider.provider);
    providerArray.push(provider);
    let count = isOne(all.care_team.provider);
    if (count === 1) {
        provider = populateProvider(all.care_team.provider);
        providerArray.push(provider);
    } else if (count > 1) {
        for (let i in all.care_team.provider) {
            provider = populateProvider(all.care_team.provider[i]);
            providerArray.push(provider);
        }
    }
    return {
        "providers":
        {
            "code": {
                "name": "",
                "code": "",
                "code_system_name": "SNOMED CT"
            },
            "date_time": {
                "low": {
                    "date": fDate(""),
                    "precision": "day"
                },
                "high": {
                    "date": fDate(""),
                    "precision": "day"
                }
            },
            "provider": providerArray,
        }
    }
}

function populateMedication(pd) {
    pd.status = 'Completed'; //@todo invoke prescribed
    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date),
                "precision": "day"
            }/*,
            "high": {
                "date": fDate(pd.end_date),
                "precision": "day"
            }*/
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension || ""
        }],
        "status": pd.status,
        "sig": pd.direction,
        "product": {
            "identifiers": [{
                "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": pd.extension + 1 || ""
            }],
            "unencoded_name": pd.drug,
            "product": {
                "name": pd.drug,
                "code": cleanCode(pd.rxnorm),
                "code_system_name": "RXNORM"
                /*"translations": [{
                    "name": pd.drug,
                    "code": pd.rxnorm,
                    "code_system_name": "RXNORM"
                }],*/
            },
            //"manufacturer": ""
        },
        "author": {
            "date_time": {
                "point": {
                    "date": fDate(authorDate),
                    "precision": getPrecision(fDate(pd.start_date))
                }
            },
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "name": {
                "prefix": pd.title,
                "last": pd.lname,
                "first": pd.fname
            }
        },
        "supply": {
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date),
                    "precision": "day"
                },
                "high": {
                    "date": fDate(pd.end_date),
                    "precision": "day"
                }
            },
            "repeatNumber": "0",
            "quantity": "0",
            "product": {
                "identifiers": [{
                    "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                    "extension": pd.extension + 1 || ""
                }],
                "unencoded_name": pd.drug,
                "product": {
                    "name": pd.drug,
                    "code": cleanCode(pd.rxnorm),
                    /*"translations": [{
                        "name": pd.drug,
                        "code": pd.rxnorm,
                        "code_system_name": "RXNORM"
                    }],*/
                    "code_system_name": "RXNORM"
                },
                //"manufacturer": ""
            },
            "author": {
                "date_time": {
                    "point": {
                        "date": fDate(pd.start_date),
                        "precision": getPrecision(fDate(pd.start_date))
                    }
                },
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": pd.npi || ""
                }],
                "name": {
                    "prefix": pd.title,
                    "last": pd.lname,
                    "first": pd.fname
                }
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
                "name": pd.form,
                "code": cleanCode(pd.form_code),
                "code_system_name": "Medication Route FDA"
            },
            "dose": {
                "value": parseFloat(pd.size),
                "unit": pd.unit,
            },
            /*"rate": {
                "value": parseFloat(pd.dosage),
                "unit": ""
            },*/
            "interval": {
                "period": {
                    "value": parseFloat(pd.dosage),
                    "unit": pd.interval
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
                    "identifier": pd.sha_extension,
                    "extension": pd.extension || ""
                }],
                "name": [pd.performer_name]
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

function populateEncounter(pd) {
    let name = '';
    let code = '';
    let code_system_name = "";
    let status = "Active";
    // just to get diagnosis. for findings..
    if (typeof pd.encounter_diagnosis !== 'undefined') {
        name = pd.encounter_diagnosis.text;
        code = cleanCode(pd.encounter_diagnosis.code);
        code_system_name = pd.encounter_diagnosis.code_type;
        status = pd.encounter_diagnosis.status;
    }
    return {
        "encounter": {
            "name": pd.visit_category ? pd.visit_category : 'UNK',
            "code": "185347001",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED CT",
            "translations": [{
                "name": "Ambulatory",
                "code": "AMB",
                "code_system_name": "ActCode"
            }]
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.date),
                "precision": "second" //getPrecision(fDate(pd.date_formatted))
            }
        },
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "code": [{
                "name": pd.physician_type,
                "code": cleanCode(pd.physician_type_code),
                "code_system_name": pd.physician_code_type
            }],
            "name": [
                {
                    "last": pd.lname || "",
                    "first": pd.fname || ""
                }
            ],
            "phone": [
                {
                    "number": pd.work_phone,
                    "type": "work place"
                }
            ]
        }],
        "locations": [{
            "name": pd.location,
            "location_type": {
                "name": pd.location_details,
                "code": "1160-1",
                "code_system_name": "HealthcareServiceLocation"
            },
            "address": [{
                "street_lines": [pd.facility_address],
                "city": pd.facility_city,
                "state": pd.facility_state,
                "zip": pd.facility_zip,
                "country": pd.facility_country || "US"
            }],
            "phone": [
                {
                    "number": pd.facility_phone,
                    "type": "work place"
                }
            ]
        }],
        "findings": [{
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension
            }],
            "value": {
                "name": name,
                "code": cleanCode(code),
                "code_system_name": code_system_name
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.date),
                    "precision": "day"
                }
            },
            "status": status,
            "reason": pd.encounter_reason
        }]
    };
}

function populateAllergy(pd) {
    if (!pd) {
        return {
            "no_know_allergies": "No Known Allergies",
            "date_time": {
                "low": templateDate("", "day"),
                //"high": templateDate(pd.enddate, "day")
            }
        }
    }
    return {
        "identifiers": [{
            "identifier": pd.sha_id,
            "extension": pd.id || ""
        }],
        "date_time": {
            "low": templateDate(pd.startdate, "day"),
            //"high": templateDate(pd.enddate, "day")
        },
        "observation": {
            "identifiers": [{
                "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": pd.id + 1 || ""
            }],
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": authorDate,
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            },
            "allergen": {
                "name": pd.title || "",
                "code": pd.rxnorm_code_text ? cleanCode(pd.rxnorm_code) : pd.snomed_code_text ? cleanCode(pd.snomed_code) : cleanCode(""),
                "code_system_name": pd.rxnorm_code_text ? "RXNORM" : pd.snomed_code_text ? "SNOMED CT" : ""
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.startdate) || fdate(""),
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
                "code": cleanCode(pd.status_code),
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
                    "name": pd.reaction_text,
                    "code": cleanCode(pd.reaction_code) || "",
                    "code_system_name": pd.reaction_code_type || "SNOMED CT"
                },
                "severity": {
                    "code": {
                        "name": pd.outcome || "",
                        "code": cleanCode(pd.outcome_code),
                        "code_system_name": "SNOMED CT"
                    }
                }
            }]
        }
    }
}

function populateProblem(pd) {
    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date_table),
                "precision": "day"
            },
            /*"high": {
                "date": fDate(pd.end_date),
                "precision": "day"
            }*/
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension || ""
        }],
        "translations": [{
            "name": "Condition",
            "code": "75323-6",
            "code_system_name": "LOINC"
        }],
        "problem": {
            "code": {
                "name": trim(pd.title),
                "code": cleanCode(pd.code),
                "code_system_name": trim(pd.code_type)
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date),
                    "precision": "day"
                },
                /*"high": {
                    "date": fDate(pd.end_date),
                    "precision": getPrecision()
                }*/
            }
        },
        "author": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": all.primary_care_provider.npi || "123456789"
            }],
            "date_time": {
                "point": {
                    "date": authorDate,
                    "precision": "day"
                }
            },
            "name": {
                "prefix": all.primary_care_provider.provider.prefix,
                "last": all.primary_care_provider.provider.lname,
                "first": all.primary_care_provider.provider.fname,
            }
        },
        "performer": [
            {
                "identifiers": [
                    {
                        "identifier": "2.16.840.1.113883.4.6",
                        "extension": all.primary_care_provider.provider.npi || ""
                    }
                ],
                "name": [
                    {
                        "last": all.primary_care_provider.provider.lname || "",
                        "first": all.primary_care_provider.provider.fname || ""
                    }
                ]
            }],
        "onset_age": pd.age,
        "onset_age_unit": "Year",
        "status": {
            "name": pd.status_table,
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date),
                    "precision": "day"
                },
                /*"high": {
                    "date": fDate(pd.end_date),
                    "precision": getPrecision()
                }*/
            }
        },
        "patient_status": pd.observation,
        "source_list_identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension || ""
        }]
    };

}

function populateProcedure(pd) {
    return {
        "procedure": {
            "name": pd.description,
            "code": cleanCode(pd.code),
            //"code_system": "2.16.840.1.113883.6.12",
            "code_system_name": "SNOMED CT"
        },
        "identifiers": [{
            "identifier": "d68b7e32-7810-4f5b-9cc2-acd54b0fd85d",
            "extension": pd.extension
        }],
        "status": "completed",
        "date_time": {
            "point": {
                "date": fDate(pd.date),
                "precision": "day"
            }
        },
        /*"body_sites": [{
            "name": "",
            "code": "",
            "code_system_name": ""
        }],
        "specimen": {
            "identifiers": [{
                "identifier": "c2ee9ee9-ae31-4628-a919-fec1cbb58683"
            }],
            "code": {
                "name": "",
                "code": "",
                "code_system_name": "SNOMED CT"
            }
        },*/
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "address": [{
                "street_lines": [pd.address],
                "city": pd.city,
                "state": pd.state,
                "zip": pd.zip,
                "country": "US"
            }],
            "phone": [{
                "number": pd.work_phone,
                "type": "work place"
            }],
            "organization": [{
                "identifiers": [{
                    "identifier": pd.facility_sha_extension,
                    "extension": pd.facility_extension
                }],
                "name": [pd.facility_name],
                "address": [{
                    "street_lines": [pd.facility_address],
                    "city": pd.facility_city,
                    "state": pd.facility_state,
                    "zip": pd.facility_zip,
                    "country": pd.facility_country || "US"
                }],
                "phone": [{
                    "number": pd.facility_phone,
                    "type": "work place"
                }]
            }]
        }],
        "procedure_type": "procedure"
    };
}

function populateMedicalDevice(pd) {
    return {
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension
        }],
        "date_time": {
            "low": {
                "date": fDate(pd.start_date),
                "precision": "day"
            }/*,
        "high": {
            "date": fDate(pd.end_date),
            "precision": "day"
        }*/
        },
        "device_type": "UDI",
        "device": {
            "name": pd.code_text,
            "code": cleanCode(pd.code),
            "code_system_name": "SNOMED CT",
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.3719",
                "extension": pd.udi
            }],
            "status": "completed",
            "body_sites": [{
                "name": "",
                "code": "",
                "code_system_name": ""
            }],
            "udi": pd.udi
        }
    }
}

function populateResult(pd) {
    let icode = pd.subtest.abnormal_flag;
    let value = parseFloat(pd.subtest.result_value) || pd.subtest.result_value || "";
    let type = isNaN(value) ? "ST" : "PQ";
    type = !pd.subtest.unit ? "ST" : type;
    value += "";
    let range_type = pd.subtest.range.toUpperCase() == "NEGATIVE" ? "CO" : type;
    type = value.toUpperCase() == "NEGATIVE" ? "CO" : type;

    switch (pd.subtest.abnormal_flag.toUpperCase()) {
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
    return {
        "identifiers": [{
            "identifier": pd.subtest.root,
            "extension": pd.subtest.extension
        }],
        "result": {
            "name": pd.title,
            "code": cleanCode(pd.subtest.result_code) || "",
            "code_system_name": "LOINC"
        },
        "date_time": {
            "point": {
                "date": fDate(pd.date_ordered),
                "precision": "day"
            }
        },
        "status": pd.order_status,
        "reference_range": {
            "low": pd.subtest.low,
            "high": pd.subtest.high,
            "unit": pd.subtest.unit,
            "type": type,
            "range_type": range_type
        },
        "interpretations": [icode],
        "value": value + "",
        "unit": pd.subtest.unit,
        "type": type,
        "range": pd.subtest.range,
        "range_type": range_type
    };
}

function getResultSet(results) {

    if (!results) return '';

    let tResult = results.result[0] || results.result;
    var resultSet = {
        "identifiers": [{
            "identifier": tResult.root,
            "extension": tResult.extension
        }],
        "author": [
            {
                "date_time": {
                    "point": {
                        "date": fDate(tResult.date_ordered),
                        "precision": getPrecision(fDate(tResult.date_ordered))
                    }
                },
                "identifiers": [
                    {
                        "identifier": "2.16.840.1.113883.4.6",
                        "extension": all.primary_care_provider.provider.npi || ""
                    }
                ],
                "name": [
                    {
                        "last": all.primary_care_provider.provider.lname || "",
                        "first": all.primary_care_provider.provider.fname || ""
                    }
                ]
            }],
        "result_set": {
            "name": tResult.test_name,
            "code": cleanCode(tResult.test_code),
            "code_system_name": "LOINC"
        }
    };
    var rs = [];
    var many = [];
    var theone = {};
    var count = 0;
    many.results = [];
    try {
        count = isOne(results.result);
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

    for (let key in all.encounter_list.encounter) {
        // skip loop if the property is from prototype
        if (!all.encounter_list.encounter.hasOwnProperty(key)) {
            continue;
        }
        encounter = all.encounter_list.encounter[key];
        if (pd.encounter == encounter.encounter_id) {
            one = false;
            name = encounter.encounter_diagnosis.text;
            code = cleanCode(encounter.encounter_diagnosis.code);
            code_system_name = encounter.encounter_diagnosis.code_type;
            status = encounter.encounter_diagnosis.status;
            encounter = all.encounter_list.encounter[key]; // to be sure.
            break;
        }
    }
    if (one) {
        let value = all.encounter_list.encounter.encounter_diagnosis || "";
        name = value.text;
        code = cleanCode(value.code);
        code_system_name = value.code_type;
        status = value.status;
        encounter = all.encounter_list.encounter;
    }

    return {
        "plan": {
            "name": pd.code_text || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || "SNOMED CT"
        },
        "identifiers": [{
            "identifier": pd.sha_extension
        }],
        "goal": {
            "code": cleanCode(pd.code) || "",
            "name": cleanText(pd.description) || ""
        },
        "date_time": {
            "point": {
                "date": fDate(pd.date_formatted),
                "precision": "day"
            }
        },
        "type": planType,
        "status": {
            "code": cleanCode(pd.status)
        },
        "performers": [{
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": encounter.npi || ""
            }],
            "code": [{
                "name": encounter.physician_type,
                "code": cleanCode(encounter.physician_type_code),
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
                    "number": encounter.work_phone,
                    "type": "work place"
                }
            ]
        }],
        "locations": [{
            "name": encounter.location,
            "location_type": {
                "name": encounter.location_details,
                "code": "1160-1",
                "code_system_name": "HealthcareServiceLocation"
            },
            "address": [{
                "street_lines": [encounter.facility_address],
                "city": encounter.facility_city,
                "state": encounter.facility_state,
                "zip": encounter.facility_zip,
                "country": encounter.facility_country || "US"
            }],
            "phone": [
                {
                    "number": encounter.facility_phone,
                    "type": "work place"
                }
            ]
        }],
        "findings": [{
            "identifiers": [{
                "identifier": encounter.sha_extension,
                "extension": encounter.extension
            }],
            "value": {
                "name": name,
                "code": code,
                "code_system_name": code_system_name
            },
            "date_time": {
                "low": {
                    "date": fDate(encounter.date),
                    "precision": "day"
                }
            },
            "status": status,
            "reason": encounter.encounter_reason
        }],
        "name": cleanText(pd.description),
        "mood_code": pd.moodCode
    };
}

function getGoals(pd) {
    return {
        "goal_code": {
            "name": pd.code_text !== "NULL" ? pd.code_text : "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || ""
        },
        "identifiers": [{
            "identifier": pd.sha_extension
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.date_formatted),
                "precision": "day"
            }
        },
        "type": "observation",
        "status": {
            "code": "active", //cleanCode(pd.status)
        },
        "name": pd.description
    };
}

function getFunctionalStatus(pd) {
    return {
        "status": "completed",
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809000"
        }],
        "observation": {
            "value": {
                "name": pd.code_text !== "NULL" ? cleanText(pd.code_text) : "",
                "code": cleanCode(pd.code) || "",
                "code_system_name": pd.code_type || "SNOMED-CT"
            },
            "identifiers": [{
                "identifier": "9a6d1bac-17d3-4195-89a4-1121bc8090ab"
            }],
            "date_time": {
                "point": {
                    "date": fDate(pd.date_formatted),
                    "precision": "day"
                }
            },
            "status": "completed"
        }
    };
}

function getMentalStatus(pd) {
    return {
        "value": {
            "name": pd.code_text !== "NULL" ? pd.code_text : "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || ""
        },
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809ccc"
        }],
        "note": cleanText(pd.description),
        "date_time": {
            "low": templateDate(pd.date_formatted, "day")
            //"high": templateDate(pd.date, "day")
        },
    };
}

function getAssessments(pd) {
    return {
        "description": cleanText(pd.description)
    };
}

function getHealthConcerns(pd) {
    let one = true;
    let issue_uuid;
    let problems = [], problem = {};
    if (isOne(pd.issues.issue_uuid) !== 0) {
        for (let key in pd.issues.issue_uuid) {
            issue_uuid = pd.issues.issue_uuid[key];
            if (issue_uuid) {
                one = false;
            }
            problem = {
                "identifiers": [{
                    "identifier": issue_uuid
                }]
            };
            problems.push(problem);
        }
    }
    if (one) {
        if (pd.issues.issue_uuid) {
            problem = {
                "identifiers": [{
                    "identifier": pd.issues.issue_uuid
                }]
            };
            problems.push(problem);
        }
    }
    return {
        "type": "act",
        "text": cleanText(pd.text),
        "value": {
            "name": pd.code_text || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || "SNOMED CT"
        },
        "identifiers": [{
            "identifier": pd.sha_extension
        }],
        problems: problems
    }
}

function getReferralReason(pd) {
    return {
        "reason": cleanText(pd.text)
    };
}

function populateVital(pd) {
    return {
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension
        }],
        "status": "completed",
        "date_time": {
            "point": {
                "date": fDate(pd.effectivetime),
                "precision": "day"
            }
        },
        // our list of vitals per organizer.
        "vital_list": [{
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_bps
            }],
            "vital": {
                "name": "Blood Pressure Systolic",
                "code": "8480-6",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.bps) || pd.bps,
            "unit": "mm[Hg]",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_bpd
            }],
            "vital": {
                "name": "Blood Pressure Diastolic",
                "code": "8462-4",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.bpd) || pd.bpd,
            "unit": "mm[Hg]",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_height
            }],
            "vital": {
                "name": "Height",
                "code": "8302-2",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.height) || pd.height,
            "unit": pd.unit_height,
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_weight
            }],
            "vital": {
                "name": "Weight Measured",
                "code": "29463-7",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.weight) || "",
            "unit": pd.unit_weight,
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_BMI
            }],
            "vital": {
                "name": "BMI (Body Mass Index)",
                "code": "39156-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": [pd.BMI_status == 'Overweight' ? 'High' : pd.BMI_status == 'Overweight' ? 'Low' : 'Normal'],
            "value": parseFloat(pd.BMI) || "",
            "unit": "kg/m2",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_pulse
            }],
            "vital": {
                "name": "Heart Rate",
                "code": "8867-4",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.pulse) || "",
            "unit": "/min",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.2",
                "extension": pd.extension_breath
            }],
            "vital": {
                "name": "Respiratory Rate",
                "code": "9279-1",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.breath) || "",
            "unit": "/min",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.3",
                "extension": pd.extension_temperature
            }],
            "vital": {
                "name": "Body Temperature",
                "code": "8310-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.temperature) || "",
            "unit": pd.unit_temperature,
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_oxygen_saturation
            }],
            "vital": {
                "name": "O2 % BldC Oximetry",
                "code": "59408-5",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.oxygen_saturation) || "",
            "unit": "%",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_ped_weight_height
            }],
            "vital": { // --------------------------------------------------------------------------------
                "name": "Weight for Height Percentile",
                "code": "77606-2",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_weight_height) || "",
            "unit": "%",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_inhaled_oxygen_concentration
            }],
            "vital": {
                "name": "Inhaled Oxygen Concentration",
                "code": "3150-0",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.inhaled_oxygen_concentration) || "",
            "unit": "%",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_ped_bmi
            }],
            "vital": {
                "name": "BMI Percentile",
                "code": "59576-9",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_bmi) || "",
            "unit": "%",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }, {
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension_ped_head_circ
            }],
            "vital": {
                "name": "Head Occipital-frontal Circumference Percentile",
                "code": "8289-1",
                "code_system_name": "LOINC"
            },
            "status": "completed",
            "date_time": {
                "point": {
                    "date": fDate(pd.effectivetime),
                    "precision": "day"
                }
            },
            "interpretations": ["Normal"],
            "value": parseFloat(pd.ped_head_circ) || "",
            "unit": "%",
            "author": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": all.primary_care_provider.npi || "123456789"
                }],
                "date_time": {
                    "point": {
                        "date": fDate(pd.effectivetime),
                        "precision": "day"
                    }
                },
                "name": {
                    "prefix": all.primary_care_provider.provider.prefix,
                    "last": all.primary_care_provider.provider.lname,
                    "first": all.primary_care_provider.provider.fname,
                }
            }
        }
        ]
    }
}

function populateSocialHistory(pd) {
    return {
        "date_time": {
            "low": templateDate(pd.date_formatted, "day")
            //"high": templateDate(pd.date, "day")
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension
        }],
        "code": {
            "name": pd.code
        },
        "element": pd.element,
        "value": pd.description,
        "gender": all.patient.gender
    };
}

function populateImmunization(pd) {
    return {
        "date_time": {
            "point": {
                "date": fDate(pd.administered_on),
                "precision": "month"
            }
        },
        "identifiers": [{
            "identifier": "e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92",
            "extension": pd.extension || ""
        }],
        "status": "complete",
        "product": {
            "product": {
                "name": pd.code_text,
                "code": cleanCode(pd.cvx_code),
                "code_system_name": "CVX"
                /*"translations": [{
                    "name": "",
                    "code": "",
                    "code_system_name": "CVX"
                }]*/
            },
            "lot_number": "",
            "manufacturer": ""
        },
        "administration": {
            "route": {
                "name": pd.route_of_administration,
                "code": cleanCode(pd.route_code) || "",
                "code_system_name": "Medication Route FDA"
            }/*,
        "dose": {
            "value": 50,
            "unit": "mcg"
        }*/
        },
        "performer": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.npi || ""
            }],
            "name": [{
                "last": pd.lname,
                "first": pd.fname
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
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": npiFacility || ""
                }],
                "name": [pd.facility_name]
            }]
        },
        "instructions": {
            "code": {
                "name": "immunization education",
                "code": "171044003",
                "code_system_name": "SNOMED CT"
            },
            "free_text": "Needs Attention for more data."
        }
    };
}

function populatePayer(pd) {
    return {
        "identifiers": [{
            "identifier": "1fe2cdd0-7aad-11db-9fe1-0800200c9a66"
        }],
        "policy": {
            "identifiers": [{
                "identifier": "3e676a50-7aac-11db-9fe1-0800200c9a66"
            }],
            "code": {
                "code": "SELF",
                "code_system_name": "HL7 RoleCode"
            },
            "insurance": {
                "code": {
                    "code": "PAYOR",
                    "code_system_name": "HL7 RoleCode"
                },
                "performer": {
                    "identifiers": [{
                        "identifier": "2.16.840.1.113883.19"
                    }],
                    "address": [{
                        "street_lines": ["123 Insurance Road"],
                        "city": "Blue Bell",
                        "state": "MA",
                        "zip": "02368",
                        "country": "US",
                        "use": "work place"
                    }],
                    "phone": [{
                        "number": "(781)555-1515",
                        "type": "work place"
                    }],
                    "organization": [{
                        "name": ["Good Health Insurance"],
                        "address": [{
                            "street_lines": ["123 Insurance Road"],
                            "city": "Blue Bell",
                            "state": "MA",
                            "zip": "02368",
                            "country": "US",
                            "use": "work place"
                        }],
                        "phone": [{
                            "number": "(781)555-1515",
                            "type": "work place"
                        }]
                    }],
                    "code": [{
                        "code": "PAYOR",
                        "code_system_name": "HL7 RoleCode"
                    }]
                }
            }
        },
        "guarantor": {
            "code": {
                "code": "GUAR",
                "code_system_name": "HL7 Role"
            },
            "identifiers": [{
                "identifier": "329fcdf0-7ab3-11db-9fe1-0800200c9a66"
            }],
            "name": [{
                "prefix": "Mr.",
                "middle": ["Frankie"],
                "last": "Everyman",
                "first": "Adam"
            }],
            "address": [{
                "street_lines": ["17 Daws Rd."],
                "city": "Blue Bell",
                "state": "MA",
                "zip": "02368",
                "country": "US",
                "use": "primary home"
            }],
            "phone": [{
                "number": "(781)555-1212",
                "type": "primary home"
            }]
        },
        "participant": {
            "code": {
                "name": "Self",
                "code": "SELF",
                "code_system_name": "HL7 Role"
            },
            "performer": {
                "identifiers": [{
                    "identifier": "14d4a520-7aae-11db-9fe1-0800200c9a66",
                    "extension": "1138345"
                }],
                "address": [{
                    "street_lines": ["17 Daws Rd."],
                    "city": "Blue Bell",
                    "state": "MA",
                    "zip": "02368",
                    "country": "US",
                    "use": "primary home"
                }],
                "code": [{
                    "name": "Self",
                    "code": "SELF",
                    "code_system_name": "HL7 Role"
                }]
            },
            "name": [{
                "prefix": "Mr.",
                "middle": ["A."],
                "last": "Everyman",
                "first": "Frank"
            }]
        },
        "policy_holder": {
            "performer": {
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.19",
                    "extension": "1138345"
                }],
                "address": [{
                    "street_lines": ["17 Daws Rd."],
                    "city": "Blue Bell",
                    "state": "MA",
                    "zip": "02368",
                    "country": "US",
                    "use": "primary home"
                }]
            }
        },
        "authorization": {
            "identifiers": [{
                "identifier": "f4dce790-8328-11db-9fe1-0800200c9a66"
            }],
            "procedure": {
                "code": {
                    "name": "Colonoscopy",
                    "code": "73761001",
                    "code_system_name": "SNOMED CT"
                }
            }
        }
    };
}

function populateNote(pd) {
    return {
        "date_time": {
            "point": {
                "date": fDate(pd.date_formatted),
                "precision": "day"
            }
        },
        "translations": {
            code_system: "2.16.840.1.113883.6.1",
            code_system_name: "LOINC",
            code: cleanCode(pd.code),
            name: pd.code_text || ""
        },
        "author": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": pd.author_npi || "123456789"
            }],
            "date_time": {
                "point": {
                    "date": fDate(pd.date_formatted),
                    "precision": "day"
                }
            },
            "name": {
                "prefix": pd.author_title,
                "last": pd.author_last,
                "first": pd.author_first,
            },
            "author_full_name": pd.author_title + " " + pd.author_first + " " + pd.author_last,
            "organization": [{
                "identity": {
                    "root": pd.facility_oid || oidFacility || "",
                },
                "name": [pd.facility_name]
            }]
        },
        "note": cleanText(pd.description),
    };
}

function populateHeader(pd) {
    const head = {
        "identifiers": [
            {
                "identifier": oidFacility,
                "extension": "TT988"
            }
        ],
        "code": {
            "name": "Continuity of Care Document", //change to toc w/code
            "code": "34133-9",
            "code_system_name": "LOINC"
        },
        "template": [
            "2.16.840.1.113883.10.20.22.1.1",
            "2.16.840.1.113883.10.20.22.1.2"
        ],
        "title": "OpenEMR Transitions of Care",
        "date_time": {
            "date": pd.created_time_timezone,
            "precision": "none"
        },
        "author": {
            "date_time": {
                "point": {
                    "date": (isOne(all.encounter_list.encounter) === 1 ? all.encounter_list.encounter.date_formatted : all.encounter_list.encounter[0].date_formatted) || pd.created_time_timezone,
                    "precision": "day"
                }
            },
            "identifiers": [
                {
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": pd.author.npi || ""
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "address": [
                {
                    "street_lines": [
                        pd.author.streetAddressLine
                    ],
                    "city": pd.author.city,
                    "state": pd.author.state,
                    "zip": pd.author.postalCode,
                    "country": pd.author.country || "US",
                    "use": "work place"
                }
            ],
            "phone": [
                {
                    "number": pd.author.telecom || "",
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
                        pd.encounter_provider.facility_name
                    ],
                    "address": [
                        {
                            "street_lines": [
                                pd.encounter_provider.facility_street
                            ],
                            "city": pd.encounter_provider.facility_city,
                            "state": pd.encounter_provider.facility_state,
                            "zip": pd.encounter_provider.facility_postal_code,
                            "country": pd.encounter_provider.facility_country_code || "US",
                            "use": "work place"
                        }
                    ],
                    "phone": [
                        {
                            "number": pd.encounter_provider.facility_phone,
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
                pd.encounter_provider.facility_name
            ],
            "address": [
                {
                    "street_lines": [
                        pd.encounter_provider.facility_street
                    ],
                    "city": pd.encounter_provider.facility_city,
                    "state": pd.encounter_provider.facility_state,
                    "zip": pd.encounter_provider.facility_postal_code,
                    "country": pd.encounter_provider.facility_country_code || "US"
                }
            ],
            "phone": [
                {
                    "number": pd.encounter_provider.facility_phone,
                    "type": "work primary"
                }
            ]
        },
        /*"data_enterer": {
            "identifiers": [
                {
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": "999999943252"
                }
            ],
            "name": [
                {
                    "last": pd.data_enterer.lname,
                    "first": pd.data_enterer.fname
                }
            ],
            "address": [
                {
                    "street_lines": [
                        pd.data_enterer.streetAddressLine
                    ],
                    "city": pd.data_enterer.city,
                    "state": pd.data_enterer.state,
                    "zip": pd.data_enterer.postalCode,
                    "country": pd.data_enterer.country
                }
            ],
            "phone": [
                {
                    "number": pd.data_enterer.telecom,
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
                    "last": pd.informer.lname || "",
                    "first": pd.informer.fname || ""
                }
            ],
            "address": [
                {
                    "street_lines": [
                        pd.informer.streetAddressLine || ""
                    ],
                    "city": pd.informer.city,
                    "state": pd.informer.state,
                    "zip": pd.informer.postalCode,
                    "country": pd.informer.country
                }
            ],
            "phone": [
                {
                    "number": pd.informer.telecom || "",
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
                    "date": pd.created_time,
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
                                    "last": pd.information_recipient.lname || "DAH",
                                    "first": pd.information_recipient.fname || "DAH"
                                }
                            ],
                            "address": [
                                {
                                    "street_lines": [
                                        pd.information_recipient.streetAddressLine
                                    ],
                                    "city": pd.information_recipient.city,
                                    "state": pd.information_recipient.state,
                                    "zip": pd.information_recipient.postalCode,
                                    "country": pd.information_recipient.country || "US"
                                }
                            ],
                            "phone": [
                                {
                                    "number": pd.information_recipient.telecom,
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
                                        pd.encounter_provider.facility_name
                                    ],
                                    "address": [
                                        {
                                            "street_lines": [
                                                pd.encounter_provider.facility_street
                                            ],
                                            "city": pd.encounter_provider.facility_city,
                                            "state": pd.encounter_provider.facility_state,
                                            "zip": pd.encounter_provider.facility_postal_code,
                                            "country": pd.encounter_provider.facility_country_code || "US"
                                        }
                                    ],
                                    "phone": [
                                        {
                                            "number": pd.encounter_provider.facility_phone,
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
    };
    return head;
}

function getMeta(pd) {
    var meta = {};
    meta = {
        "type": "CCDA",
        "identifiers": [
            {
                "identifier": oidFacility || "",
                "extension": "TT988"
            }
        ],
        "confidentiality": "Normal",
        "set_id": {
            "identifier": oidFacility || "",
            "extension": "sTT988"
        }
    }
    return meta;
}

/**
 / * function genCcda
 /* The main document builder
 /* pd array the xml parsed array of data sent from CCM.
 */
function genCcda(pd) {
    let doc = {};
    let data = {};
    let count = 0;
    let many = [];
    let theone = {};

    all = pd;
    npiProvider = all.primary_care_provider.provider.npi;
    oidFacility = all.encounter_provider.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
    npiFacility = all.encounter_provider.facility_npi;
    webRoot = all.serverRoot;

    if (all.encounter_list.encounter.date) {
        authorDate = all.encounter_list.encounter.date;
    } else if (all.encounter_list.encounter[0].date) {
        authorDate = all.encounter_list.encounter[0].date;
    }
// Demographics
    let demographic = populateDemographic(pd.patient, pd.guardian, pd);
// This populates documentationOf. We are using providerOrganization also.
    Object.assign(demographic, populateProviders());

    data.demographics = Object.assign(demographic);
// Encounters
    let encs = [];
    let enc = {};
    encs.encounters = [];
    try {
        count = isOne(pd.encounter_list.encounter);
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
    many.vitals = [];
    try {
        count = isOne(pd.history_physical.vitals_list.vitals);
    } catch (e) {
        count = 0
    }
    if (count !== 0) {
        data.vitals = Object.assign(populateVital(pd.history_physical.vitals_list.vitals));
    }
// Medications
    let meds = [];
    let m = {};
    meds.medications = [];
    try {
        count = isOne(pd.medications.medication);
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
        count = isOne(pd.allergies.allergy);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.allergies.allergy) {
            allergy[i] = populateAllergy(pd.allergies.allergy[i]);
            allergies.allergies.push(allergy[i]);
        }
    } else if (count <= 1) {
        allergy = populateAllergy(pd.allergies.allergy);
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
        count = isOne(pd.problem_lists.problem);
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
        count = isOne(pd.procedures.procedure);
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
// Medical Devices
    many = [];
    theone = {};
    many.medical_devices = [];
    try {
        count = isOne(pd.medical_devices.device);
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
        data.results = Object.assign(getResultSet(pd.results, pd)['results']);
    }

// Referral
    // different referral sources. 1st is dynamic with doc gen from CCM.
    // 2nd is latest referral from transactions.
    if (pd.referral_reason[0].text !== "") {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[0], pd));
    } else if (pd.referral_reason[1].text !== "" && typeof pd.referral_reason[1].text !== 'undefined') {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[1], pd));
    }
// Health Concerns
    many = [];
    theone = {};
    many.health_concerns = [];
    try {
        count = isOne(pd.health_concerns.concern);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.health_concerns.concern) {
            theone[i] = getHealthConcerns(pd.health_concerns.concern[i]);
            many.health_concerns.push(theone[i]);
            break;
        }
    } else if (count !== 0) {
        theone = getHealthConcerns(pd.health_concerns.concern);
        many.health_concerns.push(theone);
    }
    if (count !== 0) {
        data.health_concerns = Object.assign(many.health_concerns);
    }
// Immunizations
    many = [];
    theone = {};
    many.immunizations = [];
    try {
        count = isOne(pd.immunizations.immunization);
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
        count = isOne(pd.planofcare.item);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.planofcare.item) {
            if (cleanCode(pd.planofcare.item[i].date_formatted) === '') {
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
        count = isOne(pd.goals.item);
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
        count = isOne(pd.clinical_notes.evaluation_note);
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
        count = isOne(pd.functional_status.item);
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

// Mental Status.
    many = [];
    theone = {};
    many.mental_status = [];
    try {
        count = isOne(pd.mental_status.item);
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
        count = isOne(pd.history_physical.social_history.history_element);
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
    for (let currentNote in pd.clinical_notes) {
        many = [];
        theone = {};
        switch (pd.clinical_notes[currentNote].clinical_notes_type) {
            case 'evaluation_note':
                continue;
                break;
            case 'progress_note':

                break;
            case 'history_physical':
                pd.clinical_notes[currentNote].code_text = "History and Physical";
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
            count = isOne(pd.clinical_notes[currentNote]);
        } catch (e) {
            count = 0
        }
        if (count > 1) {
            for (let i in pd.clinical_notes[currentNote]) {
                theone[i] = populateNote(pd.clinical_notes[currentNote]);
                many.push(theone[i]);
            }
        } else if (count !== 0) {
            theone = populateNote(pd.clinical_notes[currentNote]);
            many.push(theone);
        }
        if (count !== 0) {
            data[currentNote] = Object.assign(many);
        }
    }

// ------------------------------------------ End Sections ----------------------------------------//

    doc.data = Object.assign(data);
    let meta = getMeta(pd);
    let header = populateHeader(pd);

    meta.ccda_header = Object.assign(header);
    doc.meta = Object.assign(meta);
    // build to cda
    let xml = bbg.generateCCD(doc);

    /* Debug
        fs.writeFile("ccda.json", JSON.stringify(all, null, 4), function (err) {
            if (err) {
                return console.log(err);
            }
            console.log("Json saved!");
        });

        fs.writeFile("ccda.xml", xml, function (err) {
            if (err) {
                return console.log(err);
            }
            console.log("Xml saved!");
        });
    */

    return xml;
}

function processConnection(connection) {
    conn = connection; // make it global
    let remoteAddress = conn.remoteAddress + ':' + conn.remotePort;
    conn.setEncoding('utf8');

    let xml_complete = "";

    function eventData(xml) {
        xml = xml.replace(/(\u000b|\u001c)/gm, "").trim();
        // Sanity check from service manager
        if (xml === 'status' || xml.length < 80) {
            conn.write("statusok" + String.fromCharCode(28) + "\r\r");
            conn.end('');
            return;
        }
        xml_complete += xml.toString();
        if (xml.toString().match(/<\/CCDA>$/g)) {
            // ---------------------start--------------------------------
            let doc = "";
            xml_complete = xml_complete.replace(/\t\s+/g, ' ').trim();
            // convert xml data set for document to json array
            to_json(xml_complete, function (error, data) {
                // console.log(JSON.stringify(data, null, 4));
                if (error) { // need try catch
                    console.log('toJson error: ' + error + 'Len: ' + xml_complete.length);
                    return;
                }
                // create document
                doc = genCcda(data.CCDA);
            });

            doc = headReplace(doc);
            doc = doc.toString().replace(/(\u000b|\u001c|\r)/gm, "").trim();
            //console.log(doc);
            let chunk = "";
            let numChunks = Math.ceil(doc.length / 1024);
            for (let i = 0, o = 0; i < numChunks; ++i, o += 1024) {
                chunk = doc.substr(o, 1024);
                conn.write(chunk);
            }
            conn.write(String.fromCharCode(28) + "\r\r" + '');
            conn.end();
        }
    }

    function eventCloseConn() {
        //console.log('connection from %s closed', remoteAddress);
    }

    function eventErrorConn(err) {
        console.log('Connection %s error: %s', remoteAddress, err.message);
    }

// Connection Events //
    conn.on('data', eventData);
    conn.once('close', eventCloseConn);
    conn.on('error', eventErrorConn);
}

function setUp(server) {
    server.on('connection', processConnection);
    server.listen(6661, 'localhost', function () { // never change port!
        //console.log('server listening to %j', server.address());
    });
}

// start up listener for requests from CCM or others.
setUp(server);
