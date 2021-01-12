/**
 * @package   OpenEMR CCDAServer
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";
var net = require('net');
var to_json = require('xmljson').to_json;
var bbg = require('oe-blue-button-generate');
//var bbm = require('blue-button-model'); //for use set global-not needed here
//var fs = require('fs');
//var bb = require('blue-button');
var fs = require('fs');

var server = net.createServer();
var conn = ''; // make our connection scope global to script
var oidFacility = "";
var all = "";
var npiProvider = "";
var npiFacility = "";

// some useful routines for populating template sections
function validate(toValidate, ref, retObj) {
    for (var p in ref) {
        if (typeof ref[p].dataType === "undefined") {
            retObj[p] = {};
            if (!toValidate[p]) toValidate[p] = {};
            validate(toValidate[p], ref[p], retObj[p]);
        } else {
            if (typeof toValidate === "undefined") toValidate = {};
            var trimmed = trim(toValidate[p]);
            retObj[p] = typeEnforcer(ref[p].dataType, trimmed);
        }
    }
    return retObj;
}

function typeEnforcer(type, val) {
    var validVal;
    switch (type) {
        case "boolean":
            if (typeof val === "string") {
                validVal = val.toLowerCase() === "true";
            } else {
                validVal = !!val;
            }
            break;
        case "string":
            if ((val === null) || (val === "undefined") || (typeof val === "undefined")) {
                validVal = '';
            } else if (typeof val == "object") {
                validVal = '';
            } else {
                validVal = trim(String(val));
            }
            break;
        case "array":
            if (typeof val === 'undefined' || val === null) {
                validVal = [];
            } else if (Array.isArray(val)) {
                validVal = [];
                val.forEach(function (v) {
                    validVal.push(trim(v));
                });
            } else {
                validVal = [trim(val)];
            }
            break;
        case "integer":
            var asInt = parseInt(val, 10);
            if (isNaN(asInt)) asInt = 0;
            validVal = asInt;
            break;
        case "number":
            var asNum = parseFloat(val);
            if (isNaN(asNum)) asNum = 0;
            validVal = asNum;
            break;
    }
    return validVal;
}

function trim(s) {
    if (typeof s === 'string') return s.trim();
    return s;
}

function safeId(s) {
    return trim(s).toLowerCase().replace(/[^a-zA-Z0-9]+/g, '-').replace(/\-+$/, '');
}

function fDate(str) {
    str = String(str); // at least ensure string so cast it...

    if (Number(str) === 0) {
        return (new Date()).toISOString().slice(0,10).replace(/-/g,"");
    }
    if (str.length === 8 || (str.length === 14 && (1 * str.substring(12, 14)) === 0)) {
        return [str.slice(0, 4), str.slice(4, 6), str.slice(6, 8)].join('-')
    } else if (str.length === 10 && (1 * str.substring(0, 2)) <= 12) {
        // case mm/dd/yyyy or mm-dd-yyyy
        return [str.slice(6, 10), str.slice(0, 2), str.slice(3, 5)].join('-')
    } else if (str.length === 14 && (1 * str.substring(12, 14)) > 0) {
        // maybe a real time so parse
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
        pflg = "minute";
    }
    if (str.length > 12) {
        pflg = "second";
    }

    return pflg;
}

function templateDate(date, precision) {
    return [{'date': fDate(date), 'precision': precision}]
}

function cleanCode(code) {
    return code.replace(/[.#]/, "");
}

function isOne(who) {
    try {
        if (who !== null && typeof who === 'object') {
            return who.hasOwnProperty('extension') || who.hasOwnProperty('id') ? 1 : Object.keys(who).length;
        }
    }
    catch (e) {
        return false;
    }
    return 0;
}

function headReplace(content) {
    var r = '<ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:hl7-org:v3" xmlns:voc="urn:hl7-org:v3/voc" xmlns:sdtc="urn:hl7-org:sdtc">\n';
    r += content.substr(content.search(/<realmCode/i));
    return r;
}

// Data model for Blue Button
function populateDemographic(pd, g) {
    let guardian = [{
        "relation": g.relation,
        "addresses": [{
            "street_lines": [g.address],
            "city": g.city,
            "state": g.state,
            "zip": g.postalCode,
            "country": g.country,
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

    return {
        "name": {
            "middle": [pd.mname],
            "last": pd.lname,
            "first": pd.fname
        },
        "dob": {
            "point": {
                "date": fDate(pd.dob),
                "precision": "day"
            }
        },
        "gender": pd.gender.toUpperCase(),
        "identifiers": [{
            "identifier": oidFacility,
            "extension": "PT-" + pd.id
        }],
        "marital_status": pd.status.toUpperCase() || "U",
        "addresses": [{
            "street_lines": [pd.street],
            "city": pd.city,
            "state": pd.state,
            "zip": pd.postalCode,
            "country": pd.country,
            "use": "primary home"
        }],
        "phone": [{
            "number": pd.phone_home,
            "type": "primary home"
        }],
        "race": pd.race,
        "ethnicity": pd.ethnicity || "U",
        "languages": [{
            "language": pd.language,
            "preferred": true,
            "mode": "Expressed spoken",
            "proficiency": "Good"
        }],
        "religion": pd.religion.toUpperCase() || "",
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
                    "extension": npiFacility || "UNK"
                }
            ],
            "phone": [{
                "number": all.encounter_provider.facility_phone || "",
            }],
            "name": [
                {
                    "full": all.encounter_provider.facility_name || ""
                }
            ]
        },
        "guardians": g.display_name ? guardian : ''
    }

}

function populateProviders() {
    return {
        "providers": [
            {
                "identity": [
                    {
                        "root": "2.16.840.1.113883.4.6",
                        "extension": all.primary_care_provider.provider[0].npi || "UNK"
                    }
                ],
                "type": [
                    {
                        "name": all.primary_care_provider.provider[0].physician_type || "Not Avail",
                        "code": all.primary_care_provider.provider[0].physician_type_code || "",
                        "code_system_name": "Provider Codes"
                    }
                ],
                "name": [
                    {
                        "last": all.primary_care_provider.provider[0].lname || "",
                        "first": all.primary_care_provider.provider[0].fname || ""
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
                        "country": all.encounter_provider.facility_country_code
                    }
                ],
                "phone": [
                    {
                        "value": {
                            "number": all.encounter_provider.facility_phone || "",

                        }
                    }],
                "organization": [
                    {
                        "identifiers": [
                            {
                                "identifier": "2.16.840.1.113883.19.5.9999.1393" //@todo need facility oid
                            }
                        ],
                        "name": [
                            all.encounter_provider.facility_name
                        ],
                        "address": [
                            {
                                "street_lines": [
                                    all.encounter_provider.facility_street
                                ],
                                "city": all.encounter_provider.facility_city,
                                "state": all.encounter_provider.facility_state,
                                "zip": all.encounter_provider.facility_postal_code,
                                "country": all.encounter_provider.facility_country_code
                            }
                        ],
                        "phone": [
                            {
                                "number": all.encounter_provider.facility_phone,
                                "type": "primary work"
                            }
                        ]
                    }
                ]

            }
        ]


    }
}

function populateMedication(pd) {
    pd.status = 'Completed'; //@todo invoke prescribed
    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date),
                "precision": "day"
            },
            /*"high": {
                "date": pd.end_date ? fDate(pd.end_date) : "",
                "precision": "day"
            }*/
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension || "UNK"
        }],
        "status": pd.status,
        "sig": pd.direction,
        "product": {
            "identifiers": [{
                "identifier": "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": "UNK"
            }],
            "unencoded_name": pd.drug,
            "product": {
                "name": pd.drug,
                "code": pd.rxnorm,
                /*"translations": [{
                    "name": pd.drug,
                    "code": pd.rxnorm,
                    "code_system_name": "RXNORM"
                }],*/
                "code_system_name": "RXNORM"
            },
            //"manufacturer": ""
        },
        "supply": {
            "date_time": {
                "low": {
                    "date": fDate(pd.start_date),
                    "precision": "day"
                }
            },
            "repeatNumber": "0",
            "quantity": "0",
            "author": {
                "date_time": {
                    "point": {
                        "date": fDate(pd.start_date),
                        "precision": getPrecision(fDate(pd.start_date))
                    }
                },
                "identifiers": [{
                    "identifier": "2.16.840.1.113883.4.6",
                    "extension": pd.npi || "UNK"
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
                "free_text": pd.instructions || "None Available"
            },
        },
        "administration": {
            "route": {
                "name": pd.route || "",
                "code": pd.route_code || "",
                "code_system_name": "Medication Route FDA"
            },
            "form": {
                "name": pd.form,
                "code": pd.form_code,
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
                "extension": pd.npi || "UNK"
            }],
            "organization": [{
                "identifiers": [{
                    "identifier": pd.sha_extension,
                    "extension": pd.extension || "UNK"
                }],
                "name": [pd.performer_name]
            }]
        },
        "drug_vehicle": {
            "name": pd.form,
            "code": pd.form_code,
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
        },*/
        /*"dispense": {
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
                "extension": pd.npi || "UNK"
            }],
            "code": [{
                "name": pd.physician_type,
                "code": pd.physician_type_code,
                "code_system_name": "SNOMED CT"
            }],
            "name": [
                {
                    "last": pd.lname || "UNK",
                    "first": pd.fname || "UNK"
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
                "country": pd.facility_country
            }]
        }],
        "findings": [{
            "identifiers": [{
                "identifier": pd.sha_extension,
                "extension": pd.extension
            }],
            "value": {
                "name": pd.encounter_reason,
                "code": "",
                "code_system_name": "SNOMED CT"
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.date),
                    "precision": "day"
                }
            }
        }]
    };
}

function populateAllergy(pd) {
    return {
        "identifiers": [{
            "identifier": "36e3e930-7b14-11db-9fe1-0800200c9a66",
            "extension": pd.id || "UNK"
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.startdate),
                "precision": "day"
            }
        },
        "observation": {
            "identifiers": [{
                "identifier": "4adc1020-7b14-11db-9fe1-0800200c9a66",
                "extension": pd.extension || "UNK"
            }],
            "allergen": {
                "name": pd.title,
                "code": pd.rxnorm_code === 0 ? "" : pd.rxnorm_code,
                "code_system_name": "RXNORM"
            },
            "intolerance": {
                "name": "Propensity to adverse reactions to drug",
                "code": pd.snomed_code || "420134006",
                "code_system_name": "SNOMED CT"
            },
            "date_time": {
                "low": {
                    "date": fDate(pd.startdate),
                    "precision": "day"
                }
            },
            "status": {
                "name": pd.allergy_status,
                "code": pd.status_code === 0 ? "" : pd.status_code,
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
                    "code": pd.reaction_code === 0 ? "" : pd.reaction_code,
                    "code_system_name": "SNOMED CT"
                },
                "severity": {
                    "code": {
                        "name": pd.outcome,
                        "code": pd.outcome_code === 0 ? "" : pd.outcome_code,
                        "code_system_name": "SNOMED CT"
                    },
                    /*"interpretation": {
                        "name": "",
                        "code": "",
                        "code_system_name": "Observation Interpretation"
                    }*/
                }
            }],
            "severity": {
                "code": {
                    "name": pd.outcome,
                    "code": pd.outcome_code === 0 ? "" : pd.outcome_code,
                    "code_system_name": "SNOMED CT"
                },
                /*"interpretation": {
                    "name": "UNK",
                    "code": "",
                    "code_system_name": "Observation Interpretation"
                }*/
            }
        }
    };
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
            "extension": pd.extension || "UNK"
        }],
        "problem": {
            "code": {
                "name": trim(pd.title),
                "code": cleanCode(pd.code),
                "code_system": "2.16.840.1.113883.6.90",
                "code_system_name": "ICD10"
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
        "performer": [
            {
                "identifiers": [
                    {
                        "identifier": "2.16.840.1.113883.4.6",
                        "extension": all.primary_care_provider.provider[0].npi || "UNK"
                    }
                ],
                "name": [
                    {
                        "last": all.primary_care_provider.provider[0].lname || "",
                        "first": all.primary_care_provider.provider[0].fname || ""
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
            "extension": pd.extension || "UNK"
        }]
    };

}

function populateProcedure(pd) {
    return {
        "procedure": {
            "name": pd.description,
            "code": pd.code,
            "code_system": "2.16.840.1.113883.6.12",
            "code_system_name": "CPT4"
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
                "extension": pd.npi || "UNK"
            }],
            "address": [{
                "street_lines": [pd.address],
                "city": pd.city,
                "state": pd.state,
                "zip": pd.zip,
                "country": ""
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
                    "country": pd.facility_country
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

function populateResult(pd) {
    let icode = pd.subtest.abnormal_flag;
    switch (pd.subtest.abnormal_flag.toUpperCase()) {
        case "NO":
            icode = "Normal";
            break;
        case "YES":
            icode = "Abnormal";
            break;
        case "":
            icode = "UNK";
            break;
    }
    return {
        "identifiers": [{
            "identifier": pd.subtest.root,
            "extension": pd.subtest.extension
        }],
        "result": {
            "name": pd.title,
            "code": pd.subtest.result_code || "",
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
            "range": pd.subtest.range //OpenEMR doesn't have high/low so show range as text.
        },
        /*"reference_range": {
            "low": pd.subtest.range,
            "high": pd.subtest.range,
            "unit": pd.subtest.unit
        },*/
        "interpretations": [icode],
        "value": parseFloat(pd.subtest.result_value) || pd.subtest.result_value || "",
        "unit": pd.subtest.unit
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
                        "extension": all.primary_care_provider.provider.npi || "UNK"
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
            "code": tResult.test_code,
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
    return {
        "plan": {
            "name": pd.code_text,
            "code": pd.code,
            "code_system_name": "SNOMED CT"
        },
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809b4a"
        }],
        "date_time": {
            "center": {
                "date": fDate(pd.date),
                "precision": "day"
            }
        },
        "type": "observation",
        "status": {
            "code": pd.status
        },
        "subType": pd.description
    };
}

function populateVital(pd) {
    return [{
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "value": parseFloat(pd.bps),
        "unit": "mm[Hg]"
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.bpd),
        "unit": "mm[Hg]"
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.pulse),
        "unit": "/min"
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.breath),
        "unit": "/min"
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.temperature),
        "unit": "degF"
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.height),
        "unit": pd.unit_height
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
            "extension": pd.extension_weight
        }],
        "vital": {
            "name": "Weight Measured",
            "code": "3141-9",
            "code_system_name": "LOINC"
        },
        "status": "completed",
        "date_time": {
            "point": {
                "date": fDate(pd.effectivetime),
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.weight),
        "unit": pd.unit_weight
    }, {
        "identifiers": [{
            "identifier": "2.16.840.1.113883.3.140.1.0.6.10.14.1",
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
                "precision": getPrecision(fDate(pd.effectivetime))
            }
        },
        "interpretations": ["Normal"],
        "value": parseFloat(pd.BMI),
        "unit": "kg/m2"
    }];
}

function populateSocialHistory(pd) {
    return {
        "date_time": {
            "low": templateDate(pd.date, "day")
            //"high": templateDate(pd.date, "day")
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension
        }],
        "code": {
            "name": pd.element
        },
        "value": pd.description
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
            "extension": pd.extension || "UNK"
        }],
        "status": "complete",
        "product": {
            "product": {
                "name": pd.code_text,
                "code": pd.cvx_code,
                "code_system_name": "CVX"
                /*"translations": [{
                    "name": "",
                    "code": "",
                    "code_system_name": "CVX"
                }]*/
            },
            "lot_number": "1",
            "manufacturer": "UNK"
        },
        "administration": {
            "route": {
                "name": pd.route_of_administration,
                "code": pd.route_code,
                "code_system_name": "Medication Route FDA"
            },
            "dose": {
                "value": 50,
                "unit": "mcg"
            }
        },
        "performer": {
            "identifiers": [{
                "identifier": "2.16.840.1.113883.4.6",
                "extension": npiProvider
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
                    "extension": npiFacility
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

function populateHeader(pd) {
    var head = {
        "identifiers": [
            {
                "identifier": oidFacility,
                "extension": "TT988"
            }
        ],
        "code": {
            "name": "Continuity of Care Document",
            "code": "34133-9",
            "code_system_name": "LOINC"
        },
        "template": [
            "2.16.840.1.113883.10.20.22.1.1",
            "2.16.840.1.113883.10.20.22.1.2"
        ],
        "title": "Clinical Health Summary",
        "date_time": {
            "point": {
                "date": fDate(pd.created_time) || "",
                "precision": getPrecision(fDate(pd.created_time))
            }
        },
        "author": {
            "author": [
                {
                    "identifiers": [
                        {
                            "identifier": "2.16.840.1.113883.4.6",
                            "extension": pd.author.npi || "UNK"
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
                            "country": pd.author.country
                        }
                    ],
                    "phone": [
                        {
                            "number": pd.author.telecom,
                            "use": "work place"
                        }
                    ],
                    "code": [
                        {
                            "name": "UNK",
                            "code": ""
                        }
                    ],
                    "organization": [
                        {
                            "identity": [
                                {
                                    "root": "2.16.840.1.113883.4.6",
                                    "extension": npiFacility || "UNK"
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
                                    "country": pd.encounter_provider.facility_country_code
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
                }
            ]
        },
        "custodian": {
            "identity": [
                {
                    "root": "2.16.840.1.113883.4.6",
                    "extension": npiFacility || "UNK"
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
                    "country": pd.encounter_provider.facility_country_code
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
        "service_event": { // @todo maybe move this to attributed or write z-schema template
            "code": {
                "name": "",
                "code": "",
                "code_system_name": "SNOMED CT"
            },
            "date_time": {
                "low": {
                    "date": "",
                    "precision": getPrecision()
                },
                "high": {
                    "date": pd.created_time,
                    "precision": getPrecision()
                }
            },
            "performer": [
                {
                    "performer": [
                        {
                            "identifiers": [
                                {
                                    "identifier": "2.16.840.1.113883.4.6",
                                    "extension": "PseudoMD-1"
                                }
                            ],
                            "name": [
                                {
                                    "last": pd.information_recipient.lname,
                                    "first": pd.information_recipient.fname
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
                                    "country": pd.information_recipient.country
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
                                            "country": pd.encounter_provider.facility_country_code
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
                                    "name": "UNK",
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
        }
    };
    return head;
}

function getMeta(pd) {
    var meta = {};
    meta = {
        "type": "CCDA",
        "identifiers": [
            {
                "identifier": "2.16.840.1.113883.19.5.99999.1",
                "extension": "TT988"
            }
        ],
        "confidentiality": "Normal",
        "set_id": {
            "identifier": "2.16.840.1.113883.19.5.99999.19",
            "extension": "sTT988"
        }
    }
    return meta;
}

function genCcda(pd) {
    var doc = {};
    var data = {};
    var count = 0;
    var many = [];
    var theone = {};

    all = pd;
    npiProvider = all.primary_care_provider.provider[0].npi;
    oidFacility = all.encounter_provider.facility_oid;
    npiFacility = all.encounter_provider.facility_npi;

// Demographics
    let demographic = populateDemographic(pd.patient, pd.guardian, pd);
    Object.assign(demographic, populateProviders());
    data.demographics = Object.assign(demographic);

// vitals
    many.vitals = [];
    try {
        count = isOne(pd.history_physical.vitals_list.vitals);
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.history_physical.vitals_list.vitals) {
            theone = populateVital(pd.history_physical.vitals_list.vitals[i]);
            many.vitals.push.apply(many.vitals, theone);
        }
    } else if (count === 1) {
        theone = populateVital(pd.history_physical.vitals_list.vitals);
        many.vitals.push(theone);
    }
    data.vitals = Object.assign(many.vitals);
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
    data.medications = Object.assign(meds.medications);
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
    data.encounters = Object.assign(encs.encounters);

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
    } else if (count !== 0) {
        allergy = populateAllergy(pd.allergies.allergy);
        allergies.allergies.push(allergy);
    }
    data.allergies = Object.assign(allergies.allergies);

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
    data.problems = Object.assign(problems.problems);
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

    data.procedures = Object.assign(many.procedures);
// Results
    if (pd.results)
        data.results = Object.assign(getResultSet(pd.results, pd)['results']);

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
    data.immunizations = Object.assign(many.immunizations);
// Plan of Care
    many = [];
    theone = {};
    many.plan_of_care = [];
    try {
        count = isOne(pd.planofcare); // ccm doesn't send array of items
    } catch (e) {
        count = 0
    }
    if (count > 1) {
        for (let i in pd.planofcare.item) {
            theone[i] = getPlanOfCare(pd.planofcare.item[i]);
            many.plan_of_care.push(theone[i]);
        }
    } else if (count !== 0) {
        theone = getPlanOfCare(pd.planofcare.item);
        many.plan_of_care.push(theone);
    }

    data.plan_of_care = Object.assign(many.plan_of_care);
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

    data.social_history = Object.assign(many.social_history);

    // ------------------------------------------ End Sections ----------------------------------------//

    doc.data = Object.assign(data);
    let meta = getMeta(pd);
    let header = populateHeader(pd);

    meta.ccda_header = Object.assign(header);
    doc.meta = Object.assign(meta);
    let xml = bbg.generateCCD(doc);

    // Debug
    /*fs.writeFile("ccda.json", JSON.stringify(all, null, 4), function (err) {
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
    });*/

    return xml;
}

function processConnection(connection) {
    conn = connection; // make it global
    var remoteAddress = conn.remoteAddress + ':' + conn.remotePort;
    //console.log(remoteAddress);
    conn.setEncoding('utf8');

    var xml_complete = "";

    function eventData(xml) {
        xml = xml.replace(/(\u000b|\u001c)/gm, "").trim();
        // Sanity check from service manager
        if (xml === 'status' || xml.length < 80) {
            conn.write("statusok" + String.fromCharCode(28) + "\r\r");
            conn.end('');
            return;
        }
        xml_complete += xml.toString();
        if (xml.toString().match(/\<\/CCDA\>$/g)) {
            // ---------------------start--------------------------------
            let doc = "";
            xml_complete = xml_complete.replace(/\t\s+/g, ' ').trim();

            to_json(xml_complete, function (error, data) {
                // console.log(JSON.stringify(data, null, 4));
                if (error) { // need try catch
                    console.log('toJson error: ' + error + 'Len: ' + xml_complete.length);
                    return;
                }
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
        //console.log('Connection %s error: %s', remoteAddress, err.message);
    }

    // Connection Events //
    conn.on('data', eventData);
    conn.once('close', eventCloseConn);
    conn.on('error', eventErrorConn);
}

function setUp(server) {
    server.on('connection', processConnection);
    server.listen(6661, 'localhost', function () {
        //console.log('server listening to %j', server.address());
    });
}

setUp(server);
