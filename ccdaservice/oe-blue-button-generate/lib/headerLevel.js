"use strict";

var fieldLevel = require('./fieldLevel');
var leafLevel = require('./leafLevel');
var condition = require('./condition');
var contentModifier = require("./contentModifier");
var translate = require("./translate");
var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var patientName = Object.create(fieldLevel.usRealmName);
patientName.attributes = {
    use: "L"
};

var patient = exports.patient = {
    key: "patient",
    content: [
        patientName,
        {
            key: "name",
            content: [{
                key: "given",
                attributes: {
                    qualifier: "BR"
                },
                text: leafLevel.inputProperty("first")
            }, {
                key: "given",
                text: leafLevel.inputProperty("middle"),
                existsWhen: condition.propertyNotEmpty("middle")
            }, {
                key: "family",
                attributes: {
                    qualifier: "BR"
                },
                text: leafLevel.inputProperty("last")
            }],
            dataKey: "birth_name",
            existsWhen: condition.propertyNotEmpty("last")
        }, {
            key: "administrativeGenderCode",
            attributes: {
                code: function (input) {
                    if (Object.prototype.toString.call(input) === "[object String]")
                        return input.substring(0, 1);
                    else return input.code.substring(0, 1);
                },
                codeSystem: "2.16.840.1.113883.5.1",
                codeSystemName: "HL7 AdministrativeGender",
                displayName: leafLevel.input
            },
            dataKey: "gender"
        },
        [fieldLevel.effectiveTime, key("birthTime"), dataKey("dob")], {
            key: "maritalStatusCode",
            attributes: {
                code: function (input) {
                    if (Object.prototype.toString.call(input) === "[object String]") {
                        return input.substring(0, 1);
                    } else {
                        return input.code.substring(0, 1);
                    }
                },
                displayName: leafLevel.input,
                codeSystem: "2.16.840.1.113883.5.2",
                codeSystemName: "HL7 Marital Status"
            },
            dataKey: "marital_status",
        }, {
            key: "religiousAffiliationCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.5.1076"),
            dataKey: "religion"
        }, {
            key: "raceCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.6.238"),
            dataKey: "race"
        }, {
            key: "sdtc:raceCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.6.238"),
            dataKey: "race_additional"
        }, {
            key: "ethnicGroupCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.6.238"),
            dataKey: "ethnicity"
        }, {
            key: "guardian",
            content: [{
                key: "code",
                attributes: leafLevel.codeFromName("2.16.840.1.113883.5.111"),
                dataKey: "relation"
            },
                [fieldLevel.usRealmAddress, dataKey("addresses")],
                fieldLevel.telecom, {
                    key: "guardianPerson",
                    content: {
                        key: "name",
                        content: [{
                            key: "given",
                            text: leafLevel.inputProperty("first")
                        }, {
                            key: "family",
                            text: leafLevel.inputProperty("last")
                        }],
                        dataKey: "names"
                    }
                }
            ],
            dataKey: "guardians"
        }, {
            key: "birthplace",
            content: {
                key: "place",
                content: [
                    [fieldLevel.usRealmAddress, dataKey("birthplace")]
                ]
            },
            existsWhen: condition.keyExists("birthplace")
        }, {
            key: "languageCommunication",
            content: [{
                key: "languageCode",
                attributes: {
                    code: leafLevel.input
                },
                dataKey: "language"
            }, {
                key: "modeCode",
                attributes: leafLevel.codeFromName("2.16.840.1.113883.5.60"),
                dataKey: "mode"
            }, {
                key: "proficiencyLevelCode",
                attributes: {
                    code: function (input) {
                        if (Object.prototype.toString.call(input) === "[object String]")
                            return input.substring(0, 1);
                        else return input.code.substring(0, 1);
                    },
                    displayName: leafLevel.input,
                    codeSystem: "2.16.840.1.113883.5.61",
                    codeSystemName: "LanguageAbilityProficiency"
                },
                dataKey: "proficiency"
            }, {
                key: "preferenceInd",
                attributes: {
                    value: function (input) {
                        return input.toString();
                    }
                },
                dataKey: "preferred"
            }],
            dataKey: "languages"
        }
    ]
};

var provider = exports.provider = [{
    key: "performer",
    attributes: {
        typeCode: "PRF"
    },
    content: [
        {
            key: "functionCode",
            attributes: {
                "code": "PP",
                "displayName": "Primary Performer",
                "codeSystem": "2.16.840.1.113883.12.443",
                "codeSystemName": "Provider Role"
            },
            existsWhen: condition.propertyNotEmpty('function_code'),
            content: [{key: "originalText", text: "Primary Care Provider"}]
        },
        {
            key: "assignedEntity",
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("root"),
                    extension: leafLevel.inputProperty("extension")
                },
                dataKey: "identity"
            }, {
                key: "code",
                attributes: leafLevel.code,
                content: [{key: "originalText", text: "Care Team Member"}],
                dataKey: "type"
            },
                fieldLevel.usRealmAddress,
                fieldLevel.telecom,
                {
                    key: "assignedPerson",
                    content: fieldLevel.usRealmName
                }
            ]
        }
    ],
    dataKey: "providers.provider"
}];

var providers = exports.providers = {
    key: "documentationOf",
    attributes: {
        typeCode: "DOC"
    },
    content: {
        key: "serviceEvent",
        attributes: {
            classCode: "PCPR"
        },
        content: [
            {
                key: "code",
                attributes: leafLevel.code,
                existsWhen: condition.propertyNotEmpty('code'),
                dataKey: "providers.code"
            },
            [fieldLevel.effectiveTime, key("effectiveTime"), dataKey("providers.date_time"), required],
            provider
        ]
    },
    dataKey: "data.demographics"
};

var participants = (exports.participant = [
    {
        key: "participant",
        attributes: {
            typeCode: leafLevel.inputProperty("typeCode"),
        },
        // TODO: check if this middle element can be removed
        /* eslint-disable-next-line no-sparse-arrays */
        content: [
            [fieldLevel.effectiveTime, required, key("time")],
            ,
            // associatedEntity
            fieldLevel.associatedEntity,
        ],
        dataKey: "meta.ccda_header.participants",
    },
]);

var attributed_provider = exports.attributed_provider = {
    key: "providerOrganization",
    content: [{
        key: "id",
        attributes: {
            root: leafLevel.inputProperty("root"),
            extension: leafLevel.inputProperty("extension")
        },
        dataKey: "identity"
    }, {
        key: "name",
        text: leafLevel.inputProperty("full"),
        dataKey: "name"
    }, {
        key: "telecom",
        attributes: [{
            use: "WP",
            value: function (input) {
                return input.number;
            }
        }],
        dataKey: "phone"
    }, {
        key: "addr",
        attributes: {
            use: leafLevel.use("use")
        },
        content: [{
            key: "country",
            text: leafLevel.inputProperty("country")
        }, {
            key: "state",
            text: leafLevel.inputProperty("state")
        }, {
            key: "city",
            text: leafLevel.inputProperty("city")
        }, {
            key: "postalCode",
            text: leafLevel.inputProperty("zip")
        }, {
            key: "streetAddressLine",
            text: leafLevel.input,
            dataKey: "street_lines"
        }],
        dataKey: "address"
    }],
    dataKey: "attributed_provider"
};

var recordTarget = exports.recordTarget = {
    key: "recordTarget",
    content: {
        key: "patientRole",
        content: [
            fieldLevel.id, [fieldLevel.usRealmAddress, dataKey("addresses")],
            fieldLevel.telecom,
            patient,
            attributed_provider
        ]
    },
    dataKey: "data.demographics"
};

var headerAuthor = exports.headerAuthor = {
    key: "author",
    content: [
        [fieldLevel.effectiveTime, required, key("time")],
        {
            key: "assignedAuthor",
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("identifier"),
                    extension: leafLevel.inputProperty("extension")
                },
                dataKey: 'identifiers',
            }, {
                key: "code",
                attributes: leafLevel.code,
                existsWhen: condition.propertyNotEmpty('code'),
                dataKey: "code"
            }, {
                key: "addr",
                attributes: {
                    use: leafLevel.use("use")
                },
                content: [{
                    key: "country",
                    text: leafLevel.inputProperty("country")
                }, {
                    key: "state",
                    text: leafLevel.inputProperty("state")
                }, {
                    key: "city",
                    text: leafLevel.inputProperty("city")
                }, {
                    key: "postalCode",
                    text: leafLevel.inputProperty("zip")
                }, {
                    key: "streetAddressLine",
                    text: leafLevel.input,
                    dataKey: "street_lines"
                }],
                dataKey: "address"
            }, {
                key: "telecom",
                attributes: {
                    value: leafLevel.inputProperty("value"),
                    use: leafLevel.inputProperty("use")
                },
                dataTransform: translate.telecom
            }, {
                key: "assignedPerson",
                content: {
                    key: "name",
                    content: [
                        {
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
                } // content
            }, {
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
                        dataKey: "name"
                    }, {
                        key: "telecom",
                        attributes: {
                            value: leafLevel.inputProperty("value"),
                            use: leafLevel.inputProperty("use")
                        },
                        dataTransform: translate.telecom,
                        datakey: "phone"
                    },
                    {
                        key: "addr",
                        attributes: {
                            use: leafLevel.use("use")
                        },
                        content: [{
                            key: "country",
                            text: leafLevel.inputProperty("country")
                        }, {
                            key: "state",
                            text: leafLevel.inputProperty("state")
                        }, {
                            key: "city",
                            text: leafLevel.inputProperty("city")
                        }, {
                            key: "postalCode",
                            text: leafLevel.inputProperty("zip")
                        }, {
                            key: "streetAddressLine",
                            text: leafLevel.input,
                            dataKey: "street_lines"
                        }],
                        dataKey: "address"
                    }
                ],
                dataKey: "organization"
            }
            ] // content
        }
    ],
    dataKey: "meta.ccda_header.author"
};
var headerInformant = exports.headerInformant = {
    key: "informant",
    content: {
        key: "assignedEntity",
        //attributes: {id:}
        content: [{
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("identifier")
            },
            dataKey: "identifiers"

        }, {
            key: "representedOrganization",
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("identifier")
                },
                dataKey: "identifiers"
            }, {
                key: "name",
                text: leafLevel.inputProperty("name"),
                dataKey: "name"
            }]
        }]
    },
    dataKey: "meta.ccda_header.informant"
};
var headerCustodian = exports.headerCustodian = {
    key: "custodian",
    content: {
        key: "assignedCustodian",
        //attributes: {id:}
        content: [{
            key: "representedCustodianOrganization",
            content: [
                {
                    key: "id",
                    attributes: {
                        root: leafLevel.inputProperty("root"),
                        extension: leafLevel.inputProperty("extension")
                    },
                    dataKey: "identity"
                }, {
                    key: "name",
                    text: leafLevel.input,
                    dataKey: "name"
                },
                {
                    key: "telecom",
                    attributes: {
                        value: leafLevel.inputProperty("value"),
                        use: leafLevel.inputProperty("use")
                    },
                    dataTransform: translate.telecom,
                    datakey: "phone"
                },
                {
                    key: "addr",
                    attributes: {
                        use: leafLevel.use("use")
                    },
                    content: [{
                        key: "country",
                        text: leafLevel.inputProperty("country")
                    }, {
                        key: "state",
                        text: leafLevel.inputProperty("state")
                    }, {
                        key: "city",
                        text: leafLevel.inputProperty("city")
                    }, {
                        key: "postalCode",
                        text: leafLevel.inputProperty("zip")
                    }, {
                        key: "streetAddressLine",
                        text: leafLevel.input,
                        dataKey: "street_lines"
                    }],
                    dataKey: "address"
                }
            ],
        }]
    },
    dataKey: "meta.ccda_header.custodian"
};
var headerInformationRecipient = exports.headerInformationRecipient = {
    key: "informationRecipient",
    content: {
        key: "intendedRecipient",
        content: [{
            key: "informationRecipient",
            content: {
                key: "name",
                content: [
                    {
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
                dataTransform: translate.name,
            },
        },
            {
                key: "receivedOrganization",
                content: [{
                    key: "name",
                    text: leafLevel.inputProperty("name"),
                    dataKey: "organization"
                }],
            }]
    },
    dataKey: "meta.ccda_header.information_recipient"
}

/* {
    key: "receivedOrganization",
    content: [{
        key: "name",
        text: leafLevel.inputProperty("name"),
        dataKey: "organization"
    }],
}*/

var headerComponentOf = exports.headerComponentOf = {
    key: "componentOf",
    content: {
        key: "encompassingEncounter",
        content: [
            fieldLevel.id,
            {
                key: "code",
                attributes: leafLevel.code,
                existsWhen: condition.propertyNotEmpty('code'),
                dataKey: "code"
            },
            [fieldLevel.effectiveTime, key("effectiveTime"), dataKey("date_time"), required],
            fieldLevel.responsibleParty,
            {
                key: "encounterParticipant",
                attributes: {
                    "typeCode": "ATND"
                },
                content: [{
                    key: "assignedEntity",
                    content: [{
                        key: "id",
                        attributes: {
                            root: leafLevel.inputProperty("root")
                        }
                    }
                        , fieldLevel.usRealmAddress
                        , fieldLevel.telecom
                        , {
                            key: "assignedPerson",
                            content: fieldLevel.usRealmName
                        }]
                }],
                dataKey: "encounter_participant",
                existsWhen: condition.propertyValueNotEmpty("name.last")
            }
        ]
    },
    dataKey: "meta.ccda_header.component_of"
};
