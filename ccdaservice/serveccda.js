/**
 * @package   OpenEMR CCDAServer
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";

const enableDebug = true;

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
var authorDateTime = '';
var documentLocation = '';

class DataStack {
    constructor(delimiter) {
        this.delimiter = delimiter;
        this.buffer = "";
    }

    endOfCcda() {
        return this.buffer.length === 0 || this.buffer.indexOf(this.delimiter) === -1;
    }

    pushToStack(data) {
        this.buffer += data;
    }

    fetchBuffer() {
        const delimiterIndex = this.buffer.indexOf(this.delimiter);
        if (delimiterIndex !== -1) {
            const bufferMsg = this.buffer.slice(0, delimiterIndex);
            this.buffer = this.buffer.replace(bufferMsg + this.delimiter, "");
            return bufferMsg;
        }
        return null
    }

    returnData() {
        return this.fetchBuffer();
    }

    clearStack() {
        this.buffer = "";
    }

    readStackByDelimiter(delimiter) {
        let backup = this.delimiter;
        let part = '';
        this.delimiter = delimiter;
        part = this.fetchBuffer();
        this.delimiter = backup;
        return part;
    }
}

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

// do a recursive descent transformation of the node object populating the timezone offset value if we have
// a precision property (inside a date) with the value of timezone.
function populateTimezones(node, tzOffset, depthCheck) {
    if (!node || typeof node !== 'object') {
        return node;
    }
    // we should NEVER go farther than 25 recursive loops down in our heirarchy, if we do it means we have an infinite loop
    if (depthCheck > 25) {
        console.error("Max depth traversal reached.  Potential infinite loop.  Breaking out of loop")
        return node;
    }

    if (node.hasOwnProperty('precision') && node.precision == 'tz' && !node.hasOwnProperty('timezoneOffset')) {
        node.timezoneOffset = tzOffset;
    } else {
        for (const [key, value] of Object.entries(node)) {
            node[key] = populateTimezones(value, tzOffset, depthCheck + 1);
        }
    }
    return node;
}

function fDate(str, lim8 = false) {
    str = String(str);
    if (lim8) {
        let rtn = str.substring(0, 8);
        return rtn;
    }
    if (Number(str) === 0) {
        return (new Date()).toISOString();
    }
    if (str.length === 1 || str === "0000-00-00") return (new Date()).toISOString();
    if (str.length === 8 || (str.length === 14 && (1 * str.substring(12, 14)) === 0)) {
        return [str.slice(0, 4), str.slice(4, 6), str.slice(6, 8)].join('-');
    } else if (str.length === 10 && (1 * str.substring(0, 2)) <= 12) {
        // case mm/dd/yyyy or mm-dd-yyyy
        return [str.slice(6, 10), str.slice(0, 2), str.slice(3, 5)].join('-');
    } else if (str.length === 17) {
        str = str.split(' ');
        str = [str[0].slice(0, 4), str[0].slice(4, 6), str[0].slice(6, 8)].join('-') + ' ' + str[1];
        return str;
    } else if (str.length === 19 && (str.substring(14, 15)) == '-') {
        let strZone = str.split('-');
        let strDate = [strZone[0].substring(0, 4), strZone[0].substring(4, 6), strZone[0].substring(6, 8)].join('-');
        let strTime = [str.substring(8, 10), str.substring(10, 12), str.substring(12, 14)].join(':');

        let str1 = strDate + ' ' + strTime + '-' + strZone[1];
        return str1;
    } else {
        return str;
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
    if (str.length > 23) {
        pflg = "tz";
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
    if (code.length < 1) {
        code = "null_flavor";
        return code;
    }
    return code.replace(/[.#]/, "");
}

function isOne(who) {
    try {
        if (who !== null && typeof who === 'object') {
            return (who.hasOwnProperty('npi')
                || who.hasOwnProperty('code')
                || who.hasOwnProperty('extension')
                || who.hasOwnProperty('id')
                || who.hasOwnProperty('date')
                || who.hasOwnProperty('use')
                || who.hasOwnProperty('type')
            ) ? 1 : Object.keys(who).length;
        }
    } catch (e) {
        return false;
    }
    return 0;
}

function headReplace(content, xslUrl = "") {

    let xsl = "CDA.xsl";
    if (typeof xslUrl == "string" && xslUrl.trim() != "") {
        xsl = xslUrl;
    }

    let r = '<?xml version="1.0" encoding="UTF-8"?>' + "\n" +
        '<?xml-stylesheet type="text/xsl" href="' + xsl + '"?>';
    r += "\n" + content.substring(content.search(/<ClinicalDocument/i));
    return r;
}

function fetchPreviousAddresses(pd) {
    let addressArray = [];
    let pa = pd.previous_addresses.address;
    let streetLine = [pd.street[0]];
    if (pd.street[1].length > 0) {
        streetLine = [pd.street[0], pd.street[1]];
    }
    addressArray.push({
        "use": "HP",
        "street_lines": streetLine,
        "city": pd.city,
        "state": pd.state,
        "zip": pd.postalCode,
        "country": pd.country || "US",
        "date_time": {
            // use current date for current residence
            "low": {
                "date": fDate(""),
                "precision": "day"
            }
        }
    });
    let count = isOne(pa);
    // how do we ever get here where we just have one object?
    if (count === 1) {
        streetLine = [pa.street[0]];
        if (pa.street[1].length > 0) {
            streetLine = [pa.street[0], pa.street[1]];
        }
        addressArray.push({
            "use": pa.use,
            "street_lines": streetLine,
            "city": pa.city,
            "state": pa.state,
            "zip": pa.postalCode,
            "country": pa.country || "US",
            "date_time": {
                "low": {
                    "date": fDate(pa.period_start),
                    "precision": "day"
                },
                "high": {
                    "date": fDate(pa.period_end) || fDate(""),
                    "precision": "day"
                }
            }
        });
    } else if (count > 1) {
        for (let i in pa) {
            streetLine = [pa[i].street[0]];
            if (pa[i].street[1].length > 0) {
                streetLine = [pa[i].street[0], pa[i].street[1]];
            }
            addressArray.push({
                "use": pa[i].use,
                "street_lines": streetLine,
                "city": pa[i].city,
                "state": pa[i].state,
                "zip": pa[i].postalCode,
                "country": pa[i].country || "US",
                "date_time": {
                    "low": {
                        "date": fDate(pa[i].period_start),
                        "precision": "day"
                    },
                    "high": {
                        "date": fDate(pa[i].period_end) || fDate(""),
                        "precision": "day"
                    }
                }
            });
        }
    }
    return addressArray;
}

function populateDemographic(pd, g) {
    let first = 'NI';
    let middle = 'NI';
    let last = 'NI';
    const names = g.display_name.split(' ');
    if (names.length === 2) {
        first = names[0];
        last = names[1];
    }
    if (names.length === 3) {
        first = names[0];
        last = names[2];
    }
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
            "last": last,
            "first": first
        }],
        "phone": [{
            "number": g.telecom,
            "type": "primary home"
        }]
    }];
    if (pd.race === 'Declined To Specify' || pd.race === '') {
        pd.race = "null_flavor";
    }
    if (pd.race_group === 'Declined To Specify' || pd.race_group === '') {
        pd.race_group = "null_flavor";
    }
    if (pd.ethnicity === 'Declined To Specify' || pd.ethnicity === '') {
        pd.ethnicity = "null_flavor";
    }
    let addressArray = fetchPreviousAddresses(pd);
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
            "identifier": oidFacility || npiFacility,
            "extension": pd.uuid
        }],
        "marital_status": pd.status.toUpperCase(),
        "addresses": addressArray,
        "phone": [
            {
                "number": pd.phone_home,
                "type": "primary home"
            }, {
                "number": pd.phone_mobile,
                "type": "primary mobile"
            }, {
                "number": pd.phone_work,
                "type": "work place"
            }, {
                "number": pd.phone_emergency,
                "type": "emergency contact"
            },{
                "email": pd.email,
                "type": "contact_email"
            }
        ],
        "ethnicity": pd.ethnicity || "",
        "race": pd.race || "null_flavor",
        "race_additional": pd.race_group || "null_flavor",
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
        "guardians": g.display_name ? guardian : '' //not required
    }
}

function populateProvider(provider) {
    // The provider role is a maybe and will only be provided for physicians as a
    // primary care role. All other team members will id via taxonomy only and if not physicians.
    return {
        "function_code": provider.physician_type ? "PP" : "",
        "date_time": {
            "low": {
                "date": provider.provider_since ? fDate(provider.provider_since) : fDate(""),
                "precision": "tz"
            }
        },
        "identity": [
            {
                "root": provider.npi ? "2.16.840.1.113883.4.6" : oidFacility,
                "extension": provider.npi || provider.table_id || "NI"
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

        "phone": [{
            "number": all.encounter_provider.facility_phone || ""
        }]
    }
}

function populateProviders(all) {
    let providerArray = [];
    // primary provider
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
                    "name": all.primary_diagnosis.text || "",
                    "code": cleanCode(all.primary_diagnosis.code || ""),
                    "code_system_name": all.primary_diagnosis.code_type || ""
                },
                "provider": providerArray,
            }
    }
}


function populateCareTeamMember(provider) {
    return {
        //"function_code": provider.physician_type ? "PP" : "",
        "function_code": {
            "xmlns": "urn:hl7-org:sdtc",
            "name": provider.taxonomy_description || "",
            "code": cleanCode(provider.taxonomy) || "",
            "code_system": "2.16.840.1.113883.6.101",
            "code_system_name": "NUCC Health Care Provider Taxonomy"
        },
        "status": "active",
        "date_time": {
            "low": {
                "date": fDate(provider.provider_since) || fDate(""),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": provider.npi ? "2.16.840.1.113883.4.6" : oidFacility,
                "extension": provider.npi || provider.table_id
            }
        ],
        "full_name": provider.fname + " " + provider.lname,
        "name": {
            "last": provider.lname || "",
            "first": provider.fname || ""
        },
        "address": {
            "street_lines": [
                provider.street
            ],
            "city": provider.city,
            "state": provider.state,
            "zip": provider.zip,
            "country": all.encounter_provider.facility_country_code || "US"
        },
        "phone": [
            {
                "number": provider.telecom,
                "type": "work place"
            }
        ]
    }
}

function populateAuthorFromAuthorContainer(pd) {
    let author = pd.author || {};
    return {
        "code": {
            "name": author.physician_type || '',
            "code": author.physician_type_code || '',
            "code_system": author.physician_type_system,
            "code_system_name": author.physician_type_system_name
        },
        "date_time": {
            "point": {
                "date": fDate(author.time),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": author.npi ? "2.16.840.1.113883.4.6" : author.id,
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
    let providerArray = [];
    // primary provider
    let primaryCareProvider = pd.primary_care_provider || {provider: {}};
    let providerSince = fDate(primaryCareProvider.provider.provider_since || '');
    if (pd.primary_care_provider) {
        let provider = populateCareTeamMember(pd.primary_care_provider.provider);
        providerArray.push(provider);
        let count = isOne(pd.care_team.provider);
        if (count === 1) {
            provider = populateCareTeamMember(pd.care_team.provider);
            providerSince = providerSince || fDate(provider.provider_since);
            providerArray.push(provider);
        } else if (count > 1) {
            for (let i in pd.care_team.provider) {
                provider = populateCareTeamMember(pd.care_team.provider[i]);
                providerSince = providerSince || fDate(provider.provider_since);
                providerArray.push(provider);
            }
        }
    }
    return {
        "providers":
            {
                "provider": providerArray,
            },
        "status": "active",
        "date_time": {
            "low": {
                "date": providerSince || fDate(""),
                "precision": "tz"
            }
        },
        // we treat this author a bit differently since we are working at the main pd object instead of the sub pd.care_team
        "author": populateAuthorFromAuthorContainer(pd.care_team)
    }
}

function populateMedication(pd) {
    pd.status = 'Completed'; //@todo invoke prescribed
    return {
        "date_time": {
            "low": {
                "date": fDate(pd.start_date),
                "precision": "tz"
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
            "code": {
                "name": pd.author.physician_type || '',
                "code": pd.author.physician_type_code || '',
                "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(pd.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                    "extension": pd.author.npi ? pd.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": pd.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        pd.author.facility_name
                    ]
                }
            ]
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
                "code": {
                    "name": all.author.physician_type || '',
                    "code": all.author.physician_type_code || '',
                    "code_system": all.author.physician_type_system, "code_system_name": all.author.physician_type_system_name
                },
                "date_time": {
                    "point": {
                        "date": authorDateTime,
                        "precision": "tz"
                    }
                },
                "identifiers": [
                    {
                        "identifier": all.author.npi ? "2.16.840.1.113883.4.6" : all.author.id,
                        "extension": all.author.npi ? all.author.npi : 'NI'
                    }
                ],
                "name": [
                    {
                        "last": all.author.lname,
                        "first": all.author.fname
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
                            all.encounter_provider.facility_name
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

function getFinding(pd, problem) {
    const finding = {
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": ''
        }],
        "value": {
            "name": '',
            "code": '',
            "code_system_name": ''
        },
        "date_time": {
            "low": {
                "date": '',
                "precision": "day"
            }
        },
        "status": '',
        "reason": pd.encounter_reason,
        "author": {
            "code": {
                "name": all.author.physician_type || '',
                "code": all.author.physician_type_code || '',
                "code_system": all.author.physician_type_system, "code_system_name": all.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": authorDateTime,
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": all.author.npi ? "2.16.840.1.113883.4.6" : all.author.id,
                    "extension": all.author.npi ? all.author.npi : 'UNK'
                }
            ],
            "name": [
                {
                    "last": all.author.lname,
                    "first": all.author.fname
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
                        all.encounter_provider.facility_name
                    ]
                }
            ]
        },
    };

    finding.identifiers["0"].extension = problem.extension;
    finding.date_time.low.date = fDate(problem.date);
    finding.value.name = problem.text;
    finding.value.code = cleanCode(problem.code);
    finding.value.code_system_name = problem.code_type;
    finding.status = problem.status;
    return finding;
}

function populateEncounter(pd) {
    // just to get diagnosis. for findings..
    let findingObj = [];
    let theone = {};
    let count = 0;
    try {
        count = isOne(pd.encounter_problems.problem);
    } catch (e) {
        count = 0;
    }
    if (count > 1) {
        for (let i in pd.encounter_problems.problem) {
            theone[i] = getFinding(pd, pd.encounter_problems.problem[i]);
            findingObj.push(theone[i]);
        }
    } else if (count !== 0 && pd.encounter_problems.problem.code > '') {
        let finding = getFinding(pd, pd.encounter_problems.problem);
        findingObj.push(finding);
    }

    return {
        "encounter": {
            "name": pd.visit_category ? (pd.visit_category + " | " + pd.encounter_reason) : pd.code_description,
            "code": pd.code || "185347001",
            //"code_system": "2.16.840.1.113883.6.96",
            "code_system_name": pd.code_type || "SNOMED CT",
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
                "precision": "tz"
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
        "findings": findingObj
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
    let allergyAuthor = {
        "code": {
            "name": pd.author.physician_type || '',
            "code": pd.author.physician_type_code || '',
            "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
        },
        "date_time": {
            "point": {
                "date": fDate(pd.author.time),
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                "extension": pd.author.npi ? pd.author.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": pd.author.lname,
                "first": pd.author.fname
            }
        ],
        "organization": [
            {
                "identity": [
                    {
                        "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                        "extension": pd.author.facility_npi || "NI"
                    }
                ],
                "name": [
                    pd.author.facility_name
                ]
            }
        ]
    };

    return {
        "identifiers": [{
            "identifier": pd.sha_id,
            "extension": pd.id || ""
        }],
        "date_time": {
            "low": templateDate(pd.startdate, "day"),
            //"high": templateDate(pd.enddate, "day")
        },
        "author": allergyAuthor,
        "observation": {
            "identifiers": [{
                "identifier": pd.sha_extension || "2a620155-9d11-439e-92b3-5d9815ff4ee8",
                "extension": pd.id + 1 || ""
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
    let primary_care_provider = all.primary_care_provider || {provider: {}};
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
            "code": {
                "name": pd.author.physician_type || '',
                "code": pd.author.physician_type_code || '',
                "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(pd.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                    "extension": pd.author.npi ? pd.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": pd.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        pd.author.facility_name
                    ]
                }
            ]
        },
        "performer": [
            {
                "identifiers": [
                    {
                        "identifier": "2.16.840.1.113883.4.6",
                        "extension": primary_care_provider.provider.npi || ""
                    }
                ],
                "name": [
                    {
                        "last": primary_care_provider.provider.lname || "",
                        "first": primary_care_provider.provider.fname || ""
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
            "code_system_name": pd.code_type
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
        "author": populateAuthorFromAuthorContainer(pd),
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
        },
        "author": {
            "code": {
                "name": pd.author.physician_type || '',
                "code": pd.author.physician_type_code || '',
                "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(pd.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                    "extension": pd.author.npi ? pd.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": pd.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        pd.author.facility_name
                    ]
                }
            ]
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
    let result = {
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
        "value": value + "",
        "unit": pd.subtest.unit,
        "type": type,
        "range": pd.subtest.range,
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

    if (!results) return '';

    // not sure if the result set should be grouped better on the backend as the author information needs to be more nuanced here
    let tResult = results.result[0] || results.result;
    let resultSet = {
        "identifiers": [{
            "identifier": tResult.root,
            "extension": tResult.extension
        }],
        "author": populateAuthorFromAuthorContainer(tResult),
        "result_set": {
            "name": tResult.test_name,
            "code": cleanCode(tResult.test_code),
            "code_system_name": "LOINC"
        }
    };
    let rs = [];
    let many = [];
    let theone = {};
    let count = 0;
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
        let value = "";
        if (all.encounter_list && all.encounter_list.encounter && all.encounter_list.encounter.encounter_diagnosis) {
            value = all.encounter_list.encounter.encounter_diagnosis;
        }
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
            "identifier": pd.sha_extension,
            "extension": pd.extension || ""
        }],
        "goal": {
            "code": cleanCode(pd.code) || "",
            "name": cleanText(pd.description) || ""
        },
        "date_time": {
            "point": {
                "date": fDate(pd.date),
                "precision": "day"
            }
        },
        "type": planType,
        "status": {
            "code": cleanCode(pd.status)
        },
        "author": populateAuthorFromAuthorContainer(pd),
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
            "identifier": pd.sha_extension,
            "extension": pd.extension,
        }],
        "date_time": {
            "point": {
                "date": fDate(pd.date),
                "precision": "day"
            }
        },
        "type": "observation",
        "status": {
            "code": "active", //cleanCode(pd.status)
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "name": pd.description
    };
}

function getFunctionalStatus(pd) {
    let functionalStatusAuthor = {
        "code": {
            "name": all.author.physician_type || '',
            "code": all.author.physician_type_code || '',
            "code_system": all.author.physician_type_system, "code_system_name": all.author.physician_type_system_name
        },
        "date_time": {
            "point": {
                "date": authorDateTime,
                "precision": "tz"
            }
        },
        "identifiers": [
            {
                "identifier": all.author.npi ? "2.16.840.1.113883.4.6" : all.author.id,
                "extension": all.author.npi ? all.author.npi : 'NI'
            }
        ],
        "name": [
            {
                "last": all.author.lname,
                "first": all.author.fname
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
                    all.encounter_provider.facility_name
                ]
            }
        ]
    };

    return {
        "status": "completed",
        "author": functionalStatusAuthor,
        "identifiers": [{
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809000",
            "extension": pd.extension || '',
        }],

        "observation": {
            "value": {
                "name": pd.code_text !== "NULL" ? cleanText(pd.code_text) : "",
                "code": cleanCode(pd.code) || "",
                "code_system_name": pd.code_type || "SNOMED-CT"
            },
            "identifiers": [{
                "identifier": "9a6d1bac-17d3-4195-89a4-1121bc8090ab",
                "extension": pd.extension || '',
            }],
            "date_time": {
                "point": {
                    "date": fDate(pd.date),
                    "precision": "day"
                }
            },
            "status": "completed",
            "author": functionalStatusAuthor
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
            "identifier": "9a6d1bac-17d3-4195-89a4-1121bc809ccc",
            "extension": pd.extension,
        }],
        "note": cleanText(pd.description),
        "date_time": {
            "low": templateDate(pd.date, "day")
            //"high": templateDate(pd.date, "day")
        },
        "author": {
            "code": {
                "name": all.author.physician_type || '',
                "code": all.author.physician_type_code || '',
                "code_system": all.author.physician_type_system, "code_system_name": all.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": authorDateTime,
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": all.author.npi ? "2.16.840.1.113883.4.6" : all.author.id,
                    "extension": all.author.npi ? all.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": all.author.lname,
                    "first": all.author.fname
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
                        all.encounter_provider.facility_name
                    ]
                }
            ]
        }
    };
}

function getAssessments(pd) {
    return {
        "description": cleanText(pd.description),
        "author": populateAuthorFromAuthorContainer(pd)
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
        // todo need to make array of health concerns
        "type": "act",
        "text": cleanText(pd.text),
        "value": {
            "name": pd.code_text || "",
            "code": cleanCode(pd.code) || "",
            "code_system_name": pd.code_type || "SNOMED CT"
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "identifiers": [{
            "identifier": pd.sha_extension,
            "extension": pd.extension,
        }],
        problems: problems
    }
}

function getReferralReason(pd) {
    return {
        "reason": cleanText(pd.text),
        "author": populateAuthorFromAuthorContainer(pd)
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
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
            "author": populateAuthorFromAuthorContainer(pd),
        }
        ]
    }
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
            "name": pd.code
        },
        "element": pd.element,
        "value": pd.description,
        "gender": all.patient.gender,
        "author": {
            "code": {
                "name": pd.author.physician_type || '',
                "code": pd.author.physician_type_code || '',
                "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(pd.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                    "extension": pd.author.npi ? pd.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": pd.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        pd.author.facility_name
                    ]
                }
            ]
        }
        , "gender_author": {
            "code": {
                "name": all.patient.author.physician_type || '',
                "code": all.patient.author.physician_type_code || '',
                "code_system": all.patient.author.physician_type_system, "code_system_name": all.patient.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(all.patient.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": all.patient.author.npi ? "2.16.840.1.113883.4.6" : all.patient.author.id,
                    "extension": all.patient.author.npi ? all.patient.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": all.patient.author.lname,
                    "first": all.patient.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": all.patient.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": all.patient.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        all.patient.author.facility_name
                    ]
                }
            ]
        }
    };
}

function populateImmunization(pd) {
    return {
        "date_time": {
            "low": {
                "date": fDate(pd.administered_on),
                "precision": "day"
            }
        },
        "identifiers": [{
            "identifier": pd.sha_extension,
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
        },
        "author": {
            "code": {
                "name": pd.author.physician_type || '',
                "code": pd.author.physician_type_code || '',
                "code_system": pd.author.physician_type_system, "code_system_name": pd.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": fDate(pd.author.time),
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": pd.author.npi ? "2.16.840.1.113883.4.6" : pd.author.id,
                    "extension": pd.author.npi ? pd.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                }
            ],
            "organization": [
                {
                    "identity": [
                        {
                            "root": pd.author.facility_oid || "2.16.840.1.113883.4.6",
                            "extension": pd.author.facility_npi || "NI"
                        }
                    ],
                    "name": [
                        pd.author.facility_name
                    ]
                }
            ]
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
                "date": fDate(pd.date),
                "precision": "day"
            }
        },
        "translations": {
            code_system: "2.16.840.1.113883.6.1",
            code_system_name: "LOINC",
            code: cleanCode(pd.code),
            name: pd.code_text || ""
        },
        "author": populateAuthorFromAuthorContainer(pd),
        "note": cleanText(pd.description),
    };
}

function populateParticipant(participant) {
    return {
        "name": {
            "prefix": participant.prefix || "",
            "suffix": participant.suffix || "",
            "middle": [participant.mname] || "",
            "last": participant.lname || "",
            "first": participant.fname || ""
        },
        "typeCode": participant.type || "",
        "classCode": "ASSIGNED",
        "code": {
            "name": participant.organization_taxonomy_description || "",
            "code": cleanCode(participant.organization_taxonomy) || "",
            "code_system": "2.16.840.1.113883.6.101",
            "code_system_name": "NUCC Health Care Provider Taxonomy"
        },
        "identifiers": [{
            "identifier": participant.organization_npi ? "2.16.840.1.113883.4.6" : participant.organization_id,
            "extension": participant.organization_npi ? participant.organization_npi : ''
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
                    participant.street
                ],
                "city": participant.city,
                "state": participant.state,
                "zip": participant.postalCode,
                "country": participant.country || "US",
                "use": participant.address_use || "WP"
            }
        ],
    }
}

function populateHeader(pd) {
    // default doc type ToC CCD
    let name = "Summarization of Episode Note";
    let docCode = "34133-9";
    let docOid = "2.16.840.1.113883.10.20.22.1.2";
    if (pd.doc_type == 'referral') {
        name = "Referral Note";
        docCode = "57133-1";
        docOid = "2.16.840.1.113883.10.20.22.1.14";
    }

    if (pd.doc_type == 'unstructured') {
        name = "Patient Documents";
        docCode = "34133-9";
        docOid = "2.16.840.1.113883.10.20.22.1.10";
    }

    const head = {
        "identifiers": [
            {
                "identifier": oidFacility,
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
                "date": fDate(pd.created_time_timezone),
                "precision": "tz"
            }
        },
        "author": {
            "code": {
                "name": all.author.physician_type || '',
                "code": all.author.physician_type_code || '',
                "code_system": all.author.physician_type_system, "code_system_name": all.author.physician_type_system_name
            },
            "date_time": {
                "point": {
                    "date": authorDateTime,
                    "precision": "tz"
                }
            },
            "identifiers": [
                {
                    "identifier": all.author.npi ? "2.16.840.1.113883.4.6" : all.author.id,
                    "extension": all.author.npi ? all.author.npi : 'NI'
                }
            ],
            "name": [
                {
                    "last": all.author.lname,
                    "first": all.author.fname
                }
            ],
            "address": [
                {
                    "street_lines": [
                        all.author.streetAddressLine
                    ],
                    "city": all.author.city,
                    "state": all.author.state,
                    "zip": all.author.postalCode,
                    "country": all.author.country || "US",
                    "use": "work place"
                }
            ],
            "phone": [
                {
                    "number": all.author.telecom || "",
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
                            "country": all.encounter_provider.facility_country_code || "US",
                            "use": "work place"
                        }
                    ],
                    "phone": [
                        {
                            "number": all.encounter_provider.facility_phone,
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
                pd.custodian.organization || pd.custodian.name
            ],
            "address": [
                {
                    "street_lines": [
                        pd.custodian.streetAddressLine
                    ],
                    "city": pd.custodian.city,
                    "state": pd.custodian.state,
                    "zip": pd.custodian.postalCode,
                    "country": pd.custodian.country || "US"
                }
            ],
            "phone": [
                {
                    "number": pd.custodian.telecom,
                    "type": "work primary"
                }
            ]
        },
        "information_recipient": {
            "name": {
                "prefix": pd.information_recipient.prefix || "",
                "suffix": pd.information_recipient.suffix || "",
                "middle": [pd.information_recipient.mname] || "",
                "last": pd.information_recipient.lname || "",
                "first": pd.information_recipient.fname || ""
            },
            "organization": {
                "name": pd.information_recipient.organization || "org"
            },
        }
    };
    let participants = [];
    let docParticipants = pd.document_participants || {participant: []};
    let count = 0;
    try {
        count = isOne(docParticipants.participant);
    } catch (e) {
        count = 0
    }
    if (count === 1) {
        participants = [populateParticipant(docParticipants.participant)];
    } else {
        // grab the values of our object
        participants = Object.values(docParticipants.participant).filter(pcpt => pcpt.type).map(pcpt => populateParticipant(pcpt));
    }
    if (participants.length) {
        head.participants = participants;
    }

    if (isOne(all.encounter_list.encounter) === 1) {
        let primary_care_provider = pd.primary_care_provider || {provider: {}};
        head.component_of = {
            "identifiers": [
                {
                    "identifier": oidFacility || "",
                    "extension": "PT-" + (pd.patient.id || "")
                }
            ],
            "code": {
                "name": pd.primary_diagnosis.text || "",
                "code": pd.primary_diagnosis.code || "",
                "code_system_name": pd.primary_diagnosis.code_type || ""
            },
            "date_time": {
                "low": {
                    "date": pd.primary_diagnosis.encounter_date || "",
                    "precision": "tz"
                },
                "high": {
                    "date": pd.primary_diagnosis.encounter_end_date || "",
                    "precision": "tz"
                }
            },
            "responsible_party": {
                "root": oidFacility,
                "name": {
                    "last": pd.author.lname,
                    "first": pd.author.fname
                },
            },
            "encounter_participant": {
                "root": oidFacility,
                "name": {
                    "last": primary_care_provider.provider.lname || "",
                    "first": primary_care_provider.provider.fname || ""
                },
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
        }
    }

    return head;
}

function getMeta(pd) {
    let meta = {};
    meta = {
        "type": pd.doc_type,
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
 / * function generateCcda
 /* The main document builder
 /* pd array the xml parsed array of data sent from CCM.
 */
function generateCcda(pd) {
    let doc = {};
    let data = {};
    let count = 0;
    let many = [];
    let theone = {};
    all = pd;
    let primary_care_provider = all.primary_care_provider || {};
    npiProvider = primary_care_provider.provider ? primary_care_provider.provider.npi : "NI";
    oidFacility = all.encounter_provider.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
    npiFacility = all.encounter_provider.facility_npi;
    webRoot = all.serverRoot;
    documentLocation = all.document_location;

    authorDateTime = pd.created_time_timezone;
    if (pd.author.time.length > 7) {
        authorDateTime = pd.author.time;
    } else if (all.encounter_list && all.encounter_list.encounter) {
        if (isOne(all.encounter_list.encounter) === 1) {
            authorDateTime = all.encounter_list.encounter.date;
        } else {
            authorDateTime = all.encounter_list.encounter[0].date;
        }
    }

    authorDateTime = fDate(authorDateTime);
// Demographics
    let demographic = populateDemographic(pd.patient, pd.guardian, pd);
// This populates documentationOf. We are using providerOrganization also.
    if (pd.primary_care_provider) {
        Object.assign(demographic, populateProviders(pd));
    }
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
    let vitals = [];
    let vital = {};
    vitals.vitals = [];
    try {
        count = isOne(pd.history_physical.vitals_list.vitals);
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

// Referral TODO sjp I'm not happy with this.
    // different referral sources. 1st is dynamic with doc gen from CCM.
    // 2nd is the latest referral from transactions.
    if (pd.referral_reason[0].text !== "") {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[0], pd));
    } else if (pd.referral_reason[1].text !== "" && typeof pd.referral_reason[1].text !== 'undefined') {
        data.referral_reason = Object.assign(getReferralReason(pd.referral_reason[1], pd));
    } else {
        data.referral_reason = {}; // leave as empty so we can get our null flavor section.
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
    } else {
        data.health_concerns = {"type": "act"}; // leave it as an empty section that we'll null flavor
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
            if (cleanCode(pd.planofcare.item[i].date) === '') {
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
// Care Team and members
    if (pd.care_team.is_active == 'active') {
        data.care_team = Object.assign(populateCareTeamMembers(pd));
    }

// ------------------------------------------ End Sections ---------------------------------------- //

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
    let doc = {};
    let data = {};
    let count = 0;
    let many = [];
    let theone = {};
    // include unstructured document type oid in header
    pd.doc_type = 'unstructured';
    all = pd;
    let primary_care_provider = all.primary_care_provider || {};
    npiProvider = primary_care_provider.provider ? primary_care_provider.provider.npi : "NI";
    oidFacility = all.encounter_provider.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
    npiFacility = all.encounter_provider.facility_npi || "NI";
    webRoot = all.serverRoot;
    documentLocation = all.document_location;
    authorDateTime = pd.created_time_timezone;
    if (pd.author.time.length > 7) {
        authorDateTime = pd.author.time;
    } else if (all.encounter_list && all.encounter_list.encounter) {
        if (isOne(all.encounter_list.encounter) === 1) {
            authorDateTime = all.encounter_list.encounter.date;
        } else {
            authorDateTime = all.encounter_list.encounter[0].date;
        }
    }
    authorDateTime = fDate(authorDateTime);
// Demographics is needed in unstructured
    let demographic = populateDemographic(pd.patient, pd.guardian, pd);
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
    unstructuredTemplate = unstructuredTemplate.trim();
    xml = xml.replace(/<\/ClinicalDocument>/g, unstructuredTemplate);
    xml += "</ClinicalDocument>" + "\n";

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

    let xml_complete = "";

    function eventData(xml) {
        xml_complete = xml.toString();
        // ensure we have an array start and end
        if (xml_complete.match(/^<CCDA/g) && xml_complete.match(/<\/CCDA>$/g)) {
            let doc = "";
            let xslUrl = "";
            xml_complete = xml_complete.replace(/(\u000b\u001c)/gm, "").trim();
            xml_complete = xml_complete.replace(/\t\s+/g, ' ').trim();
            // convert xml data set for document to json array
            to_json(xml_complete, function (error, data) {
                if (error) {
                    console.log('toJson error: ' + error + 'Len: ' + xml_complete.length);
                    return 'ERROR: Failed json build';
                }
                let unstructured = "";
                let isUnstruturedData = !!data.CCDA.patient_files;
                // extract unstructured documents file component templates. One per file.
                if (isUnstruturedData) {
                    unstructuredTemplate = xml_complete.substring(xml_complete.lastIndexOf('<patient_files>') + 15, xml_complete.lastIndexOf('</patient_files>'));
                }
                // create doc_type document i.e. CCD Referral etc.
                if (data.CCDA.doc_type !== 'unstructured') {
                    doc = generateCcda(data.CCDA);
                    if (data.CCDA.xslUrl) {
                        xslUrl = data.CCDA.xslUrl || "";
                    }
                    doc = headReplace(doc, xslUrl);
                } else {
                    unstructured = generateUnstructured(data.CCDA);
                    if (data.CCDA.xslUrl) {
                        xslUrl = data.CCDA.xslUrl || "";
                    }
                    doc = headReplace(unstructured, xslUrl);
                    // combine the two documents to send back all at once.
                    doc += unstructured;
                }
                // auto build an Unstructured document of supplied embedded files.
                if (data.CCDA.doc_type !== 'unstructured' && isUnstruturedData) {
                    unstructured = generateUnstructured(data.CCDA);
                    unstructured = headReplace(unstructured, xslUrl);
                    // combine the two documents to send back all at once.
                    doc += unstructured;
                }
            });
            // send results back to eagerly awaiting CCM for disposal.
            doc = doc.toString().replace(/(\u000b\u001c|\r)/gm, "").trim();
            let chunk = "";
            let numChunks = Math.ceil(doc.length / 1024);
            for (let i = 0, o = 0; i < numChunks; ++i, o += 1024) {
                chunk = doc.substring(o, o + 1024);
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
        console.log(err.stack);
        conn.destroy();
    }

// Connection Events //
    // CCM will send one File Separator characters to mark end of array.
    let received = new DataStack(String.fromCharCode(28));
    conn.on("data", data => {
        received.pushToStack(data);
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
    server.listen(6661, 'localhost', function () { // never change port!
        //console.log('server listening to %j', server.address());
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
