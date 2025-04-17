"use strict";

var oids = require("./oids");

var codeSystem = {
    codeDisplayName: function (code) {
        return this.cs.table && this.cs.table[code];
    },
    displayNameCode: (function () {
        var reverseTables = {};

        return function (name) {
            var oid = this.oid;
            var reverseTable = reverseTables[oid];
            if (!reverseTable) {
                var table = this.cs.table || {};
                reverseTable = Object.keys(table).reduce(function (r, code) {
                    var name = table[code];
                    r[name] = code;
                    return r;
                }, {});
                reverseTables[oid] = reverseTable;
            }
            return reverseTable[name];
        };
    })(),
    name: function () {
        return this.cs.name;
    },
    systemId: function () {
        var systemOID = this.cs.code_system;
        if (systemOID) {
            return {
                codeSystem: systemOID,
                codeSystemName: oids[systemOID].name
            };
        } else {
            return {
                codeSystem: this.oid,
                codeSystemName: this.cs.name
            };
        }
    }
};

exports.find = function (oid) {
    var cs = oids[oid];
    if (cs) {
        var result = Object.create(codeSystem);
        result.oid = oid;
        result.cs = cs;
        return result;
    } else {
        return null;
    }
};

exports.findFromName = (function () {
    var nameIndex;

    return function (name) {
        if (!nameIndex) {
            nameIndex = Object.keys(oids).reduce(function (r, oid) {
                var n = oids[oid].name;
                r[n] = oid;
                return r;
            }, {});
        }
        return nameIndex[name];
    };
})();
