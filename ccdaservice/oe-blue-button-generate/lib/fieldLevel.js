"use strict";

var bbm = require("../../oe-blue-button-meta");
var uuid = require('uuid');

var condition = require("./condition");
var leafLevel = require("./leafLevel");
var translate = require("./translate");
var contentModifier = require("./contentModifier");

var templateCodes = bbm.CCDA.sections_entries_codes.codes;

var key = contentModifier.key;
var required = contentModifier.required;

var moment = require('moment');

exports.templateId = function (id) {
    return {
        key: "templateId",
        attributes: {
            "root": id
        }
    };
};

exports.templateIdExt = function (id, ext) {
    return {
        key: "templateId",
        attributes: {
            "root": id,
            "extension": ext
        }
    };
};
var templateId = function (id) {
    return {
        key: "templateId",
        attributes: {
            "root": id
        }
    };
};
exports.templateCode = function (name) {
    var raw = templateCodes[name];
    var result = {
        key: "code",
        attributes: {
            code: raw.code,
            displayName: raw.name,
            codeSystem: raw.code_system,
            codeSystemName: raw.code_system_name
        }
    };
    return result;
};

exports.templateTitle = function (name) {
    var raw = templateCodes[name];
    var result = {
        key: "title",
        text: raw.name,
    };
    return result;
};

var id = exports.id = {
    key: "id",
    attributes: {
        root: leafLevel.inputProperty("identifier"),
        extension: leafLevel.nonEmptyInputProperty("extension")
    },
    dataKey: 'identifiers',
    existsWhen: condition.keyExists('identifier'),
    required: true
};

exports.uniqueId = {
    key: "id",
    attributes: {
        root: function (input, context) {
            return context.rootId;
        },
        extension: function () {
            return uuid.v4();
        }
    },
    existsWhen: function (input, context) {
        return context.rootId;
    }
};

exports.uniqueIdRoot = {
    key: "id",
    attributes: {
        root: function (input, context) {
            return uuid.v4();
        }
    }
};

exports.statusCodeCompleted = {
    key: "statusCode",
    attributes: {
        code: 'completed'
    }
};

exports.statusCodeActive = {
    key: "statusCode",
    attributes: {
        code: 'active'
    }
};

exports.statusCodeNew = {
    key: "statusCode",
    attributes: {
        code: 'new'
    }
};

var effectiveDocumentTime = exports.effectiveDocumentTime = {
    key: "effectiveTime",
    attributes: {
        "value": leafLevel.inputProperty("date"),
    },
    dataKey: 'meta.ccda_header.date_time'
};

var effectiveTimeNow = exports.effectiveTimeNow = {
    key: "effectiveTime",
    attributes: {
        "value": moment().format("YYYYMMDDHHMMSS"),
    }
};

var timeNow = exports.timeNow = {
    key: "time",
    attributes: {
        "value": moment().format("YYYYMMDD"),
    }
};

var timeDocumentTime = exports.timeDocumentTime = {
    key: "time",
    attributes: leafLevel.timeAttr
};

var effectiveTime = exports.effectiveTime = {
    key: "effectiveTime",
    attributes: leafLevel.timeAttr,
    attributeKey: 'point',
    content: [{
        key: "low",
        attributes: leafLevel.timeAttr,
        dataKey: 'low',
    }, {
        key: "high",
        attributes: leafLevel.timeAttr,
        dataKey: 'high',
    }, {
        key: "center",
        attributes: leafLevel.timeAttr,
        dataKey: 'center',
    }],
    dataKey: 'date_time',
    existsWhen: condition.eitherKeyExists('point', 'low', 'high', 'center')
};

var effectiveTimeIVL_TS = exports.effectiveTimeIVL_TS = {
    key: "effectiveTime",
    attributes: {
        "xsi:type": "IVL_TS"
    },
    //attributeKey: 'point',
    content: [{
        key: "low",
        attributes: leafLevel.timeAttr,
        dataKey: 'low',
    }, {
        key: "high",
        attributes: leafLevel.timeAttr,
        dataKey: 'high',
    }, {
        key: "center",
        attributes: leafLevel.timeAttr,
        dataKey: 'center',
    }],
    dataKey: 'date_time',
    existsWhen: condition.eitherKeyExists('point', 'low', 'high', 'center')
};

exports.text = function (referenceMethod) {
    return {
        key: "text",
        text: leafLevel.inputProperty("free_text"),
        content: {
            key: "reference",
            attributes: {
                "value": referenceMethod
            },
        }
    };
};

exports.nullFlavor = function (name) {
    return {
        key: name,
        attributes: {
            nullFlavor: "UNK"
        }
    };
};


var useablePeriod = exports.useablePeriod = {
    key: "useablePeriod",
    attributes: {
        "xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
        "xsi:type": "IVL_TS"
    },
    content: [{
        key: "low",
        attributes: leafLevel.timeAttr,
        dataKey: 'low',
    }, {
        key: "high",
        attributes: leafLevel.timeAttr,
        dataKey: 'high',
    }],
    dataKey: 'date_time',
    existsWhen: condition.eitherKeyExists('point', 'low', 'high')
};

var usRealmAddress = exports.usRealmAddress = {
    key: "addr",
    attributes: {
        use: leafLevel.use("use")
    },
    content: [{
        key: "streetAddressLine",
        text: leafLevel.input,
        dataKey: "street_lines",
        existsWhen: condition.propertyNotEmpty("street_lines[0]")
    }, {
        key: "city",
        text: leafLevel.inputProperty("city"),
        existsWhen: condition.propertyNotEmpty("city")
    }, {
        key: "state",
        text: leafLevel.inputProperty("state"),
        existsWhen: condition.propertyNotEmpty("state")
    }, {
        key: "postalCode",
        text: leafLevel.inputProperty("zip"),
        existsWhen: condition.propertyNotEmpty("zip")
    }, {
        key: "country",
        text: leafLevel.inputProperty("country"),
        existsWhen: condition.propertyNotEmpty("country")
    }, useablePeriod,
    ],
    dataKey: "address"
};

var usRealmName = exports.usRealmName = {
    key: "name",
    content: [{
        key: "family",
        text: leafLevel.inputProperty("family")
    }, {
        key: "given",
        text: leafLevel.input,
        dataKey: "given"
    }, {
        key: "prefix",
        text: leafLevel.inputProperty("prefix")
    }, {
        key: "suffix",
        text: leafLevel.inputProperty("suffix")
    }],
    dataKey: "name",
    dataTransform: translate.name
};

var telecom = exports.telecom = {
    key: "telecom",
    attributes: {
        value: leafLevel.inputProperty("value"),
        use: leafLevel.inputProperty("use")
    },
    dataTransform: translate.telecom
};

// True when a plain text node (already navigated to via dataKey) has content.
// Used to suppress empty <name/> organization names (empty ST/ON).
var nonEmptyText = function (input) {
    return (input !== null) && (input !== undefined) && (input.toString().trim() !== "");
};

// True when an organization carries a usable identifier or name, so an empty
// <representedOrganization/> (no id, no name) is suppressed rather than emitted.
var organizationHasContent = function (input) {
    if (!input) {
        return false;
    }
    var idHasContent = function (id) {
        return id && (nonEmptyText(id.root) || nonEmptyText(id.extension));
    };
    var identity = input.identity;
    if (Array.isArray(identity)) {
        for (var i = 0; i < identity.length; ++i) {
            if (idHasContent(identity[i])) {
                return true;
            }
        }
    } else if (idHasContent(identity)) {
        return true;
    }
    var name = input.name;
    if (Array.isArray(name)) {
        for (var j = 0; j < name.length; ++j) {
            if (nonEmptyText(name[j])) {
                return true;
            }
        }
    } else if (nonEmptyText(name)) {
        return true;
    }
    return false;
};

var representedOrganization = {
    key: "representedOrganization",
    content: [
        {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("root"),
                extension: leafLevel.nonEmptyInputProperty("extension"),
            },
            dataKey: "identity"
        }, {
            key: "name",
            text: leafLevel.input,
            dataKey: "name",
            existsWhen: nonEmptyText
        },
        //usRealmAddress,
        //telecom
    ],
    dataKey: "organization",
    existsWhen: organizationHasContent
};

var assignedEntity = exports.assignedEntity = {
    key: "assignedEntity",
    content: [id, {
        key: "code",
        attributes: leafLevel.code,
        dataKey: "code"
    },
        usRealmAddress,
        telecom, {
            key: "assignedPerson",
            content: usRealmName,
            existsWhen: condition.keyExists("name")
        },
        representedOrganization
    ],
    existsWhen: condition.eitherKeyExists("address", "identifiers", "organization", "name")
};

var associatedEntity = exports.associatedEntity = {
    key: "associatedEntity"
    , attributes: {
        classCode: leafLevel.inputProperty("classCode"),
    },
    content: [
        id,
        {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "code"
        },
        usRealmAddress,
        telecom,
        {
            key: "associatedPerson",
            content: usRealmName,
            existsWhen: condition.keyExists("name"),
            attributes: {
                classCode: "PSN",
                determinerCode: "INSTANCE"
            }
        }
    ]
};

// True when a (post-translate.name) name object carries at least one usable
// person name part. Used to decide between a fielded name and a nullFlavor name
// for the Author Participation assignedPerson.
var authorPersonNameHasContent = function (input) {
    if (!input) {
        return false;
    }
    var hasValue = function (value) {
        return (value !== null) && (value !== undefined) && (value.toString().trim() !== "");
    };
    if (hasValue(input.family)) {
        return true;
    }
    var given = input.given;
    if (Array.isArray(given)) {
        for (var i = 0; i < given.length; ++i) {
            if (hasValue(given[i])) {
                return true;
            }
        }
    } else if (hasValue(given)) {
        return true;
    }
    return hasValue(input.prefix) || hasValue(input.suffix);
};

// US Realm Person Name for an author, emitting only populated name parts so an
// absent last name never produces an empty <family/> (MDHT validateST on the
// ENXP; CDA R2 datatypes). Fires only when a usable name part is present.
var authorPersonName = {
    key: "name",
    content: [{
        key: "family",
        text: leafLevel.inputProperty("family"),
        existsWhen: condition.propertyNotEmpty("family")
    }, {
        key: "given",
        text: leafLevel.input,
        dataKey: "given"
    }, {
        key: "prefix",
        text: leafLevel.inputProperty("prefix"),
        existsWhen: condition.propertyNotEmpty("prefix")
    }, {
        key: "suffix",
        text: leafLevel.inputProperty("suffix"),
        existsWhen: condition.propertyNotEmpty("suffix")
    }],
    dataKey: "name",
    dataTransform: translate.name,
    existsWhen: authorPersonNameHasContent
};

// When the author has no usable person name, the name is unknown rather than
// empty: emit <name nullFlavor="UNK"/> so the PN/ENXP datatype requirement is
// satisfied. Author Participation urn:oid:2.16.840.1.113883.10.20.22.4.119.
var authorPersonNameNullFlavor = {
    key: "name",
    attributes: {
        nullFlavor: "UNK"
    },
    dataKey: "name",
    dataTransform: translate.name,
    existsWhen: function (input) {
        return !authorPersonNameHasContent(input);
    }
};

exports.author = {
    key: "author",
    attributes: {
        typeCode: "AUT"
    },
    content: [
        templateId("2.16.840.1.113883.10.20.22.4.119"),
        [effectiveTime, required, key("time")], {
            key: "assignedAuthor",
            content: [
                id, {
                    key: "code",
                    attributes: leafLevel.code,
                    existsWhen: condition.propertyNotEmpty('code'),
                    dataKey: "code"
                }, {
                    key: "assignedPerson",
                    content: [authorPersonName, authorPersonNameNullFlavor]
                },
                representedOrganization
            ]
        }
    ],
    dataKey: "author"
};

exports.performer = {
    key: "performer",
    content: [
        [assignedEntity, required]
    ],
    dataKey: "performer"
};

var linkedRepresentedOrganization = {
    key: "representedOrganization",
    content: [
        {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("root")
            },
            dataKey: "identity"
        }, {
            key: "name",
            text: leafLevel.input,
            dataKey: "name",
            existsWhen: nonEmptyText
        }
    ],
    dataKey: "organization",
    existsWhen: organizationHasContent
};

exports.actAuthor = {
    key: "author",
    content: [
        templateId("2.16.840.1.113883.10.20.22.4.119"),
        [effectiveTime, required, key("time")], {
            key: "assignedAuthor",
            content: [
                id, {
                    key: "assignedPerson",
                    content: [authorPersonName, authorPersonNameNullFlavor]
                },
                linkedRepresentedOrganization
            ]
        }
    ],
    dataKey: "author"
};

var responsibleParty = exports.responsibleParty = {
    key: "responsibleParty",
    content: [{
        key: "assignedEntity",
        content: [{
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("root")
            },
        }, {
            key: "assignedPerson",
            content: usRealmName
        }]
    }
    ],
    dataKey: "responsible_party",
    existsWhen: condition.propertyValueNotEmpty("name.last")
};
