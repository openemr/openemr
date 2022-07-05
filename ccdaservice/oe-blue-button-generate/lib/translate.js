"use strict";

var moment = require("moment");
var bbm = require("../../oe-blue-button-meta");

var css = bbm.code_systems;

exports.codeFromName = function (OID) {
    return function (input) {
        if (input === 'null_flavor') {
            return {
                nullFlavor: "UNK"
            };
        }
        var cs = css.find(OID);
        var code = cs ? cs.displayNameCode(input) : undefined;
        var systemInfo = cs.systemId(OID);
        return {
            "displayName": input,
            "code": code,
            "codeSystem": systemInfo.codeSystem,
            "codeSystemName": systemInfo.codeSystemName
        };
    };
};

exports.code = function (input) {
    var result = {};

    if (input.code === 'null_flavor' || input.code === '') {
        return {
            nullFlavor: "UNK"
        };
    }

    if (input.xmlns) {
        result.xmlns = input.xmlns;
    }

    if (input.code) {
        result.code = input.code;
    }

    if (input.name) {
        result.displayName = input.name;
    }

    if (input.code_system_name === "ICD10") {
        input.code_system_name = "ICD-10-CM";
    }

    if (input.code_system_name === "SNOMED" || input.code_system_name === "SNOMED-CT") {
        input.code_system_name = "SNOMED CT";
    }
    var code_system = input.code_system || (input.code_system_name && css.findFromName(input.code_system_name));
    if (code_system) {
        result.codeSystem = code_system;
    }

    if (input.code_system_name) {
        result.codeSystemName = input.code_system_name;
    }

    return result;
};

var precisionToFormat = {
    year: 'YYYY',
    month: 'YYYYMM',
    day: 'YYYYMMDD',
    hour: 'YYYYMMDDHH',
    minute: 'YYYYMMDDHHmm',
    second: 'YYYYMMDDHHmmss',
    tz: 'YYYYMMDDHHmmZZ'
};

exports.time = function (input) {
    let result = '';
    var m = moment.parseZone(input.date);
    if (m._isValid !== true) {
        m = moment(input.date, "YYYYMMDD HH:mm:ss")
    }
    let formatSpec = precisionToFormat[input.precision];
    if (input.precision === 'tz') {
        formatSpec = precisionToFormat['tz'];
        if (input.timezoneOffset) {
            result =  m.utcOffset(input.timezoneOffset).format(formatSpec);
        } else {
            result =  m.format(formatSpec);
        }
        return result;
    }
    result = m.format(formatSpec);
    return result;
};

var acronymize = exports.acronymize = function (string) {
    let ret = string.split(" ");
    if (ret.length > 1) {
        let fL = ret[0].slice(0, 1);
        let lL = ret[1].slice(0, 1);
        fL = fL.toUpperCase();
        lL = lL.toUpperCase();
        ret = fL + lL;
    } else {
        ret = string;
    }
    if (ret === "PH") {
        ret = "HP";
    }
    if (ret === "PM") {
        ret = "MC";
    }
    if (ret === "HA") {
        ret = "H";
    }
    if (ret === "CE") {
        ret = "EM";
    }
    if (ret.toUpperCase() === "WORK") {
        ret = "WP";
    }
    if (ret.toUpperCase() === "HOME") {
        ret = "H";
    }
    if (ret.toUpperCase() === "TEMP") {
        ret = "TMP";
    }
    if (ret.toUpperCase() === "BILLING") {
        ret = "PST";
    }
    return ret;
};

exports.telecom = function (input) {
    var transformPhones = function (input) {
        var phones = input.phone;
        if (phones) {
            return phones.reduce(function (r, phone) {
                if (phone && phone.number) {
                    var attrs = {
                        value: "tel:" + phone.number
                    };
                    if (phone.type) {
                        attrs.use = acronymize(phone.type);
                    }
                    r.push(attrs);
                } else if (phone && phone.email) {
                    var attrs = {
                        value: "mailto:" + phone.email
                    };
                    r.push(attrs);
                }
                return r;
            }, []);
        } else {
            return [];
        }
    };

    var transformEmails = function (input) {
        var emails = input.email;
        if (emails) {
            return emails.reduce(function (r, email) {
                if (email && email.address) {
                    var attrs = {
                        value: "mailto:" + email.address
                    };
                    if (email.type) {
                        attrs.use = acronymize(email.type);
                    }
                    r.push(attrs);
                }
                return r;
            }, []);
        } else {
            return [];
        }
    };

    var result = [].concat(transformPhones(input), transformEmails(input));
    return result.length === 0 ? null : result;
};

var nameSingle = function (input) {
    let given = null;
    if (input.first) {
        given = [input.first];
        if (input.middle && input.middle[0]) {
            given.push(input.middle[0]);
        }
    }
    let name = {
        given: given,
        family: input.last
    };
    if (input.suffix) {
        name.suffix = input.suffix
    }
    if (input.prefix) {
        name.prefix = input.prefix
    }
    return name;
};

exports.name = function (input) {
    if (Array.isArray(input)) {
        return input.map(function (e) {
            return nameSingle(e);
        });
    } else {
        return nameSingle(input);
    }
};
