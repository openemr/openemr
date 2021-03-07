require=(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

exports.newDocument = function () {
	return document.implementation.createDocument("", "", null);
};

exports.newNode = function (xmlDoc, name, text) {
	var doc =  xmlDoc.ownerDocument || xmlDoc;

	var element = doc.createElement(name);
	if ((text !== undefined) && (text !== null)) {
		var textNode = doc.createTextNode(text);
		element.appendChild(textNode);
	}
	if (xmlDoc.ownerDocument) {
		xmlDoc.appendChild(element);
	} else {
		xmlDoc.appendChild(element);
	}
	return element;
};

exports.nodeAttr = function (node, attr) {
	Object.keys(attr).forEach(function(key) {
		var value = attr[key];
		node.setAttribute(key, value);
	});
};

exports.serializeToString = function (xmlDoc) {
	var serializer = new XMLSerializer();
	var result = serializer.serializeToString(xmlDoc);
	return result;
};

},{}],2:[function(require,module,exports){
"use strict";

exports.keyExists = function (key) {
    return function (input) {
        return input.hasOwnProperty(key);
    };
};

exports.keyDoesntExist = function (key) {
    return function (input) {
        return !input.hasOwnProperty(key);
    };
};

exports.eitherKeyExists = function (key0, key1, key2, key3) {
    return function (input) {
        return input.hasOwnProperty(key0) || input.hasOwnProperty(key1) || input.hasOwnProperty(key2) || input.hasOwnProperty(key3);
    };
};

exports.codeOrDisplayname = function (input) {
    return input.code || input.name;
};

exports.propertyEquals = function (property, value) {
    return function (input) {
        return input && (input[property] === value);
    };
};

},{}],3:[function(require,module,exports){
"use strict";

exports.key = function (overrideKeyValue) {
    return function (template) {
        template.key = overrideKeyValue;
    };
};

exports.required = function (template) {
    template.required = true;
};

exports.dataKey = function (overrideKeyValue) {
    return function (template) {
        template.dataKey = overrideKeyValue;
    };
};

},{}],4:[function(require,module,exports){
"use strict";

var headerLevel = require('./headerLevel');
var fieldLevel = require('./fieldLevel');
var leafLevel = require('./leafLevel');
var sectionLevel = require('./sectionLevel');
var contentModifier = require("./contentModifier");
var condition = require("./condition");

var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.ccd = {
    key: "ClinicalDocument",
    attributes: {
        "xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns": "urn:hl7-org:v3",
        "xmlns:cda": "urn:hl7-org:v3",
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
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.1"),
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.2"), [fieldLevel.id, dataKey("meta.identifiers")], {
            key: "code",
            attributes: {
                codeSystem: "2.16.840.1.113883.6.1",
                codeSystemName: "LOINC",
                code: "34133-9",
                displayName: "Summarization of Episode Note"
            }
        }, {
            key: "title",
            text: leafLevel.inputProperty("title"),
            dataKey: "meta.ccda_header"
        },
        [fieldLevel.effectiveTimeNow, required], {
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
        headerLevel.providers, {
            key: "component",
            content: {
                key: "structuredBody",
                content: [
                    [sectionLevel.allergiesSectionEntriesRequired, required],
                    [sectionLevel.medicationsSectionEntriesRequired, required],
                    [sectionLevel.problemsSectionEntriesRequired, required],
                    [sectionLevel.proceduresSectionEntriesRequired, required],
                    [sectionLevel.resultsSectionEntriesRequired, required],
                    sectionLevel.encountersSectionEntriesOptional,
                    sectionLevel.immunizationsSectionEntriesOptional,
                    sectionLevel.payersSection,
                    sectionLevel.planOfCareSection,
                    sectionLevel.socialHistorySection,
                    sectionLevel.vitalSignsSectionEntriesOptional
                ],
                notImplemented: [
                    "advanceDirectivesSectionEntriesOptional",
                    "familyHistorySection",
                    "functionalStatusSection",
                    "medicalEquipmentSection",
                ]
            },
            dataKey: 'data'
        }
    ]
};

var sectionLevel2 = require('./sectionLevel2');

exports.ccd2 = function (html_renderer) {
    var ccd_template = {
        key: "ClinicalDocument",
        attributes: {
            "xmlns:xsi": "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns": "urn:hl7-org:v3",
            "xmlns:cda": "urn:hl7-org:v3",
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
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.1"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.1.2"), [fieldLevel.id, dataKey("meta.identifiers")], {
                key: "code",
                attributes: {
                    codeSystem: "2.16.840.1.113883.6.1",
                    codeSystemName: "LOINC",
                    code: "34133-9",
                    displayName: "Summarization of Episode Note"
                }
            }, {
                key: "title",
                text: leafLevel.inputProperty("title"),
                dataKey: "meta.ccda_header"
            },
            [fieldLevel.effectiveTimeNow, required], {
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
            headerLevel.providers, {
                key: "component",
                content: {
                    key: "structuredBody",
                    content: [
                        [sectionLevel2.allergiesSectionEntriesRequired(html_renderer.allergiesSectionEntriesRequiredHtmlHeader, html_renderer.allergiesSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.medicationsSectionEntriesRequired(html_renderer.medicationsSectionEntriesRequiredHtmlHeader, html_renderer.medicationsSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.problemsSectionEntriesRequired(html_renderer.problemsSectionEntriesRequiredHtmlHeader, html_renderer.problemsSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.proceduresSectionEntriesRequired(html_renderer.proceduresSectionEntriesRequiredHtmlHeader, html_renderer.proceduresSectionEntriesRequiredHtmlHeaderNA), required],
                        [sectionLevel2.resultsSectionEntriesRequired(html_renderer.resultsSectionEntriesRequiredHtmlHeader, html_renderer.resultsSectionEntriesRequiredHtmlHeaderNA), required],
                        sectionLevel2.encountersSectionEntriesOptional(html_renderer.encountersSectionEntriesOptionalHtmlHeader, html_renderer.encountersSectionEntriesOptionalHtmlHeaderNA),
                        sectionLevel2.immunizationsSectionEntriesOptional(html_renderer.immunizationsSectionEntriesOptionalHtmlHeader, html_renderer.immunizationsSectionEntriesOptionalHtmlHeaderNA),
                        sectionLevel2.payersSection(html_renderer.payersSectionHtmlHeader, html_renderer.payersSectionHtmlHeaderNA),
                        sectionLevel2.planOfCareSection(html_renderer.planOfCareSectionHtmlHeader, html_renderer.planOfCareSectionHtmlHeaderNA),
                        sectionLevel2.socialHistorySection(html_renderer.socialHistorySectionHtmlHeader, html_renderer.socialHistorySectionHtmlHeaderNA),
                        sectionLevel2.vitalSignsSectionEntriesOptional(html_renderer.vitalSignsSectionEntriesOptionalHtmlHeader, html_renderer.vitalSignsSectionEntriesOptionalHtmlHeaderNA)
                    ],
                    notImplemented: [
                        "advanceDirectivesSectionEntriesOptional",
                        "familyHistorySection",
                        "functionalStatusSection",
                        "medicalEquipmentSection",
                    ]
                },
                dataKey: 'data'
            }
        ]
    };
    return ccd_template;
};

},{"./condition":2,"./contentModifier":3,"./fieldLevel":19,"./headerLevel":20,"./leafLevel":22,"./sectionLevel":23,"./sectionLevel2":24}],5:[function(require,module,exports){
"use strict";

var xmlutil = require('./xmlutil');

var expandText = function (input, template) {
    var text = template.text;
    if (text) {
        if (typeof text === 'function') {
            text = text(input);
        }
        if ((text !== null) && (text !== undefined)) {
            return text;
        }
    }
    return null;
};

var expandAttributes = function expandAttributes(input, context, attrObj, attrs) {
    if (Array.isArray(attrObj)) {
        attrObj.forEach(function (attrObjElem) {
            expandAttributes(input, context, attrObjElem, attrs);
        });
    } else if (typeof attrObj === 'function') {
        expandAttributes(input, context, attrObj(input, context), attrs);
    } else {
        Object.keys(attrObj).forEach(function (attrKey) {
            var attrVal = attrObj[attrKey];
            if (typeof attrVal === 'function') {
                attrVal = attrVal(input, context);
            }
            if ((attrVal !== null) && (attrVal !== undefined)) {
                attrs[attrKey] = attrVal;
            }
        });
    }
};

var fillAttributes = function (node, input, context, template) {
    var attrObj = template.attributes;
    if (attrObj) {
        var inputAttrKey = template.attributeKey;
        if (inputAttrKey) {
            input = input[inputAttrKey];
        }
        if (input) {
            var attrs = {};
            expandAttributes(input, context, attrObj, attrs);
            xmlutil.nodeAttr(node, attrs);
        }
    }
};

var update;

var fillContent = function (node, input, context, template) {
    var content = template.content;
    if (content) {
        if (!Array.isArray(content)) {
            content = [content];
        }
        content.forEach(function (element) {
            if (Array.isArray(element)) {
                var actualElement = Object.create(element[0]);
                for (var i = 1; i < element.length; ++i) {
                    element[i](actualElement);
                }
                update(node, input, context, actualElement);
            } else {
                update(node, input, context, element);
            }
        });
    }
};

var updateUsingTemplate = function updateUsingTemplate(xmlDoc, input, context, template) {
    var condition = template.existsWhen;
    if ((!condition) || condition(input, context)) {
        var name = template.key;
        var text = expandText(input, template);
        if (((text !== null) && (text !== undefined)) || template.content || template.attributes) {
            var node = xmlutil.newNode(xmlDoc, name, text);

            fillAttributes(node, input, context, template);
            fillContent(node, input, context, template);
            return true;
        }
    }
    return false;
};

var transformInput = function (input, template) {
    var inputKey = template.dataKey;
    if (inputKey) {
        var pieces = inputKey.split('.');
        pieces.forEach(function (piece) {
            if (Array.isArray(input) && (piece !== "0")) {
                var nextInputs = [];
                input.forEach(function (inputElement) {
                    var nextInput = inputElement[piece];
                    if (nextInput) {
                        if (Array.isArray(nextInput)) {
                            nextInput.forEach(function (nextInputElement) {
                                if (nextInputElement) {
                                    nextInputs.push(nextInputElement);
                                }
                            });
                        } else {
                            nextInputs.push(nextInput);
                        }
                    }
                });
                if (nextInputs.length === 0) {
                    input = null;
                } else {
                    input = nextInputs;
                }
            } else {
                input = input && input[piece];
            }
        });
    }
    if (input) {
        var transform = template.dataTransform;
        if (transform) {
            input = transform(input);
        }
    }
    return input;
};

update = exports.update = function (xmlDoc, input, context, template) {
    var filled = false;
    if (input) {
        input = transformInput(input, template);
        if (input) {
            if (Array.isArray(input)) {
                input.forEach(function (element) {
                    filled = updateUsingTemplate(xmlDoc, element, context, template) || filled;
                });
            } else {
                filled = updateUsingTemplate(xmlDoc, input, context, template);
            }
        }
    }
    if ((!filled) && template.required && !context.preventNullFlavor) {
        var node = xmlutil.newNode(xmlDoc, template.key);
        xmlutil.nodeAttr(node, {
            nullFlavor: 'UNK'
        });
    }
};

exports.create = function (template, input, context) {
    var doc = new xmlutil.newDocument();
    update(doc, input, context, template);
    var result = xmlutil.serializeToString(doc);
    return result;
};

},{"./xmlutil":1}],6:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require('../condition');
var contentModifier = require("../contentModifier");

var sel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var allergyStatusObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.28"),
        fieldLevel.templateCode("AllergyStatusObservation"),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: [
                leafLevel.typeCE,
                leafLevel.code
            ],
            existsWhen: condition.codeOrDisplayname,
            required: true
        }
    ],
    dataKey: "status"
};

var allergyIntoleranceObservation = exports.allergyIntoleranceObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN",
        "negationInd": leafLevel.boolInputProperty("negation_indicator")
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.7"),
        fieldLevel.id,
        fieldLevel.templateCode("AllergyObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            content: {
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("reaction")
                    }
                }
            },
            dataKey: 'intolerance',
            existsWhen: condition.codeOrDisplayname,
            required: true
        }, {
            key: "participant",
            attributes: {
                "typeCode": "CSM"
            },
            content: [{
                key: "participantRole",
                attributes: {
                    "classCode": "MANU"
                },
                content: [{
                    key: "playingEntity",
                    attributes: {
                        classCode: "MMAT"
                    },
                    content: [{
                        key: "code",
                        attributes: leafLevel.code,
                        content: [{
                            key: "originalText",
                            content: [{
                                key: "reference",
                                attributes: {
                                    "value": leafLevel.sameReference("reaction")
                                }
                            }]
                        }, {
                            key: "translation",
                            attributes: leafLevel.code,
                            dataKey: "translations"
                        }],
                        require: true
                    }]
                }],
                required: true
            }],
            dataKey: 'allergen'
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [allergyStatusObservation, required]
            ],
            existsWhen: condition.keyExists("status")
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "MFST",
                "inversionInd": "true"
            },
            content: [
                [sel.reactionObservation, required]
            ],
            dataKey: 'reactions',
            existsWhen: condition.keyExists('reaction')
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [sel.severityObservation, required]
            ],
            existsWhen: condition.keyExists('severity')
        }
    ],
    dataKey: "observation",
    warning: [
        "negationInd attribute is not specified in specification"
    ]
};

var allergyProblemAct = exports.allergyProblemAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.30"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("AllergyProblemAct"),
        fieldLevel.statusCodeActive, [fieldLevel.effectiveTime, required], {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [allergyIntoleranceObservation, required],
            existsWhen: condition.keyExists('observation'),
            required: true,
            warning: "inversionInd is not in spec"
        }
    ],
    warning: "statusCode is not constant in spec"
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],7:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.encounterActivities = {
    key: "encounter",
    attributes: {
        classCode: "ENC",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.49"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("Encounter")
                    }
                }]
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "encounter"
        },
        [fieldLevel.effectiveTime, required],
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
                        code: "404684003",
                        name: "Finding",
                        code_system: "2.16.840.1.113883.6.96",
                        code_system_name: "SNOMED CT"
                    };
                    return e;
                });
                return input;
            },
            toDo: "move dataTransform to blue-button-meta"
        }
    ],
    notImplemented: [
        "entryRelationship:encounterDiagnosis",
        "dishargeDispositionCode"
    ]
};

},{"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],8:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var immunizationMedicationInformation = {
    key: "manufacturedProduct",
    attributes: {
        classCode: "MANU"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.54"),
        fieldLevel.id, {
            key: "manufacturedMaterial",
            content: [{
                key: "code",
                attributes: leafLevel.code,
                content: [{
                    key: "originalText",
                    text: leafLevel.inputProperty("unencoded_name"),
                    content: {
                        key: "reference",
                        attributes: {
                            "value": leafLevel.nextReference("imminfo")
                        }
                    }
                }, {
                    key: "translation",
                    attributes: leafLevel.code,
                    dataKey: "translations"
                }]
            }, {
                key: "lotNumberText",
                text: leafLevel.input,
                dataKey: "lot_number"
            }],
            dataKey: "product",
            required: true
        }, {
            key: "manufacturerOrganization",
            content: {
                key: "name",
                text: leafLevel.input,
            },
            dataKey: "manufacturer"
        }
    ],
    dataTransform: function (input) {
        if (input.product) {
            input.product.lot_number = input.lot_number;
        }
        return input;
    }
};

var immunizationRefusalReason = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.53"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.5.8"),
            required: true
        },
        fieldLevel.statusCodeCompleted
    ]
};

var immunizationActivityAttributes = function (input) {
    if (input.status) {
        if (input.status === "refused") {
            return {
                moodCode: "EVN",
                negationInd: "true"
            };
        }
        if (input.status === "pending") {
            return {
                moodCode: "INT",
                negationInd: "false"
            };
        }
        if (input.status === "complete") {
            return {
                moodCode: "EVN",
                negationInd: "false"
            };
        }
    }
    return null;
};

exports.immunizationActivity = {
    key: "substanceAdministration",
    attributes: [{
        classCode: "SBADM"
    }, immunizationActivityAttributes],
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.52"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.text(leafLevel.nextReference("immunization")),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "repeatNumber",
            attributes: {
                value: leafLevel.inputProperty("sequence_number")
            },
            existsWhen: function (input) {
                return input.sequence_number || (input.sequence_number === "");
            }
        }, {
            key: "routeCode",
            attributes: leafLevel.code,
            dataKey: "administration.route"
        }, {
            key: "approachSiteCode",
            attributes: leafLevel.code,
            dataKey: "administration.body_site"
        }, {
            key: "doseQuantity",
            attributes: {
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            dataKey: "administration.dose"
        }, {
            key: "consumable",
            content: [
                [immunizationMedicationInformation, required]
            ],
            dataKey: "product",
            required: true
        },
        fieldLevel.performer, {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [sharedEntryLevel.instructions, required],
            dataKey: "instructions"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "RSON"
            },
            content: [immunizationRefusalReason, required],
            dataKey: "refusal_reason"
        }
    ],
    notImplemented: [
        "code",
        "administrationUnitCode",
        "participant:drugVehicle",
        "entryRelationship:indication",
        "entryRelationship:medicationSupplyOrder",
        "entryRelationship:medicationDispense",
        "entryRelationship:reactionObservation",
        "entryRelationship:preconditionForSubstanceAdministration"
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],9:[function(require,module,exports){
"use strict";

var allergyEntryLevel = require("./allergyEntryLevel");
var resultEntryLevel = require("./resultEntryLevel");
var socialHistoryEntryLevel = require('./socialHistoryEntryLevel');
var payerEntryLevel = require('./payerEntryLevel');
var vitalSignEntryLevel = require('./vitalSignEntryLevel');
var planOfCareEntryLevel = require('./planOfCareEntryLevel');
var procedureEntryLevel = require("./procedureEntryLevel");
var problemEntryLevel = require("./problemEntryLevel");
var encounterEntryLevel = require("./encounterEntryLevel");
var immunizationEntryLevel = require("./immunizationEntryLevel");
var medicationEntryLevel = require("./medicationEntryLevel");

exports.allergyProblemAct = allergyEntryLevel.allergyProblemAct;

exports.medicationActivity = medicationEntryLevel.medicationActivity;

exports.immunizationActivity = immunizationEntryLevel.immunizationActivity;

exports.problemConcernAct = problemEntryLevel.problemConcernAct;

exports.encounterActivities = encounterEntryLevel.encounterActivities;

exports.procedureActivityAct = procedureEntryLevel.procedureActivityAct;
exports.procedureActivityProcedure = procedureEntryLevel.procedureActivityProcedure;
exports.procedureActivityObservation = procedureEntryLevel.procedureActivityObservation;

exports.planOfCareActivityAct = planOfCareEntryLevel.planOfCareActivityAct;
exports.planOfCareActivityObservation = planOfCareEntryLevel.planOfCareActivityObservation;
exports.planOfCareActivityProcedure = planOfCareEntryLevel.planOfCareActivityProcedure;
exports.planOfCareActivityEncounter = planOfCareEntryLevel.planOfCareActivityEncounter;
exports.planOfCareActivitySubstanceAdministration = planOfCareEntryLevel.planOfCareActivitySubstanceAdministration;
exports.planOfCareActivitySupply = planOfCareEntryLevel.planOfCareActivitySupply;
exports.planOfCareActivityInstructions = planOfCareEntryLevel.planOfCareActivityInstructions;

exports.coverageActivity = payerEntryLevel.coverageActivity;

exports.vitalSignsOrganizer = vitalSignEntryLevel.vitalSignsOrganizer;

exports.resultOrganizer = resultEntryLevel.resultOrganizer;

exports.socialHistoryObservation = socialHistoryEntryLevel.socialHistoryObservation;
exports.smokingStatusObservation = socialHistoryEntryLevel.smokingStatusObservation;

},{"./allergyEntryLevel":6,"./encounterEntryLevel":7,"./immunizationEntryLevel":8,"./medicationEntryLevel":10,"./payerEntryLevel":11,"./planOfCareEntryLevel":12,"./problemEntryLevel":13,"./procedureEntryLevel":14,"./resultEntryLevel":15,"./socialHistoryEntryLevel":17,"./vitalSignEntryLevel":18}],10:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var medicationInformation = {
    key: "manufacturedProduct",
    attributes: {
        classCode: "MANU"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.23"),
        fieldLevel.id, {
            key: "manufacturedMaterial",
            content: [{
                key: "code",
                attributes: leafLevel.code,
                content: [{
                    key: "originalText",
                    text: leafLevel.inputProperty("unencoded_name"),
                    content: [{
                        key: "reference",
                        attributes: {
                            "value": leafLevel.nextReference("medinfo")
                        }
                    }]
                }, {
                    key: "translation",
                    attributes: leafLevel.code,
                    dataKey: "translations"
                }]
            }],
            dataKey: "product",
            required: true
        }, {
            key: "manufacturerOrganization",
            content: {
                key: "name",
                text: leafLevel.input,
            },
            dataKey: "manufacturer"
        }
    ],
    dataTransform: function (input) {
        if (input.product) {
            input.product.unencoded_name = input.unencoded_name;
        }
        return input;
    }
};

var medicationSupplyOrder = {
    key: "supply",
    attributes: {
        classCode: "SPLY",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.17"),
        fieldLevel.id,
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "repeatNumber",
            attributes: {
                value: leafLevel.input
            },
            dataKey: "repeatNumber"
        }, {
            key: "quantity",
            attributes: {
                value: leafLevel.input
            },
            dataKey: "quantity"
        }, {
            key: "product",
            content: medicationInformation,
            dataKey: "product"
        },
        fieldLevel.author, {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [
                [sharedEntryLevel.instructions, required]
            ],
            dataKey: "instructions"
        }
    ],
    toDo: "statusCode needs to allow values other than completed",
    notImplemented: [
        "product:immunizationMedicationInformation"
    ]
};

var medicationDispense = {
    key: "supply",
    attributes: {
        classCode: "SPLY",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.18"),
        fieldLevel.id,
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "product",
            content: medicationInformation,
            dataKey: "product"
        },
        fieldLevel.performer
    ],
    toDo: "statusCode needs to allow different values than completed",
    notImplemented: [
        "repeatNumber",
        "quantity",
        "product:ImmunizationMedicationInformation",
        "entryRelationship:medicationSupplyOrder",
    ]
};

exports.medicationActivity = {
    key: "substanceAdministration",
    attributes: {
        classCode: "SBADM",
        moodCode: function (input) {
            var status = input.status;
            if (status) {
                if (status === 'Prescribed') {
                    return 'INT';
                }
                if (status === 'Completed') {
                    return 'EVN';
                }
            }
            return null;
        }
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.16"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "text",
            text: leafLevel.input,
            dataKey: "sig"
        },
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "effectiveTime",
            attributes: {
                "xsi:type": "PIVL_TS",
                "institutionSpecified": "true",
                "operator": "A"
            },
            content: {
                key: "period",
                attributes: {
                    value: leafLevel.inputProperty("value"),
                    unit: leafLevel.inputProperty("unit")
                },
            },
            dataKey: "administration.interval.period",
        }, {
            key: "routeCode",
            attributes: leafLevel.code,
            dataKey: "administration.route"
        }, {
            key: "doseQuantity",
            attributes: {
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            dataKey: "administration.dose"
        }, {
            key: "rateQuantity",
            attributes: {
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            dataKey: "administration.rate"
        }, {
            key: "administrationUnitCode",
            attributes: leafLevel.code,
            dataKey: "administration.form"
        }, {
            key: "consumable",
            content: medicationInformation,
            dataKey: "product"
        },
        fieldLevel.performer, {
            key: "participant",
            attributes: {
                typeCode: "CSM"
            },
            content: [
                [sharedEntryLevel.drugVehicle, required]
            ],
            dataKey: "drug_vehicle"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "RSON"
            },
            content: [
                [sharedEntryLevel.indication, required]
            ],
            dataKey: "indication"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [medicationSupplyOrder, required]
            ],
            dataKey: "supply"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [medicationDispense, required]
            ],
            dataKey: "dispense"
        }, {
            key: "precondition",
            attributes: {
                typeCode: "PRCN"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.25"), [sharedEntryLevel.preconditionForSubstanceAdministration, required]
            ],
            dataKey: "precondition",
            warning: "templateId needs to be in preconditionForSubstanceAdministration but CCD_1.xml contradicts"
        }
    ],
    notImplemented: [
        "code",
        "text:reference",
        "repeatNumber",
        "approachSiteCode",
        "maxDoseQuantity",
        "entryRelationship:instructions",
        "reactionObservation"
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],11:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var policyActivity = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.61"), {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("identifier"),
                extension: leafLevel.inputProperty("extension")
            },
            dataKey: 'policy.identifiers',
            existsWhen: condition.keyExists('identifier'),
            required: true
        },

        {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "policy.code"
        },
        fieldLevel.statusCodeCompleted, {
            key: "performer",
            attributes: {
                typeCode: "PRF"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.87"),
                fieldLevel.assignedEntity
            ],
            dataKey: "policy.insurance.performer"
        }, {
            key: "performer",
            attributes: {
                typeCode: "PRF"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.88"),
                fieldLevel.assignedEntity
            ],
            dataKey: "guarantor"
        }, {
            key: "participant",
            attributes: {
                typeCode: "COV"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.89"), [fieldLevel.effectiveTime, key("time")], {
                    key: "participantRole",
                    attributes: {
                        classCode: "PAT"
                    },
                    content: [
                        fieldLevel.id,
                        fieldLevel.usRealmAddress,
                        fieldLevel.telecom, {
                            key: "code",
                            attributes: leafLevel.code,
                            dataKey: "code"
                        }, {
                            key: "playingEntity",
                            content: fieldLevel.usRealmName
                        }
                    ]
                }
            ],
            dataKey: "participant",
            dataTransform: function (input) {
                if (input.performer) {
                    input.identifiers = input.performer.identifiers;
                    input.address = input.performer.address;
                    input.phone = input.performer.phone;
                }
                return input;
            }
        }, {
            key: "participant",
            attributes: {
                typeCode: "HLD"
            },
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.90"), {
                    key: "participantRole",
                    content: [
                        fieldLevel.id,
                        fieldLevel.usRealmAddress
                    ],
                    dataKey: "performer"
                }
            ],
            dataKey: "policy_holder"
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: {
                key: "act",
                attributes: {
                    classCode: "ACT",
                    moodCode: "EVN"
                },
                content: [
                    fieldLevel.templateId("2.16.840.1.113883.10.20.1.19"),
                    fieldLevel.id, {
                        key: "entryRelationship",
                        attributes: {
                            typeCode: "SUBJ"
                        },
                        content: {
                            key: "procedure",
                            attributes: {
                                classCode: "PROC",
                                moodCode: "PRMS"
                            },
                            content: {
                                key: "code",
                                attributes: leafLevel.code,
                                dataKey: "code"
                            }
                        },
                        dataKey: "procedure"
                    }
                ]
            },
            dataKey: "authorization"
        }
    ]
};

exports.coverageActivity = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.60"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("CoverageActivity"),
        fieldLevel.statusCodeCompleted, {
            key: "entryRelationship",
            attributes: {
                typeCode: "COMP"
            },
            content: [
                [policyActivity, required]
            ],
            required: true
        }
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22}],12:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
    ],
    existsWhen: function (input) {
        return input.type === "act";
    }
};

exports.planOfCareActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.44"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
    ],
    existsWhen: function (input) {
        return input.type === "observation";
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
    ],
    existsWhen: function (input) {
        return input.type === "encounter";
    }
};

exports.planOfCareActivitySubstanceAdministration = {
    key: "substanceAdministration",
    attributes: {
        classCode: "SBADM",
        moodCode: "RQO"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.42"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "plan"
        },
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew,
        fieldLevel.effectiveTime
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
        fieldLevel.statusCodeNew, {
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
        fieldLevel.effectiveTime, {
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

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22}],13:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

var problemStatus = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.6"),
        fieldLevel.id,
        fieldLevel.templateCode("ProblemStatus"),
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.codeFromName("2.16.840.1.113883.3.88.12.80.68")
            ],
            dataKey: "name",
            required: true
        }
    ],
    warning: "effectiveTime does not exist in the specification"
};

var healthStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.5"),
        fieldLevel.templateCode("HealthStatusObservation"),
        fieldLevel.text(leafLevel.nextReference("healthStatus")),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: {
                "xsi:type": "CD",
                code: "81323004",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED CT",
                displayName: leafLevel.inputProperty("patient_status")
            },
            required: true,
            toDo: "The attribute should not be constant"
        }
    ]
};

var problemObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN",
        negationInd: leafLevel.boolInputProperty("negation_indicator")
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.4"),
        fieldLevel.id, {
            key: "code",
            attributes: {
                nullFlavor: "UNK"
            }
        },
        fieldLevel.text(leafLevel.nextReference("problem")),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, dataKey("problem.date_time")], {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.code
            ],
            content: [{
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "problem.code",
            existsWhen: condition.codeOrDisplayname,
            required: true
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [problemStatus, required]
            ],
            dataTransform: function (input) {
                if (input && input.status) {
                    var result = input.status;
                    result.identifiers = input.identifiers;
                    return result;
                }
                return null;
            }
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ",
                inversionInd: "true"
            },
            content: [
                [sharedEntryLevel.ageObservation, required]
            ],
            existsWhen: condition.keyExists("onset_age")
        }, {
            key: "entryRelationship",
            attributes: {
                typeCode: "REFR"
            },
            content: [
                [healthStatusObservation, required]
            ],
            existsWhen: condition.keyExists("patient_status")
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: [
                [sharedEntryLevel.severityObservation]
            ],
            dataKey: "problem",
            existsWhen: condition.keyExists("severity")
        }
    ],
    notImplemented: [
        "code"
    ]
};

exports.problemConcernAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.3"),
        fieldLevel.uniqueId, {
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("identifier"),
                extension: leafLevel.inputProperty("extension")
            },
            dataKey: 'source_list_identifiers',
            existsWhen: condition.keyExists('identifier'),
            required: true
        },
        fieldLevel.templateCode("ProblemConcernAct"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "entryRelationship",
            attributes: {
                typeCode: "SUBJ"
            },
            content: [
                [problemObservation, required]
            ],
            required: true
        }
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],14:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");
var contentModifier = require("../contentModifier");

var sharedEntryLevel = require("./sharedEntryLevel");

var key = contentModifier.key;
var required = contentModifier.required;
var dataKey = contentModifier.dataKey;

exports.procedureActivityAct = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "INT" // not constant in the specification
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.12"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        },
        [fieldLevel.performer, dataKey("performer")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "act"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "entryRelationship:encounter",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};

exports.procedureActivityProcedure = {
    key: "procedure",
    attributes: {
        classCode: "PROC",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.14"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        }, {
            key: "specimen",
            attributes: {
                typeCode: "SPC"
            },
            content: {
                key: "specimenRole",
                attributes: {
                    classCode: "SPEC"
                },
                content: [
                    fieldLevel.id, {
                        key: "specimenPlayingEntity",
                        content: {
                            key: "code",
                            attributes: leafLevel.code,
                            dataKey: "code"
                        },
                        existsWhen: condition.keyExists("code")
                    }
                ],
                required: true
            },
            dataKey: "specimen"
        },
        [fieldLevel.performer, dataKey("performer")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "procedure"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "methodCode",
        "participant:productInstance",
        "entryRelationship:encounter",
        "entryRelationship:instructions",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};

exports.procedureActivityObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN" // not constant in the specification
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.13"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: [{
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("procedure")
                    }
                }]
            }],
            dataKey: "procedure",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            },
            required: true
        },
        fieldLevel.effectiveTime, {
            key: "priorityCode",
            attributes: leafLevel.code,
            dataKey: "priority"
        }, {
            key: "value",
            attributes: {
                "xsi:type": "CD"
            }
        }, {
            key: "targetSiteCode",
            attributes: leafLevel.code,
            dataKey: "body_sites"
        },
        [fieldLevel.performer, dataKey("performers")], {
            key: "participant",
            attributes: {
                typeCode: "LOC"
            },
            content: [
                [sharedEntryLevel.serviceDeliveryLocation, required]
            ],
            dataKey: "locations"
        }
    ],
    existsWhen: condition.propertyEquals("procedure_type", "observation"),
    toDo: ["moodCode should be variable"],
    notImplemented: [
        "entryRelationship:encounter",
        "entryRelationship:instructions",
        "entryRelationship:indication",
        "entryRelationship:medicationActivity"
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22,"./sharedEntryLevel":16}],15:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

var resultObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.2"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "result",
            required: true
        },
        fieldLevel.text(leafLevel.nextReference("result")),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: {
                "xsi:type": function (input) {
                    return input.text ? "ST" : "PQ";
                },
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            text: leafLevel.inputProperty("text"),
            existsWhen: condition.eitherKeyExists("value", "text"),
            required: true
        }, {
            key: "interpretationCode",
            attributes: {
                code: function (input) {
                    return input.substr(0, 1);
                },
                codeSystem: "2.16.840.1.113883.5.83",
                displayName: leafLevel.input,
                codeSystemName: "ObservationInterpretation"
            },
            dataKey: "interpretations"
        }, {
            key: "referenceRange",
            content: {
                key: "observationRange",
                content: [{
                    key: "text",
                    text: leafLevel.input,
                    dataKey: "range"
                }, {
                    key: "value",
                    attributes: {
                        "xsi:type": "IVL_PQ"
                    },
                    content: [{
                        key: "low",
                        attributes: {
                            value: leafLevel.inputProperty("low"),
                            unit: leafLevel.inputProperty("unit")
                        },
                        existsWhen: condition.keyExists("low")
                    }, {
                        key: "high",
                        attributes: {
                            value: leafLevel.inputProperty("high"),
                            unit: leafLevel.inputProperty("unit")
                        },
                        existsWhen: condition.keyExists("high")
                    }],
                    existsWhen: condition.eitherKeyExists("low", "high")
                }],
                required: true
            },
            dataKey: "reference_range"
        }
    ],
    notIplemented: [
        "variable statusCode",
        "methodCode",
        "targetSiteCode",
        "author"
    ]
};

exports.resultOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "BATTERY",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.1"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            },
            dataKey: "result_set",
            required: true
        },
        fieldLevel.statusCodeCompleted, {
            key: "component",
            content: [
                [resultObservation, required]
            ],
            dataKey: "results",
            required: true
        }
    ],
    notIplemented: [
        "variable @classCode",
        "variable statusCode"
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22}],16:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require('../condition');

var severityObservation = exports.severityObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.8"),
        fieldLevel.templateCode("SeverityObservation"),
        fieldLevel.text(leafLevel.nextReference("severity")),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "code",
            existsWhen: condition.codeOrDisplayname,
            required: true
        }, {
            key: "interpretationCode",
            attributes: leafLevel.code,
            dataKey: "interpretation",
            existsWhen: condition.codeOrDisplayname
        }
    ],
    dataKey: "severity",
    existsWhen: condition.keyExists("code")
};

var reactionObservation = exports.reactionObservation = {
    key: "observation",
    attributes: {
        "classCode": "OBS",
        "moodCode": "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.9"),
        fieldLevel.id,
        fieldLevel.nullFlavor("code"),
        fieldLevel.text(leafLevel.sameReference("reaction")),
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: 'reaction',
            existsWhen: condition.codeOrDisplayname,
            required: true
        }, {
            key: "entryRelationship",
            attributes: {
                "typeCode": "SUBJ",
                "inversionInd": "true"
            },
            content: severityObservation,
            existsWhen: condition.keyExists('severity')
        }
    ],
    notImplemented: [
        "Procedure Activity Procedure",
        "Medication Activity"
    ]
};

exports.serviceDeliveryLocation = {
    key: "participantRole",
    attributes: {
        classCode: "SDLOC"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.32"), {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "location_type",
            required: true
        },
        fieldLevel.usRealmAddress,
        fieldLevel.telecom, {
            key: "playingEntity",
            attributes: {
                classCode: "PLC"
            },
            content: {
                key: "name",
                text: leafLevel.inputProperty("name"),
            },
            existsWhen: condition.keyExists("name")
        }
    ]
};

exports.ageObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.31"),
        fieldLevel.templateCode("AgeObservation"),
        fieldLevel.statusCodeCompleted, {
            key: "value",
            attributes: {
                "xsi:type": "PQ",
                value: leafLevel.inputProperty("onset_age"),
                unit: leafLevel.codeOnlyFromName("2.16.840.1.113883.11.20.9.21", "onset_age_unit")
            },
            required: true
        }
    ]
};

exports.indication = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.19"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            dataKey: "code",
            required: true
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: [
                leafLevel.typeCD,
                leafLevel.code
            ],
            dataKey: "value",
            existsWhen: condition.codeOrDisplayname
        }
    ],
    notImplemented: [
        "value should handle nullFlavor=OTH and translation"
    ]
};

exports.preconditionForSubstanceAdministration = {
    key: "criterion",
    content: [{
        key: "code",
        attributes: {
            code: leafLevel.inputProperty("code"),
            codeSystem: "2.16.840.1.113883.5.4"
        },
        dataKey: "code"
    }, {
        key: "value",
        attributes: [
            leafLevel.typeCE, // TODO: spec has CD, spec example has CE
            leafLevel.code
        ],
        dataKey: "value",
        existsWhen: condition.codeOrDisplayname
    }],
    warning: [
        "value type is CE is example but CD in spec",
        "templateId should be here according to spec but per CCD_1 is put in the parent"
    ]
};

exports.drugVehicle = {
    key: "participantRole",
    attributes: {
        classCode: "MANU"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.24"), {
            key: "code",
            attributes: {
                code: "412307009",
                displayName: "drug vehicle",
                codeSystem: "2.16.840.1.113883.6.96",
                codeSystemName: "SNOMED CT"
            }
        }, {
            key: "playingEntity",
            attributes: {
                classCode: "MMAT"
            },
            content: [{
                key: "code",
                attributes: leafLevel.code,
                required: true
            }, {
                key: "name",
                text: leafLevel.inputProperty("name")
            }],
            required: true
        }
    ]
};

exports.instructions = {
    key: "act",
    attributes: {
        classCode: "ACT",
        moodCode: "INT"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.20"), {
            key: "code",
            attributes: [
                leafLevel.code
            ],
            dataKey: "code",
            required: true
        },
        fieldLevel.text(leafLevel.nextReference("instruction")),
        fieldLevel.statusCodeCompleted
    ]
};

},{"../condition":2,"../fieldLevel":19,"../leafLevel":22}],17:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

exports.socialHistoryObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.38"),
        fieldLevel.uniqueId,
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                text: leafLevel.inputProperty("unencoded_name"),
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("social")
                    }
                }
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "code",
        },
        fieldLevel.statusCodeCompleted,
        fieldLevel.effectiveTime, {
            key: "value",
            attributes: {
                "xsi:type": "ST"
            },
            text: leafLevel.inputProperty("value")
        }
    ],
    existsWhen: function (input) {
        return (!input.value) || input.value.indexOf("smoke") < 0;
    }
};

exports.smokingStatusObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.78"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("SmokingStatusObservation"),
        fieldLevel.statusCodeCompleted, [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: [{
                    "xsi:type": "CD"
                },
                leafLevel.codeFromName("2.16.840.1.113883.11.20.9.38")
            ],
            required: true,
            dataKey: "value"
        }
    ],
    existsWhen: function (input) {
        return input.value && input.value.indexOf("smoke") > -1;
    }
};

},{"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22}],18:[function(require,module,exports){
"use strict";

var fieldLevel = require('../fieldLevel');
var leafLevel = require('../leafLevel');
var condition = require("../condition");

var contentModifier = require("../contentModifier");

var required = contentModifier.required;

var vitalSignObservation = {
    key: "observation",
    attributes: {
        classCode: "OBS",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.27"),
        fieldLevel.id, {
            key: "code",
            attributes: leafLevel.code,
            content: [{
                key: "originalText",
                content: {
                    key: "reference",
                    attributes: {
                        "value": leafLevel.nextReference("vital")
                    }
                }
            }, {
                key: "translation",
                attributes: leafLevel.code,
                dataKey: "translations"
            }],
            dataKey: "vital",
            required: true
        }, {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required], {
            key: "value",
            attributes: {
                "xsi:type": "PQ",
                value: leafLevel.inputProperty("value"),
                unit: leafLevel.inputProperty("unit")
            },
            existsWhen: condition.keyExists("value"),
            required: true
        }, {
            key: "interpretationCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.5.83"),
            dataKey: "interpretations"
        }
    ],
    notImplemented: [
        "constant statusCode",
        "methodCode",
        "targetSiteCode",
        "author"
    ]
};

exports.vitalSignsOrganizer = {
    key: "organizer",
    attributes: {
        classCode: "CLUSTER",
        moodCode: "EVN"
    },
    content: [
        fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.26"),
        fieldLevel.uniqueId,
        fieldLevel.id,
        fieldLevel.templateCode("VitalSignsOrganizer"), {
            key: "statusCode",
            attributes: {
                code: leafLevel.inputProperty("status")
            }
        },
        [fieldLevel.effectiveTime, required], {
            key: "component",
            content: vitalSignObservation,
            required: true
        }
    ],
    notImplemented: [
        "constant statusCode"
    ]
};

},{"../condition":2,"../contentModifier":3,"../fieldLevel":19,"../leafLevel":22}],19:[function(require,module,exports){
"use strict";

var bbm = require("blue-button-meta");
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
        extension: leafLevel.inputProperty("extension")
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

var effectiveTimeNow = exports.effectiveTimeNow = {
    key: "effectiveTime",
    attributes: {
        "value": moment().format("YYYYMMDDHHMMZZ"),
    }
};

var timeNow = exports.timeNow = {
    key: "time",
    attributes: {
        "value": moment().format("YYYYMMDDHHMMZZ"),
    }
};

var effectiveTime = exports.effectiveTime = {
    key: "effectiveTime",
    attributes: {
        "value": leafLevel.time,
    },
    attributeKey: 'point',
    content: [{
        key: "low",
        attributes: {
            "value": leafLevel.time
        },
        dataKey: 'low',
    }, {
        key: "high",
        attributes: {
            "value": leafLevel.time
        },
        dataKey: 'high',
    }, {
        key: "center",
        attributes: {
            "value": leafLevel.time
        },
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

var usRealmAddress = exports.usRealmAddress = {
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

var representedOrganization = {
    key: "representedOrganization",
    content: [
        id, {
            key: "id",
            attributes: {
                extension: leafLevel.inputProperty("extension"),
                root: leafLevel.inputProperty("root")
            },
            dataKey: "identity"
        }, {
            key: "name",
            text: leafLevel.input,
            dataKey: "name"
        },
        usRealmAddress,
        telecom, {
            key: "telecom",
            attributes: [{
                use: "WP",
                value: function (input) {
                    return input.value.number;
                }
            }],
            existsWhen: condition.keyExists("value"),
            dataKey: "phone"
        }
    ],
    dataKey: "organization"
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

exports.author = {
    key: "author",
    content: [{
            key: "time",
            attributes: {
                nullFlavor: "UNK"
            },
        },
        [effectiveTime, required, key("time")], {
            key: "assignedAuthor",
            content: [
                id, {
                    key: "assignedPerson",
                    content: usRealmName
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

},{"./condition":2,"./contentModifier":3,"./leafLevel":22,"./translate":25,"blue-button-meta":26,"moment":46,"uuid":48}],20:[function(require,module,exports){
"use strict";

var fieldLevel = require('./fieldLevel');
var leafLevel = require('./leafLevel');
var condition = require('./condition');
var contentModifier = require("./contentModifier");

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
        patientName, {
            key: "administrativeGenderCode",
            attributes: {
                code: function (input) {
                    return input.substring(0, 1);
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
                    return input.substring(0, 1);
                },
                displayName: leafLevel.input,
                codeSystem: "2.16.840.1.113883.5.2",
                codeSystemName: "HL7 Marital Status"
            },
            dataKey: "marital_status"
        }, {
            key: "religiousAffiliationCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.5.1076"),
            dataKey: "religion"
        }, {
            key: "ethnicGroupCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.6.238"),
            dataKey: "ethnicity"
        }, {
            key: "raceCode",
            attributes: leafLevel.codeFromName("2.16.840.1.113883.6.238"),
            dataKey: "race"
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
                        return input.substring(0, 1);
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

var provider = exports.provider = {
    key: "performer",
    attributes: {
        typeCode: "PRF"
    },
    content: [
        [fieldLevel.effectiveTime, key("time"), dataKey("date_time")], {
            key: "assignedEntity",
            content: [{
                    key: "id",
                    attributes: {
                        extension: leafLevel.inputProperty("extension"),
                        root: leafLevel.inputProperty("root")
                    },
                    dataKey: "identity"
                }, {
                    key: "code",
                    attributes: leafLevel.code,
                    dataKey: "type"
                },

                {
                    key: "telecom",
                    attributes: [{
                        use: "WP",
                        value: function (input) {
                            return input.value.number;
                        }
                    }],
                    dataKey: "phone"
                }, {
                    key: "assignedPerson",
                    content: [{
                        key: "name",
                        content: [{
                            key: "given",
                            text: leafLevel.inputProperty("first")
                        }, {
                            key: "family",
                            text: leafLevel.inputProperty("last")
                        }],
                        dataKey: "name"
                    }]
                }
            ]
        }
    ],
    dataKey: "providers"
};

var attributed_provider = exports.attributed_provider = {
    key: "providerOrganization",
    content: [{
        key: "id",
        attributes: {
            extension: leafLevel.inputProperty("extension"),
            root: leafLevel.inputProperty("root")
        },
        dataKey: "attributed_provider.identity"
    }, {
        key: "name",
        text: leafLevel.inputProperty("full"),
        dataKey: "attributed_provider.name"
    }, {
        key: "telecom",
        attributes: [{
            use: "WP",
            value: function (input) {
                return input.value.number;
            }
        }],
        dataKey: "attributed_provider.phone"
    }],
    dataKey: "meta"
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
        [fieldLevel.timeNow, required], {
            key: "assignedAuthor",
            //attributes: {id:}
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("id")
                },
                dataKey: "author"

            }, {
                key: "representedOrganization",
                content: [{
                    key: "id",
                    attributes: {
                        root: leafLevel.inputProperty("id")
                    },
                    dataKey: "author"
                }, {
                    key: "name",
                    text: leafLevel.inputProperty("name"),
                    dataKey: "author"

                }]
            }]
        }
    ],
    dataKey: "meta.ccda_header"
};
var headerInformant = exports.headerInformant = {
    key: "informant",
    content: {
        key: "assignedEntity",
        //attributes: {id:}
        content: [{
            key: "id",
            attributes: {
                root: leafLevel.inputProperty("id")
            },
            dataKey: "informant"

        }, {
            key: "representedOrganization",
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("id")
                },
                dataKey: "informant"
            }, {
                key: "name",
                text: leafLevel.inputProperty("name"),
                dataKey: "informant"

            }]
        }]
    },
    dataKey: "meta.ccda_header"
};
var headerCustodian = exports.headerCustodian = {
    key: "custodian",
    content: {
        key: "assignedCustodian",
        //attributes: {id:}
        content: [{
            key: "representedCustodianOrganization",
            content: [{
                key: "id",
                attributes: {
                    root: leafLevel.inputProperty("id")
                },
                dataKey: "custodian"
            }, {
                key: "name",
                text: leafLevel.inputProperty("name"),
                dataKey: "custodian"

            }]
        }]
    },
    dataKey: "meta.ccda_header"
};

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
            provider
        ]
    },
    dataKey: "data.demographics"
};

},{"./condition":2,"./contentModifier":3,"./fieldLevel":19,"./leafLevel":22}],21:[function(require,module,exports){
"use strict";

var bbu = require("blue-button-util");

var fieldLevel = require("./fieldLevel");
var entryLevel = require("./entryLevel");
var leafLevel = require('./leafLevel');
var contentModifier = require("./contentModifier");

var required = contentModifier.required;
var bbud = bbu.datetime;
var bbuo = bbu.object;

var nda = "No Data Available";

var condition = require('./condition');

var getText = function (topArrayKey, headers, values) {
    var result = {
        key: "text",
        existsWhen: condition.keyExists(topArrayKey),

        content: [{
            key: "table",
            attributes: {
                border: "1",
                width: "100%"
            },
            content: [{
                key: "thead",
                content: [{
                    key: "tr",
                    content: []
                }]
            }, {
                key: "tbody",
                content: [{
                    key: "tr",
                    content: [],
                    dataKey: topArrayKey
                }]
            }]
        }]
    };
    var headerTarget = result.content[0].content[0].content[0].content;
    headers.forEach(function (header) {
        var element = {
            key: "th",
            text: header
        };
        headerTarget.push(element);
    });
    var valueTarget = result.content[0].content[1].content[0].content;
    values.forEach(function (value) {
        var data;
        if (typeof value !== 'function') {
            data = leafLevel.deepInputProperty(value, "");
        } else {
            data = value;
        }

        var element = {
            key: "td",
            text: data
        };
        valueTarget.push(element);
    });
    return result;
};

var alllergiesTextHeaders = ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
var allergiesTextRow = [
    leafLevel.deepInputProperty("observation.allergen.name", ""),
    leafLevel.deepInputProperty("observation.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.reaction.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.status.name", "")
];

exports.allergiesSectionEntriesRequiredHtmlHeader = getText('allergies', alllergiesTextHeaders, allergiesTextRow);

var medicationsTextHeaders = ["Medication Class", "# fills", "Last fill date"];
var medicationsTextRow = [ // Name, did not find class in the medication blue-button-data
    function (input) {
        var value = bbuo.deepValue(input, 'product.product.name');
        if (!bbuo.exists(value)) {
            value = bbuo.deepValue(input, 'product.unencoded_name');
        }
        if (!bbuo.exists(value)) {
            return "";
        } else {
            return value;
        }
    },
    leafLevel.deepInputProperty("supply.repeatNumber", ""),
    leafLevel.deepInputDate("supply.date_time.point", "")
];

exports.medicationsSectionEntriesRequiredHtmlHeader = getText('medications', medicationsTextHeaders, medicationsTextRow);

exports.problemsSectionEntriesRequiredHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("problems"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "2"
                    },
                    text: "Problems"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Condition', 'Severity'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("problem.code.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("problem.severity.code.name", nda)
                }]
            }],
            dataKey: 'problems'
        }]
    }]
};

exports.proceduresSectionEntriesRequiredHtmlHeader = {

    key: "text",
    existsWhen: condition.keyExists("procedures"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "5"
                    },
                    text: "Procedures"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Service', 'Procedure code', 'Service date', 'Servicing provider', 'Phone#'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("procedure.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("procedure.code", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.point", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("performer.0.organization.0.name.0", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("performer.0.organization.0.phone.0.value.number", nda)
                }]
            }],
            dataKey: 'procedures'
        }]
    }]
};

exports.resultsSectionEntriesRequiredHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("results"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "7"
                    },
                    text: "Laboratory Results"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Test', 'Result', 'Units', 'Ref low', 'Ref high', 'Date', 'Source'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    attributes: {
                        colspan: "7"
                    },
                    text: leafLevel.deepInputProperty('result_set.name', nda),
                }]
            }, {
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("result.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("value", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("unit", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("reference_range.low", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("reference_range.high", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.point", nda),
                }, {
                    key: "td",
                    text: nda
                }],
                dataKey: 'results'
            }],
            dataKey: 'results'
        }]
    }]
};

exports.encountersSectionEntriesOptionalHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("encounters"),

    content: [{
        key: "table",
        content: [{
            key: "caption",
            text: "Encounters"
        }, {
            key: "thead",
            content: [{
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Type', 'Facility', 'Date of Service', 'Diagnosis/Complaint'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("encounter.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("locations.0.name", nda)
                }, {
                    key: "td",
                    text: function (input) {
                        var value = bbuo.deepValue(input, "date_time.point");
                        if (value) {
                            value = bbud.modelToDate({
                                date: value.date,
                                precision: value.precision // workaround a bug in bbud.  Changes precision.
                            });
                            if (value) {
                                var vps = value.split('-');
                                if (vps.length === 3) {
                                    return [vps[1], vps[2], vps[0]].join('/');
                                }
                            }
                        }
                        return nda;
                    }
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("findings.0.value.name", nda)
                }],
            }],
            dataKey: 'encounters'
        }]
    }]
};

exports.immunizationsSectionEntriesOptionalHtmlHeader = {};

exports.payersSectionHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("payers"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "5"
                    },
                    text: "Payers"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Payer Name', 'Group ID', 'Member ID', 'Elegibility Start Date', 'Elegibility End Date'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("policy.insurance.performer.organization.0.name.0", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("policy.identifiers.0.extension", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("participant.performer.identifiers.0.extension", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("participant.date_time.low.date", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("participant.date_time.high.date", nda)
                }]
            }],
            dataKey: 'payers'
        }]
    }]
};

exports.planOfCareSectionHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("plan_of_care"),
    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "4"
                    },
                    text: "Plan of Care"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Program', 'Start Date', 'Severity', ''];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("plan.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.low", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("severity.name", nda)
                }, {
                    key: "td",
                    content: {

                        key: "table",
                        content: [{
                            key: "thead",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "th",
                                    text: leafLevel.input,
                                    dataTransform: function () {
                                        return ['Goals', ''];
                                    }
                                }]
                            }]
                        }, {
                            key: "tbody",
                            content: [{
                                key: "tr",
                                content: [{
                                    key: "td",
                                    text: leafLevel.deepInputProperty("goal.name", nda)
                                }, {
                                    key: "td",
                                    content: {

                                        key: "table",
                                        content: [{
                                            key: "thead",
                                            content: [{
                                                key: "tr",
                                                content: [{
                                                    key: "th",
                                                    text: leafLevel.input,
                                                    dataTransform: function () {
                                                        return ['Interventions'];
                                                    }
                                                }]
                                            }]
                                        }, {
                                            key: "tbody",
                                            content: [{
                                                key: "tr",
                                                content: [{
                                                    key: "td",
                                                    text: leafLevel.deepInputProperty("intervention.name", nda)
                                                }]
                                            }],
                                            dataKey: 'interventions'
                                        }]

                                    }

                                }]
                            }],
                            dataKey: 'goals'
                        }]
                    }

                }]
            }],
            dataKey: 'plan_of_care'
        }]
    }]
};

exports.socialHistorySectionHtmlHeader = {};

exports.vitalSignsSectionEntriesOptionalHtmlHeader = {};

exports.allergiesSectionEntriesRequiredHtmlHeaderNA = "Not Available";
exports.medicationsSectionEntriesRequiredHtmlHeaderNA = "Not Available";
exports.problemsSectionEntriesRequiredHtmlHeaderNA = "Not Available";
exports.proceduresSectionEntriesRequiredHtmlHeaderNA = "Not Available";
exports.resultsSectionEntriesRequiredHtmlHeaderNA = "Not Available";
exports.encountersSectionEntriesOptionalHtmlHeaderNA = "Not Available";
exports.immunizationsSectionEntriesOptionalHtmlHeaderNA = "Not Available";
exports.payersSectionHtmlHeaderNA = "Not Available";
exports.planOfCareSectionHtmlHeaderNA = "Not Available";
exports.socialHistorySectionHtmlHeaderNA = "Not Available";
exports.vitalSignsSectionEntriesOptionalHtmlHeaderNA = "Not Available";

},{"./condition":2,"./contentModifier":3,"./entryLevel":9,"./fieldLevel":19,"./leafLevel":22,"blue-button-util":36}],22:[function(require,module,exports){
"use strict";

var bbu = require("blue-button-util");

var translate = require('./translate');

var bbuo = bbu.object;
var bbud = bbu.datetime;

exports.input = function (input) {
    return input;
};

exports.inputProperty = function (key) {
    return function (input) {
        return input && input[key];
    };
};

exports.boolInputProperty = function (key) {
    return function (input) {
        if (input && input.hasOwnProperty(key)) {
            return input[key].toString();
        } else {
            return null;
        }
    };
};

exports.code = translate.code;

exports.codeFromName = translate.codeFromName;

exports.codeOnlyFromName = function (OID, key) {
    var f = translate.codeFromName(OID);
    return function (input) {
        if (input && input[key]) {
            return f(input[key]).code;
        } else {
            return null;
        }
    };
};

exports.time = translate.time;

exports.use = function (key) {
    return function (input) {
        var value = input && input[key];
        if (value) {
            return translate.acronymize(value);
        } else {
            return null;
        }
    };
};

exports.typeCD = {
    "xsi:type": "CD"
};

exports.typeCE = {
    "xsi:type": "CE"
};

exports.nextReference = function (referenceKey) {
    return function (input, context) {
        return context.nextReference(referenceKey);
    };
};

exports.sameReference = function (referenceKey) {
    return function (input, context) {
        return context.sameReference(referenceKey);
    };
};

exports.deepInputProperty = function (deepProperty, defaultValue) {
    return function (input) {
        var value = bbuo.deepValue(input, deepProperty);
        value = bbuo.exists(value) ? value : defaultValue;
        if (typeof value !== 'string') {
            value = value.toString();
        }
        return value;
    };
};

exports.deepInputDate = function (deepProperty, defaultValue) {
    return function (input) {
        var value = bbuo.deepValue(input, deepProperty);
        if (!bbuo.exists(value)) {
            return defaultValue;
        } else {
            value = bbud.modelToDate({
                date: value.date,
                precision: value.precision // workaround a bug in bbud.  Changes precision.
            });
            if (bbuo.exists(value)) {
                return value;
            } else {
                return defaultValue;
            }
        }
    };
};

},{"./translate":25,"blue-button-util":36}],23:[function(require,module,exports){
"use strict";

var bbu = require("blue-button-util");

var fieldLevel = require("./fieldLevel");
var entryLevel = require("./entryLevel");
var leafLevel = require('./leafLevel');
var contentModifier = require("./contentModifier");

var required = contentModifier.required;
var bbud = bbu.datetime;
var bbuo = bbu.object;

var nda = "No Data Available";

var condition = require('./condition');

var getText = function (topArrayKey, headers, values) {
    var result = {
        key: "text",
        existsWhen: condition.keyExists(topArrayKey),

        content: [{
            key: "table",
            attributes: {
                border: "1",
                width: "100%"
            },
            content: [{
                key: "thead",
                content: [{
                    key: "tr",
                    content: []
                }]
            }, {
                key: "tbody",
                content: [{
                    key: "tr",
                    content: [],
                    dataKey: topArrayKey
                }]
            }]
        }]
    };
    var headerTarget = result.content[0].content[0].content[0].content;
    headers.forEach(function (header) {
        var element = {
            key: "th",
            text: header
        };
        headerTarget.push(element);
    });
    var valueTarget = result.content[0].content[1].content[0].content;
    values.forEach(function (value) {
        var data;
        if (typeof value !== 'function') {
            data = leafLevel.deepInputProperty(value, "");
        } else {
            data = value;
        }

        var element = {
            key: "td",
            text: data
        };
        valueTarget.push(element);
    });
    return result;
};

var alllergiesTextHeaders = ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
var allergiesTextRow = [
    leafLevel.deepInputProperty("observation.allergen.name", ""),
    leafLevel.deepInputProperty("observation.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.reaction.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.status.name", "")
];

exports.allergiesSectionEntriesRequired = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6.1"),
            fieldLevel.templateCode("AllergiesSection"),
            fieldLevel.templateTitle("AllergiesSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("allergies")

            },
            getText('allergies', alllergiesTextHeaders, allergiesTextRow), {
                key: "entry",
                attributes: {
                    "typeCode": "DRIV"
                },
                content: [
                    [entryLevel.allergyProblemAct, required]
                ],
                dataKey: "allergies",
                required: true
            }
        ]
    }]
};

var medicationsTextHeaders = ["Medication Class", "# fills", "Last fill date"];
var medicationsTextRow = [ // Name, did not find class in the medication blue-button-data
    function (input) {
        var value = bbuo.deepValue(input, 'product.product.name');
        if (!bbuo.exists(value)) {
            value = bbuo.deepValue(input, 'product.unencoded_name');
        }
        if (!bbuo.exists(value)) {
            return "";
        } else {
            return value;
        }
    },
    leafLevel.deepInputProperty("supply.repeatNumber", ""),
    leafLevel.deepInputDate("supply.date_time.point", "")
];

exports.medicationsSectionEntriesRequired = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1.1"),
            fieldLevel.templateCode("MedicationsSection"),
            fieldLevel.templateTitle("MedicationsSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("medications")

            },
            getText('medications', medicationsTextHeaders, medicationsTextRow), {
                key: "entry",
                attributes: {
                    "typeCode": "DRIV"
                },
                content: [
                    [entryLevel.medicationActivity, required]
                ],
                dataKey: "medications",
                required: true
            }
        ]
    }]
};

exports.problemsSectionEntriesRequired = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5.1"),
            fieldLevel.templateCode("ProblemSection"),
            fieldLevel.templateTitle("ProblemSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("problems")

            }, {
                key: "text",
                existsWhen: condition.keyExists("problems"),

                content: [{
                    key: "table",
                    content: [{
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: {
                                key: "th",
                                attributes: {
                                    colspan: "2"
                                },
                                text: "Problems"
                            }
                        }, {
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Condition', 'Severity'];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("problem.code.name", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("problem.severity.code.name", nda)
                            }]
                        }],
                        dataKey: 'problems'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    "typeCode": "DRIV"
                },
                content: [
                    [entryLevel.problemConcernAct, required]
                ],
                dataKey: "problems",
                required: true
            }
        ]
    }]
};

exports.proceduresSectionEntriesRequired = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7.1"),
            fieldLevel.templateCode("ProceduresSection"),
            fieldLevel.templateTitle("ProceduresSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("procedures")

            }, {
                key: "text",
                existsWhen: condition.keyExists("procedures"),

                content: [{
                    key: "table",
                    content: [{
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: {
                                key: "th",
                                attributes: {
                                    colspan: "5"
                                },
                                text: "Procedures"
                            }
                        }, {
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Service', 'Procedure code', 'Service date', 'Servicing provider', 'Phone#'];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("procedure.name", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("procedure.code", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputDate("date_time.point", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("performer.0.organization.0.name.0", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("performer.0.organization.0.phone.0.value.number", nda)
                            }]
                        }],
                        dataKey: 'procedures'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    "typeCode": function (input) {
                        return input.procedure_type === "procedure" ? "DRIV" : null;
                    }
                },
                content: [
                    entryLevel.procedureActivityAct,
                    entryLevel.procedureActivityProcedure,
                    entryLevel.procedureActivityObservation
                ],
                dataKey: "procedures"
            }
        ]
    }],
    notImplemented: [
        "entry required"
    ]
};

exports.resultsSectionEntriesRequired = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3.1"),
            fieldLevel.templateCode("ResultsSection"),
            fieldLevel.templateTitle("ResultsSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("results")

            }, {
                key: "text",
                existsWhen: condition.keyExists("results"),

                content: [{
                    key: "table",
                    content: [{
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: {
                                key: "th",
                                attributes: {
                                    colspan: "7"
                                },
                                text: "Laboratory Results"
                            }
                        }, {
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Test', 'Result', 'Units', 'Ref low', 'Ref high', 'Date', 'Source'];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                attributes: {
                                    colspan: "7"
                                },
                                text: leafLevel.deepInputProperty('result_set.name', nda),
                            }]
                        }, {
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("result.name", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("value", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("unit", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("reference_range.low", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("reference_range.high", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputDate("date_time.point", nda),
                            }, {
                                key: "td",
                                text: nda
                            }],
                            dataKey: 'results'
                        }],
                        dataKey: 'results'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    typeCode: "DRIV"
                },
                content: [
                    [entryLevel.resultOrganizer, required]
                ],
                dataKey: "results",
                required: true
            }
        ]
    }]
};

exports.encountersSectionEntriesOptional = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22.1"),
            fieldLevel.templateCode("EncountersSection"),
            fieldLevel.templateTitle("EncountersSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("encounters")

            }, {
                key: "text",
                existsWhen: condition.keyExists("encounters"),

                content: [{
                    key: "table",
                    content: [{
                        key: "caption",
                        text: "Encounters"
                    }, {
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Type', 'Facility', 'Date of Service', 'Diagnosis/Complaint'];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("encounter.name", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("locations.0.name", nda)
                            }, {
                                key: "td",
                                text: function (input) {
                                    var value = bbuo.deepValue(input, "date_time.point");
                                    if (value) {
                                        value = bbud.modelToDate({
                                            date: value.date,
                                            precision: value.precision // workaround a bug in bbud.  Changes precision.
                                        });
                                        if (value) {
                                            var vps = value.split('-');
                                            if (vps.length === 3) {
                                                return [vps[1], vps[2], vps[0]].join('/');
                                            }
                                        }
                                    }
                                    return nda;
                                }
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("findings.0.value.name", nda)
                            }],
                        }],
                        dataKey: 'encounters'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    "typeCode": "DRIV"
                },
                content: [
                    [entryLevel.encounterActivities, required]
                ],
                dataKey: "encounters"
            }
        ]
    }]
};

exports.immunizationsSectionEntriesOptional = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2.1"),
            fieldLevel.templateCode("ImmunizationsSection"),
            fieldLevel.templateTitle("ImmunizationsSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("immunizations")

            }, {
                key: "entry",
                attributes: {
                    "typeCode": "DRIV"
                },
                content: [
                    [entryLevel.immunizationActivity, required]
                ],
                dataKey: "immunizations"
            }
        ]
    }]
};

exports.payersSection = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.18"),
            fieldLevel.templateCode("PayersSection"),
            fieldLevel.templateTitle("PayersSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("payers")

            }, {
                key: "text",
                existsWhen: condition.keyExists("payers"),

                content: [{
                    key: "table",
                    content: [{
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: {
                                key: "th",
                                attributes: {
                                    colspan: "5"
                                },
                                text: "Payers"
                            }
                        }, {
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Payer Name', 'Group ID', 'Member ID', 'Elegibility Start Date', 'Elegibility End Date'];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("policy.insurance.performer.organization.0.name.0", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("policy.identifiers.0.extension", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("participant.performer.identifiers.0.extension", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("participant.date_time.low.date", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("participant.date_time.high.date", nda)
                            }]
                        }],
                        dataKey: 'payers'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    typeCode: "DRIV"
                },
                content: [
                    [entryLevel.coverageActivity, required]
                ],
                dataKey: "payers"
            }
        ]
    }]
};

exports.planOfCareSection = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.10"),
            fieldLevel.templateCode("PlanOfCareSection"),
            fieldLevel.templateTitle("PlanOfCareSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("plan_of_care")

            }, {
                key: "text",
                existsWhen: condition.keyExists("plan_of_care"),
                content: [{
                    key: "table",
                    content: [{
                        key: "thead",
                        content: [{
                            key: "tr",
                            content: {
                                key: "th",
                                attributes: {
                                    colspan: "4"
                                },
                                text: "Plan of Care"
                            }
                        }, {
                            key: "tr",
                            content: [{
                                key: "th",
                                text: leafLevel.input,
                                dataTransform: function () {
                                    return ['Program', 'Start Date', 'Severity', ''];
                                }
                            }]
                        }]
                    }, {
                        key: "tbody",
                        content: [{
                            key: "tr",
                            content: [{
                                key: "td",
                                text: leafLevel.deepInputProperty("plan.name", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputDate("date_time.low", nda)
                            }, {
                                key: "td",
                                text: leafLevel.deepInputProperty("severity.name", nda)
                            }, {
                                key: "td",
                                content: {

                                    key: "table",
                                    content: [{
                                        key: "thead",
                                        content: [{
                                            key: "tr",
                                            content: [{
                                                key: "th",
                                                text: leafLevel.input,
                                                dataTransform: function () {
                                                    return ['Goals', ''];
                                                }
                                            }]
                                        }]
                                    }, {
                                        key: "tbody",
                                        content: [{
                                            key: "tr",
                                            content: [{
                                                key: "td",
                                                text: leafLevel.deepInputProperty("goal.name", nda)
                                            }, {
                                                key: "td",
                                                content: {

                                                    key: "table",
                                                    content: [{
                                                        key: "thead",
                                                        content: [{
                                                            key: "tr",
                                                            content: [{
                                                                key: "th",
                                                                text: leafLevel.input,
                                                                dataTransform: function () {
                                                                    return ['Interventions'];
                                                                }
                                                            }]
                                                        }]
                                                    }, {
                                                        key: "tbody",
                                                        content: [{
                                                            key: "tr",
                                                            content: [{
                                                                key: "td",
                                                                text: leafLevel.deepInputProperty("intervention.name", nda)
                                                            }]
                                                        }],
                                                        dataKey: 'interventions'
                                                    }]

                                                }

                                            }]
                                        }],
                                        dataKey: 'goals'
                                    }]
                                }

                            }]
                        }],
                        dataKey: 'plan_of_care'
                    }]
                }]
            }, {
                key: "entry",
                attributes: {
                    "typeCode": function (input) {
                        return input.type === "observation" ? "DRIV" : null;
                    }
                },
                content: [
                    entryLevel.planOfCareActivityAct,
                    entryLevel.planOfCareActivityObservation,
                    entryLevel.planOfCareActivityProcedure,
                    entryLevel.planOfCareActivityEncounter,
                    entryLevel.planOfCareActivitySubstanceAdministration,
                    entryLevel.planOfCareActivitySupply,
                    entryLevel.planOfCareActivityInstructions
                ],
                dataKey: "plan_of_care"
            }
        ]
    }]
};

exports.socialHistorySection = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.17"),
            fieldLevel.templateCode("SocialHistorySection"),
            fieldLevel.templateTitle("SocialHistorySection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("social_history")

            }, {
                key: "entry",
                attributes: {
                    typeCode: "DRIV"
                },
                content: [
                    entryLevel.smokingStatusObservation,
                    entryLevel.socialHistoryObservation
                ],
                dataKey: "social_history"
            }
        ]
    }],
    notImplemented: [
        "pregnancyObservation",
        "tobaccoUse"
    ]
};

exports.vitalSignsSectionEntriesOptional = {
    key: "component",
    content: [{
        key: "section",
        content: [
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4"),
            fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4.1"),
            fieldLevel.templateCode("VitalSignsSection"),
            fieldLevel.templateTitle("VitalSignsSection"), {
                key: "text",
                text: "Not Applicable",
                existsWhen: condition.keyDoesntExist("vitals")

            }, {
                key: "entry",
                attributes: {
                    typeCode: "DRIV"
                },
                content: [
                    [entryLevel.vitalSignsOrganizer, required]
                ],
                dataKey: "vitals"
            }
        ]
    }]
};

},{"./condition":2,"./contentModifier":3,"./entryLevel":9,"./fieldLevel":19,"./leafLevel":22,"blue-button-util":36}],24:[function(require,module,exports){
"use strict";

var bbu = require("blue-button-util");

var fieldLevel = require("./fieldLevel");
var entryLevel = require("./entryLevel");
var leafLevel = require('./leafLevel');
var contentModifier = require("./contentModifier");

var required = contentModifier.required;
var bbud = bbu.datetime;
var bbuo = bbu.object;

var nda = "No Data Available";

var condition = require('./condition');

var getText = function (topArrayKey, headers, values) {
    var result = {
        key: "text",
        existsWhen: condition.keyExists(topArrayKey),

        content: [{
            key: "table",
            attributes: {
                border: "1",
                width: "100%"
            },
            content: [{
                key: "thead",
                content: [{
                    key: "tr",
                    content: []
                }]
            }, {
                key: "tbody",
                content: [{
                    key: "tr",
                    content: [],
                    dataKey: topArrayKey
                }]
            }]
        }]
    };
    var headerTarget = result.content[0].content[0].content[0].content;
    headers.forEach(function (header) {
        var element = {
            key: "th",
            text: header
        };
        headerTarget.push(element);
    });
    var valueTarget = result.content[0].content[1].content[0].content;
    values.forEach(function (value) {
        var data;
        if (typeof value !== 'function') {
            data = leafLevel.deepInputProperty(value, "");
        } else {
            data = value;
        }

        var element = {
            key: "td",
            text: data
        };
        valueTarget.push(element);
    });
    return result;
};

var alllergiesTextHeaders = ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
var allergiesTextRow = [
    leafLevel.deepInputProperty("observation.allergen.name", ""),
    leafLevel.deepInputProperty("observation.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.reaction.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.status.name", "")
];

exports.allergiesSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.6.1"),
                fieldLevel.templateCode("AllergiesSection"),
                fieldLevel.templateTitle("AllergiesSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("allergies")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.allergyProblemAct, required]
                    ],
                    dataKey: "allergies",
                    required: true
                }
            ]
        }]
    };
};

var medicationsTextHeaders = ["Medication Class", "# fills", "Last fill date"];
var medicationsTextRow = [ // Name, did not find class in the medication blue-button-data
    function (input) {
        var value = bbuo.deepValue(input, 'product.product.name');
        if (!bbuo.exists(value)) {
            value = bbuo.deepValue(input, 'product.unencoded_name');
        }
        if (!bbuo.exists(value)) {
            return "";
        } else {
            return value;
        }
    },
    leafLevel.deepInputProperty("supply.repeatNumber", ""),
    leafLevel.deepInputDate("supply.date_time.point", "")
];

exports.medicationsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.1.1"),
                fieldLevel.templateCode("MedicationsSection"),
                fieldLevel.templateTitle("MedicationsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("medications")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.medicationActivity, required]
                    ],
                    dataKey: "medications",
                    required: true
                }
            ]
        }]
    };
};

exports.problemsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.5.1"),
                fieldLevel.templateCode("ProblemSection"),
                fieldLevel.templateTitle("ProblemSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("problems")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.problemConcernAct, required]
                    ],
                    dataKey: "problems",
                    required: true
                }, {

                    key: "entry",
                    existsWhen: condition.keyExists("problems_comment"),
                    content: {
                        key: "act",
                        attributes: {
                            classCode: "ACT",
                            moodCode: "EVN"
                        },
                        content: [
                            fieldLevel.templateId("2.16.840.1.113883.10.20.22.4.64"),
                            fieldLevel.templateCode("CommentActivity"), {
                                key: "text",
                                text: leafLevel.deepInputProperty("problems_comment")
                            },
                        ]

                    },
                    dataKey: "demographics.meta"
                }
            ]
        }]
    };
};

exports.proceduresSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.7.1"),
                fieldLevel.templateCode("ProceduresSection"),
                fieldLevel.templateTitle("ProceduresSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("procedures")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": function (input) {
                            return input.procedure_type === "procedure" ? "DRIV" : null;
                        }
                    },
                    content: [
                        entryLevel.procedureActivityAct,
                        entryLevel.procedureActivityProcedure,
                        entryLevel.procedureActivityObservation
                    ],
                    dataKey: "procedures"
                }
            ]
        }],
        notImplemented: [
            "entry required"
        ]
    };
};

exports.resultsSectionEntriesRequired = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.3.1"),
                fieldLevel.templateCode("ResultsSection"),
                fieldLevel.templateTitle("ResultsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("results")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        [entryLevel.resultOrganizer, required]
                    ],
                    dataKey: "results",
                    required: true
                }
            ]
        }]
    };
};

exports.encountersSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.22.1"),
                fieldLevel.templateCode("EncountersSection"),
                fieldLevel.templateTitle("EncountersSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("encounters")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.encounterActivities, required]
                    ],
                    dataKey: "encounters"
                }
            ]
        }]
    };
};

exports.immunizationsSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.2.1"),
                fieldLevel.templateCode("ImmunizationsSection"),
                fieldLevel.templateTitle("ImmunizationsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("immunizations")

                }, {
                    key: "entry",
                    attributes: {
                        "typeCode": "DRIV"
                    },
                    content: [
                        [entryLevel.immunizationActivity, required]
                    ],
                    dataKey: "immunizations"
                }
            ]
        }]
    };
};

exports.payersSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.18"),
                fieldLevel.templateCode("PayersSection"),
                fieldLevel.templateTitle("PayersSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("payers")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        [entryLevel.coverageActivity, required]
                    ],
                    dataKey: "payers"
                }
            ]
        }]
    };
};

exports.planOfCareSection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.10"),
                fieldLevel.templateCode("PlanOfCareSection"),
                fieldLevel.templateTitle("PlanOfCareSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("plan_of_care")

                },
                htmlHeader, {
                    key: "entry",
                    attributes: {
                        "typeCode": function (input) {
                            return input.type === "observation" ? "DRIV" : null;
                        }
                    },
                    content: [
                        entryLevel.planOfCareActivityAct,
                        entryLevel.planOfCareActivityObservation,
                        entryLevel.planOfCareActivityProcedure,
                        entryLevel.planOfCareActivityEncounter,
                        entryLevel.planOfCareActivitySubstanceAdministration,
                        entryLevel.planOfCareActivitySupply,
                        entryLevel.planOfCareActivityInstructions
                    ],
                    dataKey: "plan_of_care"
                }
            ]
        }]
    };
};

exports.socialHistorySection = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.17"),
                fieldLevel.templateCode("SocialHistorySection"),
                fieldLevel.templateTitle("SocialHistorySection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("social_history")

                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        entryLevel.smokingStatusObservation,
                        entryLevel.socialHistoryObservation
                    ],
                    dataKey: "social_history"
                }
            ]
        }],
        notImplemented: [
            "pregnancyObservation",
            "tobaccoUse"
        ]
    };
};

exports.vitalSignsSectionEntriesOptional = function (htmlHeader, na) {
    return {
        key: "component",
        content: [{
            key: "section",
            content: [
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4"),
                fieldLevel.templateId("2.16.840.1.113883.10.20.22.2.4.1"),
                fieldLevel.templateCode("VitalSignsSection"),
                fieldLevel.templateTitle("VitalSignsSection"), {
                    key: "text",
                    text: na,
                    existsWhen: condition.keyDoesntExist("vitals")

                }, {
                    key: "entry",
                    attributes: {
                        typeCode: "DRIV"
                    },
                    content: [
                        [entryLevel.vitalSignsOrganizer, required]
                    ],
                    dataKey: "vitals"
                }
            ]
        }]
    };
};

},{"./condition":2,"./contentModifier":3,"./entryLevel":9,"./fieldLevel":19,"./leafLevel":22,"blue-button-util":36}],25:[function(require,module,exports){
"use strict";

var moment = require("moment");
var bbm = require("blue-button-meta");

var css = bbm.code_systems;

exports.codeFromName = function (OID) {
    return function (input) {
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
    if (input.code) {
        result.code = input.code;
    }

    if (input.name) {
        result.displayName = input.name;
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
    minute: 'YYYYMMDDHHMM',
    second: 'YYYYMMDDHHmmssZZ',
    subsecond: 'YYYYMMDDHHmmss.SSSZZ'
};

exports.time = function (input) {
    var m = moment.parseZone(input.date);
    var formatSpec = precisionToFormat[input.precision];
    var result = m.format(formatSpec);
    return result;
};

var acronymize = exports.acronymize = function (string) {
    var ret = string.split(" ");
    var fL = ret[0].slice(0, 1);
    var lL = ret[1].slice(0, 1);
    fL = fL.toUpperCase();
    lL = lL.toUpperCase();
    ret = fL + lL;
    if (ret === "PH") {
        ret = "HP";
    }
    if (ret === "HA") {
        ret = "H";
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
    var given = null;
    if (input.first) {
        given = [input.first];
        if (input.middle && input.middle[0]) {
            given.push(input.middle[0]);
        }
    }
    return {
        prefix: input.prefix,
        given: given,
        family: input.last,
        suffix: input.suffix
    };
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

},{"blue-button-meta":26,"moment":46}],26:[function(require,module,exports){
var CCDA = require("./lib/CCDA/index.js");

//CCDA metadata stuff
var meta = {};
meta.CCDA = CCDA;

meta.supported_sections = [
    'allergies',
    'procedures',
    'immunizations',
    'medications',
    'encounters',
    'vitals',
    'results',
    'social_history',
    'demographics',
    'problems',
    'insurance',
    'claims',
    'plan_of_care',
    'payers',
    'providers',
    'organizations'
];

meta.code_systems = require("./lib/code-systems");

module.exports = exports = meta;

},{"./lib/CCDA/index.js":29,"./lib/code-systems":34}],27:[function(require,module,exports){
var clinicalstatements = {
    "AdmissionMedication": "2.16.840.1.113883.10.20.22.4.36",
    "AdvanceDirectiveObservation": "2.16.840.1.113883.10.20.22.4.48",
    "AgeObservation": "2.16.840.1.113883.10.20.22.4.31",
    "AllergyObservation": "2.16.840.1.113883.10.20.22.4.7",
    "AllergyProblemAct": "2.16.840.1.113883.10.20.22.4.30",
    "AllergyStatusObservation": "2.16.840.1.113883.10.20.22.4.28",
    "AssessmentScaleObservation": "2.16.840.1.113883.10.20.22.4.69",
    "AssessmentScaleSupportingObservation": "2.16.840.1.113883.10.20.22.4.86",
    "AuthorizationActivity": "2.16.840.1.113883.10.20.1.19",
    "BoundaryObservation": "2.16.840.1.113883.10.20.6.2.11",
    "CaregiverCharacteristics": "2.16.840.1.113883.10.20.22.4.72",
    "CodeObservations": "2.16.840.1.113883.10.20.6.2.13",
    "CognitiveStatusProblemObservation": "2.16.840.1.113883.10.20.22.4.73",
    "CognitiveStatusResultObservation": "2.16.840.1.113883.10.20.22.4.74",
    "CognitiveStatusResultOrganizer": "2.16.840.1.113883.10.20.22.4.75",
    "CommentActivity": "2.16.840.1.113883.10.20.22.4.64",
    "CoverageActivity": "2.16.840.1.113883.10.20.22.4.60",
    "DeceasedObservation": "2.16.840.1.113883.10.20.22.4.79",
    "DischargeMedication": "2.16.840.1.113883.10.20.22.4.35",
    "EncounterActivities": "2.16.840.1.113883.10.20.22.4.49",
    "EncounterDiagnosis": "2.16.840.1.113883.10.20.22.4.80",
    "EstimatedDateOfDelivery": "2.16.840.1.113883.10.20.15.3.1",
    "FamilyHistoryDeathObservation": "2.16.840.1.113883.10.20.22.4.47",
    "FamilyHistoryObservation": "2.16.840.1.113883.10.20.22.4.46",
    "FamilyHistoryOrganizer": "2.16.840.1.113883.10.20.22.4.45",
    "FunctionalStatusProblemObservation": "2.16.840.1.113883.10.20.22.4.68",
    "FunctionalStatusResultObservation": "2.16.840.1.113883.10.20.22.4.67",
    "FunctionalStatusResultOrganizer": "2.16.840.1.113883.10.20.22.4.66",
    "HealthStatusObservation": "2.16.840.1.113883.10.20.22.4.5",
    "HighestPressureUlcerStage": "2.16.840.1.113883.10.20.22.4.77",
    "HospitalAdmissionDiagnosis": "2.16.840.1.113883.10.20.22.4.34",
    "HospitalDischargeDiagnosis": "2.16.840.1.113883.10.20.22.4.33",
    "ImmunizationActivity": "2.16.840.1.113883.10.20.22.4.52",
    "ImmunizationRefusalReason": "2.16.840.1.113883.10.20.22.4.53",
    "Indication": "2.16.840.1.113883.10.20.22.4.19",
    "Instructions": "2.16.840.1.113883.10.20.22.4.20",
    "MedicationActivity": "2.16.840.1.113883.10.20.22.4.16",
    "MedicationDispense": "2.16.840.1.113883.10.20.22.4.18",
    "MedicationSupplyOrder": "2.16.840.1.113883.10.20.22.4.17",
    "MedicationUseNoneKnown": "2.16.840.1.113883.10.20.22.4.29",
    "NonMedicinalSupplyActivity": "2.16.840.1.113883.10.20.22.4.50",
    "NumberOfPressureUlcersObservation": "2.16.840.1.113883.10.20.22.4.76",
    "PlanOfCareActivityAct": "2.16.840.1.113883.10.20.22.4.39",
    "PlanOfCareActivityEncounter": "2.16.840.1.113883.10.20.22.4.40",
    "PlanOfCareActivityObservation": "2.16.840.1.113883.10.20.22.4.44",
    "PlanOfCareActivityProcedure": "2.16.840.1.113883.10.20.22.4.41",
    "PlanOfCareActivitySubstanceAdministration": "2.16.840.1.113883.10.20.22.4.42",
    "PlanOfCareActivitySupply": "2.16.840.1.113883.10.20.22.4.43",
    "PolicyActivity": "2.16.840.1.113883.10.20.22.4.61",
    "PostprocedureDiagnosis": "2.16.840.1.113883.10.20.22.4.51",
    "PregnancyObservation": "2.16.840.1.113883.10.20.15.3.8",
    "PreoperativeDiagnosis": "2.16.840.1.113883.10.20.22.4.65",
    "PressureUlcerObservation": "2.16.840.1.113883.10.20.22.4.70",
    "ProblemConcernAct": "2.16.840.1.113883.10.20.22.4.3",
    "ProblemObservation": "2.16.840.1.113883.10.20.22.4.4",
    "ProblemStatus": "2.16.840.1.113883.10.20.22.4.6",
    "ProcedureActivityAct": "2.16.840.1.113883.10.20.22.4.12",
    "ProcedureActivityObservation": "2.16.840.1.113883.10.20.22.4.13",
    "ProcedureActivityProcedure": "2.16.840.1.113883.10.20.22.4.14",
    "ProcedureContext": "2.16.840.1.113883.10.20.6.2.5",
    "PurposeofReferenceObservation": "2.16.840.1.113883.10.20.6.2.9",
    "QuantityMeasurementObservation": "2.16.840.1.113883.10.20.6.2.14",
    "ReactionObservation": "2.16.840.1.113883.10.20.22.4.9",
    "ReferencedFramesObservation": "2.16.840.1.113883.10.20.6.2.10",
    "ResultObservation": "2.16.840.1.113883.10.20.22.4.2",
    "ResultOrganizer": "2.16.840.1.113883.10.20.22.4.1",
    "SeriesAct": "2.16.840.1.113883.10.20.22.4.63",
    "SeverityObservation": "2.16.840.1.113883.10.20.22.4.8",
    "SmokingStatusObservation": "2.16.840.1.113883.10.20.22.4.78",
    "SocialHistoryObservation": "2.16.840.1.113883.10.20.22.4.38",
    "SOPInstanceObservation": "2.16.840.1.113883.10.20.6.2.8",
    "StudyAct": "2.16.840.1.113883.10.20.6.2.6",
    "TextObservation": "2.16.840.1.113883.10.20.6.2.12",
    "TobaccoUse": "2.16.840.1.113883.10.20.22.4.85",
    "VitalSignObservation": "2.16.840.1.113883.10.20.22.4.27",
    "VitalSignsOrganizer": "2.16.840.1.113883.10.20.22.4.26"
};

var clinicalstatements_r1 = {
    "AdvanceDirectiveObservation": "2.16.840.1.113883.10.20.1.17",
    "AlertObservation": "2.16.840.1.113883.10.20.1.18",
    "AuthorizationActivity": "2.16.840.1.113883.10.20.1.19",
    "CoverageActivity": "2.16.840.1.113883.10.20.1.20",
    "EncounterActivity": "2.16.840.1.113883.10.20.1.21",
    "FamilyHistoryObservation": "2.16.840.1.113883.10.20.1.22",
    "FamilyHistoryOrganizer": "2.16.840.1.113883.10.20.1.23",
    "MedicationActivity": "2.16.840.1.113883.10.20.1.24",
    "PlanOfCareActivity": "2.16.840.1.113883.10.20.1.25",
    "PolicyActivity": "2.16.840.1.113883.10.20.1.26",
    "ProblemAct": "2.16.840.1.113883.10.20.1.27",
    "ProblemObservation": "2.16.840.1.113883.10.20.1.28",
    "ProcedureActivity": "2.16.840.1.113883.10.20.1.29",
    "PurposeActivity": "2.16.840.1.113883.10.20.1.30",
    "ResultObservation": "2.16.840.1.113883.10.20.1.31",
    "ResultOrganizer": "2.16.840.1.113883.10.20.1.32",
    "SocialHistoryObservation": "2.16.840.1.113883.10.20.1.33",
    "SupplyActivity": "2.16.840.1.113883.10.20.1.34",
    "VitalSignObservation": "2.16.840.1.113883.10.20.1.31",
    "Indication": "2.16.840.1.113883.10.20.22.4.19",
    "VitalSignsOrganizer": "2.16.840.1.113883.10.20.1.35",
    "AdvanceDirectiveReference": "2.16.840.1.113883.10.20.1.36",
    "AdvanceDirectiveStatusObservation": "2.16.840.1.113883.10.20.1.37",
    "AgeObservation": "2.16.840.1.113883.10.20.1.38",
    "AlertStatusObservation": "2.16.840.1.113883.10.20.1.39",
    "Comment": "2.16.840.1.113883.10.20.1.40",
    "EpisodeObservation": "2.16.840.1.113883.10.20.1.41",
    "FamilyHistoryCauseOfDeathObservation": "2.16.840.1.113883.10.20.1.42",
    "FulfillmentInstruction": "2.16.840.1.113883.10.20.1.43",
    "LocationParticipation": "2.16.840.1.113883.10.20.1.45",
    "MedicationSeriesNumberObservation": "2.16.840.1.113883.10.20.1.46",
    "MedicationStatusObservation": "2.16.840.1.113883.10.20.1.47",
    "PatientAwareness": "2.16.840.1.113883.10.20.1.48",
    "PatientInstruction": "2.16.840.1.113883.10.20.1.49",
    "ProblemHealthstatusObservation": "2.16.840.1.113883.10.20.1.51",
    "ProblemStatusObservation": "2.16.840.1.113883.10.20.1.50",
    "Product": "2.16.840.1.113883.10.20.1.53",
    "ProductInstance": "2.16.840.1.113883.10.20.1.52",
    "ReactionObservation": "2.16.840.1.113883.10.20.1.54",
    "SeverityObservation": "2.16.840.1.113883.10.20.1.55",
    "SocialHistoryStatusObservation": "2.16.840.1.113883.10.20.1.56",
    "StatusObservation": "2.16.840.1.113883.10.20.1.57",
    "StatusOfFunctionalStatusObservation": "2.16.840.1.113883.10.20.1.44",
    "VerificationOfAnAdvanceDirectiveObservation": "2.16.840.1.113883.10.20.1.58"
};

module.exports.clinicalstatements = clinicalstatements;
module.exports.clinicalstatements_r1 = clinicalstatements_r1;

},{}],28:[function(require,module,exports){
var codeSystems = {
    "LOINC": ["2.16.840.1.113883.6.1", "8716-3"],
    "SNOMED CT": ["2.16.840.1.113883.6.96", "46680005"],
    "RXNORM": ["2.16.840.1.113883.6.88"],
    "ActCode": ["2.16.840.1.113883.5.4"],
    "CPT-4": ["2.16.840.1.113883.6.12"],
    "CVX": ["2.16.840.1.113883.12.292"],
    "HL7 Role": ["2.16.840.1.113883.5.111"],
    "HL7 RoleCode": ["2.16.840.1.113883.5.110"],
    "UNII": ["2.16.840.1.113883.4.9"],
    "Observation Interpretation": ["2.16.840.1.113883.1.11.78"],
    "CPT": ["2.16.840.1.113883.6.12"],
    "HealthcareServiceLocation": ["2.16.840.1.113883.6.259"],
    "HL7 Result Interpretation": ["2.16.840.1.113883.5.83"],
    "Act Reason": ["2.16.840.1.113883.5.8"],
    "Medication Route FDA": ["2.16.840.1.113883.3.26.1.1"],
    "Body Site Value Set": ["2.16.840.1.113883.3.88.12.3221.8.9"],
    "MediSpan DDID": ["2.16.840.1.113883.6.253"],
    "ActPriority": ["2.16.840.1.113883.5.7"],
    "InsuranceType Code": ["2.16.840.1.113883.6.255.1336"],
    "ICD-9-CM": ["2.16.840.1.113883.6.103"]
};

var sections_entries_codes = {
    "codes": {
        "AdvanceDirectivesSectionEntriesOptional": {
            "code": "42348-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Advance Directives"
        },
        "AdvanceDirectivesSection": {
            "code": "42348-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Advance Directives"
        },
        "AllergiesSectionEntriesOptional": {
            "code": "48765-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Allergies, adverse reactions, alerts"
        },
        "AllergiesSection": {
            "code": "48765-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Allergies, adverse reactions, alerts"
        },
        "AnesthesiaSection": {
            "code": "59774-0",
            "code_system": "",
            "code_system_name": "",
            "name": "Anesthesia"
        },
        "AssessmentAndPlanSection": {
            "code": "51847-2",
            "code_system": "",
            "code_system_name": "",
            "name": "Assessment and Plan"
        },
        "AssessmentSection": {
            "code": "51848-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Assessments"
        },
        "ChiefComplaintAndReasonForVisitSection": {
            "code": "46239-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Chief Complaint and Reason for Visit"
        },
        "ChiefComplaintSection": {
            "code": "10154-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Chief Complaint"
        },
        "undefined": "",
        "ComplicationsSection": {
            "code": "55109-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Complications"
        },
        "DICOMObjectCatalogSection": {
            "code": "121181",
            "code_system": "1.2.840.10008.2.16.4",
            "code_system_name": "DCM",
            "name": "Dicom Object Catalog"
        },
        "DischargeDietSection": {
            "code": "42344-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Discharge Diet"
        },
        "EncountersSectionEntriesOptional": {
            "code": "46240-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Encounters"
        },
        "EncountersSection": {
            "code": "46240-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Encounters"
        },
        "FamilyHistorySection": {
            "code": "10157-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Family History"
        },
        "FindingsSection": "",
        "FunctionalStatusSection": {
            "code": "47420-5",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Functional Status"
        },
        "GeneralStatusSection": {
            "code": "10210-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "General Status"
        },
        "HistoryOfPastIllnessSection": {
            "code": "11348-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History of Past Illness"
        },
        "HistoryOfPresentIllnessSection": {
            "code": "10164-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History Of Present Illness Section"
        },
        "HospitalAdmissionDiagnosisSection": {
            "code": "46241-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Admission Diagnosis"
        },
        "HospitalAdmissionMedicationsSectionEntriesOptional": {
            "code": "42346-7",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Medications on Admission"
        },
        "HospitalConsultationsSection": {
            "code": "18841-7",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Consultations Section"
        },
        "HospitalCourseSection": {
            "code": "8648-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Course"
        },
        "HospitalDischargeDiagnosisSection": {
            "code": "11535-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Discharge Diagnosis"
        },
        "HospitalDischargeInstructionsSection": {
            "code": "8653-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Discharge Instructions"
        },
        "HospitalDischargeMedicationsSectionEntriesOptional": {
            "code": "10183-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Discharge Medications"
        },
        "HospitalDischargePhysicalSection": {
            "code": "10184-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Discharge Physical"
        },
        "HospitalDischargeStudiesSummarySection": {
            "code": "11493-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital Discharge Studies Summary"
        },
        "ImmunizationsSectionEntriesOptional": {
            "code": "11369-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Immunizations"
        },
        "ImmunizationsSection": {
            "code": "11369-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Immunizations"
        },
        "InstructionsSection": {
            "code": "69730-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Instructions"
        },
        "InterventionsSection": {
            "code": "62387-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Interventions Provided"
        },
        "MedicalHistorySection": {
            "code": "11329-0",
            "code_system": "",
            "code_system_name": "",
            "name": "Medical"
        },
        "MedicalEquipmentSection": {
            "code": "46264-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Medical Equipment"
        },
        "MedicationsAdministeredSection": {
            "code": "29549-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Medications Administered"
        },
        "MedicationsSectionEntriesOptional": {
            "code": "10160-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History of medication use"
        },
        "MedicationsSection": {
            "code": "10160-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History of medication use"
        },
        "ObjectiveSection": {
            "code": "61149-1",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Objective"
        },
        "OperativeNoteFluidSection": {
            "code": "10216-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Operative Note Fluids"
        },
        "OperativeNoteSurgicalProcedureSection": {
            "code": "10223-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Operative Note Surgical Procedure"
        },
        "PayersSection": {
            "code": "48768-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Payers"
        },
        "PhysicalExamSection": {
            "code": "29545-1",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Physical Findings"
        },
        "PlanOfCareSection": {
            "code": "18776-5",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Plan of Care"
        },
        "PlannedProcedureSection": {
            "code": "59772-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Planned Procedure"
        },
        "PostoperativeDiagnosisSection": {
            "code": "10218-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Postoperative Diagnosis"
        },
        "PostprocedureDiagnosisSection": {
            "code": "59769-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Postprocedure Diagnosis"
        },
        "PreoperativeDiagnosisSection": {
            "code": "10219-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Preoperative Diagnosis"
        },
        "ProblemSectionEntriesOptional": {
            "code": "11450-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Problem List"
        },
        "ProblemSection": {
            "code": "11450-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Problem List"
        },
        "ProcedureDescriptionSection": {
            "code": "29554-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Description"
        },
        "ProcedureDispositionSection": {
            "code": "59775-7",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Disposition"
        },
        "ProcedureEstimatedBloodLossSection": {
            "code": "59770-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Estimated Blood Loss"
        },
        "ProcedureFindingsSection": {
            "code": "59776-5",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Findings"
        },
        "ProcedureImplantsSection": {
            "code": "59771-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Implants"
        },
        "ProcedureIndicationsSection": {
            "code": "59768-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Indications"
        },
        "ProcedureSpecimensTakenSection": {
            "code": "59773-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Procedure Specimens Taken"
        },
        "ProceduresSectionEntriesOptional": {
            "code": "47519-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History of Procedures"
        },
        "ProceduresSection": {
            "code": "47519-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "History of Procedures"
        },
        "ReasonForReferralSection": {
            "code": "42349-1",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Reason for Referral"
        },
        "ReasonForVisitSection": {
            "code": "29299-5",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Reason for Visit"
        },
        "ResultsSectionEntriesOptional": {
            "code": "30954-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Relevant diagnostic tests and/or laboratory data"
        },
        "ResultsSection": {
            "code": "30954-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Relevant diagnostic tests and/or laboratory data"
        },
        "ReviewOfSystemsSection": {
            "code": "10187-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Review of Systems"
        },
        "SocialHistorySection": {
            "code": "29762-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Social History"
        },
        "SubjectiveSection": {
            "code": "61150-9",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Subjective"
        },
        "SurgicalDrainsSection": {
            "code": "11537-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Surgical Drains"
        },
        "VitalSignsSectionEntriesOptional": {
            "code": "8716-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Vital Signs"
        },
        "VitalSignsSection": {
            "code": "8716-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Vital Signs"
        },
        "AdmissionMedication": {
            "code": "42346-7",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Medications on Admission"
        },
        "AdvanceDirectiveObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "AgeObservation": {
            "code": "445518008",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "Age At Onset"
        },
        "AllergyObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "AllergyProblemAct": {
            "code": "48765-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Allergies, adverse reactions, alerts"
        },
        "AllergyStatusObservation": {
            "code": "33999-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Status"
        },
        "AssessmentScaleObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "AssessmentScaleSupportingObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "AuthorizationActivity": "",
        "BoundaryObservation": {
            "code": "113036",
            "code_system": "1.2.840.10008.2.16.4",
            "code_system_name": "DCM",
            "name": "Frames for Display"
        },
        "CaregiverCharacteristics": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "CodeObservations": "",
        "CognitiveStatusProblemObservation": {
            "code": "373930000",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "Cognitive function finding"
        },
        "CognitiveStatusResultObservation": {
            "code": "373930000",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "Cognitive function finding"
        },
        "CognitiveStatusResultOrganizer": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "CommentActivity": {
            "code": "48767-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Annotation Comment"
        },
        "CoverageActivity": {
            "code": "48768-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Payment sources"
        },
        "DeceasedObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "DischargeMedication": {
            "code": "10183-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Discharge medication"
        },
        "EncounterActivities": "",
        "EncounterDiagnosis": {
            "code": "29308-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Diagnosis"
        },
        "EstimatedDateOfDelivery": {
            "code": "11778-8",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Estimated date of delivery"
        },
        "FamilyHistoryDeathObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "FamilyHistoryObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "FamilyHistoryOrganizer": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "FunctionalStatusProblemObservation": {
            "code": "248536006",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "finding of functional performance and activity"
        },
        "FunctionalStatusResultObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "FunctionalStatusResultOrganizer": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "HealthStatusObservation": {
            "code": "11323-3",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Health status"
        },
        "HighestPressureUlcerStage": {
            "code": "420905001",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "Highest Pressure Ulcer Stage"
        },
        "HospitalAdmissionDiagnosis": {
            "code": "46241-6",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Admission diagnosis"
        },
        "HospitalDischargeDiagnosis": {
            "code": "11535-2",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Hospital discharge diagnosis"
        },
        "ImmunizationActivity": "",
        "ImmunizationRefusalReason": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "Indication": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "Instructions": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "MedicationActivity": "",
        "MedicationDispense": "",
        "MedicationSupplyOrder": "",
        "MedicationUseNoneKnown": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "NonMedicinalSupplyActivity": "",
        "NumberOfPressureUlcersObservation": {
            "code": "2264892003",
            "code_system": "",
            "code_system_name": "",
            "name": "number of pressure ulcers"
        },
        "PlanOfCareActivityAct": "",
        "PlanOfCareActivityEncounter": "",
        "PlanOfCareActivityObservation": "",
        "PlanOfCareActivityProcedure": "",
        "PlanOfCareActivitySubstanceAdministration": "",
        "PlanOfCareActivitySupply": "",
        "PolicyActivity": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "PostprocedureDiagnosis": {
            "code": "59769-0",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Postprocedure diagnosis"
        },
        "PregnancyObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "PreoperativeDiagnosis": {
            "code": "10219-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Preoperative Diagnosis"
        },
        "PressureUlcerObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "ProblemConcernAct": {
            "code": "CONC",
            "code_system": "2.16.840.1.113883.5.6",
            "code_system_name": "HL7ActClass",
            "name": "Concern"
        },
        "ProblemObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "ProblemStatus": {
            "code": "33999-4",
            "code_system": "2.16.840.1.113883.6.1",
            "code_system_name": "LOINC",
            "name": "Status"
        },
        "ProcedureActivityAct": "",
        "ProcedureActivityObservation": "",
        "ProcedureActivityProcedure": "",
        "ProcedureContext": "",
        "PurposeofReferenceObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "QuantityMeasurementObservation": "",
        "ReactionObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "ReferencedFramesObservation": {
            "code": "121190",
            "code_system": "1.2.840.10008.2.16.4",
            "code_system_name": "DCM",
            "name": "Referenced Frames"
        },
        "ResultObservation": "",
        "ResultOrganizer": "",
        "SeriesAct": {
            "code": "113015",
            "code_system": "1.2.840.10008.2.16.4",
            "code_system_name": "DCM",
            "name": "Series Act"
        },
        "SeverityObservation": {
            "code": "SEV",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Severity Observation"
        },
        "SmokingStatusObservation": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "SocialHistoryObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "SOPInstanceObservation": "",
        "StudyAct": {
            "code": "113014",
            "code_system": "1.2.840.10008.2.16.4",
            "code_system_name": "DCM",
            "name": "Study Act"
        },
        "TextObservation": "",
        "TobaccoUse": {
            "code": "ASSERTION",
            "code_system": "2.16.840.1.113883.5.4",
            "code_system_name": "ActCode",
            "name": "Assertion"
        },
        "VitalSignObservation": {
            "code": "completed",
            "code_system": "2.16.840.1.113883.5.14",
            "code_system_name": "ActStatus",
            "name": "Completed"
        },
        "VitalSignsOrganizer": {
            "code": "46680005",
            "code_system": "2.16.840.1.113883.6.96",
            "code_system_name": "SNOMED-CT",
            "name": "Vital signs"
        }
    }
};
module.exports.codeSystems = codeSystems;
module.exports.sections_entries_codes = sections_entries_codes;

},{}],29:[function(require,module,exports){
var templates = require("./templates.js");
var sections = require("./sections.js");
var statements = require("./clinicalstatements.js");

var templatesconstraints = require("./templates-constraints.js");
var sectionsconstraints = require("./sections-constraints.js");
var codeSystems = require("./code-systems.js");

//General Header Constraints
var CCDA = {
    "document": {
        "name": "CCDA",
        "templateId": "2.16.840.1.113883.10.20.22.1.1"
    },
    "templates": templates,
    "sections": sections.sections,
    "sections_r1": sections.sections_r1,
    "statements": statements.clinicalstatements,
    "statements_r1": statements.clinicalstatements_r1,
    "constraints": {
        "sections": sectionsconstraints,
        "templates": templatesconstraints
    },
    "codeSystems": codeSystems.codeSystems,
    "sections_entries_codes": codeSystems.sections_entries_codes

    /*
		,
    //DOCUMENT-LEVEL TEMPLATES
    "templates":[
		{
			"name":"Consultation Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.4"
		},
		{
			"name":"Continuity Of Care Document",
			"templateId":"2.16.840.1.113883.10.20.22.1.2"
		},
		{
			"name":"Diagnostic Imaging Report",
			"templateId":"2.16.840.1.113883.10.20.22.1.5"
		},
		{
			"name":"Discharge Summary",
			"templateId":"2.16.840.1.113883.10.20.22.1.8"
		},
		{
			"name":"History And Physical Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.3"
		},
		{
			"name":"Operative Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.7"
		},
		{
			"name":"Procedure Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.6"
		},
		{
			"name":"Progress Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.9"
		},
		{
			"name":"Unstructured Document",
			"templateId":"2.16.840.1.113883.10.20.21.1.10"
		},
    ],
    //Sections
    "sections":[
		{"name": "Allergies",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.6', '2.16.840.1.113883.10.20.22.2.6.1']
		},
		{"name": "Encounters",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.22', '2.16.840.1.113883.10.20.22.2.22.1']
		},
		{"name": "Immunizations",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.2", "2.16.840.1.113883.10.20.22.2.2.1"]
		},
		{"name": "Medications",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.1", "2.16.840.1.113883.10.20.22.2.1.1"]
		},
		{"name": "Problems",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.5.1"]
		},
		{"name": "Procedures",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.7', '2.16.840.1.113883.10.20.22.2.7.1']
		},
		{"name": "Results",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.3', '2.16.840.1.113883.10.20.22.2.3.1']
		},
		{"name": "Vital Signs",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.4","2.16.840.1.113883.10.20.22.2.4.1"]
		},
		{"name": "Social History",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.17"]
		}		
    ]
    */
};

//Good source http://cdatools.org/SectionMatrix.html
//and http://cdatools.org/ClinicalStatementMatrix.html

module.exports = exports = CCDA;

},{"./clinicalstatements.js":27,"./code-systems.js":28,"./sections-constraints.js":30,"./sections.js":31,"./templates-constraints.js":32,"./templates.js":33}],30:[function(require,module,exports){
var sectionsconstraints = {
    "VitalSignsSection": {
        "full": {
            "VitalSignsOrganizer": {
                "id": [
                    "7276",
                    "7277"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "VitalSignsOrganizer": [
                "7276",
                "7277"
            ]
        }
    },
    "DICOMObjectCatalogSection": {
        "full": {
            "StudyAct": {
                "id": [
                    "8530",
                    "15458"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "StudyAct": [
                "8530",
                "15458"
            ]
        }
    },
    "PayersSection": {
        "full": {
            "CoverageActivity": {
                "id": [
                    "7959",
                    "8905"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "CoverageActivity": [
                "7959",
                "8905"
            ]
        }
    },
    "HospitalDischargeDiagnosisSection": {
        "full": {
            "HospitalDischargeDiagnosis": {
                "id": [
                    "7984"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "HospitalDischargeDiagnosis": [
                "7984"
            ]
        }
    },
    "SocialHistorySection": {
        "may": {
            "TobaccoUse": [
                "16816",
                "16817"
            ],
            "PregnancyObservation": [
                "9133",
                "9132"
            ],
            "SocialHistoryObservation": [
                "7954",
                "7953"
            ]
        },
        "full": {
            "SmokingStatusObservation": {
                "id": [
                    "14824",
                    "14823"
                ],
                "constraint": "should"
            },
            "TobaccoUse": {
                "id": [
                    "16816",
                    "16817"
                ],
                "constraint": "may"
            },
            "PregnancyObservation": {
                "id": [
                    "9133",
                    "9132"
                ],
                "constraint": "may"
            },
            "SocialHistoryObservation": {
                "id": [
                    "7954",
                    "7953"
                ],
                "constraint": "may"
            }
        },
        "should": {
            "SmokingStatusObservation": [
                "14824",
                "14823"
            ]
        }
    },
    "AssessmentAndPlanSection": {
        "may": {
            "PlanOfCareActivityAct": [
                "8798"
            ]
        },
        "full": {
            "PlanOfCareActivityAct": {
                "id": [
                    "8798"
                ],
                "constraint": "may"
            }
        }
    },
    "ResultsSection": {
        "full": {
            "ResultOrganizer": {
                "id": [
                    "7113",
                    "7112"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "ResultOrganizer": [
                "7113",
                "7112"
            ]
        }
    },
    "HospitalAdmissionMedicationsSectionEntriesOptional": {
        "full": {
            "AdmissionMedication": {
                "id": [
                    "10110",
                    "10102"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "AdmissionMedication": [
                "10110",
                "10102"
            ]
        }
    },
    "AllergiesSection": {
        "full": {
            "AllergyProblemAct": {
                "id": [
                    "7531",
                    "7532"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "AllergyProblemAct": [
                "7531",
                "7532"
            ]
        }
    },
    "ComplicationsSection": {
        "may": {
            "ProblemObservation": [
                "8796",
                "8795"
            ]
        },
        "full": {
            "ProblemObservation": {
                "id": [
                    "8796",
                    "8795"
                ],
                "constraint": "may"
            }
        }
    },
    "AdvanceDirectivesSection": {
        "full": {
            "AdvanceDirectiveObservation": {
                "id": [
                    "8801",
                    "8647"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "AdvanceDirectiveObservation": [
                "8801",
                "8647"
            ]
        }
    },
    "MedicationsSectionEntriesOptional": {
        "full": {
            "MedicationActivity": {
                "id": [
                    "7795",
                    "7573"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "MedicationActivity": [
                "7795",
                "7573"
            ]
        }
    },
    "MedicationsAdministeredSection": {
        "may": {
            "MedicationActivity": [
                "8156"
            ]
        },
        "full": {
            "MedicationActivity": {
                "id": [
                    "8156"
                ],
                "constraint": "may"
            }
        }
    },
    "MedicalEquipmentSection": {
        "full": {
            "NonMedicinalSupplyActivity": {
                "id": [
                    "7948.",
                    "8755"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "NonMedicinalSupplyActivity": [
                "7948.",
                "8755"
            ]
        }
    },
    "MedicationsSection": {
        "full": {
            "MedicationActivity": {
                "id": [
                    "7573",
                    "7572"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "MedicationActivity": [
                "7573",
                "7572"
            ]
        }
    },
    "ImmunizationsSection": {
        "full": {
            "ImmunizationActivity": {
                "id": [
                    "9019",
                    "9020"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "ImmunizationActivity": [
                "9019",
                "9020"
            ]
        }
    },
    "AdvanceDirectivesSectionEntriesOptional": {
        "may": {
            "AdvanceDirectiveObservation": [
                "8800",
                "7957"
            ]
        },
        "full": {
            "AdvanceDirectiveObservation": {
                "id": [
                    "8800",
                    "7957"
                ],
                "constraint": "may"
            }
        }
    },
    "ResultsSectionEntriesOptional": {
        "full": {
            "ResultOrganizer": {
                "id": [
                    "7119",
                    "7120"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "ResultOrganizer": [
                "7119",
                "7120"
            ]
        }
    },
    "AnesthesiaSection": {
        "may": {
            "ProcedureActivityProcedure": [
                "8092"
            ],
            "MedicationActivity": [
                "8094"
            ]
        },
        "full": {
            "ProcedureActivityProcedure": {
                "id": [
                    "8092"
                ],
                "constraint": "may"
            },
            "MedicationActivity": {
                "id": [
                    "8094"
                ],
                "constraint": "may"
            }
        }
    },
    "VitalSignsSectionEntriesOptional": {
        "full": {
            "VitalSignsOrganizer": {
                "id": [
                    "7271",
                    "7272"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "VitalSignsOrganizer": [
                "7271",
                "7272"
            ]
        }
    },
    "ImmunizationsSectionEntriesOptional": {
        "full": {
            "ImmunizationActivity": {
                "id": [
                    "7969",
                    "7970"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "ImmunizationActivity": [
                "7969",
                "7970"
            ]
        }
    },
    "FunctionalStatusSection": {
        "may": {
            "PressureUlcerObservation": [
                "16778",
                "16777"
            ],
            "FunctionalStatusProblemObservation": [
                "14422",
                "14423"
            ],
            "CognitiveStatusResultObservation": [
                "14421",
                "14420"
            ],
            "NumberOfPressureUlcersObservation": [
                "16779",
                "16780"
            ],
            "HighestPressureUlcerStage": [
                "16781",
                "16782"
            ],
            "AssessmentScaleObservation": [
                "14581",
                "14580"
            ],
            "FunctionalStatusResultObservation": [
                "14418",
                "14419"
            ],
            "CognitiveStatusProblemObservation": [
                "14425",
                "14424"
            ],
            "FunctionalStatusResultOrganizer": [
                "14414",
                "14415"
            ],
            "CaregiverCharacteristics": [
                "14426",
                "14427"
            ],
            "CognitiveStatusResultOrganizer": [
                "14416",
                "14417"
            ],
            "NonMedicinalSupplyActivity": [
                "14583",
                "14582"
            ]
        },
        "full": {
            "PressureUlcerObservation": {
                "id": [
                    "16778",
                    "16777"
                ],
                "constraint": "may"
            },
            "FunctionalStatusProblemObservation": {
                "id": [
                    "14422",
                    "14423"
                ],
                "constraint": "may"
            },
            "CognitiveStatusResultObservation": {
                "id": [
                    "14421",
                    "14420"
                ],
                "constraint": "may"
            },
            "NumberOfPressureUlcersObservation": {
                "id": [
                    "16779",
                    "16780"
                ],
                "constraint": "may"
            },
            "HighestPressureUlcerStage": {
                "id": [
                    "16781",
                    "16782"
                ],
                "constraint": "may"
            },
            "AssessmentScaleObservation": {
                "id": [
                    "14581",
                    "14580"
                ],
                "constraint": "may"
            },
            "FunctionalStatusResultObservation": {
                "id": [
                    "14418",
                    "14419"
                ],
                "constraint": "may"
            },
            "CognitiveStatusProblemObservation": {
                "id": [
                    "14425",
                    "14424"
                ],
                "constraint": "may"
            },
            "FunctionalStatusResultOrganizer": {
                "id": [
                    "14414",
                    "14415"
                ],
                "constraint": "may"
            },
            "CaregiverCharacteristics": {
                "id": [
                    "14426",
                    "14427"
                ],
                "constraint": "may"
            },
            "CognitiveStatusResultOrganizer": {
                "id": [
                    "14416",
                    "14417"
                ],
                "constraint": "may"
            },
            "NonMedicinalSupplyActivity": {
                "id": [
                    "14583",
                    "14582"
                ],
                "constraint": "may"
            }
        }
    },
    "PreoperativeDiagnosisSection": {
        "full": {
            "PreoperativeDiagnosis": {
                "id": [
                    "10097",
                    "10096"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "PreoperativeDiagnosis": [
                "10097",
                "10096"
            ]
        }
    },
    "HospitalAdmissionDiagnosisSection": {
        "full": {
            "HospitalAdmissionDiagnosis": {
                "id": [
                    "9935",
                    "9934"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "HospitalAdmissionDiagnosis": [
                "9935",
                "9934"
            ]
        }
    },
    "AllergiesSectionEntriesOptional": {
        "full": {
            "AllergyProblemAct": {
                "id": [
                    "7805",
                    "7804"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "AllergyProblemAct": [
                "7805",
                "7804"
            ]
        }
    },
    "PlannedProcedureSection": {
        "may": {
            "PlanOfCareActivityProcedure": [
                "8766",
                "8744"
            ]
        },
        "full": {
            "PlanOfCareActivityProcedure": {
                "id": [
                    "8766",
                    "8744"
                ],
                "constraint": "may"
            }
        }
    },
    "ProblemSection": {
        "full": {
            "ProblemConcernAct": {
                "id": [
                    "9183"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "ProblemConcernAct": [
                "9183"
            ]
        }
    },
    "EncountersSectionEntriesOptional": {
        "full": {
            "EncounterActivities": {
                "id": [
                    "7951",
                    "8802"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "EncounterActivities": [
                "7951",
                "8802"
            ]
        }
    },
    "HospitalDischargeMedicationsSectionEntriesOptional": {
        "full": {
            "DischargeMedication": {
                "id": [
                    "7883"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "DischargeMedication": [
                "7883"
            ]
        }
    },
    "ProcedureFindingsSection": {
        "may": {
            "ProblemObservation": [
                "8090",
                "8091"
            ]
        },
        "full": {
            "ProblemObservation": {
                "id": [
                    "8090",
                    "8091"
                ],
                "constraint": "may"
            }
        }
    },
    "PlanOfCareSection": {
        "may": {
            "PlanOfCareActivityAct": [
                "7726.",
                "8804"
            ],
            "PlanOfCareActivityProcedure": [
                "8810",
                "8809"
            ],
            "PlanOfCareActivitySubstanceAdministration": [
                "8811",
                "8812"
            ],
            "PlanOfCareActivitySupply": [
                "14756",
                "8813"
            ],
            "PlanOfCareActivityEncounter": [
                "8806",
                "8805"
            ],
            "PlanOfCareActivityObservation": [
                "8808",
                "8807"
            ],
            "Instructions": [
                "14695",
                "16751"
            ]
        },
        "full": {
            "PlanOfCareActivityAct": {
                "id": [
                    "7726.",
                    "8804"
                ],
                "constraint": "may"
            },
            "PlanOfCareActivityProcedure": {
                "id": [
                    "8810",
                    "8809"
                ],
                "constraint": "may"
            },
            "PlanOfCareActivitySubstanceAdministration": {
                "id": [
                    "8811",
                    "8812"
                ],
                "constraint": "may"
            },
            "PlanOfCareActivitySupply": {
                "id": [
                    "14756",
                    "8813"
                ],
                "constraint": "may"
            },
            "PlanOfCareActivityEncounter": {
                "id": [
                    "8806",
                    "8805"
                ],
                "constraint": "may"
            },
            "PlanOfCareActivityObservation": {
                "id": [
                    "8808",
                    "8807"
                ],
                "constraint": "may"
            },
            "Instructions": {
                "id": [
                    "14695",
                    "16751"
                ],
                "constraint": "may"
            }
        }
    },
    "InstructionsSection": {
        "full": {
            "Instructions": {
                "id": [
                    "10116",
                    "10117"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "Instructions": [
                "10116",
                "10117"
            ]
        }
    },
    "ProceduresSection": {
        "may": {
            "ProcedureActivityProcedure": [
                "7896",
                "7895"
            ],
            "ProcedureActivityAct": [
                "8020",
                "8019"
            ],
            "ProcedureActivityObservation": [
                "8018",
                "8017"
            ]
        },
        "full": {
            "ProcedureActivityProcedure": {
                "id": [
                    "7896",
                    "7895"
                ],
                "constraint": "may"
            },
            "ProcedureActivityAct": {
                "id": [
                    "8020",
                    "8019"
                ],
                "constraint": "may"
            },
            "ProcedureActivityObservation": {
                "id": [
                    "8018",
                    "8017"
                ],
                "constraint": "may"
            }
        }
    },
    "HospitalDischargeMedicationsSection": {
        "full": {
            "DischargeMedication": {
                "id": [
                    "7827"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "DischargeMedication": [
                "7827"
            ]
        }
    },
    "PostprocedureDiagnosisSection": {
        "full": {
            "PostprocedureDiagnosis": {
                "id": [
                    "8762",
                    "8764"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "PostprocedureDiagnosis": [
                "8762",
                "8764"
            ]
        }
    },
    "HistoryOfPastIllnessSection": {
        "may": {
            "ProblemObservation": [
                "8792"
            ]
        },
        "full": {
            "ProblemObservation": {
                "id": [
                    "8792"
                ],
                "constraint": "may"
            }
        }
    },
    "ProblemSectionEntriesOptional": {
        "full": {
            "ProblemConcernAct": {
                "id": [
                    "7882"
                ],
                "constraint": "should"
            }
        },
        "should": {
            "ProblemConcernAct": [
                "7882"
            ]
        }
    },
    "FamilyHistorySection": {
        "may": {
            "FamilyHistoryOrganizer": [
                "7955"
            ]
        },
        "full": {
            "FamilyHistoryOrganizer": {
                "id": [
                    "7955"
                ],
                "constraint": "may"
            }
        }
    },
    "ProcedureIndicationsSection": {
        "may": {
            "Indication": [
                "8765",
                "8743"
            ]
        },
        "full": {
            "Indication": {
                "id": [
                    "8765",
                    "8743"
                ],
                "constraint": "may"
            }
        }
    },
    "ProceduresSectionEntriesOptional": {
        "may": {
            "ProcedureActivityProcedure": [
                "15509",
                "6274"
            ],
            "ProcedureActivityAct": [
                "8533",
                "15511"
            ],
            "ProcedureActivityObservation": [
                "6278",
                "15510"
            ]
        },
        "full": {
            "ProcedureActivityProcedure": {
                "id": [
                    "15509",
                    "6274"
                ],
                "constraint": "may"
            },
            "ProcedureActivityAct": {
                "id": [
                    "8533",
                    "15511"
                ],
                "constraint": "may"
            },
            "ProcedureActivityObservation": {
                "id": [
                    "6278",
                    "15510"
                ],
                "constraint": "may"
            }
        }
    },
    "PhysicalExamSection": {
        "may": {
            "PressureUlcerObservation": [
                "17094",
                "17095"
            ],
            "NumberOfPressureUlcersObservation": [
                "17096",
                "17097"
            ],
            "HighestPressureUlcerStage": [
                "17098",
                "17099"
            ]
        },
        "full": {
            "PressureUlcerObservation": {
                "id": [
                    "17094",
                    "17095"
                ],
                "constraint": "may"
            },
            "NumberOfPressureUlcersObservation": {
                "id": [
                    "17096",
                    "17097"
                ],
                "constraint": "may"
            },
            "HighestPressureUlcerStage": {
                "id": [
                    "17098",
                    "17099"
                ],
                "constraint": "may"
            }
        }
    },
    "EncountersSection": {
        "full": {
            "EncounterActivities": {
                "id": [
                    "8709",
                    "8803"
                ],
                "constraint": "shall"
            }
        },
        "shall": {
            "EncounterActivities": [
                "8709",
                "8803"
            ]
        }
    }
};

module.exports = exports = sectionsconstraints;

},{}],31:[function(require,module,exports){
var sections = {
    "AdvanceDirectivesSection": "2.16.840.1.113883.10.20.22.2.21.1",
    "AdvanceDirectivesSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.21",
    "AllergiesSection": "2.16.840.1.113883.10.20.22.2.6.1",
    "AllergiesSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.6",
    "AnesthesiaSection": "2.16.840.1.113883.10.20.22.2.25",
    "AssessmentAndPlanSection": "2.16.840.1.113883.10.20.22.2.9",
    "AssessmentSection": "2.16.840.1.113883.10.20.22.2.8",
    "ChiefComplaintAndReasonForVisitSection": "2.16.840.1.113883.10.20.22.2.13",
    "ChiefComplaintSection": "1.3.6.1.4.1.19376.1.5.3.1.1.13.2.1",
    "ComplicationsSection": "2.16.840.1.113883.10.20.22.2.37",
    "DICOMObjectCatalogSection": "2.16.840.1.113883.10.20.6.1.1",
    "DischargeDietSection": "1.3.6.1.4.1.19376.1.5.3.1.3.33",
    "EncountersSection": "2.16.840.1.113883.10.20.22.2.22.1",
    "EncountersSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.22",
    "FamilyHistorySection": "2.16.840.1.113883.10.20.22.2.15",
    "FindingsSection": "2.16.840.1.113883.10.20.6.1.2",
    "FunctionalStatusSection": "2.16.840.1.113883.10.20.22.2.14",
    "GeneralStatusSection": "2.16.840.1.113883.10.20.2.5",
    "HistoryOfPastIllnessSection": "2.16.840.1.113883.10.20.22.2.20",
    "HistoryOfPresentIllnessSection": "1.3.6.1.4.1.19376.1.5.3.1.3.4",
    "HospitalAdmissionDiagnosisSection": "2.16.840.1.113883.10.20.22.2.43",
    "HospitalAdmissionMedicationsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.44",
    "HospitalConsultationsSection": "2.16.840.1.113883.10.20.22.2.42",
    "HospitalCourseSection": "1.3.6.1.4.1.19376.1.5.3.1.3.5",
    "HospitalDischargeDiagnosisSection": "2.16.840.1.113883.10.20.22.2.24",
    "HospitalDischargeInstructionsSection": "2.16.840.1.113883.10.20.22.2.41",
    "HospitalDischargeMedicationsSection": "2.16.840.1.113883.10.20.22.2.11.1",
    "HospitalDischargeMedicationsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.11",
    "HospitalDischargePhysicalSection": "1.3.6.1.4.1.19376.1.5.3.1.3.26",
    "HospitalDischargeStudiesSummarySection": "2.16.840.1.113883.10.20.22.2.16",
    "ImmunizationsSection": "2.16.840.1.113883.10.20.22.2.2.1",
    "ImmunizationsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.2",
    "InstructionsSection": "2.16.840.1.113883.10.20.22.2.45",
    "InterventionsSection": "2.16.840.1.113883.10.20.21.2.3",
    "MedicalEquipmentSection": "2.16.840.1.113883.10.20.22.2.23",
    "MedicalHistorySection": "2.16.840.1.113883.10.20.22.2.39",
    "MedicationsAdministeredSection": "2.16.840.1.113883.10.20.22.2.38",
    "MedicationsSection": "2.16.840.1.113883.10.20.22.2.1.1",
    "MedicationsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.1",
    "ObjectiveSection": "2.16.840.1.113883.10.20.21.2.1",
    "OperativeNoteFluidSection": "2.16.840.1.113883.10.20.7.12",
    "OperativeNoteSurgicalProcedureSection": "2.16.840.1.113883.10.20.7.14",
    "PayersSection": "2.16.840.1.113883.10.20.22.2.18",
    "PhysicalExamSection": "2.16.840.1.113883.10.20.2.10",
    "PlannedProcedureSection": "2.16.840.1.113883.10.20.22.2.30",
    "PlanOfCareSection": "2.16.840.1.113883.10.20.22.2.10",
    "PostoperativeDiagnosisSection": "2.16.840.1.113883.10.20.22.2.35",
    "PostprocedureDiagnosisSection": "2.16.840.1.113883.10.20.22.2.36",
    "PreoperativeDiagnosisSection": "2.16.840.1.113883.10.20.22.2.34",
    "ProblemSection": "2.16.840.1.113883.10.20.22.2.5.1",
    "ProblemSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.5",
    "ProcedureDescriptionSection": "2.16.840.1.113883.10.20.22.2.27",
    "ProcedureDispositionSection": "2.16.840.1.113883.10.20.18.2.12",
    "ProcedureEstimatedBloodLossSection": "2.16.840.1.113883.10.20.18.2.9",
    "ProcedureFindingsSection": "2.16.840.1.113883.10.20.22.2.28",
    "ProcedureImplantsSection": "2.16.840.1.113883.10.20.22.2.40",
    "ProcedureIndicationsSection": "2.16.840.1.113883.10.20.22.2.29",
    "ProcedureSpecimensTakenSection": "2.16.840.1.113883.10.20.22.2.31",
    "ProceduresSection": "2.16.840.1.113883.10.20.22.2.7.1",
    "ProceduresSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.7",
    "ReasonForReferralSection": "1.3.6.1.4.1.19376.1.5.3.1.3.1",
    "ReasonForVisitSection": "2.16.840.1.113883.10.20.22.2.12",
    "ResultsSection": "2.16.840.1.113883.10.20.22.2.3.1",
    "ResultsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.3",
    "ReviewOfSystemsSection": "1.3.6.1.4.1.19376.1.5.3.1.3.18",
    "SocialHistorySection": "2.16.840.1.113883.10.20.22.2.17",
    "SubjectiveSection": "2.16.840.1.113883.10.20.21.2.2",
    "SurgicalDrainsSection": "2.16.840.1.113883.10.20.7.13",
    "VitalSignsSection": "2.16.840.1.113883.10.20.22.2.4.1",
    "VitalSignsSectionEntriesOptional": "2.16.840.1.113883.10.20.22.2.4"
};

var sections_r1 = {
    "AdvanceDirectivesSection": "2.16.840.1.113883.10.20.1.1",
    "AlertsSection": "2.16.840.1.113883.10.20.1.2",
    "EncountersSection": "2.16.840.1.113883.10.20.1.3",
    "FamilyHistorySection": "2.16.840.1.113883.10.20.1.4",
    "FunctionalStatusSection": "2.16.840.1.113883.10.20.1.5",
    "ImmunizationsSection": "2.16.840.1.113883.10.20.1.6",
    "MedicalEquipmentSection": "2.16.840.1.113883.10.20.1.7",
    "MedicationsSection": "2.16.840.1.113883.10.20.1.8",
    "PayersSection": "2.16.840.1.113883.10.20.1.9",
    "PlanOfCareSection": "2.16.840.1.113883.10.20.1.10",
    "ProblemSection": "2.16.840.1.113883.10.20.1.11",
    "ProceduresSection": "2.16.840.1.113883.10.20.1.12",
    "PurposeSection": "2.16.840.1.113883.10.20.1.13",
    "ResultsSection": "2.16.840.1.113883.10.20.1.14",
    "SocialHistorySection": "2.16.840.1.113883.10.20.1.15",
    "VitalSignsSection": "2.16.840.1.113883.10.20.1.16"
};

module.exports.sections = sections;
module.exports.sections_r1 = sections_r1;

},{}],32:[function(require,module,exports){
var templatesconstraints = {
    "ContinuityOfCareDocument": {
        "may": {
            "AdvanceDirectivesSection": "9455",
            "PayersSection": "9468",
            "SocialHistorySection": "9472",
            "ImmunizationsSectionEntriesOptional": "9463",
            "MedicalEquipmentSection": "9466",
            "FamilyHistorySection": "9459",
            "PlanOfCareSection": "9470",
            "FunctionalStatusSection": "9461",
            "VitalSignsSectionEntriesOptional": "9474",
            "EncountersSection": "9457"
        },
        "full": {
            "AdvanceDirectivesSection": {
                "id": "9455",
                "constraint": "may"
            },
            "PayersSection": {
                "id": "9468",
                "constraint": "may"
            },
            "MedicationsSection": {
                "id": "9447",
                "constraint": "shall"
            },
            "ProblemSection": {
                "id": "9449",
                "constraint": "shall"
            },
            "ImmunizationsSectionEntriesOptional": {
                "id": "9463",
                "constraint": "may"
            },
            "SocialHistorySection": {
                "id": "9472",
                "constraint": "may"
            },
            "MedicalEquipmentSection": {
                "id": "9466",
                "constraint": "may"
            },
            "FamilyHistorySection": {
                "id": "9459",
                "constraint": "may"
            },
            "ProceduresSection": {
                "id": "9451",
                "constraint": "shall"
            },
            "PlanOfCareSection": {
                "id": "9470",
                "constraint": "may"
            },
            "FunctionalStatusSection": {
                "id": "9461",
                "constraint": "may"
            },
            "VitalSignsSectionEntriesOptional": {
                "id": "9474",
                "constraint": "may"
            },
            "AllergiesSection": {
                "id": "9445",
                "constraint": "shall"
            },
            "EncountersSection": {
                "id": "9457",
                "constraint": "may"
            },
            "ResultsSection": {
                "id": "9453",
                "constraint": "shall"
            }
        },
        "shall": {
            "ProblemSection": "9449",
            "ResultsSection": "9453",
            "AllergiesSection": "9445",
            "ProceduresSection": "9451",
            "MedicationsSection": "9447"
        }
    },
    "HistoryAndPhysicalNote": {
        "may": {
            "ChiefComplaintSection": "9611",
            "ImmunizationsSectionEntriesOptional": "9637",
            "ProblemSectionEntriesOptional": "9639",
            "ReasonForVisitSection": "9627",
            "ProceduresSectionEntriesOptional": "9641",
            "AssessmentAndPlanSection": "9987",
            "ChiefComplaintAndReasonForVisitSection": "9613",
            "PlanOfCareSection": "9607",
            "InstructionsSection": "16807",
            "AssessmentSection": "9605"
        },
        "should": {
            "HistoryOfPresentIllnessSection": "9621"
        },
        "full": {
            "ChiefComplaintSection": {
                "id": "9611",
                "constraint": "may"
            },
            "ProblemSectionEntriesOptional": {
                "id": "9639",
                "constraint": "may"
            },
            "AllergiesSectionEntriesOptional": {
                "id": "9602",
                "constraint": "shall"
            },
            "FamilyHistorySection": {
                "id": "9615",
                "constraint": "shall"
            },
            "ResultsSectionEntriesOptional": {
                "id": "9629",
                "constraint": "shall"
            },
            "HistoryOfPastIllnessSection": {
                "id": "9619",
                "constraint": "shall"
            },
            "SocialHistorySection": {
                "id": "9633",
                "constraint": "shall"
            },
            "PlanOfCareSection": {
                "id": "9607",
                "constraint": "may"
            },
            "MedicationsSectionEntriesOptional": {
                "id": "9623",
                "constraint": "shall"
            },
            "ReasonForVisitSection": {
                "id": "9627",
                "constraint": "may"
            },
            "ProceduresSectionEntriesOptional": {
                "id": "9641",
                "constraint": "may"
            },
            "AssessmentAndPlanSection": {
                "id": "9987",
                "constraint": "may"
            },
            "GeneralStatusSection": {
                "id": "9617",
                "constraint": "shall"
            },
            "ChiefComplaintAndReasonForVisitSection": {
                "id": "9613",
                "constraint": "may"
            },
            "ImmunizationsSectionEntriesOptional": {
                "id": "9637",
                "constraint": "may"
            },
            "ReviewOfSystemsSection": {
                "id": "9631",
                "constraint": "shall"
            },
            "InstructionsSection": {
                "id": "16807",
                "constraint": "may"
            },
            "PhysicalExamSection": {
                "id": "9625",
                "constraint": "shall"
            },
            "VitalSignsSectionEntriesOptional": {
                "id": "9635",
                "constraint": "shall"
            },
            "AssessmentSection": {
                "id": "9605",
                "constraint": "may"
            },
            "HistoryOfPresentIllnessSection": {
                "id": "9621",
                "constraint": "should"
            }
        },
        "shall": {
            "MedicationsSectionEntriesOptional": "9623",
            "AllergiesSectionEntriesOptional": "9602",
            "ResultsSectionEntriesOptional": "9629",
            "HistoryOfPastIllnessSection": "9619",
            "VitalSignsSectionEntriesOptional": "9635",
            "FamilyHistorySection": "9615",
            "GeneralStatusSection": "9617",
            "ReviewOfSystemsSection": "9631",
            "PhysicalExamSection": "9625",
            "SocialHistorySection": "9633"
        }
    },
    "DischargeSummary": {
        "may": {
            "VitalSignsSectionEntriesOptional": "9584",
            "ChiefComplaintSection": "9554",
            "HospitalDischargePhysicalSection": "9568",
            "HospitalConsultationsSection": "9924",
            "SocialHistorySection": "9582",
            "HistoryOfPastIllnessSection": "9564",
            "HospitalDischargeInstructionsSection": "9926",
            "ProblemSectionEntriesOptional": "9574",
            "HospitalDischargeStudiesSummarySection": "9570",
            "ProceduresSectionEntriesOptional": "9576",
            "FamilyHistorySection": "9560",
            "ReasonForVisitSection": "9578",
            "ChiefComplaintAndReasonForVisitSection": "9556",
            "ImmunizationsSectionEntriesOptional": "9572",
            "FunctionalStatusSection": "9562",
            "HospitalAdmissionMedicationsSectionEntriesOptional": "10111",
            "HistoryOfPresentIllnessSection": "9566",
            "ReviewOfSystemsSection": "9580",
            "DischargeDietSection": "9558"
        },
        "full": {
            "HospitalDischargeDiagnosisSection": {
                "id": "9546",
                "constraint": "shall"
            },
            "SocialHistorySection": {
                "id": "9582",
                "constraint": "may"
            },
            "HospitalDischargeStudiesSummarySection": {
                "id": "9570",
                "constraint": "may"
            },
            "ChiefComplaintAndReasonForVisitSection": {
                "id": "9556",
                "constraint": "may"
            },
            "HospitalAdmissionMedicationsSectionEntriesOptional": {
                "id": "10111",
                "constraint": "may"
            },
            "HistoryOfPresentIllnessSection": {
                "id": "9566",
                "constraint": "may"
            },
            "HospitalConsultationsSection": {
                "id": "9924",
                "constraint": "may"
            },
            "FunctionalStatusSection": {
                "id": "9562",
                "constraint": "may"
            },
            "DischargeDietSection": {
                "id": "9558",
                "constraint": "may"
            },
            "HospitalAdmissionDiagnosisSection": {
                "id": "9928",
                "constraint": "shall"
            },
            "AllergiesSectionEntriesOptional": {
                "id": "9542",
                "constraint": "shall"
            },
            "HospitalDischargePhysicalSection": {
                "id": "9568",
                "constraint": "may"
            },
            "ImmunizationsSectionEntriesOptional": {
                "id": "9572",
                "constraint": "may"
            },
            "ReasonForVisitSection": {
                "id": "9578",
                "constraint": "may"
            },
            "HospitalDischargeMedicationsSectionEntriesOptional": {
                "id": "9548",
                "constraint": "shall"
            },
            "PlanOfCareSection": {
                "id": "9550",
                "constraint": "shall"
            },
            "VitalSignsSectionEntriesOptional": {
                "id": "9584",
                "constraint": "may"
            },
            "HospitalCourseSection": {
                "id": "9544",
                "constraint": "shall"
            },
            "ChiefComplaintSection": {
                "id": "9554",
                "constraint": "may"
            },
            "ProceduresSectionEntriesOptional": {
                "id": "9576",
                "constraint": "may"
            },
            "HospitalDischargeInstructionsSection": {
                "id": "9926",
                "constraint": "may"
            },
            "ProblemSectionEntriesOptional": {
                "id": "9574",
                "constraint": "may"
            },
            "FamilyHistorySection": {
                "id": "9560",
                "constraint": "may"
            },
            "HistoryOfPastIllnessSection": {
                "id": "9564",
                "constraint": "may"
            },
            "ReviewOfSystemsSection": {
                "id": "9580",
                "constraint": "may"
            }
        },
        "shall": {
            "HospitalAdmissionDiagnosisSection": "9928",
            "AllergiesSectionEntriesOptional": "9542",
            "HospitalDischargeDiagnosisSection": "9546",
            "HospitalDischargeMedicationsSectionEntriesOptional": "9548",
            "PlanOfCareSection": "9550",
            "HospitalCourseSection": "9544"
        }
    },
    "OperativeNote": {
        "may": {
            "PlannedProcedureSection": "9906",
            "OperativeNoteFluidSection": "9900",
            "OperativeNoteSurgicalProcedureSection": "9902",
            "SurgicalDrainsSection": "9912",
            "ProcedureDispositionSection": "9908",
            "ProcedureImplantsSection": "9898",
            "ProcedureIndicationsSection": "9910",
            "PlanOfCareSection": "9904"
        },
        "full": {
            "ProcedureSpecimensTakenSection": {
                "id": "9894",
                "constraint": "shall"
            },
            "PlannedProcedureSection": {
                "id": "9906",
                "constraint": "may"
            },
            "OperativeNoteFluidSection": {
                "id": "9900",
                "constraint": "may"
            },
            "OperativeNoteSurgicalProcedureSection": {
                "id": "9902",
                "constraint": "may"
            },
            "ProcedureIndicationsSection": {
                "id": "9910",
                "constraint": "may"
            },
            "SurgicalDrainsSection": {
                "id": "9912",
                "constraint": "may"
            },
            "PostoperativeDiagnosisSection": {
                "id": "9913",
                "constraint": "shall"
            },
            "ProcedureDispositionSection": {
                "id": "9908",
                "constraint": "may"
            },
            "ProcedureEstimatedBloodLossSection": {
                "id": "9890",
                "constraint": "shall"
            },
            "ProcedureImplantsSection": {
                "id": "9898",
                "constraint": "may"
            },
            "ProcedureDescriptionSection": {
                "id": "9896",
                "constraint": "shall"
            },
            "AnesthesiaSection": {
                "id": "9883",
                "constraint": "shall"
            },
            "ProcedureFindingsSection": {
                "id": "9892",
                "constraint": "shall"
            },
            "PlanOfCareSection": {
                "id": "9904",
                "constraint": "may"
            },
            "PreoperativeDiagnosisSection": {
                "id": "9888",
                "constraint": "shall"
            },
            "ComplicationsSection": {
                "id": "9885",
                "constraint": "shall"
            }
        },
        "shall": {
            "ProcedureSpecimensTakenSection": "9894",
            "ProcedureEstimatedBloodLossSection": "9890",
            "PostoperativeDiagnosisSection": "9913",
            "ProcedureDescriptionSection": "9896",
            "AnesthesiaSection": "9883",
            "ProcedureFindingsSection": "9892",
            "PreoperativeDiagnosisSection": "9888",
            "ComplicationsSection": "9885"
        }
    },
    "ProcedureNote": {
        "may": {
            "SocialHistorySection": "9849",
            "ProcedureDispositionSection": "9833",
            "AssessmentAndPlanSection": "9649",
            "ChiefComplaintAndReasonForVisitSection": "9815",
            "HistoryOfPresentIllnessSection": "9821",
            "ProcedureSpecimensTakenSection": "9841",
            "PlannedProcedureSection": "9831",
            "MedicationsSectionEntriesOptional": "9825",
            "MedicationsAdministeredSection": "9827",
            "ProcedureImplantsSection": "9839",
            "AnesthesiaSection": "9811",
            "MedicalHistorySection": "9823",
            "AllergiesSectionEntriesOptional": "9809",
            "ReasonForVisitSection": "9845",
            "ProcedureFindingsSection": "9837",
            "PlanOfCareSection": "9647",
            "ChiefComplaintSection": "9813",
            "ProcedureEstimatedBloodLossSection": "9835",
            "HistoryOfPastIllnessSection": "9819",
            "FamilyHistorySection": "9817",
            "ProceduresSectionEntriesOptional": "9843",
            "ReviewOfSystemsSection": "9847",
            "PhysicalExamSection": "9829",
            "AssessmentSection": "9645"
        },
        "full": {
            "SocialHistorySection": {
                "id": "9849",
                "constraint": "may"
            },
            "ProcedureDispositionSection": {
                "id": "9833",
                "constraint": "may"
            },
            "AssessmentAndPlanSection": {
                "id": "9649",
                "constraint": "may"
            },
            "ChiefComplaintAndReasonForVisitSection": {
                "id": "9815",
                "constraint": "may"
            },
            "ComplicationsSection": {
                "id": "9802",
                "constraint": "shall"
            },
            "HistoryOfPresentIllnessSection": {
                "id": "9821",
                "constraint": "may"
            },
            "ProcedureSpecimensTakenSection": {
                "id": "9841",
                "constraint": "may"
            },
            "PlannedProcedureSection": {
                "id": "9831",
                "constraint": "may"
            },
            "MedicationsSectionEntriesOptional": {
                "id": "9825",
                "constraint": "may"
            },
            "MedicationsAdministeredSection": {
                "id": "9827",
                "constraint": "may"
            },
            "ProcedureImplantsSection": {
                "id": "9839",
                "constraint": "may"
            },
            "ProcedureDescriptionSection": {
                "id": "9805",
                "constraint": "shall"
            },
            "AnesthesiaSection": {
                "id": "9811",
                "constraint": "may"
            },
            "MedicalHistorySection": {
                "id": "9823",
                "constraint": "may"
            },
            "AllergiesSectionEntriesOptional": {
                "id": "9809",
                "constraint": "may"
            },
            "ReasonForVisitSection": {
                "id": "9845",
                "constraint": "may"
            },
            "ProcedureFindingsSection": {
                "id": "9837",
                "constraint": "may"
            },
            "PlanOfCareSection": {
                "id": "9647",
                "constraint": "may"
            },
            "ChiefComplaintSection": {
                "id": "9813",
                "constraint": "may"
            },
            "ProcedureEstimatedBloodLossSection": {
                "id": "9835",
                "constraint": "may"
            },
            "PostprocedureDiagnosisSection": {
                "id": "9850",
                "constraint": "shall"
            },
            "HistoryOfPastIllnessSection": {
                "id": "9819",
                "constraint": "may"
            },
            "FamilyHistorySection": {
                "id": "9817",
                "constraint": "may"
            },
            "ProcedureIndicationsSection": {
                "id": "9807",
                "constraint": "shall"
            },
            "ProceduresSectionEntriesOptional": {
                "id": "9843",
                "constraint": "may"
            },
            "ReviewOfSystemsSection": {
                "id": "9847",
                "constraint": "may"
            },
            "PhysicalExamSection": {
                "id": "9829",
                "constraint": "may"
            },
            "AssessmentSection": {
                "id": "9645",
                "constraint": "may"
            }
        },
        "shall": {
            "ProcedureDescriptionSection": "9805",
            "PostprocedureDiagnosisSection": "9850",
            "ProcedureIndicationsSection": "9807",
            "ComplicationsSection": "9802"
        }
    },
    "DiagnosticImagingReport": {
        "full": {
            "FindingsSection": {
                "id": "8776",
                "constraint": "shall"
            },
            "DICOMObjectCatalogSection": {
                "id": "15141",
                "constraint": "should"
            }
        },
        "shall": {
            "FindingsSection": "8776"
        },
        "should": {
            "DICOMObjectCatalogSection": "15141"
        }
    },
    "ConsultationNote": {
        "may": {
            "ChiefComplaintSection": "9509",
            "AllergiesSectionEntriesOptional": "9507",
            "FamilyHistorySection": "9513",
            "ResultsSectionEntriesOptional": "9527",
            "HistoryOfPastIllnessSection": "9517",
            "SocialHistorySection": "9531",
            "ProblemSectionEntriesOptional": "9523",
            "MedicationsSectionEntriesOptional": "9521)",
            "ImmunizationsSection": "9519",
            "ProceduresSectionEntriesOptional": "9525",
            "AssessmentAndPlanSection": "9491",
            "GeneralStatusSection": "9515",
            "ReasonForVisitSection": "9500",
            "ChiefComplaintAndReasonForVisitSection": "10029",
            "PlanOfCareSection": "9489",
            "ReviewOfSystemsSection": "9529",
            "ReasonForReferralSection": "9498",
            "VitalSignsSectionEntriesOptional": "9533",
            "AssessmentSection": "9487"
        },
        "should": {
            "PhysicalExamSection": "9495"
        },
        "full": {
            "ChiefComplaintSection": {
                "id": "9509",
                "constraint": "may"
            },
            "AllergiesSectionEntriesOptional": {
                "id": "9507",
                "constraint": "may"
            },
            "FamilyHistorySection": {
                "id": "9513",
                "constraint": "may"
            },
            "ResultsSectionEntriesOptional": {
                "id": "9527",
                "constraint": "may"
            },
            "HistoryOfPastIllnessSection": {
                "id": "9517",
                "constraint": "may"
            },
            "SocialHistorySection": {
                "id": "9531",
                "constraint": "may"
            },
            "ProblemSectionEntriesOptional": {
                "id": "9523",
                "constraint": "may"
            },
            "MedicationsSectionEntriesOptional": {
                "id": "9521)",
                "constraint": "may"
            },
            "ImmunizationsSection": {
                "id": "9519",
                "constraint": "may"
            },
            "ProceduresSectionEntriesOptional": {
                "id": "9525",
                "constraint": "may"
            },
            "AssessmentAndPlanSection": {
                "id": "9491",
                "constraint": "may"
            },
            "GeneralStatusSection": {
                "id": "9515",
                "constraint": "may"
            },
            "ReasonForVisitSection": {
                "id": "9500",
                "constraint": "may"
            },
            "ChiefComplaintAndReasonForVisitSection": {
                "id": "10029",
                "constraint": "may"
            },
            "PlanOfCareSection": {
                "id": "9489",
                "constraint": "may"
            },
            "ReviewOfSystemsSection": {
                "id": "9529",
                "constraint": "may"
            },
            "ReasonForReferralSection": {
                "id": "9498",
                "constraint": "may"
            },
            "PhysicalExamSection": {
                "id": "9495",
                "constraint": "should"
            },
            "VitalSignsSectionEntriesOptional": {
                "id": "9533",
                "constraint": "may"
            },
            "AssessmentSection": {
                "id": "9487",
                "constraint": "may"
            },
            "HistoryOfPresentIllnessSection": {
                "id": "9493",
                "constraint": "shall"
            }
        },
        "shall": {
            "HistoryOfPresentIllnessSection": "9493"
        }
    },
    "ProgressNote": {
        "may": {
            "ChiefComplaintSection": "8772",
            "AllergiesSectionEntriesOptional": "8773",
            "ResultsSectionEntriesOptional": "8782",
            "ProblemSectionEntriesOptional": "8786",
            "MedicationsSectionEntriesOptional": "8771",
            "InterventionsSection": "8778",
            "AssessmentAndPlanSection": "8774",
            "ObjectiveSection": "8770",
            "VitalSignsSectionEntriesOptional": "8784",
            "PlanOfCareSection": "8775",
            "ReviewOfSystemsSection": "8788",
            "InstructionsSection": "16806",
            "PhysicalExamSection": "8780",
            "SubjectiveSection": "8790",
            "AssessmentSection": "8776"
        },
        "full": {
            "ChiefComplaintSection": {
                "id": "8772",
                "constraint": "may"
            },
            "AllergiesSectionEntriesOptional": {
                "id": "8773",
                "constraint": "may"
            },
            "ResultsSectionEntriesOptional": {
                "id": "8782",
                "constraint": "may"
            },
            "ProblemSectionEntriesOptional": {
                "id": "8786",
                "constraint": "may"
            },
            "MedicationsSectionEntriesOptional": {
                "id": "8771",
                "constraint": "may"
            },
            "InterventionsSection": {
                "id": "8778",
                "constraint": "may"
            },
            "AssessmentAndPlanSection": {
                "id": "8774",
                "constraint": "may"
            },
            "ObjectiveSection": {
                "id": "8770",
                "constraint": "may"
            },
            "VitalSignsSectionEntriesOptional": {
                "id": "8784",
                "constraint": "may"
            },
            "PlanOfCareSection": {
                "id": "8775",
                "constraint": "may"
            },
            "ReviewOfSystemsSection": {
                "id": "8788",
                "constraint": "may"
            },
            "InstructionsSection": {
                "id": "16806",
                "constraint": "may"
            },
            "PhysicalExamSection": {
                "id": "8780",
                "constraint": "may"
            },
            "SubjectiveSection": {
                "id": "8790",
                "constraint": "may"
            },
            "AssessmentSection": {
                "id": "8776",
                "constraint": "may"
            }
        }
    }
};

module.exports = exports = templatesconstraints;

},{}],33:[function(require,module,exports){
var templates = {
    "ConsultationNote": "2.16.840.1.113883.10.20.22.1.4",
    "ContinuityOfCareDocument": "2.16.840.1.113883.10.20.22.1.2",
    "DiagnosticImagingReport": "2.16.840.1.113883.10.20.22.1.5",
    "DischargeSummary": "2.16.840.1.113883.10.20.22.1.8",
    "HistoryAndPhysicalNote": "2.16.840.1.113883.10.20.22.1.3",
    "OperativeNote": "2.16.840.1.113883.10.20.22.1.7",
    "ProcedureNote": "2.16.840.1.113883.10.20.22.1.6",
    "ProgressNote": "2.16.840.1.113883.10.20.22.1.9",
    "UnstructuredDocument": "2.16.840.1.113883.10.20.21.1.10"
};

module.exports = exports = templates;

},{}],34:[function(require,module,exports){
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

},{"./oids":35}],35:[function(require,module,exports){
module.exports = OIDs = {
    "2.16.840.1.113883.11.20.9.19": {
        name: "Problem Status",
        table: {
            "active": "active",
            "suspended": "suspended",
            "aborted": "aborted",
            "completed": "completed"
        }
    },
    "2.16.840.1.113883.5.8": {
        name: "Act Reason",
        table: {
            "IMMUNE": "Immunity",
            "MEDPREC": "Medical precaution",
            "OSTOCK": "Out of stock",
            "PATOBJ": "Patient objection",
            "PHILISOP": "Philosophical objection",
            "RELIG": "Religious objection",
            "VACEFF": "Vaccine efficacy concerns",
            "VACSAF": "Vaccine safety concerns"
        }
    },
    "2.16.840.1.113883.6.103": {
        name: "ICD-9-CM",
        uri: "http://www.cms.gov/medicare-coverage-database/staticpages/icd-9-code-lookup.aspx"
    },
    "2.16.840.1.113883.6.233": {
        name: "US Department of Veterans Affairs",
        uri: "http://www.hl7.org/documentcenter/public_temp_36CB0CDC-1C23-BA17-0C356EB233D41682/standards/vocabulary/vocabulary_tables/infrastructure/vocabulary/voc_ExternalSystems.html"
    },
    "2.16.840.1.113883.6.69": {
        name: "NDC-FDA Drug Registration",
        uri: "http://phinvads.cdc.gov/vads/ViewCodeSystem.action?id=2.16.840.1.113883.6.69"
    },
    "2.16.840.1.113883.6.253": {
        name: "MediSpan DDID"
    },
    "2.16.840.1.113883.6.27": {
        name: "Multum",
        uri: "http://multum-look-me-up#"
    },
    "2.16.840.1.113883.6.312": {
        name: "multum-drug-synonym-id",
        uri: "http://multum-drug-synonym-id-look-me-up#"
    },
    "2.16.840.1.113883.6.314": {
        name: "multum-drug-id",
        uri: "http://multum-drug-id-look-me-up#"
    },
    "2.16.840.1.113883.6.59": {
        name: "CVX Vaccine",
        uri: "http://www2a.cdc.gov/vaccines/iis/iisstandards/vaccines.asp?rpt=cvx&code="
    },
    "2.16.840.1.113883.5.112": {
        name: "Route Code",
        uri: "http://hl7.org/codes/RouteCode#"
    },
    "2.16.840.1.113883.6.255.1336": {
        name: "InsuranceType Code"
    },
    "2.16.840.1.113883.6.1": {
        name: "LOINC",
        uri: "http://purl.bioontology.org/ontology/LNC/"
    },
    "2.16.840.1.113883.6.88": {
        name: "RXNORM",
        uri: "http://purl.bioontology.org/ontology/RXNORM/"
    },
    "2.16.840.1.113883.6.96": {
        name: "SNOMED CT",
        uri: "http://purl.bioontology.org/ontology/SNOMEDCT/",
        table: {
            "55561003": "Active",
            "421139008": "On Hold",
            "392521001": "Prior History",
            "73425007": "No Longer Active"
        }
    },
    "2.16.840.1.113883.6.12": {
        name: "CPT",
        uri: "http://purl.bioontology.org/ontology/CPT/"
    },
    "2.16.840.1.113883.5.4": {
        name: "ActCode",
        uri: "http://hl7.org/actcode/"
    },
    "2.16.840.1.113883.4.9": {
        name: "UNII",
    },
    "2.16.840.1.113883.1.11.78": {
        name: "Observation Interpretation"
    },
    "2.16.840.1.113883.19": {
        name: "Good Health Clinic",
        uri: "http://hl7.org/goodhealth/"
    },
    "2.16.840.1.113883.6.259": {
        name: "HealthcareServiceLocation",
        uri: "http://hl7.org/healthcareservice/"
    },
    "2.16.840.1.113883.1.11.19185": {
        name: "HL7 Religion",
        uri: "http://hl7.org/codes/ReligiousAffiliation#"
    },
    "2.16.840.1.113883.5.60": {
        name: "LanguageAbilityMode",
        uri: "http://hl7.org/codes/LanguageAbility#",
        table: {
            ESGN: "Expressed signed",
            ESP: "Expressed spoken",
            EWR: "Expressed written",
            RSGN: "Received signed",
            RSP: "Received spoken",
            RWR: "Received written"
        }
    },
    "2.16.840.1.113883.5.2": {
        name: "HL7 Marital Status",
        uri: "http://hl7.org/codes/MaritalStatus#",
        table: {
            "A": "Annulled",
            "D": "Divorced",
            "I": "Interlocutory",
            "L": "Legally Separated",
            "M": "Married",
            "P": "Polygamous",
            "S": "Never Married",
            "T": "Domestic partner",
            "U": "Unmarried",
            "W": "Widowed"
        }
    },
    "2.16.840.1.113883.5.25": {
        name: "Confidentiality Code",
        table: {
            "N": "Normal",
            "R": "Restricted",
            "V": "Very Restricted",
            "U": "Unrestricted"
        }
    },
    "2.16.840.1.113883.5.83": {
        name: "HL7 Result Interpretation",
        uri: "http://hl7.org/codes/ResultInterpretation#",
        table: {
            "B": "better",
            "D": "decreased",
            "U": "increased",
            "W": "worse",
            "<": "low off scale",
            ">": "high off scale",
            "A": "Abnormal",
            "AA": "abnormal alert",
            "H": "High",
            "HH": "high alert",
            "L": "Low",
            "LL": "low alert",
            "N": "Normal",
            "I": "intermediate",
            "MS": "moderately susceptible",
            "R": "resistent",
            "S": "susceptible",
            "VS": "very susceptible",
            "EX": "outside threshold",
            "HX": "above high threshold",
            "LX": "below low threshold",

        }
    },
    "2.16.840.1.113883.5.111": {
        name: "HL7 Role",
        uri: "http://hl7.org/codes/PersonalRelationship#",
        table: {
            "PRN": "Parent"
        }
    },
    "2.16.840.1.113883.5.110": {
        name: "HL7 RoleCode"
    },
    "2.16.840.1.113883.5.1119": {
        name: "HL7 Address",
        uri: "http://hl7.org/codes/Address#",
        table: {
            "BAD": "bad address",
            "CONF": "confidential",
            "DIR": "direct",
            "H": "home address",
            "HP": "primary home",
            "HV": "vacation home",
            "PHYS": "physical visit address",
            "PST": "postal address",
            "PUB": "public",
            "TMP": "temporary",
            "WP": "work place",
            "MC": "mobile contact",
            "PG": "pager",
            "EC": "emergency contact",
            "AS": "answering service"
        }
    },
    "2.16.840.1.113883.5.45": {
        name: "HL7 EntityName",
        uri: "http://hl7.org/codes/EntityName#",
        table: {
            "A": "Artist/Stage",
            "ABC": "Alphabetic",
            "ASGN": "Assigned",
            "C": "License",
            "I": "Indigenous/Tribal",
            "IDE": "Ideographic",
            "L": "Legal",
            "P": "Pseudonym",
            "PHON": "Phonetic",
            "R": "Religious",
            "SNDX": "Soundex",
            "SRCH": "Search",
            "SYL": "Syllabic"
        }
    },
    "2.16.840.1.113883.5.1": {
        name: "HL7 AdministrativeGender",
        uri: "http://hl7.org/codes/AdministrativeGender#",
        table: {
            "F": "Female",
            "M": "Male",
            "UN": "Undifferentiated"
        }
    },
    "2.16.840.1.113883.3.88.12.3221.6.8": {
        name: "Problem Severity",
        uri: "http://purl.bioontology.org/ontology/SNOMEDCT/",
        code_system: "2.16.840.1.113883.6.96",
        table: {
            "255604002": "Mild",
            "371923003": "Mild to moderate",
            "6736007": "Moderate",
            "371924009": "Moderate to severe",
            "24484000": "Severe",
            "399166001": "Fatal"
        }
    },
    "2.16.840.1.113883.3.88.12.80.68": {
        name: "HITSP Problem Status",
        uri: "http://purl.bioontology.org/ontology/SNOMEDCT/",
        code_system: "2.16.840.1.113883.6.96",
        table: {
            "55561003": "Active",
            "73425007": "Inactive",
            "413322009": "Resolved"
        }
    },
    "2.16.840.1.113883.11.20.9.38": {
        name: "Smoking Status/Social History Observation",
        uri: "http://purl.bioontology.org/ontology/SNOMEDCT/",
        code_system: "2.16.840.1.113883.6.96",
        table: {
            "449868002": "Current every day smoker",
            "428041000124106": "Current some day smoker",
            "8517006": "Former smoker",
            "266919005": "Never smoker",
            "77176002": "Smoker, current status unknown",
            "266927001": "Unknown if ever smoked",
            "230056004": "Smoker, current status unknown",
            "229819007": "Tobacco use and exposure",
            "256235009": "Exercise",
            "160573003": "Alcohol intake",
            "364393001": "Nutritional observable",
            "364703007": "Employment detail",
            "425400000": "Toxic exposure status",
            "363908000": "Details of drug misuse behavior",
            "228272008": "Health-related behavior",
            "105421008": "Educational Achievement"
        }
    },
    "2.16.840.1.113883.11.20.9.21": {
        name: "Age Unified Code for Units of Measure",
        uri: "http://phinvads.cdc.gov/vads/ViewValueSet.action?oid=2.16.840.1.114222.4.11.878",
        table: {
            "min": "Minute",
            "h": "Hour",
            "d": "Day",
            "wk": "Week",
            "mo": "Month",
            "a": "Year"
        }
    },
    "2.16.840.1.113883.12.292": {
        name: "CVX",
        uri: "http://phinvads.cdc.gov/vads/ViewCodeSystem.action?id=2.16.840.1.113883.12.292"
    },
    "2.16.840.1.113883.5.1076": {
        name: "HL7 Religious Affiliation",
        uri: "http://ushik.ahrq.gov/ViewItemDetails?system=mdr&itemKey=83154000",
        table: {
            "1008": "Babi & Baha´I faiths",
            "1009": "Baptist",
            "1010": "Bon",
            "1011": "Cao Dai",
            "1012": "Celticism",
            "1013": "Christian (non-Catholic, non-specific)",
            "1014": "Confucianism",
            "1015": "Cyberculture Religions",
            "1016": "Divination",
            "1017": "Fourth Way",
            "1018": "Free Daism",
            "1019": "Gnosis",
            "1020": "Hinduism",
            "1021": "Humanism",
            "1022": "Independent",
            "1023": "Islam",
            "1024": "Jainism",
            "1025": "Jehovah´s Witnesses",
            "1026": "Judaism",
            "1027": "Latter Day Saints",
            "1028": "Lutheran",
            "1029": "Mahayana",
            "1030": "Meditation",
            "1031": "Messianic Judaism",
            "1032": "Mitraism",
            "1033": "New Age",
            "1034": "non-Roman Catholic",
            "1035": "Occult",
            "1036": "Orthodox",
            "1037": "Paganism",
            "1038": "Pentecostal",
            "1039": "Process, The",
            "1040": "Reformed/Presbyterian",
            "1041": "Roman Catholic Church",
            "1042": "Satanism",
            "1043": "Scientology",
            "1044": "Shamanism",
            "1045": "Shiite (Islam)",
            "1046": "Shinto",
            "1047": "Sikism",
            "1048": "Spiritualism",
            "1049": "Sunni (Islam)",
            "1050": "Taoism",
            "1051": "Theravada",
            "1052": "Unitarian-Universalism",
            "1053": "Universal Life Church",
            "1054": "Vajrayana (Tibetan)",
            "1055": "Veda",
            "1056": "Voodoo",
            "1057": "Wicca",
            "1058": "Yaohushua",
            "1059": "Zen Buddhism",
            "1060": "Zoroastrianism",
            "1062": "Brethren",
            "1063": "Christian Scientist",
            "1064": "Church of Christ",
            "1065": "Church of God",
            "1066": "Congregational",
            "1067": "Disciples of Christ",
            "1068": "Eastern Orthodox",
            "1069": "Episcopalian",
            "1070": "Evangelical Covenant",
            "1071": "Friends",
            "1072": "Full Gospel",
            "1073": "Methodist",
            "1074": "Native American",
            "1075": "Nazarene",
            "1076": "Presbyterian",
            "1077": "Protestant",
            "1078": "Protestant, No Denomination",
            "1079": "Reformed",
            "1080": "Salvation Army",
            "1081": "Unitarian Universalist",
            "1082": "United Church of Christ"
        }
    },
    "2.16.840.1.113883.1.11.11526": {
        "name": "Internet Society Language",
        "uri": "http://www.loc.gov/standards/iso639-2/php/English_list.php"
    },
    "2.16.840.1.113883.11.20.9.22": {
        name: "ActStatus",
        table: {
            "completed": "Completed",
            "active": "Active",
            "aborted": "Aborted",
            "cancelled": "Cancelled"
        }
    },
    "2.16.840.1.113883.6.238": {
        name: "Race and Ethnicity - CDC",
        uri: "http://phinvads.cdc.gov/vads/ViewCodeSystemConcept.action?oid=2.16.840.1.113883.6.238&code=",
        table: {
            "1002-5": "American Indian or Alaska Native",
            "2028-9": "Asian",
            "2054-5": "Black or African American",
            "2076-8": "Native Hawaiian or Other Pacific Islander",
            "2106-3": "White",
            "2131-1": "Other Race",
            "1004-1": "American Indian",
            "1735-0": "Alaska Native",
            "2029-7": "Asian Indian",
            "2030-5": "Bangladeshi",
            "2031-3": "Bhutanese",
            "2032-1": "Burmese",
            "2033-9": "Cambodian",
            "2034-7": "Chinese",
            "2035-4": "Taiwanese",
            "2036-2": "Filipino",
            "2037-0": "Hmong",
            "2038-8": "Indonesian",
            "2039-6": "Japanese",
            "2040-4": "Korean",
            "2041-2": "Laotian",
            "2042-0": "Malaysian",
            "2043-8": "Okinawan",
            "2044-6": "Pakistani",
            "2045-3": "Sri Lankan",
            "2046-1": "Thai",
            "2047-9": "Vietnamese",
            "2048-7": "Iwo Jiman",
            "2049-5": "Maldivian",
            "2050-3": "Nepalese",
            "2051-1": "Singaporean",
            "2052-9": "Madagascar",
            "2056-0": "Black",
            "2058-6": "African American",
            "2060-2": "African",
            "2067-7": "Bahamian",
            "2068-5": "Barbadian",
            "2069-3": "Dominican",
            "2070-1": "Dominica Islander",
            "2071-9": "Haitian",
            "2072-7": "Jamaican",
            "2073-5": "Tobagoan",
            "2074-3": "Trinidadian",
            "2075-0": "West Indian",
            "2078-4": "Polynesian",
            "2085-9": "Micronesian",
            "2100-6": "Melanesian",
            "2500-7": "Other Pacific Islander",
            "2108-9": "European",
            "2118-8": "Middle Eastern or North African",
            "2129-5": "Arab",
            "1006-6": "Abenaki",
            "1008-2": "Algonquian",
            "1010-8": "Apache",
            "1021-5": "Arapaho",
            "1026-4": "Arikara",
            "1028-0": "Assiniboine",
            "1030-6": "Assiniboine Sioux",
            "1033-0": "Bannock",
            "1035-5": "Blackfeet",
            "1037-1": "Brotherton",
            "1039-7": "Burt Lake Band",
            "1041-3": "Caddo",
            "1044-7": "Cahuilla",
            "1053-8": "California Tribes",
            "1068-6": "Canadian and Latin American Indian",
            "1076-9": "Catawba",
            "1078-5": "Cayuse",
            "1080-1": "Chehalis",
            "1082-7": "Chemakuan",
            "1086-8": "Chemehuevi",
            "1088-4": "Cherokee",
            "1100-7": "Cherokee Shawnee",
            "1102-3": "Cheyenne",
            "1106-4": "Cheyenne-Arapaho",
            "1108-0": "Chickahominy",
            "1112-2": "Chickasaw",
            "1114-8": "Chinook",
            "1123-9": "Chippewa",
            "1150-2": "Chippewa Cree",
            "1153-6": "Chitimacha",
            "1155-1": "Choctaw",
            "1162-7": "Chumash",
            "1165-0": "Clear Lake",
            "1167-6": "Coeur D'Alene",
            "1169-2": "Coharie",
            "1171-8": "Colorado River",
            "1173-4": "Colville",
            "1175-9": "Comanche",
            "1178-3": "Coos, Lower Umpqua, Siuslaw",
            "1180-9": "Coos",
            "1182-5": "Coquilles",
            "1184-1": "Costanoan",
            "1186-6": "Coushatta",
            "1189-0": "Cowlitz",
            "1191-6": "Cree",
            "1193-2": "Creek",
            "1207-0": "Croatan",
            "1209-6": "Crow",
            "1211-2": "Cupeno",
            "1214-6": "Delaware",
            "1222-9": "Diegueno",
            "1233-6": "Eastern Tribes",
            "1250-0": "Esselen",
            "1252-6": "Fort Belknap",
            "1254-2": "Fort Berthold",
            "1256-7": "Fort Mcdowell",
            "1258-3": "Fort Hall",
            "1260-9": "Gabrieleno",
            "1262-5": "Grand Ronde",
            "1264-1": "Gros Ventres",
            "1267-4": "Haliwa",
            "1269-0": "Hidatsa",
            "1271-6": "Hoopa",
            "1275-7": "Hoopa Extension",
            "1277-3": "Houma",
            "1279-9": "Inaja-Cosmit",
            "1281-5": "Iowa",
            "1285-6": "Iroquois",
            "1297-1": "Juaneno",
            "1299-7": "Kalispel",
            "1301-1": "Karuk",
            "1303-7": "Kaw",
            "1305-2": "Kickapoo",
            "1309-4": "Kiowa",
            "1312-8": "Klallam",
            "1317-7": "Klamath",
            "1319-3": "Konkow",
            "1321-9": "Kootenai",
            "1323-5": "Lassik",
            "1325-0": "Long Island",
            "1331-8": "Luiseno",
            "1340-9": "Lumbee",
            "1342-5": "Lummi",
            "1344-1": "Maidu",
            "1348-2": "Makah",
            "1350-8": "Maliseet",
            "1352-4": "Mandan",
            "1354-0": "Mattaponi",
            "1356-5": "Menominee",
            "1358-1": "Miami",
            "1363-1": "Miccosukee",
            "1365-6": "Micmac",
            "1368-0": "Mission Indians",
            "1370-6": "Miwok",
            "1372-2": "Modoc",
            "1374-8": "Mohegan",
            "1376-3": "Mono",
            "1378-9": "Nanticoke",
            "1380-5": "Narragansett",
            "1382-1": "Navajo",
            "1387-0": "Nez Perce",
            "1389-6": "Nomalaki",
            "1391-2": "Northwest Tribes",
            "1403-5": "Omaha",
            "1405-0": "Oregon Athabaskan",
            "1407-6": "Osage",
            "1409-2": "Otoe-Missouria",
            "1411-8": "Ottawa",
            "1416-7": "Paiute",
            "1439-9": "Pamunkey",
            "1441-5": "Passamaquoddy",
            "1445-6": "Pawnee",
            "1448-0": "Penobscot",
            "1450-6": "Peoria",
            "1453-0": "Pequot",
            "1456-3": "Pima",
            "1460-5": "Piscataway",
            "1462-1": "Pit River",
            "1464-7": "Pomo",
            "1474-6": "Ponca",
            "1478-7": "Potawatomi",
            "1487-8": "Powhatan",
            "1489-4": "Pueblo",
            "1518-0": "Puget Sound Salish",
            "1541-2": "Quapaw",
            "1543-8": "Quinault",
            "1545-3": "Rappahannock",
            "1547-9": "Reno-Sparks",
            "1549-5": "Round Valley",
            "1551-1": "Sac and Fox",
            "1556-0": "Salinan",
            "1558-6": "Salish",
            "1560-2": "Salish and Kootenai",
            "1562-8": "Schaghticoke",
            "1564-4": "Scott Valley",
            "1566-9": "Seminole",
            "1573-5": "Serrano",
            "1576-8": "Shasta",
            "1578-4": "Shawnee",
            "1582-6": "Shinnecock",
            "1584-2": "Shoalwater Bay",
            "1586-7": "Shoshone",
            "1602-2": "Shoshone Paiute",
            "1607-1": "Siletz",
            "1609-7": "Sioux",
            "1643-6": "Siuslaw",
            "1645-1": "Spokane",
            "1647-7": "Stewart",
            "1649-3": "Stockbridge",
            "1651-9": "Susanville",
            "1653-5": "Tohono O'Odham",
            "1659-2": "Tolowa",
            "1661-8": "Tonkawa",
            "1663-4": "Tygh",
            "1665-9": "Umatilla",
            "1667-5": "Umpqua",
            "1670-9": "Ute",
            "1675-8": "Wailaki",
            "1677-4": "Walla-Walla",
            "1679-0": "Wampanoag",
            "1683-2": "Warm Springs",
            "1685-7": "Wascopum",
            "1687-3": "Washoe",
            "1692-3": "Wichita",
            "1694-9": "Wind River",
            "1696-4": "Winnebago",
            "1700-4": "Winnemucca",
            "1702-0": "Wintun",
            "1704-6": "Wiyot",
            "1707-9": "Yakama",
            "1709-5": "Yakama Cowlitz",
            "1711-1": "Yaqui",
            "1715-2": "Yavapai Apache",
            "1717-8": "Yokuts",
            "1722-8": "Yuchi",
            "1724-4": "Yuman",
            "1732-7": "Yurok",
            "1737-6": "Alaska Indian",
            "1840-8": "Eskimo",
            "1966-1": "Aleut",
            "2061-0": "Botswanan",
            "2062-8": "Ethiopian",
            "2063-6": "Liberian",
            "2064-4": "Namibian",
            "2065-1": "Nigerian",
            "2066-9": "Zairean",
            "2079-2": "Native Hawaiian",
            "2080-0": "Samoan",
            "2081-8": "Tahitian",
            "2082-6": "Tongan",
            "2083-4": "Tokelauan",
            "2086-7": "Guamanian or Chamorro",
            "2087-5": "Guamanian",
            "2088-3": "Chamorro",
            "2089-1": "Mariana Islander",
            "2090-9": "Marshallese",
            "2091-7": "Palauan",
            "2092-5": "Carolinian",
            "2093-3": "Kosraean",
            "2094-1": "Pohnpeian",
            "2095-8": "Saipanese",
            "2096-6": "Kiribati",
            "2097-4": "Chuukese",
            "2098-2": "Yapese",
            "2101-4": "Fijian",
            "2102-2": "Papua New Guinean",
            "2103-0": "Solomon Islander",
            "2104-8": "New Hebrides",
            "2109-7": "Armenian",
            "2110-5": "English",
            "2111-3": "French",
            "2112-1": "German",
            "2113-9": "Irish",
            "2114-7": "Italian",
            "2115-4": "Polish",
            "2116-2": "Scottish",
            "2119-6": "Assyrian",
            "2120-4": "Egyptian",
            "2121-2": "Iranian",
            "2122-0": "Iraqi",
            "2123-8": "Lebanese",
            "2124-6": "Palestinian",
            "2125-3": "Syrian",
            "2126-1": "Afghanistani",
            "2127-9": "Israeili",
            "1011-6": "Chiricahua",
            "1012-4": "Fort Sill Apache",
            "1013-2": "Jicarilla Apache",
            "1014-0": "Lipan Apache",
            "1015-7": "Mescalero Apache",
            "1016-5": "Oklahoma Apache",
            "1017-3": "Payson Apache",
            "1018-1": "San Carlos Apache",
            "1019-9": "White Mountain Apache",
            "1022-3": "Northern Arapaho",
            "1023-1": "Southern Arapaho",
            "1024-9": "Wind River Arapaho",
            "1031-4": "Fort Peck Assiniboine Sioux",
            "1042-1": "Oklahoma Cado",
            "1045-4": "Agua Caliente Cahuilla",
            "1046-2": "Augustine",
            "1047-0": "Cabazon",
            "1048-8": "Los Coyotes",
            "1049-6": "Morongo",
            "1050-4": "Santa Rosa Cahuilla",
            "1051-2": "Torres-Martinez",
            "1054-6": "Cahto",
            "1055-3": "Chimariko",
            "1056-1": "Coast Miwok",
            "1057-9": "Digger",
            "1058-7": "Kawaiisu",
            "1059-5": "Kern River",
            "1060-3": "Mattole",
            "1061-1": "Red Wood",
            "1062-9": "Santa Rosa",
            "1063-7": "Takelma",
            "1064-5": "Wappo",
            "1065-2": "Yana",
            "1066-0": "Yuki",
            "1069-4": "Canadian Indian",
            "1070-2": "Central American Indian",
            "1071-0": "French American Indian",
            "1072-8": "Mexican American Indian",
            "1073-6": "South American Indian",
            "1074-4": "Spanish American Indian",
            "1083-5": "Hoh",
            "1084-3": "Quileute",
            "1089-2": "Cherokee Alabama",
            "1090-0": "Cherokees of Northeast Alabama",
            "1091-8": "Cherokees of Southeast Alabama",
            "1092-6": "Eastern Cherokee",
            "1093-4": "Echota Cherokee",
            "1094-2": "Etowah Cherokee",
            "1095-9": "Northern Cherokee",
            "1096-7": "Tuscola",
            "1097-5": "United Keetowah Band of Cherokee",
            "1098-3": "Western Cherokee",
            "1103-1": "Northern Cheyenne",
            "1104-9": "Southern Cheyenne",
            "1109-8": "Eastern Chickahominy",
            "1110-6": "Western Chickahominy",
            "1115-5": "Clatsop",
            "1116-3": "Columbia River Chinook",
            "1117-1": "Kathlamet",
            "1118-9": "Upper Chinook",
            "1119-7": "Wakiakum Chinook",
            "1120-5": "Willapa Chinook",
            "1121-3": "Wishram",
            "1124-7": "Bad River",
            "1125-4": "Bay Mills Chippewa",
            "1126-2": "Bois Forte",
            "1127-0": "Burt Lake Chippewa",
            "1128-8": "Fond du Lac",
            "1129-6": "Grand Portage",
            "1130-4": "Grand Traverse Band of Ottawa/Chippewa",
            "1131-2": "Keweenaw",
            "1132-0": "Lac Courte Oreilles",
            "1133-8": "Lac du Flambeau",
            "1134-6": "Lac Vieux Desert Chippewa",
            "1135-3": "Lake Superior",
            "1136-1": "Leech Lake",
            "1137-9": "Little Shell Chippewa",
            "1138-7": "Mille Lacs",
            "1139-5": "Minnesota Chippewa",
            "1140-3": "Ontonagon",
            "1141-1": "Red Cliff Chippewa",
            "1142-9": "Red Lake Chippewa",
            "1143-7": "Saginaw Chippewa",
            "1144-5": "St. Croix Chippewa",
            "1145-2": "Sault Ste. Marie Chippewa",
            "1146-0": "Sokoagon Chippewa",
            "1147-8": "Turtle Mountain",
            "1148-6": "White Earth",
            "1151-0": "Rocky Boy's Chippewa Cree",
            "1156-9": "Clifton Choctaw",
            "1157-7": "Jena Choctaw",
            "1158-5": "Mississippi Choctaw",
            "1159-3": "Mowa Band of Choctaw",
            "1160-1": "Oklahoma Choctaw",
            "1163-5": "Santa Ynez",
            "1176-7": "Oklahoma Comanche",
            "1187-4": "Alabama Coushatta",
            "1194-0": "Alabama Creek",
            "1195-7": "Alabama Quassarte",
            "1196-5": "Eastern Creek",
            "1197-3": "Eastern Muscogee",
            "1198-1": "Kialegee",
            "1199-9": "Lower Muscogee",
            "1200-5": "Machis Lower Creek Indian",
            "1201-3": "Poarch Band",
            "1202-1": "Principal Creek Indian Nation",
            "1203-9": "Star Clan of Muscogee Creeks",
            "1204-7": "Thlopthlocco",
            "1205-4": "Tuckabachee",
            "1212-0": "Agua Caliente",
            "1215-3": "Eastern Delaware",
            "1216-1": "Lenni-Lenape",
            "1217-9": "Munsee",
            "1218-7": "Oklahoma Delaware",
            "1219-5": "Rampough Mountain",
            "1220-3": "Sand Hill",
            "1223-7": "Campo",
            "1224-5": "Capitan Grande",
            "1225-2": "Cuyapaipe",
            "1226-0": "La Posta",
            "1227-8": "Manzanita",
            "1228-6": "Mesa Grande",
            "1229-4": "San Pasqual",
            "1230-2": "Santa Ysabel",
            "1231-0": "Sycuan",
            "1234-4": "Attacapa",
            "1235-1": "Biloxi",
            "1236-9": "Georgetown (Eastern Tribes)",
            "1237-7": "Moor",
            "1238-5": "Nansemond",
            "1239-3": "Natchez",
            "1240-1": "Nausu Waiwash",
            "1241-9": "Nipmuc",
            "1242-7": "Paugussett",
            "1243-5": "Pocomoke Acohonock",
            "1244-3": "Southeastern Indians",
            "1245-0": "Susquehanock",
            "1246-8": "Tunica Biloxi",
            "1247-6": "Waccamaw-Siousan",
            "1248-4": "Wicomico",
            "1265-8": "Atsina",
            "1272-4": "Trinity",
            "1273-2": "Whilkut",
            "1282-3": "Iowa of Kansas-Nebraska",
            "1283-1": "Iowa of Oklahoma",
            "1286-4": "Cayuga",
            "1287-2": "Mohawk",
            "1288-0": "Oneida",
            "1289-8": "Onondaga",
            "1290-6": "Seneca",
            "1291-4": "Seneca Nation",
            "1292-2": "Seneca-Cayuga",
            "1293-0": "Tonawanda Seneca",
            "1294-8": "Tuscarora",
            "1295-5": "Wyandotte",
            "1306-0": "Oklahoma Kickapoo",
            "1307-8": "Texas Kickapoo",
            "1310-2": "Oklahoma Kiowa",
            "1313-6": "Jamestown",
            "1314-4": "Lower Elwha",
            "1315-1": "Port Gamble Klallam",
            "1326-8": "Matinecock",
            "1327-6": "Montauk",
            "1328-4": "Poospatuck",
            "1329-2": "Setauket",
            "1332-6": "La Jolla",
            "1333-4": "Pala",
            "1334-2": "Pauma",
            "1335-9": "Pechanga",
            "1336-7": "Soboba",
            "1337-5": "Twenty-Nine Palms",
            "1338-3": "Temecula",
            "1345-8": "Mountain Maidu",
            "1346-6": "Nishinam",
            "1359-9": "Illinois Miami",
            "1360-7": "Indiana Miami",
            "1361-5": "Oklahoma Miami",
            "1366-4": "Aroostook",
            "1383-9": "Alamo Navajo",
            "1384-7": "Canoncito Navajo",
            "1385-4": "Ramah Navajo",
            "1392-0": "Alsea",
            "1393-8": "Celilo",
            "1394-6": "Columbia",
            "1395-3": "Kalapuya",
            "1396-1": "Molala",
            "1397-9": "Talakamish",
            "1398-7": "Tenino",
            "1399-5": "Tillamook",
            "1400-1": "Wenatchee",
            "1401-9": "Yahooskin",
            "1412-6": "Burt Lake Ottawa",
            "1413-4": "Michigan Ottawa",
            "1414-2": "Oklahoma Ottawa",
            "1417-5": "Bishop",
            "1418-3": "Bridgeport",
            "1419-1": "Burns Paiute",
            "1420-9": "Cedarville",
            "1421-7": "Fort Bidwell",
            "1422-5": "Fort Independence",
            "1423-3": "Kaibab",
            "1424-1": "Las Vegas",
            "1425-8": "Lone Pine",
            "1426-6": "Lovelock",
            "1427-4": "Malheur Paiute",
            "1428-2": "Moapa",
            "1429-0": "Northern Paiute",
            "1430-8": "Owens Valley",
            "1431-6": "Pyramid Lake",
            "1432-4": "San Juan Southern Paiute",
            "1433-2": "Southern Paiute",
            "1434-0": "Summit Lake",
            "1435-7": "Utu Utu Gwaitu Paiute",
            "1436-5": "Walker River",
            "1437-3": "Yerington Paiute",
            "1442-3": "Indian Township",
            "1443-1": "Pleasant Point Passamaquoddy",
            "1446-4": "Oklahoma Pawnee",
            "1451-4": "Oklahoma Peoria",
            "1454-8": "Marshantucket Pequot",
            "1457-1": "Gila River Pima-Maricopa",
            "1458-9": "Salt River Pima-Maricopa",
            "1465-4": "Central Pomo",
            "1466-2": "Dry Creek",
            "1467-0": "Eastern Pomo",
            "1468-8": "Kashia",
            "1469-6": "Northern Pomo",
            "1470-4": "Scotts Valley",
            "1471-2": "Stonyford",
            "1472-0": "Sulphur Bank",
            "1475-3": "Nebraska Ponca",
            "1476-1": "Oklahoma Ponca",
            "1479-5": "Citizen Band Potawatomi",
            "1480-3": "Forest County",
            "1481-1": "Hannahville",
            "1482-9": "Huron Potawatomi",
            "1483-7": "Pokagon Potawatomi",
            "1484-5": "Prairie Band",
            "1485-2": "Wisconsin Potawatomi",
            "1490-2": "Acoma",
            "1491-0": "Arizona Tewa",
            "1492-8": "Cochiti",
            "1493-6": "Hopi",
            "1494-4": "Isleta",
            "1495-1": "Jemez",
            "1496-9": "Keres",
            "1497-7": "Laguna",
            "1498-5": "Nambe",
            "1499-3": "Picuris",
            "1500-8": "Piro",
            "1501-6": "Pojoaque",
            "1502-4": "San Felipe",
            "1503-2": "San Ildefonso",
            "1504-0": "San Juan Pueblo",
            "1505-7": "San Juan De",
            "1506-5": "San Juan",
            "1507-3": "Sandia",
            "1508-1": "Santa Ana",
            "1509-9": "Santa Clara",
            "1510-7": "Santo Domingo",
            "1511-5": "Taos",
            "1512-3": "Tesuque",
            "1513-1": "Tewa",
            "1514-9": "Tigua",
            "1515-6": "Zia",
            "1516-4": "Zuni",
            "1519-8": "Duwamish",
            "1520-6": "Kikiallus",
            "1521-4": "Lower Skagit",
            "1522-2": "Muckleshoot",
            "1523-0": "Nisqually",
            "1524-8": "Nooksack",
            "1525-5": "Port Madison",
            "1526-3": "Puyallup",
            "1527-1": "Samish",
            "1528-9": "Sauk-Suiattle",
            "1529-7": "Skokomish",
            "1530-5": "Skykomish",
            "1531-3": "Snohomish",
            "1532-1": "Snoqualmie",
            "1533-9": "Squaxin Island",
            "1534-7": "Steilacoom",
            "1535-4": "Stillaguamish",
            "1536-2": "Suquamish",
            "1537-0": "Swinomish",
            "1538-8": "Tulalip",
            "1539-6": "Upper Skagit",
            "1552-9": "Iowa Sac and Fox",
            "1553-7": "Missouri Sac and Fox",
            "1554-5": "Oklahoma Sac and Fox",
            "1567-7": "Big Cypress",
            "1568-5": "Brighton",
            "1569-3": "Florida Seminole",
            "1570-1": "Hollywood Seminole",
            "1571-9": "Oklahoma Seminole",
            "1574-3": "San Manual",
            "1579-2": "Absentee Shawnee",
            "1580-0": "Eastern Shawnee",
            "1587-5": "Battle Mountain",
            "1588-3": "Duckwater",
            "1589-1": "Elko",
            "1590-9": "Ely",
            "1591-7": "Goshute",
            "1592-5": "Panamint",
            "1593-3": "Ruby Valley",
            "1594-1": "Skull Valley",
            "1595-8": "South Fork Shoshone",
            "1596-6": "Te-Moak Western Shoshone",
            "1597-4": "Timbi-Sha Shoshone",
            "1598-2": "Washakie",
            "1599-0": "Wind River Shoshone",
            "1600-6": "Yomba",
            "1603-0": "Duck Valley",
            "1604-8": "Fallon",
            "1605-5": "Fort McDermitt",
            "1610-5": "Blackfoot Sioux",
            "1611-3": "Brule Sioux",
            "1612-1": "Cheyenne River Sioux",
            "1613-9": "Crow Creek Sioux",
            "1614-7": "Dakota Sioux",
            "1615-4": "Flandreau Santee",
            "1616-2": "Fort Peck",
            "1617-0": "Lake Traverse Sioux",
            "1618-8": "Lower Brule Sioux",
            "1619-6": "Lower Sioux",
            "1620-4": "Mdewakanton Sioux",
            "1621-2": "Miniconjou",
            "1622-0": "Oglala Sioux",
            "1623-8": "Pine Ridge Sioux",
            "1624-6": "Pipestone Sioux",
            "1625-3": "Prairie Island Sioux",
            "1626-1": "Prior Lake Sioux",
            "1627-9": "Rosebud Sioux",
            "1628-7": "Sans Arc Sioux",
            "1629-5": "Santee Sioux",
            "1630-3": "Sisseton-Wahpeton",
            "1631-1": "Sisseton Sioux",
            "1632-9": "Spirit Lake Sioux",
            "1633-7": "Standing Rock Sioux",
            "1634-5": "Teton Sioux",
            "1635-2": "Two Kettle Sioux",
            "1636-0": "Upper Sioux",
            "1637-8": "Wahpekute Sioux",
            "1638-6": "Wahpeton Sioux",
            "1639-4": "Wazhaza Sioux",
            "1640-2": "Yankton Sioux",
            "1641-0": "Yanktonai Sioux",
            "1654-3": "Ak-Chin",
            "1655-0": "Gila Bend",
            "1656-8": "San Xavier",
            "1657-6": "Sells",
            "1668-3": "Cow Creek Umpqua",
            "1671-7": "Allen Canyon",
            "1672-5": "Uintah Ute",
            "1673-3": "Ute Mountain Ute",
            "1680-8": "Gay Head Wampanoag",
            "1681-6": "Mashpee Wampanoag",
            "1688-1": "Alpine",
            "1689-9": "Carson",
            "1690-7": "Dresslerville",
            "1697-2": "Ho-chunk",
            "1698-0": "Nebraska Winnebago",
            "1705-3": "Table Bluff",
            "1712-9": "Barrio Libre",
            "1713-7": "Pascua Yaqui",
            "1718-6": "Chukchansi",
            "1719-4": "Tachi",
            "1720-2": "Tule River",
            "1725-1": "Cocopah",
            "1726-9": "Havasupai",
            "1727-7": "Hualapai",
            "1728-5": "Maricopa",
            "1729-3": "Mohave",
            "1730-1": "Quechan",
            "1731-9": "Yavapai",
            "1733-5": "Coast Yurok",
            "1739-2": "Alaskan Athabascan",
            "1811-9": "Southeast Alaska",
            "1842-4": "Greenland Eskimo",
            "1844-0": "Inupiat Eskimo",
            "1891-1": "Siberian Eskimo",
            "1896-0": "Yupik Eskimo",
            "1968-7": "Alutiiq Aleut",
            "1972-9": "Bristol Bay Aleut",
            "1984-4": "Chugach Aleut",
            "1990-1": "Eyak",
            "1992-7": "Koniag Aleut",
            "2002-4": "Sugpiaq",
            "2004-0": "Suqpigaq",
            "2006-5": "Unangan Aleut",
            "1740-0": "Ahtna",
            "1741-8": "Alatna",
            "1742-6": "Alexander",
            "1743-4": "Allakaket",
            "1744-2": "Alanvik",
            "1745-9": "Anvik",
            "1746-7": "Arctic",
            "1747-5": "Beaver",
            "1748-3": "Birch Creek",
            "1749-1": "Cantwell",
            "1750-9": "Chalkyitsik",
            "1751-7": "Chickaloon",
            "1752-5": "Chistochina",
            "1753-3": "Chitina",
            "1754-1": "Circle",
            "1755-8": "Cook Inlet",
            "1756-6": "Copper Center",
            "1757-4": "Copper River",
            "1758-2": "Dot Lake",
            "1759-0": "Doyon",
            "1760-8": "Eagle",
            "1761-6": "Eklutna",
            "1762-4": "Evansville",
            "1763-2": "Fort Yukon",
            "1764-0": "Gakona",
            "1765-7": "Galena",
            "1766-5": "Grayling",
            "1767-3": "Gulkana",
            "1768-1": "Healy Lake",
            "1769-9": "Holy Cross",
            "1770-7": "Hughes",
            "1771-5": "Huslia",
            "1772-3": "Iliamna",
            "1773-1": "Kaltag",
            "1774-9": "Kluti Kaah",
            "1775-6": "Knik",
            "1776-4": "Koyukuk",
            "1777-2": "Lake Minchumina",
            "1778-0": "Lime",
            "1779-8": "Mcgrath",
            "1780-6": "Manley Hot Springs",
            "1781-4": "Mentasta Lake",
            "1782-2": "Minto",
            "1783-0": "Nenana",
            "1784-8": "Nikolai",
            "1785-5": "Ninilchik",
            "1786-3": "Nondalton",
            "1787-1": "Northway",
            "1788-9": "Nulato",
            "1789-7": "Pedro Bay",
            "1790-5": "Rampart",
            "1791-3": "Ruby",
            "1792-1": "Salamatof",
            "1793-9": "Seldovia",
            "1794-7": "Slana",
            "1795-4": "Shageluk",
            "1796-2": "Stevens",
            "1797-0": "Stony River",
            "1798-8": "Takotna",
            "1799-6": "Tanacross",
            "1800-2": "Tanaina",
            "1801-0": "Tanana",
            "1802-8": "Tanana Chiefs",
            "1803-6": "Tazlina",
            "1804-4": "Telida",
            "1805-1": "Tetlin",
            "1806-9": "Tok",
            "1807-7": "Tyonek",
            "1808-5": "Venetie",
            "1809-3": "Wiseman",
            "1813-5": "Tlingit-Haida",
            "1837-4": "Tsimshian",
            "1845-7": "Ambler",
            "1846-5": "Anaktuvuk",
            "1847-3": "Anaktuvuk Pass",
            "1848-1": "Arctic Slope Inupiat",
            "1849-9": "Arctic Slope Corporation",
            "1850-7": "Atqasuk",
            "1851-5": "Barrow",
            "1852-3": "Bering Straits Inupiat",
            "1853-1": "Brevig Mission",
            "1854-9": "Buckland",
            "1855-6": "Chinik",
            "1856-4": "Council",
            "1857-2": "Deering",
            "1858-0": "Elim",
            "1859-8": "Golovin",
            "1860-6": "Inalik Diomede",
            "1861-4": "Inupiaq",
            "1862-2": "Kaktovik",
            "1863-0": "Kawerak",
            "1864-8": "Kiana",
            "1865-5": "Kivalina",
            "1866-3": "Kobuk",
            "1867-1": "Kotzebue",
            "1868-9": "Koyuk",
            "1869-7": "Kwiguk",
            "1870-5": "Mauneluk Inupiat",
            "1871-3": "Nana Inupiat",
            "1872-1": "Noatak",
            "1873-9": "Nome",
            "1874-7": "Noorvik",
            "1875-4": "Nuiqsut",
            "1876-2": "Point Hope",
            "1877-0": "Point Lay",
            "1878-8": "Selawik",
            "1879-6": "Shaktoolik",
            "1880-4": "Shishmaref",
            "1881-2": "Shungnak",
            "1882-0": "Solomon",
            "1883-8": "Teller",
            "1884-6": "Unalakleet",
            "1885-3": "Wainwright",
            "1886-1": "Wales",
            "1887-9": "White Mountain",
            "1888-7": "White Mountain Inupiat",
            "1889-5": "Mary's Igloo",
            "1892-9": "Gambell",
            "1893-7": "Savoonga",
            "1894-5": "Siberian Yupik",
            "1897-8": "Akiachak",
            "1898-6": "Akiak",
            "1899-4": "Alakanuk",
            "1900-0": "Aleknagik",
            "1901-8": "Andreafsky",
            "1902-6": "Aniak",
            "1903-4": "Atmautluak",
            "1904-2": "Bethel",
            "1905-9": "Bill Moore's Slough",
            "1906-7": "Bristol Bay Yupik",
            "1907-5": "Calista Yupik",
            "1908-3": "Chefornak",
            "1909-1": "Chevak",
            "1910-9": "Chuathbaluk",
            "1911-7": "Clark's Point",
            "1912-5": "Crooked Creek",
            "1913-3": "Dillingham",
            "1914-1": "Eek",
            "1915-8": "Ekuk",
            "1916-6": "Ekwok",
            "1917-4": "Emmonak",
            "1918-2": "Goodnews Bay",
            "1919-0": "Hooper Bay",
            "1920-8": "Iqurmuit (Russian Mission)",
            "1921-6": "Kalskag",
            "1922-4": "Kasigluk",
            "1923-2": "Kipnuk",
            "1924-0": "Koliganek",
            "1925-7": "Kongiganak",
            "1926-5": "Kotlik",
            "1927-3": "Kwethluk",
            "1928-1": "Kwigillingok",
            "1929-9": "Levelock",
            "1930-7": "Lower Kalskag",
            "1931-5": "Manokotak",
            "1932-3": "Marshall",
            "1933-1": "Mekoryuk",
            "1934-9": "Mountain Village",
            "1935-6": "Naknek",
            "1936-4": "Napaumute",
            "1937-2": "Napakiak",
            "1938-0": "Napaskiak",
            "1939-8": "Newhalen",
            "1940-6": "New Stuyahok",
            "1941-4": "Newtok",
            "1942-2": "Nightmute",
            "1943-0": "Nunapitchukv",
            "1944-8": "Oscarville",
            "1945-5": "Pilot Station",
            "1946-3": "Pitkas Point",
            "1947-1": "Platinum",
            "1948-9": "Portage Creek",
            "1949-7": "Quinhagak",
            "1950-5": "Red Devil",
            "1951-3": "St. Michael",
            "1952-1": "Scammon Bay",
            "1953-9": "Sheldon's Point",
            "1954-7": "Sleetmute",
            "1955-4": "Stebbins",
            "1956-2": "Togiak",
            "1957-0": "Toksook",
            "1958-8": "Tulukskak",
            "1959-6": "Tuntutuliak",
            "1960-4": "Tununak",
            "1961-2": "Twin Hills",
            "1962-0": "Georgetown (Yupik-Eskimo)",
            "1963-8": "St. Mary's",
            "1964-6": "Umkumiate",
            "1969-5": "Tatitlek",
            "1970-3": "Ugashik",
            "1973-7": "Chignik",
            "1974-5": "Chignik Lake",
            "1975-2": "Egegik",
            "1976-0": "Igiugig",
            "1977-8": "Ivanof Bay",
            "1978-6": "King Salmon",
            "1979-4": "Kokhanok",
            "1980-2": "Perryville",
            "1981-0": "Pilot Point",
            "1982-8": "Port Heiden",
            "1985-1": "Chenega",
            "1986-9": "Chugach Corporation",
            "1987-7": "English Bay",
            "1988-5": "Port Graham",
            "1993-5": "Akhiok",
            "1994-3": "Agdaagux",
            "1995-0": "Karluk",
            "1996-8": "Kodiak",
            "1997-6": "Larsen Bay",
            "1998-4": "Old Harbor",
            "1999-2": "Ouzinkie",
            "2000-8": "Port Lions",
            "2007-3": "Akutan",
            "2008-1": "Aleut Corporation",
            "2009-9": "Aleutian",
            "2010-7": "Aleutian Islander",
            "2011-5": "Atka",
            "2012-3": "Belkofski",
            "2013-1": "Chignik Lagoon",
            "2014-9": "King Cove",
            "2015-6": "False Pass",
            "2016-4": "Nelson Lagoon",
            "2017-2": "Nikolski",
            "2018-0": "Pauloff Harbor",
            "2019-8": "Qagan Toyagungin",
            "2020-6": "Qawalangin",
            "2021-4": "St. George",
            "2022-2": "St. Paul",
            "2023-0": "Sand Point",
            "2024-8": "South Naknek",
            "2025-5": "Unalaska",
            "2026-3": "Unga",
            "1814-3": "Angoon",
            "1815-0": "Central Council of Tlingit and Haida Tribes",
            "1816-8": "Chilkat",
            "1817-6": "Chilkoot",
            "1818-4": "Craig",
            "1819-2": "Douglas",
            "1820-0": "Haida",
            "1821-8": "Hoonah",
            "1822-6": "Hydaburg",
            "1823-4": "Kake",
            "1824-2": "Kasaan",
            "1825-9": "Kenaitze",
            "1826-7": "Ketchikan",
            "1827-5": "Klawock",
            "1828-3": "Pelican",
            "1829-1": "Petersburg",
            "1830-9": "Saxman",
            "1831-7": "Sitka",
            "1832-5": "Tenakee Springs",
            "1833-3": "Tlingit",
            "1834-1": "Wrangell",
            "1835-8": "Yakutat",
            "1838-2": "Metlakatla",
            "2135-2": "Hispanic or Latino",
            "2186-5": "Not Hispanic or Latino"
        }
    },
    "2.16.840.1.113883.3.26.1.1": {
        name: "Medication Route FDA",
        uri: "http://nci-thesaurus-look-me-up#",
        table: {
            "C38192": "AURICULAR (OTIC)",
            "C38193": "BUCCAL",
            "C38194": "CONJUNCTIVAL",
            "C38675": "CUTANEOUS",
            "C38197": "DENTAL",
            "C38633": "ELECTRO-OSMOSIS",
            "C38205": "ENDOCERVICAL",
            "C38206": "ENDOSINUSIAL",
            "C38208": "ENDOTRACHEAL",
            "C38209": "ENTERAL",
            "C38210": "EPIDURAL",
            "C38211": "EXTRA-AMNIOTIC",
            "C38212": "EXTRACORPOREAL",
            "C38200": "HEMODIALYSIS",
            "C38215": "INFILTRATION",
            "C38219": "INTERSTITIAL",
            "C38220": "INTRA-ABDOMINAL",
            "C38221": "INTRA-AMNIOTIC",
            "C38222": "INTRA-ARTERIAL",
            "C38223": "INTRA-ARTICULAR",
            "C38224": "INTRABILIARY",
            "C38225": "INTRABRONCHIAL",
            "C38226": "INTRABURSAL",
            "C38227": "INTRACARDIAC",
            "C38228": "INTRACARTILAGINOUS",
            "C38229": "INTRACAUDAL",
            "C38230": "INTRACAVERNOUS",
            "C38231": "INTRACAVITARY",
            "C38232": "INTRACEREBRAL",
            "C38233": "INTRACISTERNAL",
            "C38234": "INTRACORNEAL",
            "C38217": "INTRACORONAL, DENTAL",
            "C38218": "INTRACORONARY",
            "C38235": "INTRACORPORUS CAVERNOSUM",
            "C38238": "INTRADERMAL",
            "C38239": "INTRADISCAL",
            "C38240": "INTRADUCTAL",
            "C38241": "INTRADUODENAL",
            "C38242": "INTRADURAL",
            "C38243": "INTRAEPIDERMAL",
            "C38245": "INTRAESOPHAGEAL",
            "C38246": "INTRAGASTRIC",
            "C38247": "INTRAGINGIVAL",
            "C38249": "INTRAILEAL",
            "C38250": "INTRALESIONAL",
            "C38251": "INTRALUMINAL",
            "C38252": "INTRALYMPHATIC",
            "C38253": "INTRAMEDULLARY",
            "C38254": "INTRAMENINGEAL",
            "C28161": "INTRAMUSCULAR",
            "C38255": "INTRAOCULAR",
            "C38256": "INTRAOVARIAN",
            "C38257": "INTRAPERICARDIAL",
            "C38258": "INTRAPERITONEAL",
            "C38259": "INTRAPLEURAL",
            "C38260": "INTRAPROSTATIC",
            "C38261": "INTRAPULMONARY",
            "C38262": "INTRASINAL",
            "C38263": "INTRASPINAL",
            "C38264": "INTRASYNOVIAL",
            "C38265": "INTRATENDINOUS",
            "C38266": "INTRATESTICULAR",
            "C38267": "INTRATHECAL",
            "C38207": "INTRATHORACIC",
            "C38268": "INTRATUBULAR",
            "C38269": "INTRATUMOR",
            "C38270": "INTRATYMPANIC",
            "C38272": "INTRAUTERINE",
            "C38273": "INTRAVASCULAR",
            "C38276": "INTRAVENOUS",
            "C38277": "INTRAVENTRICULAR",
            "C38278": "INTRAVESICAL",
            "C38280": "INTRAVITREAL",
            "C38203": "IONTOPHORESIS",
            "C38281": "IRRIGATION",
            "C38282": "LARYNGEAL",
            "C38284": "NASAL",
            "C38285": "NASOGASTRIC",
            "C48623": "NOT APPLICABLE",
            "C38286": "OCCLUSIVE DRESSING TECHNIQUE",
            "C38287": "OPHTHALMIC",
            "C38288": "ORAL",
            "C38289": "OROPHARYNGEAL",
            "C38291": "PARENTERAL",
            "C38676": "PERCUTANEOUS",
            "C38292": "PERIARTICULAR",
            "C38677": "PERIDURAL",
            "C38293": "PERINEURAL",
            "C38294": "PERIODONTAL",
            "C38295": "RECTAL",
            "C38216": "RESPIRATORY (INHALATION)",
            "C38296": "RETROBULBAR",
            "C38198": "SOFT TISSUE",
            "C38297": "SUBARACHNOID",
            "C38298": "SUBCONJUNCTIVAL",
            "C38299": "SUBCUTANEOUS",
            "C38300": "SUBLINGUAL",
            "C38301": "SUBMUCOSAL",
            "C38304": "TOPICAL",
            "C38305": "TRANSDERMAL",
            "C38283": "TRANSMUCOSAL",
            "C38307": "TRANSPLACENTAL",
            "C38308": "TRANSTRACHEAL",
            "C38309": "TRANSTYMPANIC",
            "C38312": "URETERAL",
            "C38271": "URETHRAL"
        }
    },
    "2.16.840.1.113883.11.20.9.33": {
        name: "IND Roleclass Codes",
        uri: "",
        code_system: "2.16.840.1.113883.5.110",
        table: {
            "PRS": "Personal Relationship",
            "NOK": "Next of Kin",
            "CAREGIVER": "Caregiver",
            "AGNT": "Agent",
            "GUAR": "Guarantor",
            "ECON": "Emergency Contact"
        }
    },
    "2.16.840.1.113883.5.139": {
        name: "Domain TimingEvent",
        table: {
            //https://groups.google.com/forum/#!msg/ccda_samples/WawmwNMYT_8/pqnp5bG1IygJ
            "AC": "before meal",
            "ACD": "before lunch",
            "ACM": "before breakfast",
            "ACV": "before dinner",
            "C": "with meal",
            "CD": "with lunch",
            "CM": "with breakfast",
            "CV": "with dinner",
            "HS": "at bedtime",
            "IC": "between meals",
            "ICD": "between lunch and dinner",
            "ICM": "between breakfast and lunch",
            "ICV": "between dinner and bedtime",
            "PC": "after meal",
            "PCD": "after lunch",
            "PCM": "after breakfast",
            "PCV": "after dinner",
            "WAKE": "upon waking"
        }
    },
    "2.16.840.1.113883.6.14": {
        name: "HCPCS",
        uri: "http://www.cms.gov/Medicare/Coding/MedHCPCSGenInfo/index.html?redirect=/medhcpcsgeninfo/"
    },
    "2.16.840.1.113883.3.88.12.3221.8.9": {
        name: "Body Site Value Set"
    },
    "2.16.840.1.113883.5.7": {
        name: "ActPriority"
    },
    "2.16.840.1.113883.6.63": {
        name: "First DataBank Diagnostic Codes"
    },
    "2.16.840.1.113883.3.26.1.2": {
        name: "NCI Metathesaurus"
    }
};

},{}],36:[function(require,module,exports){
"use strict";

exports.arrayset = require('./lib/arrayset');
exports.object = require('./lib/object');
exports.objectset = require('./lib/objectset');
exports.datetime = require('./lib/datetime');
exports.predicate = require('./lib/predicate');
exports.jsonpath = require('./lib/jsonpath');

},{"./lib/arrayset":37,"./lib/datetime":38,"./lib/jsonpath":39,"./lib/object":40,"./lib/objectset":41,"./lib/predicate":42}],37:[function(require,module,exports){
"use strict";

var lodash = require('lodash');

exports.append = function (arr, arrToAppend) {
    Array.prototype.push.apply(arr, arrToAppend);
};

exports.remove = lodash.remove; // remove 1.5, leaving now just to be safe

},{"lodash":43}],38:[function(require,module,exports){
"use strict";

var moment = require('moment');

var dateToModel = exports.dateToModel = (function () {
    var precisions = ['year', 'month', 'day'];

    return function (d) {
        d = d.replace(/-/g, '');
        var m = moment.utc(d, 'YYYYMMDD');
        var dl = d.length;
        dl = (dl > 8) ? 8 : dl;
        var precisionIndex = dl / 2 - 2;
        var precision = precisions[precisionIndex];
        if (m.isValid() && precision) {
            return {
                date: m.toISOString(),
                precision: precision
            };
        } else {
            return null;
        }
    };
})();

exports.dateTimeToModel = function (d) {
    if (d.indexOf('T') > 0) {
        return {
            date: d,
            precision: 'second'
        };
    } else {
        return dateToModel(d);
    }
};

var modelToDateTime = exports.modelToDateTime = (function () {
    var precisionBasedFormatter = {
        year: function (t) {
            return moment(t, 'YYYY').format('YYYY');
        },
        month: function (t) {
            return moment(t, 'YYYYMM').format('YYYY-MM');
        },
        day: function (t) {
            return moment(t, 'YYYYMMDD').format('YYYY-MM-DD');
        }
    };

    return function (dt) {
        var f = precisionBasedFormatter[dt.precision];
        if (f) {
            return f(dt.date);
        } else {
            return dt.date;
        }
    };
})();

exports.modelToDate = function (dt) {
    var validPrecisions = {
        'day': true,
        'month': true,
        'year': true
    };
    if (!validPrecisions[dt.precision]) {
        dt.precision = 'day';
    }
    return modelToDateTime(dt);
};

},{"moment":44}],39:[function(require,module,exports){
/*global module, exports, require*/
/*jslint vars:true, evil:true*/
/* JSONPath 0.8.0 - XPath for JSON
 *
 * Copyright (c) 2007 Stefan Goessner (goessner.net)
 * Licensed under the MIT (MIT-LICENSE.txt) licence.
 */

'use strict';

var vm = require('vm');
var _ = require("lodash");

var arrayset = require('../arrayset');

var append = arrayset.append;

var properties = function (obj) {
    if (!obj) {
        return null;
    } else if (Array.isArray(obj)) {
        return _.range(obj.length);
    } else if (typeof obj === 'object') {
        return Object.keys(obj);
    } else {
        return null;
    }
};

var asPath = function (path) {
    var n = path.length;
    var r = '$';
    for (var i = 1; i < n; ++i) {
        var p = path[i];
        if (p[p.length - 1] === ')') {
            r += '.' + p;
        } else {
            r += /^[0-9*]+$/.test(p) ? ('[' + p + ']') : ("['" + p + "']");
        }
    }
    return r;
};

var normalize = exports.normalize = function (expr) {
    var exprList = [];
    var index = 0;
    var length = expr.length;
    var lastIndex = 0;
    while (index < length) {
        var c = expr.charAt(index);
        if (c === '.') {
            var cp1 = expr.charAt(index + 1);
            if (lastIndex !== index) {
                var subExpr = expr.substring(lastIndex, index);
                exprList.push(subExpr);
            }
            if (cp1 === '.') {
                exprList.push('..');
                ++index;
            }
            ++index;
            lastIndex = index;
            continue;
        }
        if (c === '[') {
            if (lastIndex !== index) {
                var subExprLB = expr.substring(lastIndex, index);
                exprList.push(subExprLB);
            }
            var openBrackets = 1;
            ++index;
            lastIndex = index;
            while (index < length) {
                var cinside = expr.charAt(index);
                if (cinside === '[') {
                    ++openBrackets;
                    ++index;
                    continue;
                }
                if (cinside === ']') {
                    --openBrackets;
                    if (openBrackets === 0) {
                        var subExprInside = expr.substring(lastIndex, index);
                        exprList.push(subExprInside);
                        ++index;
                        lastIndex = index;
                        break;
                    }
                }
                ++index;
            }
            continue;
        }
        if ((c === '^') || (c === '$')) {
            if (lastIndex !== index) {
                throw new Error('Invalid use of \'^\' or \'$\' in the expression.');
            }
            exprList.push(c);
            ++index;
            lastIndex = index;
            continue;
        }
        ++index;
    }
    if (lastIndex < index) {
        var subExprFinal = expr.substring(lastIndex, index);
        exprList.push(subExprFinal);
    }
    return exprList;
};

var processOptions = function (opts) {
    return {
        resultType: (opts && opts.resultType && opts.resultType.toLowerCase()) || 'value',
        flatten: (opts && opts.flatten) || false,
        wrap: (opts && opts.hasOwnProperty('wrap')) ? opts.wrap : null,
        sandbox: (opts && opts.sandbox) || {},
        functions: (opts && opts.functions) || {}
    };
};

var Accumulator = {
    init: function (lookbackNeeded, pathNeeded) {
        this.result = [];
        this.step = 0;
        if (lookbackNeeded) {
            this.objHistory = [];
        }
        if (pathNeeded) {
            this.path = [];
        }
    },
    clone: function () {
        var r = Object.create(Accumulator);
        r.result = this.result;
        r.obj = this.obj;
        r.location = this.location;
        r.step = this.step;
        if (this.objHistory) {
            r.objHistory = this.objHistory.slice();
        }
        if (this.path) {
            r.path = this.path.slice();
        }
        return r;
    },
    add: function (obj, location) {
        if (this.objHistory && (this.obj !== undefined)) {
            this.objHistory.push(this.obj);
        }
        if (this.path && (this.location !== undefined)) {
            this.path.push(this.location);
        }
        this.obj = obj;
        this.location = location;
    },
    back: function () {
        if (this.objHistory.length > 0) {
            this.obj = this.objHistory.splice(-1, 1)[0];
        }

        if (this.path && this.path.length > 0) {
            this.location = this.path.splice(-1, 1)[0];
        }
    },
    currentObject: function () {
        return this.obj;
    },
    currentPath: function () {
        if (this.path) {
            var r = this.path.slice();
            r.push(this.location);
            return r;
        } else {
            return null;
        }
    },
    addToResult: function () {
        this.result.push({
            path: this.currentPath(),
            value: this.currentObject()
        });
    },
    incrementStep: function () {
        ++this.step;
        return this.step;
    },
    toResult: function (options) {
        var result = this.result.slice();
        if (!result.length) {
            return options.wrap ? options.emptyValue : null;
        }
        if (result.length === 1 && !options.wrap && !Array.isArray(result[0].value)) {
            return result[0][options.resultType];
        }
        return result.reduce(function (result, ea) {
            var valOrPath = ea[options.resultType];
            if (options.resultType === 'path') {
                valOrPath = asPath(valOrPath);
            }
            if (options.flatten && Array.isArray(valOrPath)) {
                result = result.concat(valOrPath);
            } else {
                result.push(valOrPath);
            }
            return result;
        }, []);
    }
};

var PATH_VAR = '_$_path';
var VALUE_VAR = '_$_v';

var Tracer = {
    init: function (obj, traceSteps, sandbox, context) {
        this._obj = obj;
        this.traceSteps = traceSteps;
        this.sandbox = sandbox;
        this.context = context || {};
    },
    traceStart: function (accumulator) {
        accumulator.add(this._obj, '$');
        this.traceNext(accumulator);
    },
    traceAllProperties: function (accumulator) {
        var obj = accumulator.currentObject();
        var ps = properties(obj);
        this.traceProperties(ps, accumulator);
    },
    traceProperties: function (properties, accumulator) {
        if (properties) {
            properties.forEach(function (p) {
                var newAccumulator = accumulator.clone();
                this.traceProperty(p, newAccumulator);
            }, this);
        }
    },
    traceProperty: function (property, accumulator) {
        var obj = accumulator.currentObject();
        if (obj && obj.hasOwnProperty(property)) {
            accumulator.add(obj[property], property);
            this.traceNext(accumulator);
        }
    },
    traceAll: function (accumulator) {
        var rootJspResult = accumulator.clone();
        this.traceNext(rootJspResult);
        var obj = accumulator.currentObject();
        var ps = properties(obj);
        if (ps) {
            ps = ps.filter(function (p) {
                return typeof obj[p] === 'object';
            });
        }
        if (ps) {
            ps.forEach(function (p) {
                var newAccumulator = accumulator.clone();
                newAccumulator.add(obj[p], p);
                this.traceAll(newAccumulator);
            }, this);
        }
    },
    traceBack: function (accumulator) {
        accumulator.back();
        this.traceNext(accumulator);
    },
    traceEnd: function (accumulator) {
        accumulator.addToResult();
    },
    traceNext: function (accumulator) {
        var step = accumulator.incrementStep();
        var action = this.traceSteps[step];
        action.call(this, accumulator);
    },
    run: function (opts) {
        var accumulator = Object.create(Accumulator);
        accumulator.init(opts.lookbackNeeded, opts.pathNeeded);
        var action = this.traceSteps[0];
        action.call(this, accumulator);
        return accumulator.toResult(opts);
    },
    eval: function (code, obj, addlLocation, path) {
        if (code.indexOf(PATH_VAR) > -1) {
            path = path.slice();
            path.push(addlLocation);
            this.sandbox[PATH_VAR] = asPath(path);
        }
        if (code.indexOf(VALUE_VAR) > -1) {
            this.sandbox[VALUE_VAR] = obj;
        }
        try {
            return vm.runInNewContext(code, this.sandbox);
        } catch (e) {
            throw new Error('jsonPath: ' + e.message + ': ' + code);
        }
    },
    traceJsExprPropertyGen: function (expr) {
        return function (accumulator) {
            var path = accumulator.currentPath();
            var obj = accumulator.currentObject();
            var property = this.eval(expr, obj, null, path);
            this.traceProperty(property, accumulator);
        };
    },
    traceFilteredPropertiesGen: function (expr) {
        var jsExpr = expr.replace(/^\?\((.*?)\)$/, '$1');
        return function (accumulator) {
            var obj = accumulator.currentObject();
            var ps = properties(obj);
            if (ps) {
                var path = accumulator.currentPath();
                ps = ps.filter(function (p) {
                    return this.eval(jsExpr, obj[p], p, path);
                }, this);
            }
            this.traceProperties(ps, accumulator);
        };
    },
    traceCommaDelimitedPropertiesGen: function (expr) {
        var properties = expr.split(',');
        return function (accumulator) {
            this.traceProperties(properties, accumulator);
        };
    },
    traceArrayRangeGen: function (expr) {
        var indices = expr.split(':');
        var start = (indices[0] && parseInt(indices[0], 10)) || 0;
        var end = (indices[1] && parseInt(indices[1], 10)) || null;
        var step = (indices[2] && parseInt(indices[2], 10)) || 1;

        return function (accumulator) {
            var obj = accumulator.currentObject();
            var length = obj.length;
            var localStart = (start < 0) ? Math.max(0, start + length) : Math.min(length, start);
            var localEnd = (end === null) ? length : end;
            localEnd = (localEnd < 0) ? Math.max(0, localEnd + length) : Math.min(length, localEnd);
            var range = _.range(localStart, localEnd, step);
            this.traceProperties(range, accumulator);
        };
    },
    tracePropertyGen: function (property) {
        return function (accumulator) {
            this.traceProperty(property, accumulator);
        };
    },
    tracePredefinedFunctionGen: function (fn, expr) {
        return function (accumulator) {
            var obj = accumulator.currentObject();
            accumulator.add(fn(obj), expr);
            this.traceNext(accumulator);
        };
    },
    tracePostdefinedFunctionGen: function (expr) {
        return function (accumulator) {
            var obj = accumulator.currentObject();
            var fnName = expr.substring(0, expr.length - 2);
            var fn = this.context[fnName];
            if (!fn) {
                throw new Error('No function named ' + fnName + '.');
            }
            accumulator.add(fn(obj), expr);
            this.traceNext(accumulator);
        };
    },
    traceRootPathPropertyGen: function (propertySteps, opts) {
        return function (accumulator) {
            var tracer = Object.create(Tracer);
            tracer.init(this._obj, propertySteps, this.sandbox, this.context);

            var property = tracer.run(opts);
            if (property !== null) {
                if (Array.isArray(property)) {
                    this.traceProperties(property, accumulator);
                } else {
                    this.traceProperty(property, accumulator);
                }
            }
        };
    }
};

var tracerAdapter = (function () {
    var adapter = {};

    adapter.directMap = {
        "*": {
            method: Tracer.traceAllProperties,
            branches: true
        },
        "..": {
            method: Tracer.traceAll,
            branches: true
        },
        "$": {
            method: Tracer.traceStart
        },
        "^": {
            method: Tracer.traceBack,
            lookbackNeeded: true
        }
    };

    adapter.handleJSExpression = function (expr) {
        expr = expr.replace(/@path/g, PATH_VAR);
        expr = expr.replace(/@/g, VALUE_VAR);
        return expr;
    };

    adapter.reMap = [{
        re: /^\(.+\)$/,
        obj: function (expr) {
            var modifiedExpr = adapter.handleJSExpression(expr);
            return {
                method: Tracer.traceJsExprPropertyGen(modifiedExpr),
                pathNeeded: expr.indexOf('@path') > -1
            };
        }
    }, {
        re: /^\?\(.+\)$/,
        obj: function (expr) {
            var modifiedExpr = adapter.handleJSExpression(expr);
            return {
                method: Tracer.traceFilteredPropertiesGen(modifiedExpr),
                pathNeeded: expr.indexOf('@path') > -1,
                branches: true
            };
        }
    }, {
        re: /,/,
        obj: function (expr) {
            return {
                method: Tracer.traceCommaDelimitedPropertiesGen(expr),
                branches: true
            };
        }
    }, {
        re: /^(-?[0-9]*):(-?[0-9]*):?([0-9]*)$/,
        obj: function (expr) {
            return {
                method: Tracer.traceArrayRangeGen(expr),
                branches: true
            };
        }
    }, {
        re: /\(\)$/,
        obj: function (expr, opts) {
            var fnName = expr.substring(0, expr.length - 2);
            var fn = opts.functions[fnName];
            if (fn) {
                return {
                    method: Tracer.tracePredefinedFunctionGen(fn, expr)
                };
            } else {
                return {
                    method: Tracer.tracePostdefinedFunctionGen(expr)
                };
            }
        }
    }, {
        re: /^\$/,
        obj: function (expr, opts) {
            var internalOpts = processOptions({});
            var propertySteps = adapter.run(expr, internalOpts);
            return {
                method: Tracer.traceRootPathPropertyGen(propertySteps, internalOpts)
            };
        }
    }];

    adapter.exprToMethod = function (expr, opts) {
        var r = this.directMap[expr];
        if (r) {
            return r;
        }
        for (var i = 0; i < this.reMap.length; ++i) {
            var reItem = this.reMap[i];
            if (reItem.re.test(expr)) {
                return reItem.obj(expr, opts);
            }
        }
        return {
            method: Tracer.tracePropertyGen(expr)
        };
    };

    adapter.exprToMethodUpdateOptions = function (expr, opts) {
        var r = this.exprToMethod(expr, opts);
        if (r.branches && (opts.wrap === null)) {
            opts.wrap = true;
        }
        if (r.lookbackNeeded) {
            opts.lookbackNeeded = true;
        }
        if (r.pathNeeded) {
            opts.pathNeeded = true;
        }
        return r.method;
    };

    adapter.run = function (inputExpr, opts) {
        var normalizedExprList = normalize(inputExpr);
        var steps = normalizedExprList.map(function (expr) {
            return adapter.exprToMethodUpdateOptions(expr, opts);
        });
        steps.push(Tracer.traceEnd);
        return steps;
    };

    return adapter.run;
})();

exports.instance = function (inputExpr, opts) {

    opts = processOptions(opts);
    if ((opts.resultType !== 'value') && (opts.resultType !== 'path')) {
        throw new Error('Invalid option resultType: ' + opts.resultType);
    }
    opts.pathNeeded = (opts.resultType === 'path');
    opts.lookbackNeeded = false;
    opts.emptyValue = (opts.wrap === null) ? null : [];

    if (!inputExpr) {
        throw new Error('An input expression is required.');
    }

    var traceSteps = tracerAdapter(inputExpr, opts);
    if (traceSteps[0] !== Tracer.traceStart) {
        traceSteps.unshift(Tracer.traceStart);
    }

    return function (obj, context) {
        var tracer = Object.create(Tracer);
        tracer.init(obj, traceSteps, opts.sandbox, context);

        return tracer.run(opts);
    };
};

},{"../arrayset":37,"lodash":43,"vm":49}],40:[function(require,module,exports){
"use strict";

var lodash = require('lodash');

exports.isObject = lodash.isObject; // remove 1.5, leaving now just to be safe

var exists = exports.exists = function (obj) {
    return (obj !== undefined) && (obj !== null);
};

exports.deepValue = function (obj, deepProperty) {
    var propertyPieces = deepProperty.split('.');
    var n = propertyPieces.length;
    for (var i = 0; i < n; ++i) {
        if ((!exists(obj)) || (typeof obj !== 'object')) {
            return null;
        }
        var property = propertyPieces[i];
        obj = obj[property];
    }
    return (obj === undefined) ? null : obj;
};

},{"lodash":43}],41:[function(require,module,exports){
"use strict";

var lodash = require('lodash');

var object = require('./object');

exports.compact = function compact(obj) {
    if (typeof obj === 'object') {
        Object.keys(obj).forEach(function (key) {
            if (object.exists(obj[key])) {
                compact(obj[key]);
            } else {
                delete obj[key];
            }
        });
    }
};

exports.merge = lodash.merge; // remove 1.5, leaving now just to be safe

exports.deepValue = function (obj, deepProperty, value) {
    if ((!object.exists(obj)) || (typeof obj !== 'object')) {
        return null;
    }
    var currentObj = obj;
    var propertyPieces = deepProperty.split('.');
    var lastIndex = propertyPieces.length - 1;
    for (var i = 0; i < lastIndex; ++i) {
        var propertyPiece = propertyPieces[i];
        var nextObj = currentObj[propertyPiece];
        if ((!object.exists(nextObj)) || (typeof nextObj !== 'object')) {
            currentObj[propertyPiece] = nextObj = {};
        }
        currentObj = nextObj;
    }
    currentObj[propertyPieces[lastIndex]] = value;
    return obj;
};

},{"./object":40,"lodash":43}],42:[function(require,module,exports){
"use strict";

var object = require('./object');

var exists = object.exists;

var hasProperty = exports.hasProperty = function (deepProperty) {
    var propertyPieces = deepProperty.split('.');
    if (propertyPieces.length > 1) {
        return function (input) {
            if (!exists(input)) {
                return false;
            } else {
                return propertyPieces.every(function (piece) {
                    if (typeof input !== 'object') {
                        return false;
                    }
                    if (!input.hasOwnProperty(piece)) {
                        return false;
                    }
                    input = input[piece];
                    if (!exists(input)) {
                        return false;
                    } else {
                        return true;
                    }
                });
            }
        };
    } else {
        return function (input) {
            if (exists(input) && (typeof input === 'object')) {
                return input.hasOwnProperty(deepProperty);
            } else {
                return false;
            }
        };
    }
};

exports.hasNoProperty = function (deepProperty) {
    var f = hasProperty(deepProperty);
    return function (input) {
        return !f(input);
    };
};

exports.inValueSet = function (valueSet) {
    return function (input) {
        return (valueSet.indexOf(input) >= 0);
    };
};

exports.hasNoProperties = function (deepProperties) {
    var fns = deepProperties.map(function (p) {
        return hasProperty(p);
    });
    return function (input) {
        var some = fns.some(function (fn) {
            return fn(input);
        });
        return !some;
    };
};

exports.propertyValue = function (deepProperty) {
    return function (input) {
        return object.deepValue(input, deepProperty);
    };
};

exports.falsyPropertyValue = function (deepProperty) {
    return function (input) {
        return !object.deepValue(input, deepProperty);
    };
};

exports.and = function (fns) {
    return function (input) {
        return fns.every(function (fn) {
            return fn(input);
        });
    };
};

exports.or = function (fns) {
    return function (input) {
        return fns.some(function (fn) {
            return fn(input);
        });
    };
};

exports.not = function (fn) {
    return function (input) {
        return !fn(input);
    };
};

},{"./object":40}],43:[function(require,module,exports){
(function (global){
/**
 * @license
 * Lo-Dash 2.4.2 (Custom Build) <https://lodash.com/>
 * Build: `lodash modern -o ./dist/lodash.js`
 * Copyright 2012-2013 The Dojo Foundation <http://dojofoundation.org/>
 * Based on Underscore.js 1.5.2 <http://underscorejs.org/LICENSE>
 * Copyright 2009-2013 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
 * Available under MIT license <https://lodash.com/license>
 */
;(function() {

  /** Used as a safe reference for `undefined` in pre ES5 environments */
  var undefined;

  /** Used to pool arrays and objects used internally */
  var arrayPool = [],
      objectPool = [];

  /** Used to generate unique IDs */
  var idCounter = 0;

  /** Used to prefix keys to avoid issues with `__proto__` and properties on `Object.prototype` */
  var keyPrefix = +new Date + '';

  /** Used as the size when optimizations are enabled for large arrays */
  var largeArraySize = 75;

  /** Used as the max size of the `arrayPool` and `objectPool` */
  var maxPoolSize = 40;

  /** Used to detect and test whitespace */
  var whitespace = (
    // whitespace
    ' \t\x0B\f\xA0\ufeff' +

    // line terminators
    '\n\r\u2028\u2029' +

    // unicode category "Zs" space separators
    '\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u202f\u205f\u3000'
  );

  /** Used to match empty string literals in compiled template source */
  var reEmptyStringLeading = /\b__p \+= '';/g,
      reEmptyStringMiddle = /\b(__p \+=) '' \+/g,
      reEmptyStringTrailing = /(__e\(.*?\)|\b__t\)) \+\n'';/g;

  /**
   * Used to match ES6 template delimiters
   * http://people.mozilla.org/~jorendorff/es6-draft.html#sec-literals-string-literals
   */
  var reEsTemplate = /\$\{([^\\}]*(?:\\.[^\\}]*)*)\}/g;

  /** Used to match regexp flags from their coerced string values */
  var reFlags = /\w*$/;

  /** Used to detected named functions */
  var reFuncName = /^\s*function[ \n\r\t]+\w/;

  /** Used to match "interpolate" template delimiters */
  var reInterpolate = /<%=([\s\S]+?)%>/g;

  /** Used to match leading whitespace and zeros to be removed */
  var reLeadingSpacesAndZeros = RegExp('^[' + whitespace + ']*0+(?=.$)');

  /** Used to ensure capturing order of template delimiters */
  var reNoMatch = /($^)/;

  /** Used to detect functions containing a `this` reference */
  var reThis = /\bthis\b/;

  /** Used to match unescaped characters in compiled string literals */
  var reUnescapedString = /['\n\r\t\u2028\u2029\\]/g;

  /** Used to assign default `context` object properties */
  var contextProps = [
    'Array', 'Boolean', 'Date', 'Function', 'Math', 'Number', 'Object',
    'RegExp', 'String', '_', 'attachEvent', 'clearTimeout', 'isFinite', 'isNaN',
    'parseInt', 'setTimeout'
  ];

  /** Used to make template sourceURLs easier to identify */
  var templateCounter = 0;

  /** `Object#toString` result shortcuts */
  var argsClass = '[object Arguments]',
      arrayClass = '[object Array]',
      boolClass = '[object Boolean]',
      dateClass = '[object Date]',
      funcClass = '[object Function]',
      numberClass = '[object Number]',
      objectClass = '[object Object]',
      regexpClass = '[object RegExp]',
      stringClass = '[object String]';

  /** Used to identify object classifications that `_.clone` supports */
  var cloneableClasses = {};
  cloneableClasses[funcClass] = false;
  cloneableClasses[argsClass] = cloneableClasses[arrayClass] =
  cloneableClasses[boolClass] = cloneableClasses[dateClass] =
  cloneableClasses[numberClass] = cloneableClasses[objectClass] =
  cloneableClasses[regexpClass] = cloneableClasses[stringClass] = true;

  /** Used as an internal `_.debounce` options object */
  var debounceOptions = {
    'leading': false,
    'maxWait': 0,
    'trailing': false
  };

  /** Used as the property descriptor for `__bindData__` */
  var descriptor = {
    'configurable': false,
    'enumerable': false,
    'value': null,
    'writable': false
  };

  /** Used to determine if values are of the language type Object */
  var objectTypes = {
    'boolean': false,
    'function': true,
    'object': true,
    'number': false,
    'string': false,
    'undefined': false
  };

  /** Used to escape characters for inclusion in compiled string literals */
  var stringEscapes = {
    '\\': '\\',
    "'": "'",
    '\n': 'n',
    '\r': 'r',
    '\t': 't',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  /** Used as a reference to the global object */
  var root = (objectTypes[typeof window] && window) || this;

  /** Detect free variable `exports` */
  var freeExports = objectTypes[typeof exports] && exports && !exports.nodeType && exports;

  /** Detect free variable `module` */
  var freeModule = objectTypes[typeof module] && module && !module.nodeType && module;

  /** Detect the popular CommonJS extension `module.exports` */
  var moduleExports = freeModule && freeModule.exports === freeExports && freeExports;

  /** Detect free variable `global` from Node.js or Browserified code and use it as `root` */
  var freeGlobal = objectTypes[typeof global] && global;
  if (freeGlobal && (freeGlobal.global === freeGlobal || freeGlobal.window === freeGlobal)) {
    root = freeGlobal;
  }

  /*--------------------------------------------------------------------------*/

  /**
   * The base implementation of `_.indexOf` without support for binary searches
   * or `fromIndex` constraints.
   *
   * @private
   * @param {Array} array The array to search.
   * @param {*} value The value to search for.
   * @param {number} [fromIndex=0] The index to search from.
   * @returns {number} Returns the index of the matched value or `-1`.
   */
  function baseIndexOf(array, value, fromIndex) {
    var index = (fromIndex || 0) - 1,
        length = array ? array.length : 0;

    while (++index < length) {
      if (array[index] === value) {
        return index;
      }
    }
    return -1;
  }

  /**
   * An implementation of `_.contains` for cache objects that mimics the return
   * signature of `_.indexOf` by returning `0` if the value is found, else `-1`.
   *
   * @private
   * @param {Object} cache The cache object to inspect.
   * @param {*} value The value to search for.
   * @returns {number} Returns `0` if `value` is found, else `-1`.
   */
  function cacheIndexOf(cache, value) {
    var type = typeof value;
    cache = cache.cache;

    if (type == 'boolean' || value == null) {
      return cache[value] ? 0 : -1;
    }
    if (type != 'number' && type != 'string') {
      type = 'object';
    }
    var key = type == 'number' ? value : keyPrefix + value;
    cache = (cache = cache[type]) && cache[key];

    return type == 'object'
      ? (cache && baseIndexOf(cache, value) > -1 ? 0 : -1)
      : (cache ? 0 : -1);
  }

  /**
   * Adds a given value to the corresponding cache object.
   *
   * @private
   * @param {*} value The value to add to the cache.
   */
  function cachePush(value) {
    var cache = this.cache,
        type = typeof value;

    if (type == 'boolean' || value == null) {
      cache[value] = true;
    } else {
      if (type != 'number' && type != 'string') {
        type = 'object';
      }
      var key = type == 'number' ? value : keyPrefix + value,
          typeCache = cache[type] || (cache[type] = {});

      if (type == 'object') {
        (typeCache[key] || (typeCache[key] = [])).push(value);
      } else {
        typeCache[key] = true;
      }
    }
  }

  /**
   * Used by `_.max` and `_.min` as the default callback when a given
   * collection is a string value.
   *
   * @private
   * @param {string} value The character to inspect.
   * @returns {number} Returns the code unit of given character.
   */
  function charAtCallback(value) {
    return value.charCodeAt(0);
  }

  /**
   * Used by `sortBy` to compare transformed `collection` elements, stable sorting
   * them in ascending order.
   *
   * @private
   * @param {Object} a The object to compare to `b`.
   * @param {Object} b The object to compare to `a`.
   * @returns {number} Returns the sort order indicator of `1` or `-1`.
   */
  function compareAscending(a, b) {
    var ac = a.criteria,
        bc = b.criteria,
        index = -1,
        length = ac.length;

    while (++index < length) {
      var value = ac[index],
          other = bc[index];

      if (value !== other) {
        if (value > other || typeof value == 'undefined') {
          return 1;
        }
        if (value < other || typeof other == 'undefined') {
          return -1;
        }
      }
    }
    // Fixes an `Array#sort` bug in the JS engine embedded in Adobe applications
    // that causes it, under certain circumstances, to return the same value for
    // `a` and `b`. See https://github.com/jashkenas/underscore/pull/1247
    //
    // This also ensures a stable sort in V8 and other engines.
    // See http://code.google.com/p/v8/issues/detail?id=90
    return a.index - b.index;
  }

  /**
   * Creates a cache object to optimize linear searches of large arrays.
   *
   * @private
   * @param {Array} [array=[]] The array to search.
   * @returns {null|Object} Returns the cache object or `null` if caching should not be used.
   */
  function createCache(array) {
    var index = -1,
        length = array.length,
        first = array[0],
        mid = array[(length / 2) | 0],
        last = array[length - 1];

    if (first && typeof first == 'object' &&
        mid && typeof mid == 'object' && last && typeof last == 'object') {
      return false;
    }
    var cache = getObject();
    cache['false'] = cache['null'] = cache['true'] = cache['undefined'] = false;

    var result = getObject();
    result.array = array;
    result.cache = cache;
    result.push = cachePush;

    while (++index < length) {
      result.push(array[index]);
    }
    return result;
  }

  /**
   * Used by `template` to escape characters for inclusion in compiled
   * string literals.
   *
   * @private
   * @param {string} match The matched character to escape.
   * @returns {string} Returns the escaped character.
   */
  function escapeStringChar(match) {
    return '\\' + stringEscapes[match];
  }

  /**
   * Gets an array from the array pool or creates a new one if the pool is empty.
   *
   * @private
   * @returns {Array} The array from the pool.
   */
  function getArray() {
    return arrayPool.pop() || [];
  }

  /**
   * Gets an object from the object pool or creates a new one if the pool is empty.
   *
   * @private
   * @returns {Object} The object from the pool.
   */
  function getObject() {
    return objectPool.pop() || {
      'array': null,
      'cache': null,
      'criteria': null,
      'false': false,
      'index': 0,
      'null': false,
      'number': null,
      'object': null,
      'push': null,
      'string': null,
      'true': false,
      'undefined': false,
      'value': null
    };
  }

  /**
   * Releases the given array back to the array pool.
   *
   * @private
   * @param {Array} [array] The array to release.
   */
  function releaseArray(array) {
    array.length = 0;
    if (arrayPool.length < maxPoolSize) {
      arrayPool.push(array);
    }
  }

  /**
   * Releases the given object back to the object pool.
   *
   * @private
   * @param {Object} [object] The object to release.
   */
  function releaseObject(object) {
    var cache = object.cache;
    if (cache) {
      releaseObject(cache);
    }
    object.array = object.cache = object.criteria = object.object = object.number = object.string = object.value = null;
    if (objectPool.length < maxPoolSize) {
      objectPool.push(object);
    }
  }

  /**
   * Slices the `collection` from the `start` index up to, but not including,
   * the `end` index.
   *
   * Note: This function is used instead of `Array#slice` to support node lists
   * in IE < 9 and to ensure dense arrays are returned.
   *
   * @private
   * @param {Array|Object|string} collection The collection to slice.
   * @param {number} start The start index.
   * @param {number} end The end index.
   * @returns {Array} Returns the new array.
   */
  function slice(array, start, end) {
    start || (start = 0);
    if (typeof end == 'undefined') {
      end = array ? array.length : 0;
    }
    var index = -1,
        length = end - start || 0,
        result = Array(length < 0 ? 0 : length);

    while (++index < length) {
      result[index] = array[start + index];
    }
    return result;
  }

  /*--------------------------------------------------------------------------*/

  /**
   * Create a new `lodash` function using the given context object.
   *
   * @static
   * @memberOf _
   * @category Utilities
   * @param {Object} [context=root] The context object.
   * @returns {Function} Returns the `lodash` function.
   */
  function runInContext(context) {
    // Avoid issues with some ES3 environments that attempt to use values, named
    // after built-in constructors like `Object`, for the creation of literals.
    // ES5 clears this up by stating that literals must use built-in constructors.
    // See http://es5.github.io/#x11.1.5.
    context = context ? _.defaults(root.Object(), context, _.pick(root, contextProps)) : root;

    /** Native constructor references */
    var Array = context.Array,
        Boolean = context.Boolean,
        Date = context.Date,
        Function = context.Function,
        Math = context.Math,
        Number = context.Number,
        Object = context.Object,
        RegExp = context.RegExp,
        String = context.String,
        TypeError = context.TypeError;

    /**
     * Used for `Array` method references.
     *
     * Normally `Array.prototype` would suffice, however, using an array literal
     * avoids issues in Narwhal.
     */
    var arrayRef = [];

    /** Used for native method references */
    var objectProto = Object.prototype;

    /** Used to restore the original `_` reference in `noConflict` */
    var oldDash = context._;

    /** Used to resolve the internal [[Class]] of values */
    var toString = objectProto.toString;

    /** Used to detect if a method is native */
    var reNative = RegExp('^' +
      String(toString)
        .replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
        .replace(/toString| for [^\]]+/g, '.*?') + '$'
    );

    /** Native method shortcuts */
    var ceil = Math.ceil,
        clearTimeout = context.clearTimeout,
        floor = Math.floor,
        fnToString = Function.prototype.toString,
        getPrototypeOf = isNative(getPrototypeOf = Object.getPrototypeOf) && getPrototypeOf,
        hasOwnProperty = objectProto.hasOwnProperty,
        push = arrayRef.push,
        setTimeout = context.setTimeout,
        splice = arrayRef.splice,
        unshift = arrayRef.unshift;

    /** Used to set meta data on functions */
    var defineProperty = (function() {
      // IE 8 only accepts DOM elements
      try {
        var o = {},
            func = isNative(func = Object.defineProperty) && func,
            result = func(o, o, o) && func;
      } catch(e) { }
      return result;
    }());

    /* Native method shortcuts for methods with the same name as other `lodash` methods */
    var nativeCreate = isNative(nativeCreate = Object.create) && nativeCreate,
        nativeIsArray = isNative(nativeIsArray = Array.isArray) && nativeIsArray,
        nativeIsFinite = context.isFinite,
        nativeIsNaN = context.isNaN,
        nativeKeys = isNative(nativeKeys = Object.keys) && nativeKeys,
        nativeMax = Math.max,
        nativeMin = Math.min,
        nativeParseInt = context.parseInt,
        nativeRandom = Math.random;

    /** Used to lookup a built-in constructor by [[Class]] */
    var ctorByClass = {};
    ctorByClass[arrayClass] = Array;
    ctorByClass[boolClass] = Boolean;
    ctorByClass[dateClass] = Date;
    ctorByClass[funcClass] = Function;
    ctorByClass[objectClass] = Object;
    ctorByClass[numberClass] = Number;
    ctorByClass[regexpClass] = RegExp;
    ctorByClass[stringClass] = String;

    /*--------------------------------------------------------------------------*/

    /**
     * Creates a `lodash` object which wraps the given value to enable intuitive
     * method chaining.
     *
     * In addition to Lo-Dash methods, wrappers also have the following `Array` methods:
     * `concat`, `join`, `pop`, `push`, `reverse`, `shift`, `slice`, `sort`, `splice`,
     * and `unshift`
     *
     * Chaining is supported in custom builds as long as the `value` method is
     * implicitly or explicitly included in the build.
     *
     * The chainable wrapper functions are:
     * `after`, `assign`, `bind`, `bindAll`, `bindKey`, `chain`, `compact`,
     * `compose`, `concat`, `countBy`, `create`, `createCallback`, `curry`,
     * `debounce`, `defaults`, `defer`, `delay`, `difference`, `filter`, `flatten`,
     * `forEach`, `forEachRight`, `forIn`, `forInRight`, `forOwn`, `forOwnRight`,
     * `functions`, `groupBy`, `indexBy`, `initial`, `intersection`, `invert`,
     * `invoke`, `keys`, `map`, `max`, `memoize`, `merge`, `min`, `object`, `omit`,
     * `once`, `pairs`, `partial`, `partialRight`, `pick`, `pluck`, `pull`, `push`,
     * `range`, `reject`, `remove`, `rest`, `reverse`, `shuffle`, `slice`, `sort`,
     * `sortBy`, `splice`, `tap`, `throttle`, `times`, `toArray`, `transform`,
     * `union`, `uniq`, `unshift`, `unzip`, `values`, `where`, `without`, `wrap`,
     * and `zip`
     *
     * The non-chainable wrapper functions are:
     * `clone`, `cloneDeep`, `contains`, `escape`, `every`, `find`, `findIndex`,
     * `findKey`, `findLast`, `findLastIndex`, `findLastKey`, `has`, `identity`,
     * `indexOf`, `isArguments`, `isArray`, `isBoolean`, `isDate`, `isElement`,
     * `isEmpty`, `isEqual`, `isFinite`, `isFunction`, `isNaN`, `isNull`, `isNumber`,
     * `isObject`, `isPlainObject`, `isRegExp`, `isString`, `isUndefined`, `join`,
     * `lastIndexOf`, `mixin`, `noConflict`, `parseInt`, `pop`, `random`, `reduce`,
     * `reduceRight`, `result`, `shift`, `size`, `some`, `sortedIndex`, `runInContext`,
     * `template`, `unescape`, `uniqueId`, and `value`
     *
     * The wrapper functions `first` and `last` return wrapped values when `n` is
     * provided, otherwise they return unwrapped values.
     *
     * Explicit chaining can be enabled by using the `_.chain` method.
     *
     * @name _
     * @constructor
     * @category Chaining
     * @param {*} value The value to wrap in a `lodash` instance.
     * @returns {Object} Returns a `lodash` instance.
     * @example
     *
     * var wrapped = _([1, 2, 3]);
     *
     * // returns an unwrapped value
     * wrapped.reduce(function(sum, num) {
     *   return sum + num;
     * });
     * // => 6
     *
     * // returns a wrapped value
     * var squares = wrapped.map(function(num) {
     *   return num * num;
     * });
     *
     * _.isArray(squares);
     * // => false
     *
     * _.isArray(squares.value());
     * // => true
     */
    function lodash(value) {
      // don't wrap if already wrapped, even if wrapped by a different `lodash` constructor
      return (value && typeof value == 'object' && !isArray(value) && hasOwnProperty.call(value, '__wrapped__'))
       ? value
       : new lodashWrapper(value);
    }

    /**
     * A fast path for creating `lodash` wrapper objects.
     *
     * @private
     * @param {*} value The value to wrap in a `lodash` instance.
     * @param {boolean} chainAll A flag to enable chaining for all methods
     * @returns {Object} Returns a `lodash` instance.
     */
    function lodashWrapper(value, chainAll) {
      this.__chain__ = !!chainAll;
      this.__wrapped__ = value;
    }
    // ensure `new lodashWrapper` is an instance of `lodash`
    lodashWrapper.prototype = lodash.prototype;

    /**
     * An object used to flag environments features.
     *
     * @static
     * @memberOf _
     * @type Object
     */
    var support = lodash.support = {};

    /**
     * Detect if functions can be decompiled by `Function#toString`
     * (all but PS3 and older Opera mobile browsers & avoided in Windows 8 apps).
     *
     * @memberOf _.support
     * @type boolean
     */
    support.funcDecomp = !isNative(context.WinRTError) && reThis.test(runInContext);

    /**
     * Detect if `Function#name` is supported (all but IE).
     *
     * @memberOf _.support
     * @type boolean
     */
    support.funcNames = typeof Function.name == 'string';

    /**
     * By default, the template delimiters used by Lo-Dash are similar to those in
     * embedded Ruby (ERB). Change the following template settings to use alternative
     * delimiters.
     *
     * @static
     * @memberOf _
     * @type Object
     */
    lodash.templateSettings = {

      /**
       * Used to detect `data` property values to be HTML-escaped.
       *
       * @memberOf _.templateSettings
       * @type RegExp
       */
      'escape': /<%-([\s\S]+?)%>/g,

      /**
       * Used to detect code to be evaluated.
       *
       * @memberOf _.templateSettings
       * @type RegExp
       */
      'evaluate': /<%([\s\S]+?)%>/g,

      /**
       * Used to detect `data` property values to inject.
       *
       * @memberOf _.templateSettings
       * @type RegExp
       */
      'interpolate': reInterpolate,

      /**
       * Used to reference the data object in the template text.
       *
       * @memberOf _.templateSettings
       * @type string
       */
      'variable': '',

      /**
       * Used to import variables into the compiled template.
       *
       * @memberOf _.templateSettings
       * @type Object
       */
      'imports': {

        /**
         * A reference to the `lodash` function.
         *
         * @memberOf _.templateSettings.imports
         * @type Function
         */
        '_': lodash
      }
    };

    /*--------------------------------------------------------------------------*/

    /**
     * The base implementation of `_.bind` that creates the bound function and
     * sets its meta data.
     *
     * @private
     * @param {Array} bindData The bind data array.
     * @returns {Function} Returns the new bound function.
     */
    function baseBind(bindData) {
      var func = bindData[0],
          partialArgs = bindData[2],
          thisArg = bindData[4];

      function bound() {
        // `Function#bind` spec
        // http://es5.github.io/#x15.3.4.5
        if (partialArgs) {
          // avoid `arguments` object deoptimizations by using `slice` instead
          // of `Array.prototype.slice.call` and not assigning `arguments` to a
          // variable as a ternary expression
          var args = slice(partialArgs);
          push.apply(args, arguments);
        }
        // mimic the constructor's `return` behavior
        // http://es5.github.io/#x13.2.2
        if (this instanceof bound) {
          // ensure `new bound` is an instance of `func`
          var thisBinding = baseCreate(func.prototype),
              result = func.apply(thisBinding, args || arguments);
          return isObject(result) ? result : thisBinding;
        }
        return func.apply(thisArg, args || arguments);
      }
      setBindData(bound, bindData);
      return bound;
    }

    /**
     * The base implementation of `_.clone` without argument juggling or support
     * for `thisArg` binding.
     *
     * @private
     * @param {*} value The value to clone.
     * @param {boolean} [isDeep=false] Specify a deep clone.
     * @param {Function} [callback] The function to customize cloning values.
     * @param {Array} [stackA=[]] Tracks traversed source objects.
     * @param {Array} [stackB=[]] Associates clones with source counterparts.
     * @returns {*} Returns the cloned value.
     */
    function baseClone(value, isDeep, callback, stackA, stackB) {
      if (callback) {
        var result = callback(value);
        if (typeof result != 'undefined') {
          return result;
        }
      }
      // inspect [[Class]]
      var isObj = isObject(value);
      if (isObj) {
        var className = toString.call(value);
        if (!cloneableClasses[className]) {
          return value;
        }
        var ctor = ctorByClass[className];
        switch (className) {
          case boolClass:
          case dateClass:
            return new ctor(+value);

          case numberClass:
          case stringClass:
            return new ctor(value);

          case regexpClass:
            result = ctor(value.source, reFlags.exec(value));
            result.lastIndex = value.lastIndex;
            return result;
        }
      } else {
        return value;
      }
      var isArr = isArray(value);
      if (isDeep) {
        // check for circular references and return corresponding clone
        var initedStack = !stackA;
        stackA || (stackA = getArray());
        stackB || (stackB = getArray());

        var length = stackA.length;
        while (length--) {
          if (stackA[length] == value) {
            return stackB[length];
          }
        }
        result = isArr ? ctor(value.length) : {};
      }
      else {
        result = isArr ? slice(value) : assign({}, value);
      }
      // add array properties assigned by `RegExp#exec`
      if (isArr) {
        if (hasOwnProperty.call(value, 'index')) {
          result.index = value.index;
        }
        if (hasOwnProperty.call(value, 'input')) {
          result.input = value.input;
        }
      }
      // exit for shallow clone
      if (!isDeep) {
        return result;
      }
      // add the source value to the stack of traversed objects
      // and associate it with its clone
      stackA.push(value);
      stackB.push(result);

      // recursively populate clone (susceptible to call stack limits)
      (isArr ? forEach : forOwn)(value, function(objValue, key) {
        result[key] = baseClone(objValue, isDeep, callback, stackA, stackB);
      });

      if (initedStack) {
        releaseArray(stackA);
        releaseArray(stackB);
      }
      return result;
    }

    /**
     * The base implementation of `_.create` without support for assigning
     * properties to the created object.
     *
     * @private
     * @param {Object} prototype The object to inherit from.
     * @returns {Object} Returns the new object.
     */
    function baseCreate(prototype, properties) {
      return isObject(prototype) ? nativeCreate(prototype) : {};
    }
    // fallback for browsers without `Object.create`
    if (!nativeCreate) {
      baseCreate = (function() {
        function Object() {}
        return function(prototype) {
          if (isObject(prototype)) {
            Object.prototype = prototype;
            var result = new Object;
            Object.prototype = null;
          }
          return result || context.Object();
        };
      }());
    }

    /**
     * The base implementation of `_.createCallback` without support for creating
     * "_.pluck" or "_.where" style callbacks.
     *
     * @private
     * @param {*} [func=identity] The value to convert to a callback.
     * @param {*} [thisArg] The `this` binding of the created callback.
     * @param {number} [argCount] The number of arguments the callback accepts.
     * @returns {Function} Returns a callback function.
     */
    function baseCreateCallback(func, thisArg, argCount) {
      if (typeof func != 'function') {
        return identity;
      }
      // exit early for no `thisArg` or already bound by `Function#bind`
      if (typeof thisArg == 'undefined' || !('prototype' in func)) {
        return func;
      }
      var bindData = func.__bindData__;
      if (typeof bindData == 'undefined') {
        if (support.funcNames) {
          bindData = !func.name;
        }
        bindData = bindData || !support.funcDecomp;
        if (!bindData) {
          var source = fnToString.call(func);
          if (!support.funcNames) {
            bindData = !reFuncName.test(source);
          }
          if (!bindData) {
            // checks if `func` references the `this` keyword and stores the result
            bindData = reThis.test(source);
            setBindData(func, bindData);
          }
        }
      }
      // exit early if there are no `this` references or `func` is bound
      if (bindData === false || (bindData !== true && bindData[1] & 1)) {
        return func;
      }
      switch (argCount) {
        case 1: return function(value) {
          return func.call(thisArg, value);
        };
        case 2: return function(a, b) {
          return func.call(thisArg, a, b);
        };
        case 3: return function(value, index, collection) {
          return func.call(thisArg, value, index, collection);
        };
        case 4: return function(accumulator, value, index, collection) {
          return func.call(thisArg, accumulator, value, index, collection);
        };
      }
      return bind(func, thisArg);
    }

    /**
     * The base implementation of `createWrapper` that creates the wrapper and
     * sets its meta data.
     *
     * @private
     * @param {Array} bindData The bind data array.
     * @returns {Function} Returns the new function.
     */
    function baseCreateWrapper(bindData) {
      var func = bindData[0],
          bitmask = bindData[1],
          partialArgs = bindData[2],
          partialRightArgs = bindData[3],
          thisArg = bindData[4],
          arity = bindData[5];

      var isBind = bitmask & 1,
          isBindKey = bitmask & 2,
          isCurry = bitmask & 4,
          isCurryBound = bitmask & 8,
          key = func;

      function bound() {
        var thisBinding = isBind ? thisArg : this;
        if (partialArgs) {
          var args = slice(partialArgs);
          push.apply(args, arguments);
        }
        if (partialRightArgs || isCurry) {
          args || (args = slice(arguments));
          if (partialRightArgs) {
            push.apply(args, partialRightArgs);
          }
          if (isCurry && args.length < arity) {
            bitmask |= 16 & ~32;
            return baseCreateWrapper([func, (isCurryBound ? bitmask : bitmask & ~3), args, null, thisArg, arity]);
          }
        }
        args || (args = arguments);
        if (isBindKey) {
          func = thisBinding[key];
        }
        if (this instanceof bound) {
          thisBinding = baseCreate(func.prototype);
          var result = func.apply(thisBinding, args);
          return isObject(result) ? result : thisBinding;
        }
        return func.apply(thisBinding, args);
      }
      setBindData(bound, bindData);
      return bound;
    }

    /**
     * The base implementation of `_.difference` that accepts a single array
     * of values to exclude.
     *
     * @private
     * @param {Array} array The array to process.
     * @param {Array} [values] The array of values to exclude.
     * @returns {Array} Returns a new array of filtered values.
     */
    function baseDifference(array, values) {
      var index = -1,
          indexOf = getIndexOf(),
          length = array ? array.length : 0,
          isLarge = length >= largeArraySize && indexOf === baseIndexOf,
          result = [];

      if (isLarge) {
        var cache = createCache(values);
        if (cache) {
          indexOf = cacheIndexOf;
          values = cache;
        } else {
          isLarge = false;
        }
      }
      while (++index < length) {
        var value = array[index];
        if (indexOf(values, value) < 0) {
          result.push(value);
        }
      }
      if (isLarge) {
        releaseObject(values);
      }
      return result;
    }

    /**
     * The base implementation of `_.flatten` without support for callback
     * shorthands or `thisArg` binding.
     *
     * @private
     * @param {Array} array The array to flatten.
     * @param {boolean} [isShallow=false] A flag to restrict flattening to a single level.
     * @param {boolean} [isStrict=false] A flag to restrict flattening to arrays and `arguments` objects.
     * @param {number} [fromIndex=0] The index to start from.
     * @returns {Array} Returns a new flattened array.
     */
    function baseFlatten(array, isShallow, isStrict, fromIndex) {
      var index = (fromIndex || 0) - 1,
          length = array ? array.length : 0,
          result = [];

      while (++index < length) {
        var value = array[index];

        if (value && typeof value == 'object' && typeof value.length == 'number'
            && (isArray(value) || isArguments(value))) {
          // recursively flatten arrays (susceptible to call stack limits)
          if (!isShallow) {
            value = baseFlatten(value, isShallow, isStrict);
          }
          var valIndex = -1,
              valLength = value.length,
              resIndex = result.length;

          result.length += valLength;
          while (++valIndex < valLength) {
            result[resIndex++] = value[valIndex];
          }
        } else if (!isStrict) {
          result.push(value);
        }
      }
      return result;
    }

    /**
     * The base implementation of `_.isEqual`, without support for `thisArg` binding,
     * that allows partial "_.where" style comparisons.
     *
     * @private
     * @param {*} a The value to compare.
     * @param {*} b The other value to compare.
     * @param {Function} [callback] The function to customize comparing values.
     * @param {Function} [isWhere=false] A flag to indicate performing partial comparisons.
     * @param {Array} [stackA=[]] Tracks traversed `a` objects.
     * @param {Array} [stackB=[]] Tracks traversed `b` objects.
     * @returns {boolean} Returns `true` if the values are equivalent, else `false`.
     */
    function baseIsEqual(a, b, callback, isWhere, stackA, stackB) {
      // used to indicate that when comparing objects, `a` has at least the properties of `b`
      if (callback) {
        var result = callback(a, b);
        if (typeof result != 'undefined') {
          return !!result;
        }
      }
      // exit early for identical values
      if (a === b) {
        // treat `+0` vs. `-0` as not equal
        return a !== 0 || (1 / a == 1 / b);
      }
      var type = typeof a,
          otherType = typeof b;

      // exit early for unlike primitive values
      if (a === a &&
          !(a && objectTypes[type]) &&
          !(b && objectTypes[otherType])) {
        return false;
      }
      // exit early for `null` and `undefined` avoiding ES3's Function#call behavior
      // http://es5.github.io/#x15.3.4.4
      if (a == null || b == null) {
        return a === b;
      }
      // compare [[Class]] names
      var className = toString.call(a),
          otherClass = toString.call(b);

      if (className == argsClass) {
        className = objectClass;
      }
      if (otherClass == argsClass) {
        otherClass = objectClass;
      }
      if (className != otherClass) {
        return false;
      }
      switch (className) {
        case boolClass:
        case dateClass:
          // coerce dates and booleans to numbers, dates to milliseconds and booleans
          // to `1` or `0` treating invalid dates coerced to `NaN` as not equal
          return +a == +b;

        case numberClass:
          // treat `NaN` vs. `NaN` as equal
          return (a != +a)
            ? b != +b
            // but treat `+0` vs. `-0` as not equal
            : (a == 0 ? (1 / a == 1 / b) : a == +b);

        case regexpClass:
        case stringClass:
          // coerce regexes to strings (http://es5.github.io/#x15.10.6.4)
          // treat string primitives and their corresponding object instances as equal
          return a == String(b);
      }
      var isArr = className == arrayClass;
      if (!isArr) {
        // unwrap any `lodash` wrapped values
        var aWrapped = hasOwnProperty.call(a, '__wrapped__'),
            bWrapped = hasOwnProperty.call(b, '__wrapped__');

        if (aWrapped || bWrapped) {
          return baseIsEqual(aWrapped ? a.__wrapped__ : a, bWrapped ? b.__wrapped__ : b, callback, isWhere, stackA, stackB);
        }
        // exit for functions and DOM nodes
        if (className != objectClass) {
          return false;
        }
        // in older versions of Opera, `arguments` objects have `Array` constructors
        var ctorA = a.constructor,
            ctorB = b.constructor;

        // non `Object` object instances with different constructors are not equal
        if (ctorA != ctorB &&
              !(isFunction(ctorA) && ctorA instanceof ctorA && isFunction(ctorB) && ctorB instanceof ctorB) &&
              ('constructor' in a && 'constructor' in b)
            ) {
          return false;
        }
      }
      // assume cyclic structures are equal
      // the algorithm for detecting cyclic structures is adapted from ES 5.1
      // section 15.12.3, abstract operation `JO` (http://es5.github.io/#x15.12.3)
      var initedStack = !stackA;
      stackA || (stackA = getArray());
      stackB || (stackB = getArray());

      var length = stackA.length;
      while (length--) {
        if (stackA[length] == a) {
          return stackB[length] == b;
        }
      }
      var size = 0;
      result = true;

      // add `a` and `b` to the stack of traversed objects
      stackA.push(a);
      stackB.push(b);

      // recursively compare objects and arrays (susceptible to call stack limits)
      if (isArr) {
        // compare lengths to determine if a deep comparison is necessary
        length = a.length;
        size = b.length;
        result = size == length;

        if (result || isWhere) {
          // deep compare the contents, ignoring non-numeric properties
          while (size--) {
            var index = length,
                value = b[size];

            if (isWhere) {
              while (index--) {
                if ((result = baseIsEqual(a[index], value, callback, isWhere, stackA, stackB))) {
                  break;
                }
              }
            } else if (!(result = baseIsEqual(a[size], value, callback, isWhere, stackA, stackB))) {
              break;
            }
          }
        }
      }
      else {
        // deep compare objects using `forIn`, instead of `forOwn`, to avoid `Object.keys`
        // which, in this case, is more costly
        forIn(b, function(value, key, b) {
          if (hasOwnProperty.call(b, key)) {
            // count the number of properties.
            size++;
            // deep compare each property value.
            return (result = hasOwnProperty.call(a, key) && baseIsEqual(a[key], value, callback, isWhere, stackA, stackB));
          }
        });

        if (result && !isWhere) {
          // ensure both objects have the same number of properties
          forIn(a, function(value, key, a) {
            if (hasOwnProperty.call(a, key)) {
              // `size` will be `-1` if `a` has more properties than `b`
              return (result = --size > -1);
            }
          });
        }
      }
      stackA.pop();
      stackB.pop();

      if (initedStack) {
        releaseArray(stackA);
        releaseArray(stackB);
      }
      return result;
    }

    /**
     * The base implementation of `_.merge` without argument juggling or support
     * for `thisArg` binding.
     *
     * @private
     * @param {Object} object The destination object.
     * @param {Object} source The source object.
     * @param {Function} [callback] The function to customize merging properties.
     * @param {Array} [stackA=[]] Tracks traversed source objects.
     * @param {Array} [stackB=[]] Associates values with source counterparts.
     */
    function baseMerge(object, source, callback, stackA, stackB) {
      (isArray(source) ? forEach : forOwn)(source, function(source, key) {
        var found,
            isArr,
            result = source,
            value = object[key];

        if (source && ((isArr = isArray(source)) || isPlainObject(source))) {
          // avoid merging previously merged cyclic sources
          var stackLength = stackA.length;
          while (stackLength--) {
            if ((found = stackA[stackLength] == source)) {
              value = stackB[stackLength];
              break;
            }
          }
          if (!found) {
            var isShallow;
            if (callback) {
              result = callback(value, source);
              if ((isShallow = typeof result != 'undefined')) {
                value = result;
              }
            }
            if (!isShallow) {
              value = isArr
                ? (isArray(value) ? value : [])
                : (isPlainObject(value) ? value : {});
            }
            // add `source` and associated `value` to the stack of traversed objects
            stackA.push(source);
            stackB.push(value);

            // recursively merge objects and arrays (susceptible to call stack limits)
            if (!isShallow) {
              baseMerge(value, source, callback, stackA, stackB);
            }
          }
        }
        else {
          if (callback) {
            result = callback(value, source);
            if (typeof result == 'undefined') {
              result = source;
            }
          }
          if (typeof result != 'undefined') {
            value = result;
          }
        }
        object[key] = value;
      });
    }

    /**
     * The base implementation of `_.random` without argument juggling or support
     * for returning floating-point numbers.
     *
     * @private
     * @param {number} min The minimum possible value.
     * @param {number} max The maximum possible value.
     * @returns {number} Returns a random number.
     */
    function baseRandom(min, max) {
      return min + floor(nativeRandom() * (max - min + 1));
    }

    /**
     * The base implementation of `_.uniq` without support for callback shorthands
     * or `thisArg` binding.
     *
     * @private
     * @param {Array} array The array to process.
     * @param {boolean} [isSorted=false] A flag to indicate that `array` is sorted.
     * @param {Function} [callback] The function called per iteration.
     * @returns {Array} Returns a duplicate-value-free array.
     */
    function baseUniq(array, isSorted, callback) {
      var index = -1,
          indexOf = getIndexOf(),
          length = array ? array.length : 0,
          result = [];

      var isLarge = !isSorted && length >= largeArraySize && indexOf === baseIndexOf,
          seen = (callback || isLarge) ? getArray() : result;

      if (isLarge) {
        var cache = createCache(seen);
        indexOf = cacheIndexOf;
        seen = cache;
      }
      while (++index < length) {
        var value = array[index],
            computed = callback ? callback(value, index, array) : value;

        if (isSorted
              ? !index || seen[seen.length - 1] !== computed
              : indexOf(seen, computed) < 0
            ) {
          if (callback || isLarge) {
            seen.push(computed);
          }
          result.push(value);
        }
      }
      if (isLarge) {
        releaseArray(seen.array);
        releaseObject(seen);
      } else if (callback) {
        releaseArray(seen);
      }
      return result;
    }

    /**
     * Creates a function that aggregates a collection, creating an object composed
     * of keys generated from the results of running each element of the collection
     * through a callback. The given `setter` function sets the keys and values
     * of the composed object.
     *
     * @private
     * @param {Function} setter The setter function.
     * @returns {Function} Returns the new aggregator function.
     */
    function createAggregator(setter) {
      return function(collection, callback, thisArg) {
        var result = {};
        callback = lodash.createCallback(callback, thisArg, 3);

        var index = -1,
            length = collection ? collection.length : 0;

        if (typeof length == 'number') {
          while (++index < length) {
            var value = collection[index];
            setter(result, value, callback(value, index, collection), collection);
          }
        } else {
          forOwn(collection, function(value, key, collection) {
            setter(result, value, callback(value, key, collection), collection);
          });
        }
        return result;
      };
    }

    /**
     * Creates a function that, when called, either curries or invokes `func`
     * with an optional `this` binding and partially applied arguments.
     *
     * @private
     * @param {Function|string} func The function or method name to reference.
     * @param {number} bitmask The bitmask of method flags to compose.
     *  The bitmask may be composed of the following flags:
     *  1 - `_.bind`
     *  2 - `_.bindKey`
     *  4 - `_.curry`
     *  8 - `_.curry` (bound)
     *  16 - `_.partial`
     *  32 - `_.partialRight`
     * @param {Array} [partialArgs] An array of arguments to prepend to those
     *  provided to the new function.
     * @param {Array} [partialRightArgs] An array of arguments to append to those
     *  provided to the new function.
     * @param {*} [thisArg] The `this` binding of `func`.
     * @param {number} [arity] The arity of `func`.
     * @returns {Function} Returns the new function.
     */
    function createWrapper(func, bitmask, partialArgs, partialRightArgs, thisArg, arity) {
      var isBind = bitmask & 1,
          isBindKey = bitmask & 2,
          isCurry = bitmask & 4,
          isCurryBound = bitmask & 8,
          isPartial = bitmask & 16,
          isPartialRight = bitmask & 32;

      if (!isBindKey && !isFunction(func)) {
        throw new TypeError;
      }
      if (isPartial && !partialArgs.length) {
        bitmask &= ~16;
        isPartial = partialArgs = false;
      }
      if (isPartialRight && !partialRightArgs.length) {
        bitmask &= ~32;
        isPartialRight = partialRightArgs = false;
      }
      var bindData = func && func.__bindData__;
      if (bindData && bindData !== true) {
        // clone `bindData`
        bindData = slice(bindData);
        if (bindData[2]) {
          bindData[2] = slice(bindData[2]);
        }
        if (bindData[3]) {
          bindData[3] = slice(bindData[3]);
        }
        // set `thisBinding` is not previously bound
        if (isBind && !(bindData[1] & 1)) {
          bindData[4] = thisArg;
        }
        // set if previously bound but not currently (subsequent curried functions)
        if (!isBind && bindData[1] & 1) {
          bitmask |= 8;
        }
        // set curried arity if not yet set
        if (isCurry && !(bindData[1] & 4)) {
          bindData[5] = arity;
        }
        // append partial left arguments
        if (isPartial) {
          push.apply(bindData[2] || (bindData[2] = []), partialArgs);
        }
        // append partial right arguments
        if (isPartialRight) {
          unshift.apply(bindData[3] || (bindData[3] = []), partialRightArgs);
        }
        // merge flags
        bindData[1] |= bitmask;
        return createWrapper.apply(null, bindData);
      }
      // fast path for `_.bind`
      var creater = (bitmask == 1 || bitmask === 17) ? baseBind : baseCreateWrapper;
      return creater([func, bitmask, partialArgs, partialRightArgs, thisArg, arity]);
    }

    /**
     * Used by `escape` to convert characters to HTML entities.
     *
     * @private
     * @param {string} match The matched character to escape.
     * @returns {string} Returns the escaped character.
     */
    function escapeHtmlChar(match) {
      return htmlEscapes[match];
    }

    /**
     * Gets the appropriate "indexOf" function. If the `_.indexOf` method is
     * customized, this method returns the custom method, otherwise it returns
     * the `baseIndexOf` function.
     *
     * @private
     * @returns {Function} Returns the "indexOf" function.
     */
    function getIndexOf() {
      var result = (result = lodash.indexOf) === indexOf ? baseIndexOf : result;
      return result;
    }

    /**
     * Checks if `value` is a native function.
     *
     * @private
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a native function, else `false`.
     */
    function isNative(value) {
      return typeof value == 'function' && reNative.test(value);
    }

    /**
     * Sets `this` binding data on a given function.
     *
     * @private
     * @param {Function} func The function to set data on.
     * @param {Array} value The data array to set.
     */
    var setBindData = !defineProperty ? noop : function(func, value) {
      descriptor.value = value;
      defineProperty(func, '__bindData__', descriptor);
      descriptor.value = null;
    };

    /**
     * A fallback implementation of `isPlainObject` which checks if a given value
     * is an object created by the `Object` constructor, assuming objects created
     * by the `Object` constructor have no inherited enumerable properties and that
     * there are no `Object.prototype` extensions.
     *
     * @private
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
     */
    function shimIsPlainObject(value) {
      var ctor,
          result;

      // avoid non Object objects, `arguments` objects, and DOM elements
      if (!(value && toString.call(value) == objectClass) ||
          (ctor = value.constructor, isFunction(ctor) && !(ctor instanceof ctor))) {
        return false;
      }
      // In most environments an object's own properties are iterated before
      // its inherited properties. If the last iterated property is an object's
      // own property then there are no inherited enumerable properties.
      forIn(value, function(value, key) {
        result = key;
      });
      return typeof result == 'undefined' || hasOwnProperty.call(value, result);
    }

    /**
     * Used by `unescape` to convert HTML entities to characters.
     *
     * @private
     * @param {string} match The matched character to unescape.
     * @returns {string} Returns the unescaped character.
     */
    function unescapeHtmlChar(match) {
      return htmlUnescapes[match];
    }

    /*--------------------------------------------------------------------------*/

    /**
     * Checks if `value` is an `arguments` object.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is an `arguments` object, else `false`.
     * @example
     *
     * (function() { return _.isArguments(arguments); })(1, 2, 3);
     * // => true
     *
     * _.isArguments([1, 2, 3]);
     * // => false
     */
    function isArguments(value) {
      return value && typeof value == 'object' && typeof value.length == 'number' &&
        toString.call(value) == argsClass || false;
    }

    /**
     * Checks if `value` is an array.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is an array, else `false`.
     * @example
     *
     * (function() { return _.isArray(arguments); })();
     * // => false
     *
     * _.isArray([1, 2, 3]);
     * // => true
     */
    var isArray = nativeIsArray || function(value) {
      return value && typeof value == 'object' && typeof value.length == 'number' &&
        toString.call(value) == arrayClass || false;
    };

    /**
     * A fallback implementation of `Object.keys` which produces an array of the
     * given object's own enumerable property names.
     *
     * @private
     * @type Function
     * @param {Object} object The object to inspect.
     * @returns {Array} Returns an array of property names.
     */
    var shimKeys = function(object) {
      var index, iterable = object, result = [];
      if (!iterable) return result;
      if (!(objectTypes[typeof object])) return result;
        for (index in iterable) {
          if (hasOwnProperty.call(iterable, index)) {
            result.push(index);
          }
        }
      return result
    };

    /**
     * Creates an array composed of the own enumerable property names of an object.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to inspect.
     * @returns {Array} Returns an array of property names.
     * @example
     *
     * _.keys({ 'one': 1, 'two': 2, 'three': 3 });
     * // => ['one', 'two', 'three'] (property order is not guaranteed across environments)
     */
    var keys = !nativeKeys ? shimKeys : function(object) {
      if (!isObject(object)) {
        return [];
      }
      return nativeKeys(object);
    };

    /**
     * Used to convert characters to HTML entities:
     *
     * Though the `>` character is escaped for symmetry, characters like `>` and `/`
     * don't require escaping in HTML and have no special meaning unless they're part
     * of a tag or an unquoted attribute value.
     * http://mathiasbynens.be/notes/ambiguous-ampersands (under "semi-related fun fact")
     */
    var htmlEscapes = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    };

    /** Used to convert HTML entities to characters */
    var htmlUnescapes = invert(htmlEscapes);

    /** Used to match HTML entities and HTML characters */
    var reEscapedHtml = RegExp('(' + keys(htmlUnescapes).join('|') + ')', 'g'),
        reUnescapedHtml = RegExp('[' + keys(htmlEscapes).join('') + ']', 'g');

    /*--------------------------------------------------------------------------*/

    /**
     * Assigns own enumerable properties of source object(s) to the destination
     * object. Subsequent sources will overwrite property assignments of previous
     * sources. If a callback is provided it will be executed to produce the
     * assigned values. The callback is bound to `thisArg` and invoked with two
     * arguments; (objectValue, sourceValue).
     *
     * @static
     * @memberOf _
     * @type Function
     * @alias extend
     * @category Objects
     * @param {Object} object The destination object.
     * @param {...Object} [source] The source objects.
     * @param {Function} [callback] The function to customize assigning values.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns the destination object.
     * @example
     *
     * _.assign({ 'name': 'fred' }, { 'employer': 'slate' });
     * // => { 'name': 'fred', 'employer': 'slate' }
     *
     * var defaults = _.partialRight(_.assign, function(a, b) {
     *   return typeof a == 'undefined' ? b : a;
     * });
     *
     * var object = { 'name': 'barney' };
     * defaults(object, { 'name': 'fred', 'employer': 'slate' });
     * // => { 'name': 'barney', 'employer': 'slate' }
     */
    var assign = function(object, source, guard) {
      var index, iterable = object, result = iterable;
      if (!iterable) return result;
      var args = arguments,
          argsIndex = 0,
          argsLength = typeof guard == 'number' ? 2 : args.length;
      if (argsLength > 3 && typeof args[argsLength - 2] == 'function') {
        var callback = baseCreateCallback(args[--argsLength - 1], args[argsLength--], 2);
      } else if (argsLength > 2 && typeof args[argsLength - 1] == 'function') {
        callback = args[--argsLength];
      }
      while (++argsIndex < argsLength) {
        iterable = args[argsIndex];
        if (iterable && objectTypes[typeof iterable]) {
        var ownIndex = -1,
            ownProps = objectTypes[typeof iterable] && keys(iterable),
            length = ownProps ? ownProps.length : 0;

        while (++ownIndex < length) {
          index = ownProps[ownIndex];
          result[index] = callback ? callback(result[index], iterable[index]) : iterable[index];
        }
        }
      }
      return result
    };

    /**
     * Creates a clone of `value`. If `isDeep` is `true` nested objects will also
     * be cloned, otherwise they will be assigned by reference. If a callback
     * is provided it will be executed to produce the cloned values. If the
     * callback returns `undefined` cloning will be handled by the method instead.
     * The callback is bound to `thisArg` and invoked with one argument; (value).
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to clone.
     * @param {boolean} [isDeep=false] Specify a deep clone.
     * @param {Function} [callback] The function to customize cloning values.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the cloned value.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * var shallow = _.clone(characters);
     * shallow[0] === characters[0];
     * // => true
     *
     * var deep = _.clone(characters, true);
     * deep[0] === characters[0];
     * // => false
     *
     * _.mixin({
     *   'clone': _.partialRight(_.clone, function(value) {
     *     return _.isElement(value) ? value.cloneNode(false) : undefined;
     *   })
     * });
     *
     * var clone = _.clone(document.body);
     * clone.childNodes.length;
     * // => 0
     */
    function clone(value, isDeep, callback, thisArg) {
      // allows working with "Collections" methods without using their `index`
      // and `collection` arguments for `isDeep` and `callback`
      if (typeof isDeep != 'boolean' && isDeep != null) {
        thisArg = callback;
        callback = isDeep;
        isDeep = false;
      }
      return baseClone(value, isDeep, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 1));
    }

    /**
     * Creates a deep clone of `value`. If a callback is provided it will be
     * executed to produce the cloned values. If the callback returns `undefined`
     * cloning will be handled by the method instead. The callback is bound to
     * `thisArg` and invoked with one argument; (value).
     *
     * Note: This method is loosely based on the structured clone algorithm. Functions
     * and DOM nodes are **not** cloned. The enumerable properties of `arguments` objects and
     * objects created by constructors other than `Object` are cloned to plain `Object` objects.
     * See http://www.w3.org/TR/html5/infrastructure.html#internal-structured-cloning-algorithm.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to deep clone.
     * @param {Function} [callback] The function to customize cloning values.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the deep cloned value.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * var deep = _.cloneDeep(characters);
     * deep[0] === characters[0];
     * // => false
     *
     * var view = {
     *   'label': 'docs',
     *   'node': element
     * };
     *
     * var clone = _.cloneDeep(view, function(value) {
     *   return _.isElement(value) ? value.cloneNode(true) : undefined;
     * });
     *
     * clone.node == view.node;
     * // => false
     */
    function cloneDeep(value, callback, thisArg) {
      return baseClone(value, true, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 1));
    }

    /**
     * Creates an object that inherits from the given `prototype` object. If a
     * `properties` object is provided its own enumerable properties are assigned
     * to the created object.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} prototype The object to inherit from.
     * @param {Object} [properties] The properties to assign to the object.
     * @returns {Object} Returns the new object.
     * @example
     *
     * function Shape() {
     *   this.x = 0;
     *   this.y = 0;
     * }
     *
     * function Circle() {
     *   Shape.call(this);
     * }
     *
     * Circle.prototype = _.create(Shape.prototype, { 'constructor': Circle });
     *
     * var circle = new Circle;
     * circle instanceof Circle;
     * // => true
     *
     * circle instanceof Shape;
     * // => true
     */
    function create(prototype, properties) {
      var result = baseCreate(prototype);
      return properties ? assign(result, properties) : result;
    }

    /**
     * Assigns own enumerable properties of source object(s) to the destination
     * object for all destination properties that resolve to `undefined`. Once a
     * property is set, additional defaults of the same property will be ignored.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Objects
     * @param {Object} object The destination object.
     * @param {...Object} [source] The source objects.
     * @param- {Object} [guard] Allows working with `_.reduce` without using its
     *  `key` and `object` arguments as sources.
     * @returns {Object} Returns the destination object.
     * @example
     *
     * var object = { 'name': 'barney' };
     * _.defaults(object, { 'name': 'fred', 'employer': 'slate' });
     * // => { 'name': 'barney', 'employer': 'slate' }
     */
    var defaults = function(object, source, guard) {
      var index, iterable = object, result = iterable;
      if (!iterable) return result;
      var args = arguments,
          argsIndex = 0,
          argsLength = typeof guard == 'number' ? 2 : args.length;
      while (++argsIndex < argsLength) {
        iterable = args[argsIndex];
        if (iterable && objectTypes[typeof iterable]) {
        var ownIndex = -1,
            ownProps = objectTypes[typeof iterable] && keys(iterable),
            length = ownProps ? ownProps.length : 0;

        while (++ownIndex < length) {
          index = ownProps[ownIndex];
          if (typeof result[index] == 'undefined') result[index] = iterable[index];
        }
        }
      }
      return result
    };

    /**
     * This method is like `_.findIndex` except that it returns the key of the
     * first element that passes the callback check, instead of the element itself.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to search.
     * @param {Function|Object|string} [callback=identity] The function called per
     *  iteration. If a property name or object is provided it will be used to
     *  create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {string|undefined} Returns the key of the found element, else `undefined`.
     * @example
     *
     * var characters = {
     *   'barney': {  'age': 36, 'blocked': false },
     *   'fred': {    'age': 40, 'blocked': true },
     *   'pebbles': { 'age': 1,  'blocked': false }
     * };
     *
     * _.findKey(characters, function(chr) {
     *   return chr.age < 40;
     * });
     * // => 'barney' (property order is not guaranteed across environments)
     *
     * // using "_.where" callback shorthand
     * _.findKey(characters, { 'age': 1 });
     * // => 'pebbles'
     *
     * // using "_.pluck" callback shorthand
     * _.findKey(characters, 'blocked');
     * // => 'fred'
     */
    function findKey(object, callback, thisArg) {
      var result;
      callback = lodash.createCallback(callback, thisArg, 3);
      forOwn(object, function(value, key, object) {
        if (callback(value, key, object)) {
          result = key;
          return false;
        }
      });
      return result;
    }

    /**
     * This method is like `_.findKey` except that it iterates over elements
     * of a `collection` in the opposite order.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to search.
     * @param {Function|Object|string} [callback=identity] The function called per
     *  iteration. If a property name or object is provided it will be used to
     *  create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {string|undefined} Returns the key of the found element, else `undefined`.
     * @example
     *
     * var characters = {
     *   'barney': {  'age': 36, 'blocked': true },
     *   'fred': {    'age': 40, 'blocked': false },
     *   'pebbles': { 'age': 1,  'blocked': true }
     * };
     *
     * _.findLastKey(characters, function(chr) {
     *   return chr.age < 40;
     * });
     * // => returns `pebbles`, assuming `_.findKey` returns `barney`
     *
     * // using "_.where" callback shorthand
     * _.findLastKey(characters, { 'age': 40 });
     * // => 'fred'
     *
     * // using "_.pluck" callback shorthand
     * _.findLastKey(characters, 'blocked');
     * // => 'pebbles'
     */
    function findLastKey(object, callback, thisArg) {
      var result;
      callback = lodash.createCallback(callback, thisArg, 3);
      forOwnRight(object, function(value, key, object) {
        if (callback(value, key, object)) {
          result = key;
          return false;
        }
      });
      return result;
    }

    /**
     * Iterates over own and inherited enumerable properties of an object,
     * executing the callback for each property. The callback is bound to `thisArg`
     * and invoked with three arguments; (value, key, object). Callbacks may exit
     * iteration early by explicitly returning `false`.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Objects
     * @param {Object} object The object to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns `object`.
     * @example
     *
     * function Shape() {
     *   this.x = 0;
     *   this.y = 0;
     * }
     *
     * Shape.prototype.move = function(x, y) {
     *   this.x += x;
     *   this.y += y;
     * };
     *
     * _.forIn(new Shape, function(value, key) {
     *   console.log(key);
     * });
     * // => logs 'x', 'y', and 'move' (property order is not guaranteed across environments)
     */
    var forIn = function(collection, callback, thisArg) {
      var index, iterable = collection, result = iterable;
      if (!iterable) return result;
      if (!objectTypes[typeof iterable]) return result;
      callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
        for (index in iterable) {
          if (callback(iterable[index], index, collection) === false) return result;
        }
      return result
    };

    /**
     * This method is like `_.forIn` except that it iterates over elements
     * of a `collection` in the opposite order.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns `object`.
     * @example
     *
     * function Shape() {
     *   this.x = 0;
     *   this.y = 0;
     * }
     *
     * Shape.prototype.move = function(x, y) {
     *   this.x += x;
     *   this.y += y;
     * };
     *
     * _.forInRight(new Shape, function(value, key) {
     *   console.log(key);
     * });
     * // => logs 'move', 'y', and 'x' assuming `_.forIn ` logs 'x', 'y', and 'move'
     */
    function forInRight(object, callback, thisArg) {
      var pairs = [];

      forIn(object, function(value, key) {
        pairs.push(key, value);
      });

      var length = pairs.length;
      callback = baseCreateCallback(callback, thisArg, 3);
      while (length--) {
        if (callback(pairs[length--], pairs[length], object) === false) {
          break;
        }
      }
      return object;
    }

    /**
     * Iterates over own enumerable properties of an object, executing the callback
     * for each property. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, key, object). Callbacks may exit iteration early by
     * explicitly returning `false`.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Objects
     * @param {Object} object The object to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns `object`.
     * @example
     *
     * _.forOwn({ '0': 'zero', '1': 'one', 'length': 2 }, function(num, key) {
     *   console.log(key);
     * });
     * // => logs '0', '1', and 'length' (property order is not guaranteed across environments)
     */
    var forOwn = function(collection, callback, thisArg) {
      var index, iterable = collection, result = iterable;
      if (!iterable) return result;
      if (!objectTypes[typeof iterable]) return result;
      callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
        var ownIndex = -1,
            ownProps = objectTypes[typeof iterable] && keys(iterable),
            length = ownProps ? ownProps.length : 0;

        while (++ownIndex < length) {
          index = ownProps[ownIndex];
          if (callback(iterable[index], index, collection) === false) return result;
        }
      return result
    };

    /**
     * This method is like `_.forOwn` except that it iterates over elements
     * of a `collection` in the opposite order.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns `object`.
     * @example
     *
     * _.forOwnRight({ '0': 'zero', '1': 'one', 'length': 2 }, function(num, key) {
     *   console.log(key);
     * });
     * // => logs 'length', '1', and '0' assuming `_.forOwn` logs '0', '1', and 'length'
     */
    function forOwnRight(object, callback, thisArg) {
      var props = keys(object),
          length = props.length;

      callback = baseCreateCallback(callback, thisArg, 3);
      while (length--) {
        var key = props[length];
        if (callback(object[key], key, object) === false) {
          break;
        }
      }
      return object;
    }

    /**
     * Creates a sorted array of property names of all enumerable properties,
     * own and inherited, of `object` that have function values.
     *
     * @static
     * @memberOf _
     * @alias methods
     * @category Objects
     * @param {Object} object The object to inspect.
     * @returns {Array} Returns an array of property names that have function values.
     * @example
     *
     * _.functions(_);
     * // => ['all', 'any', 'bind', 'bindAll', 'clone', 'compact', 'compose', ...]
     */
    function functions(object) {
      var result = [];
      forIn(object, function(value, key) {
        if (isFunction(value)) {
          result.push(key);
        }
      });
      return result.sort();
    }

    /**
     * Checks if the specified property name exists as a direct property of `object`,
     * instead of an inherited property.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to inspect.
     * @param {string} key The name of the property to check.
     * @returns {boolean} Returns `true` if key is a direct property, else `false`.
     * @example
     *
     * _.has({ 'a': 1, 'b': 2, 'c': 3 }, 'b');
     * // => true
     */
    function has(object, key) {
      return object ? hasOwnProperty.call(object, key) : false;
    }

    /**
     * Creates an object composed of the inverted keys and values of the given object.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to invert.
     * @returns {Object} Returns the created inverted object.
     * @example
     *
     * _.invert({ 'first': 'fred', 'second': 'barney' });
     * // => { 'fred': 'first', 'barney': 'second' }
     */
    function invert(object) {
      var index = -1,
          props = keys(object),
          length = props.length,
          result = {};

      while (++index < length) {
        var key = props[index];
        result[object[key]] = key;
      }
      return result;
    }

    /**
     * Checks if `value` is a boolean value.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a boolean value, else `false`.
     * @example
     *
     * _.isBoolean(null);
     * // => false
     */
    function isBoolean(value) {
      return value === true || value === false ||
        value && typeof value == 'object' && toString.call(value) == boolClass || false;
    }

    /**
     * Checks if `value` is a date.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a date, else `false`.
     * @example
     *
     * _.isDate(new Date);
     * // => true
     */
    function isDate(value) {
      return value && typeof value == 'object' && toString.call(value) == dateClass || false;
    }

    /**
     * Checks if `value` is a DOM element.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a DOM element, else `false`.
     * @example
     *
     * _.isElement(document.body);
     * // => true
     */
    function isElement(value) {
      return value && value.nodeType === 1 || false;
    }

    /**
     * Checks if `value` is empty. Arrays, strings, or `arguments` objects with a
     * length of `0` and objects with no own enumerable properties are considered
     * "empty".
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Array|Object|string} value The value to inspect.
     * @returns {boolean} Returns `true` if the `value` is empty, else `false`.
     * @example
     *
     * _.isEmpty([1, 2, 3]);
     * // => false
     *
     * _.isEmpty({});
     * // => true
     *
     * _.isEmpty('');
     * // => true
     */
    function isEmpty(value) {
      var result = true;
      if (!value) {
        return result;
      }
      var className = toString.call(value),
          length = value.length;

      if ((className == arrayClass || className == stringClass || className == argsClass ) ||
          (className == objectClass && typeof length == 'number' && isFunction(value.splice))) {
        return !length;
      }
      forOwn(value, function() {
        return (result = false);
      });
      return result;
    }

    /**
     * Performs a deep comparison between two values to determine if they are
     * equivalent to each other. If a callback is provided it will be executed
     * to compare values. If the callback returns `undefined` comparisons will
     * be handled by the method instead. The callback is bound to `thisArg` and
     * invoked with two arguments; (a, b).
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} a The value to compare.
     * @param {*} b The other value to compare.
     * @param {Function} [callback] The function to customize comparing values.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {boolean} Returns `true` if the values are equivalent, else `false`.
     * @example
     *
     * var object = { 'name': 'fred' };
     * var copy = { 'name': 'fred' };
     *
     * object == copy;
     * // => false
     *
     * _.isEqual(object, copy);
     * // => true
     *
     * var words = ['hello', 'goodbye'];
     * var otherWords = ['hi', 'goodbye'];
     *
     * _.isEqual(words, otherWords, function(a, b) {
     *   var reGreet = /^(?:hello|hi)$/i,
     *       aGreet = _.isString(a) && reGreet.test(a),
     *       bGreet = _.isString(b) && reGreet.test(b);
     *
     *   return (aGreet || bGreet) ? (aGreet == bGreet) : undefined;
     * });
     * // => true
     */
    function isEqual(a, b, callback, thisArg) {
      return baseIsEqual(a, b, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 2));
    }

    /**
     * Checks if `value` is, or can be coerced to, a finite number.
     *
     * Note: This is not the same as native `isFinite` which will return true for
     * booleans and empty strings. See http://es5.github.io/#x15.1.2.5.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is finite, else `false`.
     * @example
     *
     * _.isFinite(-101);
     * // => true
     *
     * _.isFinite('10');
     * // => true
     *
     * _.isFinite(true);
     * // => false
     *
     * _.isFinite('');
     * // => false
     *
     * _.isFinite(Infinity);
     * // => false
     */
    function isFinite(value) {
      return nativeIsFinite(value) && !nativeIsNaN(parseFloat(value));
    }

    /**
     * Checks if `value` is a function.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a function, else `false`.
     * @example
     *
     * _.isFunction(_);
     * // => true
     */
    function isFunction(value) {
      return typeof value == 'function';
    }

    /**
     * Checks if `value` is the language type of Object.
     * (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is an object, else `false`.
     * @example
     *
     * _.isObject({});
     * // => true
     *
     * _.isObject([1, 2, 3]);
     * // => true
     *
     * _.isObject(1);
     * // => false
     */
    function isObject(value) {
      // check if the value is the ECMAScript language type of Object
      // http://es5.github.io/#x8
      // and avoid a V8 bug
      // http://code.google.com/p/v8/issues/detail?id=2291
      return !!(value && objectTypes[typeof value]);
    }

    /**
     * Checks if `value` is `NaN`.
     *
     * Note: This is not the same as native `isNaN` which will return `true` for
     * `undefined` and other non-numeric values. See http://es5.github.io/#x15.1.2.4.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is `NaN`, else `false`.
     * @example
     *
     * _.isNaN(NaN);
     * // => true
     *
     * _.isNaN(new Number(NaN));
     * // => true
     *
     * isNaN(undefined);
     * // => true
     *
     * _.isNaN(undefined);
     * // => false
     */
    function isNaN(value) {
      // `NaN` as a primitive is the only value that is not equal to itself
      // (perform the [[Class]] check first to avoid errors with some host objects in IE)
      return isNumber(value) && value != +value;
    }

    /**
     * Checks if `value` is `null`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is `null`, else `false`.
     * @example
     *
     * _.isNull(null);
     * // => true
     *
     * _.isNull(undefined);
     * // => false
     */
    function isNull(value) {
      return value === null;
    }

    /**
     * Checks if `value` is a number.
     *
     * Note: `NaN` is considered a number. See http://es5.github.io/#x8.5.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a number, else `false`.
     * @example
     *
     * _.isNumber(8.4 * 5);
     * // => true
     */
    function isNumber(value) {
      return typeof value == 'number' ||
        value && typeof value == 'object' && toString.call(value) == numberClass || false;
    }

    /**
     * Checks if `value` is an object created by the `Object` constructor.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
     * @example
     *
     * function Shape() {
     *   this.x = 0;
     *   this.y = 0;
     * }
     *
     * _.isPlainObject(new Shape);
     * // => false
     *
     * _.isPlainObject([1, 2, 3]);
     * // => false
     *
     * _.isPlainObject({ 'x': 0, 'y': 0 });
     * // => true
     */
    var isPlainObject = !getPrototypeOf ? shimIsPlainObject : function(value) {
      if (!(value && toString.call(value) == objectClass)) {
        return false;
      }
      var valueOf = value.valueOf,
          objProto = isNative(valueOf) && (objProto = getPrototypeOf(valueOf)) && getPrototypeOf(objProto);

      return objProto
        ? (value == objProto || getPrototypeOf(value) == objProto)
        : shimIsPlainObject(value);
    };

    /**
     * Checks if `value` is a regular expression.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a regular expression, else `false`.
     * @example
     *
     * _.isRegExp(/fred/);
     * // => true
     */
    function isRegExp(value) {
      return value && typeof value == 'object' && toString.call(value) == regexpClass || false;
    }

    /**
     * Checks if `value` is a string.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is a string, else `false`.
     * @example
     *
     * _.isString('fred');
     * // => true
     */
    function isString(value) {
      return typeof value == 'string' ||
        value && typeof value == 'object' && toString.call(value) == stringClass || false;
    }

    /**
     * Checks if `value` is `undefined`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {*} value The value to check.
     * @returns {boolean} Returns `true` if the `value` is `undefined`, else `false`.
     * @example
     *
     * _.isUndefined(void 0);
     * // => true
     */
    function isUndefined(value) {
      return typeof value == 'undefined';
    }

    /**
     * Creates an object with the same keys as `object` and values generated by
     * running each own enumerable property of `object` through the callback.
     * The callback is bound to `thisArg` and invoked with three arguments;
     * (value, key, object).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new object with values of the results of each `callback` execution.
     * @example
     *
     * _.mapValues({ 'a': 1, 'b': 2, 'c': 3} , function(num) { return num * 3; });
     * // => { 'a': 3, 'b': 6, 'c': 9 }
     *
     * var characters = {
     *   'fred': { 'name': 'fred', 'age': 40 },
     *   'pebbles': { 'name': 'pebbles', 'age': 1 }
     * };
     *
     * // using "_.pluck" callback shorthand
     * _.mapValues(characters, 'age');
     * // => { 'fred': 40, 'pebbles': 1 }
     */
    function mapValues(object, callback, thisArg) {
      var result = {};
      callback = lodash.createCallback(callback, thisArg, 3);

      forOwn(object, function(value, key, object) {
        result[key] = callback(value, key, object);
      });
      return result;
    }

    /**
     * Recursively merges own enumerable properties of the source object(s), that
     * don't resolve to `undefined` into the destination object. Subsequent sources
     * will overwrite property assignments of previous sources. If a callback is
     * provided it will be executed to produce the merged values of the destination
     * and source properties. If the callback returns `undefined` merging will
     * be handled by the method instead. The callback is bound to `thisArg` and
     * invoked with two arguments; (objectValue, sourceValue).
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The destination object.
     * @param {...Object} [source] The source objects.
     * @param {Function} [callback] The function to customize merging properties.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns the destination object.
     * @example
     *
     * var names = {
     *   'characters': [
     *     { 'name': 'barney' },
     *     { 'name': 'fred' }
     *   ]
     * };
     *
     * var ages = {
     *   'characters': [
     *     { 'age': 36 },
     *     { 'age': 40 }
     *   ]
     * };
     *
     * _.merge(names, ages);
     * // => { 'characters': [{ 'name': 'barney', 'age': 36 }, { 'name': 'fred', 'age': 40 }] }
     *
     * var food = {
     *   'fruits': ['apple'],
     *   'vegetables': ['beet']
     * };
     *
     * var otherFood = {
     *   'fruits': ['banana'],
     *   'vegetables': ['carrot']
     * };
     *
     * _.merge(food, otherFood, function(a, b) {
     *   return _.isArray(a) ? a.concat(b) : undefined;
     * });
     * // => { 'fruits': ['apple', 'banana'], 'vegetables': ['beet', 'carrot] }
     */
    function merge(object) {
      var args = arguments,
          length = 2;

      if (!isObject(object)) {
        return object;
      }
      // allows working with `_.reduce` and `_.reduceRight` without using
      // their `index` and `collection` arguments
      if (typeof args[2] != 'number') {
        length = args.length;
      }
      if (length > 3 && typeof args[length - 2] == 'function') {
        var callback = baseCreateCallback(args[--length - 1], args[length--], 2);
      } else if (length > 2 && typeof args[length - 1] == 'function') {
        callback = args[--length];
      }
      var sources = slice(arguments, 1, length),
          index = -1,
          stackA = getArray(),
          stackB = getArray();

      while (++index < length) {
        baseMerge(object, sources[index], callback, stackA, stackB);
      }
      releaseArray(stackA);
      releaseArray(stackB);
      return object;
    }

    /**
     * Creates a shallow clone of `object` excluding the specified properties.
     * Property names may be specified as individual arguments or as arrays of
     * property names. If a callback is provided it will be executed for each
     * property of `object` omitting the properties the callback returns truey
     * for. The callback is bound to `thisArg` and invoked with three arguments;
     * (value, key, object).
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The source object.
     * @param {Function|...string|string[]} [callback] The properties to omit or the
     *  function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns an object without the omitted properties.
     * @example
     *
     * _.omit({ 'name': 'fred', 'age': 40 }, 'age');
     * // => { 'name': 'fred' }
     *
     * _.omit({ 'name': 'fred', 'age': 40 }, function(value) {
     *   return typeof value == 'number';
     * });
     * // => { 'name': 'fred' }
     */
    function omit(object, callback, thisArg) {
      var result = {};
      if (typeof callback != 'function') {
        var props = [];
        forIn(object, function(value, key) {
          props.push(key);
        });
        props = baseDifference(props, baseFlatten(arguments, true, false, 1));

        var index = -1,
            length = props.length;

        while (++index < length) {
          var key = props[index];
          result[key] = object[key];
        }
      } else {
        callback = lodash.createCallback(callback, thisArg, 3);
        forIn(object, function(value, key, object) {
          if (!callback(value, key, object)) {
            result[key] = value;
          }
        });
      }
      return result;
    }

    /**
     * Creates a two dimensional array of an object's key-value pairs,
     * i.e. `[[key1, value1], [key2, value2]]`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to inspect.
     * @returns {Array} Returns new array of key-value pairs.
     * @example
     *
     * _.pairs({ 'barney': 36, 'fred': 40 });
     * // => [['barney', 36], ['fred', 40]] (property order is not guaranteed across environments)
     */
    function pairs(object) {
      var index = -1,
          props = keys(object),
          length = props.length,
          result = Array(length);

      while (++index < length) {
        var key = props[index];
        result[index] = [key, object[key]];
      }
      return result;
    }

    /**
     * Creates a shallow clone of `object` composed of the specified properties.
     * Property names may be specified as individual arguments or as arrays of
     * property names. If a callback is provided it will be executed for each
     * property of `object` picking the properties the callback returns truey
     * for. The callback is bound to `thisArg` and invoked with three arguments;
     * (value, key, object).
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The source object.
     * @param {Function|...string|string[]} [callback] The function called per
     *  iteration or property names to pick, specified as individual property
     *  names or arrays of property names.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns an object composed of the picked properties.
     * @example
     *
     * _.pick({ 'name': 'fred', '_userid': 'fred1' }, 'name');
     * // => { 'name': 'fred' }
     *
     * _.pick({ 'name': 'fred', '_userid': 'fred1' }, function(value, key) {
     *   return key.charAt(0) != '_';
     * });
     * // => { 'name': 'fred' }
     */
    function pick(object, callback, thisArg) {
      var result = {};
      if (typeof callback != 'function') {
        var index = -1,
            props = baseFlatten(arguments, true, false, 1),
            length = isObject(object) ? props.length : 0;

        while (++index < length) {
          var key = props[index];
          if (key in object) {
            result[key] = object[key];
          }
        }
      } else {
        callback = lodash.createCallback(callback, thisArg, 3);
        forIn(object, function(value, key, object) {
          if (callback(value, key, object)) {
            result[key] = value;
          }
        });
      }
      return result;
    }

    /**
     * An alternative to `_.reduce` this method transforms `object` to a new
     * `accumulator` object which is the result of running each of its own
     * enumerable properties through a callback, with each callback execution
     * potentially mutating the `accumulator` object. The callback is bound to
     * `thisArg` and invoked with four arguments; (accumulator, value, key, object).
     * Callbacks may exit iteration early by explicitly returning `false`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Array|Object} object The object to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [accumulator] The custom accumulator value.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the accumulated value.
     * @example
     *
     * var squares = _.transform([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], function(result, num) {
     *   num *= num;
     *   if (num % 2) {
     *     return result.push(num) < 3;
     *   }
     * });
     * // => [1, 9, 25]
     *
     * var mapped = _.transform({ 'a': 1, 'b': 2, 'c': 3 }, function(result, num, key) {
     *   result[key] = num * 3;
     * });
     * // => { 'a': 3, 'b': 6, 'c': 9 }
     */
    function transform(object, callback, accumulator, thisArg) {
      var isArr = isArray(object);
      if (accumulator == null) {
        if (isArr) {
          accumulator = [];
        } else {
          var ctor = object && object.constructor,
              proto = ctor && ctor.prototype;

          accumulator = baseCreate(proto);
        }
      }
      if (callback) {
        callback = lodash.createCallback(callback, thisArg, 4);
        (isArr ? forEach : forOwn)(object, function(value, index, object) {
          return callback(accumulator, value, index, object);
        });
      }
      return accumulator;
    }

    /**
     * Creates an array composed of the own enumerable property values of `object`.
     *
     * @static
     * @memberOf _
     * @category Objects
     * @param {Object} object The object to inspect.
     * @returns {Array} Returns an array of property values.
     * @example
     *
     * _.values({ 'one': 1, 'two': 2, 'three': 3 });
     * // => [1, 2, 3] (property order is not guaranteed across environments)
     */
    function values(object) {
      var index = -1,
          props = keys(object),
          length = props.length,
          result = Array(length);

      while (++index < length) {
        result[index] = object[props[index]];
      }
      return result;
    }

    /*--------------------------------------------------------------------------*/

    /**
     * Creates an array of elements from the specified indexes, or keys, of the
     * `collection`. Indexes may be specified as individual arguments or as arrays
     * of indexes.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {...(number|number[]|string|string[])} [index] The indexes of `collection`
     *   to retrieve, specified as individual indexes or arrays of indexes.
     * @returns {Array} Returns a new array of elements corresponding to the
     *  provided indexes.
     * @example
     *
     * _.at(['a', 'b', 'c', 'd', 'e'], [0, 2, 4]);
     * // => ['a', 'c', 'e']
     *
     * _.at(['fred', 'barney', 'pebbles'], 0, 2);
     * // => ['fred', 'pebbles']
     */
    function at(collection) {
      var args = arguments,
          index = -1,
          props = baseFlatten(args, true, false, 1),
          length = (args[2] && args[2][args[1]] === collection) ? 1 : props.length,
          result = Array(length);

      while(++index < length) {
        result[index] = collection[props[index]];
      }
      return result;
    }

    /**
     * Checks if a given value is present in a collection using strict equality
     * for comparisons, i.e. `===`. If `fromIndex` is negative, it is used as the
     * offset from the end of the collection.
     *
     * @static
     * @memberOf _
     * @alias include
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {*} target The value to check for.
     * @param {number} [fromIndex=0] The index to search from.
     * @returns {boolean} Returns `true` if the `target` element is found, else `false`.
     * @example
     *
     * _.contains([1, 2, 3], 1);
     * // => true
     *
     * _.contains([1, 2, 3], 1, 2);
     * // => false
     *
     * _.contains({ 'name': 'fred', 'age': 40 }, 'fred');
     * // => true
     *
     * _.contains('pebbles', 'eb');
     * // => true
     */
    function contains(collection, target, fromIndex) {
      var index = -1,
          indexOf = getIndexOf(),
          length = collection ? collection.length : 0,
          result = false;

      fromIndex = (fromIndex < 0 ? nativeMax(0, length + fromIndex) : fromIndex) || 0;
      if (isArray(collection)) {
        result = indexOf(collection, target, fromIndex) > -1;
      } else if (typeof length == 'number') {
        result = (isString(collection) ? collection.indexOf(target, fromIndex) : indexOf(collection, target, fromIndex)) > -1;
      } else {
        forOwn(collection, function(value) {
          if (++index >= fromIndex) {
            return !(result = value === target);
          }
        });
      }
      return result;
    }

    /**
     * Creates an object composed of keys generated from the results of running
     * each element of `collection` through the callback. The corresponding value
     * of each key is the number of times the key was returned by the callback.
     * The callback is bound to `thisArg` and invoked with three arguments;
     * (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns the composed aggregate object.
     * @example
     *
     * _.countBy([4.3, 6.1, 6.4], function(num) { return Math.floor(num); });
     * // => { '4': 1, '6': 2 }
     *
     * _.countBy([4.3, 6.1, 6.4], function(num) { return this.floor(num); }, Math);
     * // => { '4': 1, '6': 2 }
     *
     * _.countBy(['one', 'two', 'three'], 'length');
     * // => { '3': 2, '5': 1 }
     */
    var countBy = createAggregator(function(result, value, key) {
      (hasOwnProperty.call(result, key) ? result[key]++ : result[key] = 1);
    });

    /**
     * Checks if the given callback returns truey value for **all** elements of
     * a collection. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias all
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {boolean} Returns `true` if all elements passed the callback check,
     *  else `false`.
     * @example
     *
     * _.every([true, 1, null, 'yes']);
     * // => false
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.every(characters, 'age');
     * // => true
     *
     * // using "_.where" callback shorthand
     * _.every(characters, { 'age': 36 });
     * // => false
     */
    function every(collection, callback, thisArg) {
      var result = true;
      callback = lodash.createCallback(callback, thisArg, 3);

      var index = -1,
          length = collection ? collection.length : 0;

      if (typeof length == 'number') {
        while (++index < length) {
          if (!(result = !!callback(collection[index], index, collection))) {
            break;
          }
        }
      } else {
        forOwn(collection, function(value, index, collection) {
          return (result = !!callback(value, index, collection));
        });
      }
      return result;
    }

    /**
     * Iterates over elements of a collection, returning an array of all elements
     * the callback returns truey for. The callback is bound to `thisArg` and
     * invoked with three arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias select
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new array of elements that passed the callback check.
     * @example
     *
     * var evens = _.filter([1, 2, 3, 4, 5, 6], function(num) { return num % 2 == 0; });
     * // => [2, 4, 6]
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36, 'blocked': false },
     *   { 'name': 'fred',   'age': 40, 'blocked': true }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.filter(characters, 'blocked');
     * // => [{ 'name': 'fred', 'age': 40, 'blocked': true }]
     *
     * // using "_.where" callback shorthand
     * _.filter(characters, { 'age': 36 });
     * // => [{ 'name': 'barney', 'age': 36, 'blocked': false }]
     */
    function filter(collection, callback, thisArg) {
      var result = [];
      callback = lodash.createCallback(callback, thisArg, 3);

      var index = -1,
          length = collection ? collection.length : 0;

      if (typeof length == 'number') {
        while (++index < length) {
          var value = collection[index];
          if (callback(value, index, collection)) {
            result.push(value);
          }
        }
      } else {
        forOwn(collection, function(value, index, collection) {
          if (callback(value, index, collection)) {
            result.push(value);
          }
        });
      }
      return result;
    }

    /**
     * Iterates over elements of a collection, returning the first element that
     * the callback returns truey for. The callback is bound to `thisArg` and
     * invoked with three arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias detect, findWhere
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the found element, else `undefined`.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney',  'age': 36, 'blocked': false },
     *   { 'name': 'fred',    'age': 40, 'blocked': true },
     *   { 'name': 'pebbles', 'age': 1,  'blocked': false }
     * ];
     *
     * _.find(characters, function(chr) {
     *   return chr.age < 40;
     * });
     * // => { 'name': 'barney', 'age': 36, 'blocked': false }
     *
     * // using "_.where" callback shorthand
     * _.find(characters, { 'age': 1 });
     * // =>  { 'name': 'pebbles', 'age': 1, 'blocked': false }
     *
     * // using "_.pluck" callback shorthand
     * _.find(characters, 'blocked');
     * // => { 'name': 'fred', 'age': 40, 'blocked': true }
     */
    function find(collection, callback, thisArg) {
      callback = lodash.createCallback(callback, thisArg, 3);

      var index = -1,
          length = collection ? collection.length : 0;

      if (typeof length == 'number') {
        while (++index < length) {
          var value = collection[index];
          if (callback(value, index, collection)) {
            return value;
          }
        }
      } else {
        var result;
        forOwn(collection, function(value, index, collection) {
          if (callback(value, index, collection)) {
            result = value;
            return false;
          }
        });
        return result;
      }
    }

    /**
     * This method is like `_.find` except that it iterates over elements
     * of a `collection` from right to left.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the found element, else `undefined`.
     * @example
     *
     * _.findLast([1, 2, 3, 4], function(num) {
     *   return num % 2 == 1;
     * });
     * // => 3
     */
    function findLast(collection, callback, thisArg) {
      var result;
      callback = lodash.createCallback(callback, thisArg, 3);
      forEachRight(collection, function(value, index, collection) {
        if (callback(value, index, collection)) {
          result = value;
          return false;
        }
      });
      return result;
    }

    /**
     * Iterates over elements of a collection, executing the callback for each
     * element. The callback is bound to `thisArg` and invoked with three arguments;
     * (value, index|key, collection). Callbacks may exit iteration early by
     * explicitly returning `false`.
     *
     * Note: As with other "Collections" methods, objects with a `length` property
     * are iterated like arrays. To avoid this behavior `_.forIn` or `_.forOwn`
     * may be used for object iteration.
     *
     * @static
     * @memberOf _
     * @alias each
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array|Object|string} Returns `collection`.
     * @example
     *
     * _([1, 2, 3]).forEach(function(num) { console.log(num); }).join(',');
     * // => logs each number and returns '1,2,3'
     *
     * _.forEach({ 'one': 1, 'two': 2, 'three': 3 }, function(num) { console.log(num); });
     * // => logs each number and returns the object (property order is not guaranteed across environments)
     */
    function forEach(collection, callback, thisArg) {
      var index = -1,
          length = collection ? collection.length : 0;

      callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
      if (typeof length == 'number') {
        while (++index < length) {
          if (callback(collection[index], index, collection) === false) {
            break;
          }
        }
      } else {
        forOwn(collection, callback);
      }
      return collection;
    }

    /**
     * This method is like `_.forEach` except that it iterates over elements
     * of a `collection` from right to left.
     *
     * @static
     * @memberOf _
     * @alias eachRight
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array|Object|string} Returns `collection`.
     * @example
     *
     * _([1, 2, 3]).forEachRight(function(num) { console.log(num); }).join(',');
     * // => logs each number from right to left and returns '3,2,1'
     */
    function forEachRight(collection, callback, thisArg) {
      var length = collection ? collection.length : 0;
      callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
      if (typeof length == 'number') {
        while (length--) {
          if (callback(collection[length], length, collection) === false) {
            break;
          }
        }
      } else {
        var props = keys(collection);
        length = props.length;
        forOwn(collection, function(value, key, collection) {
          key = props ? props[--length] : --length;
          return callback(collection[key], key, collection);
        });
      }
      return collection;
    }

    /**
     * Creates an object composed of keys generated from the results of running
     * each element of a collection through the callback. The corresponding value
     * of each key is an array of the elements responsible for generating the key.
     * The callback is bound to `thisArg` and invoked with three arguments;
     * (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns the composed aggregate object.
     * @example
     *
     * _.groupBy([4.2, 6.1, 6.4], function(num) { return Math.floor(num); });
     * // => { '4': [4.2], '6': [6.1, 6.4] }
     *
     * _.groupBy([4.2, 6.1, 6.4], function(num) { return this.floor(num); }, Math);
     * // => { '4': [4.2], '6': [6.1, 6.4] }
     *
     * // using "_.pluck" callback shorthand
     * _.groupBy(['one', 'two', 'three'], 'length');
     * // => { '3': ['one', 'two'], '5': ['three'] }
     */
    var groupBy = createAggregator(function(result, value, key) {
      (hasOwnProperty.call(result, key) ? result[key] : result[key] = []).push(value);
    });

    /**
     * Creates an object composed of keys generated from the results of running
     * each element of the collection through the given callback. The corresponding
     * value of each key is the last element responsible for generating the key.
     * The callback is bound to `thisArg` and invoked with three arguments;
     * (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Object} Returns the composed aggregate object.
     * @example
     *
     * var keys = [
     *   { 'dir': 'left', 'code': 97 },
     *   { 'dir': 'right', 'code': 100 }
     * ];
     *
     * _.indexBy(keys, 'dir');
     * // => { 'left': { 'dir': 'left', 'code': 97 }, 'right': { 'dir': 'right', 'code': 100 } }
     *
     * _.indexBy(keys, function(key) { return String.fromCharCode(key.code); });
     * // => { 'a': { 'dir': 'left', 'code': 97 }, 'd': { 'dir': 'right', 'code': 100 } }
     *
     * _.indexBy(characters, function(key) { this.fromCharCode(key.code); }, String);
     * // => { 'a': { 'dir': 'left', 'code': 97 }, 'd': { 'dir': 'right', 'code': 100 } }
     */
    var indexBy = createAggregator(function(result, value, key) {
      result[key] = value;
    });

    /**
     * Invokes the method named by `methodName` on each element in the `collection`
     * returning an array of the results of each invoked method. Additional arguments
     * will be provided to each invoked method. If `methodName` is a function it
     * will be invoked for, and `this` bound to, each element in the `collection`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|string} methodName The name of the method to invoke or
     *  the function invoked per iteration.
     * @param {...*} [arg] Arguments to invoke the method with.
     * @returns {Array} Returns a new array of the results of each invoked method.
     * @example
     *
     * _.invoke([[5, 1, 7], [3, 2, 1]], 'sort');
     * // => [[1, 5, 7], [1, 2, 3]]
     *
     * _.invoke([123, 456], String.prototype.split, '');
     * // => [['1', '2', '3'], ['4', '5', '6']]
     */
    function invoke(collection, methodName) {
      var args = slice(arguments, 2),
          index = -1,
          isFunc = typeof methodName == 'function',
          length = collection ? collection.length : 0,
          result = Array(typeof length == 'number' ? length : 0);

      forEach(collection, function(value) {
        result[++index] = (isFunc ? methodName : value[methodName]).apply(value, args);
      });
      return result;
    }

    /**
     * Creates an array of values by running each element in the collection
     * through the callback. The callback is bound to `thisArg` and invoked with
     * three arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias collect
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new array of the results of each `callback` execution.
     * @example
     *
     * _.map([1, 2, 3], function(num) { return num * 3; });
     * // => [3, 6, 9]
     *
     * _.map({ 'one': 1, 'two': 2, 'three': 3 }, function(num) { return num * 3; });
     * // => [3, 6, 9] (property order is not guaranteed across environments)
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.map(characters, 'name');
     * // => ['barney', 'fred']
     */
    function map(collection, callback, thisArg) {
      var index = -1,
          length = collection ? collection.length : 0;

      callback = lodash.createCallback(callback, thisArg, 3);
      if (typeof length == 'number') {
        var result = Array(length);
        while (++index < length) {
          result[index] = callback(collection[index], index, collection);
        }
      } else {
        result = [];
        forOwn(collection, function(value, key, collection) {
          result[++index] = callback(value, key, collection);
        });
      }
      return result;
    }

    /**
     * Retrieves the maximum value of a collection. If the collection is empty or
     * falsey `-Infinity` is returned. If a callback is provided it will be executed
     * for each value in the collection to generate the criterion by which the value
     * is ranked. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, index, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the maximum value.
     * @example
     *
     * _.max([4, 2, 8, 6]);
     * // => 8
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * _.max(characters, function(chr) { return chr.age; });
     * // => { 'name': 'fred', 'age': 40 };
     *
     * // using "_.pluck" callback shorthand
     * _.max(characters, 'age');
     * // => { 'name': 'fred', 'age': 40 };
     */
    function max(collection, callback, thisArg) {
      var computed = -Infinity,
          result = computed;

      // allows working with functions like `_.map` without using
      // their `index` argument as a callback
      if (typeof callback != 'function' && thisArg && thisArg[callback] === collection) {
        callback = null;
      }
      if (callback == null && isArray(collection)) {
        var index = -1,
            length = collection.length;

        while (++index < length) {
          var value = collection[index];
          if (value > result) {
            result = value;
          }
        }
      } else {
        callback = (callback == null && isString(collection))
          ? charAtCallback
          : lodash.createCallback(callback, thisArg, 3);

        forEach(collection, function(value, index, collection) {
          var current = callback(value, index, collection);
          if (current > computed) {
            computed = current;
            result = value;
          }
        });
      }
      return result;
    }

    /**
     * Retrieves the minimum value of a collection. If the collection is empty or
     * falsey `Infinity` is returned. If a callback is provided it will be executed
     * for each value in the collection to generate the criterion by which the value
     * is ranked. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, index, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the minimum value.
     * @example
     *
     * _.min([4, 2, 8, 6]);
     * // => 2
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * _.min(characters, function(chr) { return chr.age; });
     * // => { 'name': 'barney', 'age': 36 };
     *
     * // using "_.pluck" callback shorthand
     * _.min(characters, 'age');
     * // => { 'name': 'barney', 'age': 36 };
     */
    function min(collection, callback, thisArg) {
      var computed = Infinity,
          result = computed;

      // allows working with functions like `_.map` without using
      // their `index` argument as a callback
      if (typeof callback != 'function' && thisArg && thisArg[callback] === collection) {
        callback = null;
      }
      if (callback == null && isArray(collection)) {
        var index = -1,
            length = collection.length;

        while (++index < length) {
          var value = collection[index];
          if (value < result) {
            result = value;
          }
        }
      } else {
        callback = (callback == null && isString(collection))
          ? charAtCallback
          : lodash.createCallback(callback, thisArg, 3);

        forEach(collection, function(value, index, collection) {
          var current = callback(value, index, collection);
          if (current < computed) {
            computed = current;
            result = value;
          }
        });
      }
      return result;
    }

    /**
     * Retrieves the value of a specified property from all elements in the collection.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {string} property The name of the property to pluck.
     * @returns {Array} Returns a new array of property values.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * _.pluck(characters, 'name');
     * // => ['barney', 'fred']
     */
    var pluck = map;

    /**
     * Reduces a collection to a value which is the accumulated result of running
     * each element in the collection through the callback, where each successive
     * callback execution consumes the return value of the previous execution. If
     * `accumulator` is not provided the first element of the collection will be
     * used as the initial `accumulator` value. The callback is bound to `thisArg`
     * and invoked with four arguments; (accumulator, value, index|key, collection).
     *
     * @static
     * @memberOf _
     * @alias foldl, inject
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [accumulator] Initial value of the accumulator.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the accumulated value.
     * @example
     *
     * var sum = _.reduce([1, 2, 3], function(sum, num) {
     *   return sum + num;
     * });
     * // => 6
     *
     * var mapped = _.reduce({ 'a': 1, 'b': 2, 'c': 3 }, function(result, num, key) {
     *   result[key] = num * 3;
     *   return result;
     * }, {});
     * // => { 'a': 3, 'b': 6, 'c': 9 }
     */
    function reduce(collection, callback, accumulator, thisArg) {
      if (!collection) return accumulator;
      var noaccum = arguments.length < 3;
      callback = lodash.createCallback(callback, thisArg, 4);

      var index = -1,
          length = collection.length;

      if (typeof length == 'number') {
        if (noaccum) {
          accumulator = collection[++index];
        }
        while (++index < length) {
          accumulator = callback(accumulator, collection[index], index, collection);
        }
      } else {
        forOwn(collection, function(value, index, collection) {
          accumulator = noaccum
            ? (noaccum = false, value)
            : callback(accumulator, value, index, collection)
        });
      }
      return accumulator;
    }

    /**
     * This method is like `_.reduce` except that it iterates over elements
     * of a `collection` from right to left.
     *
     * @static
     * @memberOf _
     * @alias foldr
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function} [callback=identity] The function called per iteration.
     * @param {*} [accumulator] Initial value of the accumulator.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the accumulated value.
     * @example
     *
     * var list = [[0, 1], [2, 3], [4, 5]];
     * var flat = _.reduceRight(list, function(a, b) { return a.concat(b); }, []);
     * // => [4, 5, 2, 3, 0, 1]
     */
    function reduceRight(collection, callback, accumulator, thisArg) {
      var noaccum = arguments.length < 3;
      callback = lodash.createCallback(callback, thisArg, 4);
      forEachRight(collection, function(value, index, collection) {
        accumulator = noaccum
          ? (noaccum = false, value)
          : callback(accumulator, value, index, collection);
      });
      return accumulator;
    }

    /**
     * The opposite of `_.filter` this method returns the elements of a
     * collection that the callback does **not** return truey for.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new array of elements that failed the callback check.
     * @example
     *
     * var odds = _.reject([1, 2, 3, 4, 5, 6], function(num) { return num % 2 == 0; });
     * // => [1, 3, 5]
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36, 'blocked': false },
     *   { 'name': 'fred',   'age': 40, 'blocked': true }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.reject(characters, 'blocked');
     * // => [{ 'name': 'barney', 'age': 36, 'blocked': false }]
     *
     * // using "_.where" callback shorthand
     * _.reject(characters, { 'age': 36 });
     * // => [{ 'name': 'fred', 'age': 40, 'blocked': true }]
     */
    function reject(collection, callback, thisArg) {
      callback = lodash.createCallback(callback, thisArg, 3);
      return filter(collection, function(value, index, collection) {
        return !callback(value, index, collection);
      });
    }

    /**
     * Retrieves a random element or `n` random elements from a collection.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to sample.
     * @param {number} [n] The number of elements to sample.
     * @param- {Object} [guard] Allows working with functions like `_.map`
     *  without using their `index` arguments as `n`.
     * @returns {Array} Returns the random sample(s) of `collection`.
     * @example
     *
     * _.sample([1, 2, 3, 4]);
     * // => 2
     *
     * _.sample([1, 2, 3, 4], 2);
     * // => [3, 1]
     */
    function sample(collection, n, guard) {
      if (collection && typeof collection.length != 'number') {
        collection = values(collection);
      }
      if (n == null || guard) {
        return collection ? collection[baseRandom(0, collection.length - 1)] : undefined;
      }
      var result = shuffle(collection);
      result.length = nativeMin(nativeMax(0, n), result.length);
      return result;
    }

    /**
     * Creates an array of shuffled values, using a version of the Fisher-Yates
     * shuffle. See http://en.wikipedia.org/wiki/Fisher-Yates_shuffle.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to shuffle.
     * @returns {Array} Returns a new shuffled collection.
     * @example
     *
     * _.shuffle([1, 2, 3, 4, 5, 6]);
     * // => [4, 1, 6, 3, 5, 2]
     */
    function shuffle(collection) {
      var index = -1,
          length = collection ? collection.length : 0,
          result = Array(typeof length == 'number' ? length : 0);

      forEach(collection, function(value) {
        var rand = baseRandom(0, ++index);
        result[index] = result[rand];
        result[rand] = value;
      });
      return result;
    }

    /**
     * Gets the size of the `collection` by returning `collection.length` for arrays
     * and array-like objects or the number of own enumerable properties for objects.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to inspect.
     * @returns {number} Returns `collection.length` or number of own enumerable properties.
     * @example
     *
     * _.size([1, 2]);
     * // => 2
     *
     * _.size({ 'one': 1, 'two': 2, 'three': 3 });
     * // => 3
     *
     * _.size('pebbles');
     * // => 7
     */
    function size(collection) {
      var length = collection ? collection.length : 0;
      return typeof length == 'number' ? length : keys(collection).length;
    }

    /**
     * Checks if the callback returns a truey value for **any** element of a
     * collection. The function returns as soon as it finds a passing value and
     * does not iterate over the entire collection. The callback is bound to
     * `thisArg` and invoked with three arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias any
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {boolean} Returns `true` if any element passed the callback check,
     *  else `false`.
     * @example
     *
     * _.some([null, 0, 'yes', false], Boolean);
     * // => true
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36, 'blocked': false },
     *   { 'name': 'fred',   'age': 40, 'blocked': true }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.some(characters, 'blocked');
     * // => true
     *
     * // using "_.where" callback shorthand
     * _.some(characters, { 'age': 1 });
     * // => false
     */
    function some(collection, callback, thisArg) {
      var result;
      callback = lodash.createCallback(callback, thisArg, 3);

      var index = -1,
          length = collection ? collection.length : 0;

      if (typeof length == 'number') {
        while (++index < length) {
          if ((result = callback(collection[index], index, collection))) {
            break;
          }
        }
      } else {
        forOwn(collection, function(value, index, collection) {
          return !(result = callback(value, index, collection));
        });
      }
      return !!result;
    }

    /**
     * Creates an array of elements, sorted in ascending order by the results of
     * running each element in a collection through the callback. This method
     * performs a stable sort, that is, it will preserve the original sort order
     * of equal elements. The callback is bound to `thisArg` and invoked with
     * three arguments; (value, index|key, collection).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an array of property names is provided for `callback` the collection
     * will be sorted by each property value.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Array|Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new array of sorted elements.
     * @example
     *
     * _.sortBy([1, 2, 3], function(num) { return Math.sin(num); });
     * // => [3, 1, 2]
     *
     * _.sortBy([1, 2, 3], function(num) { return this.sin(num); }, Math);
     * // => [3, 1, 2]
     *
     * var characters = [
     *   { 'name': 'barney',  'age': 36 },
     *   { 'name': 'fred',    'age': 40 },
     *   { 'name': 'barney',  'age': 26 },
     *   { 'name': 'fred',    'age': 30 }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.map(_.sortBy(characters, 'age'), _.values);
     * // => [['barney', 26], ['fred', 30], ['barney', 36], ['fred', 40]]
     *
     * // sorting by multiple properties
     * _.map(_.sortBy(characters, ['name', 'age']), _.values);
     * // = > [['barney', 26], ['barney', 36], ['fred', 30], ['fred', 40]]
     */
    function sortBy(collection, callback, thisArg) {
      var index = -1,
          isArr = isArray(callback),
          length = collection ? collection.length : 0,
          result = Array(typeof length == 'number' ? length : 0);

      if (!isArr) {
        callback = lodash.createCallback(callback, thisArg, 3);
      }
      forEach(collection, function(value, key, collection) {
        var object = result[++index] = getObject();
        if (isArr) {
          object.criteria = map(callback, function(key) { return value[key]; });
        } else {
          (object.criteria = getArray())[0] = callback(value, key, collection);
        }
        object.index = index;
        object.value = value;
      });

      length = result.length;
      result.sort(compareAscending);
      while (length--) {
        var object = result[length];
        result[length] = object.value;
        if (!isArr) {
          releaseArray(object.criteria);
        }
        releaseObject(object);
      }
      return result;
    }

    /**
     * Converts the `collection` to an array.
     *
     * @static
     * @memberOf _
     * @category Collections
     * @param {Array|Object|string} collection The collection to convert.
     * @returns {Array} Returns the new converted array.
     * @example
     *
     * (function() { return _.toArray(arguments).slice(1); })(1, 2, 3, 4);
     * // => [2, 3, 4]
     */
    function toArray(collection) {
      if (collection && typeof collection.length == 'number') {
        return slice(collection);
      }
      return values(collection);
    }

    /**
     * Performs a deep comparison of each element in a `collection` to the given
     * `properties` object, returning an array of all elements that have equivalent
     * property values.
     *
     * @static
     * @memberOf _
     * @type Function
     * @category Collections
     * @param {Array|Object|string} collection The collection to iterate over.
     * @param {Object} props The object of property values to filter by.
     * @returns {Array} Returns a new array of elements that have the given properties.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36, 'pets': ['hoppy'] },
     *   { 'name': 'fred',   'age': 40, 'pets': ['baby puss', 'dino'] }
     * ];
     *
     * _.where(characters, { 'age': 36 });
     * // => [{ 'name': 'barney', 'age': 36, 'pets': ['hoppy'] }]
     *
     * _.where(characters, { 'pets': ['dino'] });
     * // => [{ 'name': 'fred', 'age': 40, 'pets': ['baby puss', 'dino'] }]
     */
    var where = filter;

    /*--------------------------------------------------------------------------*/

    /**
     * Creates an array with all falsey values removed. The values `false`, `null`,
     * `0`, `""`, `undefined`, and `NaN` are all falsey.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to compact.
     * @returns {Array} Returns a new array of filtered values.
     * @example
     *
     * _.compact([0, 1, false, 2, '', 3]);
     * // => [1, 2, 3]
     */
    function compact(array) {
      var index = -1,
          length = array ? array.length : 0,
          result = [];

      while (++index < length) {
        var value = array[index];
        if (value) {
          result.push(value);
        }
      }
      return result;
    }

    /**
     * Creates an array excluding all values of the provided arrays using strict
     * equality for comparisons, i.e. `===`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to process.
     * @param {...Array} [values] The arrays of values to exclude.
     * @returns {Array} Returns a new array of filtered values.
     * @example
     *
     * _.difference([1, 2, 3, 4, 5], [5, 2, 10]);
     * // => [1, 3, 4]
     */
    function difference(array) {
      return baseDifference(array, baseFlatten(arguments, true, true, 1));
    }

    /**
     * This method is like `_.find` except that it returns the index of the first
     * element that passes the callback check, instead of the element itself.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to search.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {number} Returns the index of the found element, else `-1`.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney',  'age': 36, 'blocked': false },
     *   { 'name': 'fred',    'age': 40, 'blocked': true },
     *   { 'name': 'pebbles', 'age': 1,  'blocked': false }
     * ];
     *
     * _.findIndex(characters, function(chr) {
     *   return chr.age < 20;
     * });
     * // => 2
     *
     * // using "_.where" callback shorthand
     * _.findIndex(characters, { 'age': 36 });
     * // => 0
     *
     * // using "_.pluck" callback shorthand
     * _.findIndex(characters, 'blocked');
     * // => 1
     */
    function findIndex(array, callback, thisArg) {
      var index = -1,
          length = array ? array.length : 0;

      callback = lodash.createCallback(callback, thisArg, 3);
      while (++index < length) {
        if (callback(array[index], index, array)) {
          return index;
        }
      }
      return -1;
    }

    /**
     * This method is like `_.findIndex` except that it iterates over elements
     * of a `collection` from right to left.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to search.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {number} Returns the index of the found element, else `-1`.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney',  'age': 36, 'blocked': true },
     *   { 'name': 'fred',    'age': 40, 'blocked': false },
     *   { 'name': 'pebbles', 'age': 1,  'blocked': true }
     * ];
     *
     * _.findLastIndex(characters, function(chr) {
     *   return chr.age > 30;
     * });
     * // => 1
     *
     * // using "_.where" callback shorthand
     * _.findLastIndex(characters, { 'age': 36 });
     * // => 0
     *
     * // using "_.pluck" callback shorthand
     * _.findLastIndex(characters, 'blocked');
     * // => 2
     */
    function findLastIndex(array, callback, thisArg) {
      var length = array ? array.length : 0;
      callback = lodash.createCallback(callback, thisArg, 3);
      while (length--) {
        if (callback(array[length], length, array)) {
          return length;
        }
      }
      return -1;
    }

    /**
     * Gets the first element or first `n` elements of an array. If a callback
     * is provided elements at the beginning of the array are returned as long
     * as the callback returns truey. The callback is bound to `thisArg` and
     * invoked with three arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias head, take
     * @category Arrays
     * @param {Array} array The array to query.
     * @param {Function|Object|number|string} [callback] The function called
     *  per element or the number of elements to return. If a property name or
     *  object is provided it will be used to create a "_.pluck" or "_.where"
     *  style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the first element(s) of `array`.
     * @example
     *
     * _.first([1, 2, 3]);
     * // => 1
     *
     * _.first([1, 2, 3], 2);
     * // => [1, 2]
     *
     * _.first([1, 2, 3], function(num) {
     *   return num < 3;
     * });
     * // => [1, 2]
     *
     * var characters = [
     *   { 'name': 'barney',  'blocked': true,  'employer': 'slate' },
     *   { 'name': 'fred',    'blocked': false, 'employer': 'slate' },
     *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.first(characters, 'blocked');
     * // => [{ 'name': 'barney', 'blocked': true, 'employer': 'slate' }]
     *
     * // using "_.where" callback shorthand
     * _.pluck(_.first(characters, { 'employer': 'slate' }), 'name');
     * // => ['barney', 'fred']
     */
    function first(array, callback, thisArg) {
      var n = 0,
          length = array ? array.length : 0;

      if (typeof callback != 'number' && callback != null) {
        var index = -1;
        callback = lodash.createCallback(callback, thisArg, 3);
        while (++index < length && callback(array[index], index, array)) {
          n++;
        }
      } else {
        n = callback;
        if (n == null || thisArg) {
          return array ? array[0] : undefined;
        }
      }
      return slice(array, 0, nativeMin(nativeMax(0, n), length));
    }

    /**
     * Flattens a nested array (the nesting can be to any depth). If `isShallow`
     * is truey, the array will only be flattened a single level. If a callback
     * is provided each element of the array is passed through the callback before
     * flattening. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to flatten.
     * @param {boolean} [isShallow=false] A flag to restrict flattening to a single level.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new flattened array.
     * @example
     *
     * _.flatten([1, [2], [3, [[4]]]]);
     * // => [1, 2, 3, 4];
     *
     * _.flatten([1, [2], [3, [[4]]]], true);
     * // => [1, 2, 3, [[4]]];
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 30, 'pets': ['hoppy'] },
     *   { 'name': 'fred',   'age': 40, 'pets': ['baby puss', 'dino'] }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.flatten(characters, 'pets');
     * // => ['hoppy', 'baby puss', 'dino']
     */
    function flatten(array, isShallow, callback, thisArg) {
      // juggle arguments
      if (typeof isShallow != 'boolean' && isShallow != null) {
        thisArg = callback;
        callback = (typeof isShallow != 'function' && thisArg && thisArg[isShallow] === array) ? null : isShallow;
        isShallow = false;
      }
      if (callback != null) {
        array = map(array, callback, thisArg);
      }
      return baseFlatten(array, isShallow);
    }

    /**
     * Gets the index at which the first occurrence of `value` is found using
     * strict equality for comparisons, i.e. `===`. If the array is already sorted
     * providing `true` for `fromIndex` will run a faster binary search.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to search.
     * @param {*} value The value to search for.
     * @param {boolean|number} [fromIndex=0] The index to search from or `true`
     *  to perform a binary search on a sorted array.
     * @returns {number} Returns the index of the matched value or `-1`.
     * @example
     *
     * _.indexOf([1, 2, 3, 1, 2, 3], 2);
     * // => 1
     *
     * _.indexOf([1, 2, 3, 1, 2, 3], 2, 3);
     * // => 4
     *
     * _.indexOf([1, 1, 2, 2, 3, 3], 2, true);
     * // => 2
     */
    function indexOf(array, value, fromIndex) {
      if (typeof fromIndex == 'number') {
        var length = array ? array.length : 0;
        fromIndex = (fromIndex < 0 ? nativeMax(0, length + fromIndex) : fromIndex || 0);
      } else if (fromIndex) {
        var index = sortedIndex(array, value);
        return array[index] === value ? index : -1;
      }
      return baseIndexOf(array, value, fromIndex);
    }

    /**
     * Gets all but the last element or last `n` elements of an array. If a
     * callback is provided elements at the end of the array are excluded from
     * the result as long as the callback returns truey. The callback is bound
     * to `thisArg` and invoked with three arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to query.
     * @param {Function|Object|number|string} [callback=1] The function called
     *  per element or the number of elements to exclude. If a property name or
     *  object is provided it will be used to create a "_.pluck" or "_.where"
     *  style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a slice of `array`.
     * @example
     *
     * _.initial([1, 2, 3]);
     * // => [1, 2]
     *
     * _.initial([1, 2, 3], 2);
     * // => [1]
     *
     * _.initial([1, 2, 3], function(num) {
     *   return num > 1;
     * });
     * // => [1]
     *
     * var characters = [
     *   { 'name': 'barney',  'blocked': false, 'employer': 'slate' },
     *   { 'name': 'fred',    'blocked': true,  'employer': 'slate' },
     *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.initial(characters, 'blocked');
     * // => [{ 'name': 'barney',  'blocked': false, 'employer': 'slate' }]
     *
     * // using "_.where" callback shorthand
     * _.pluck(_.initial(characters, { 'employer': 'na' }), 'name');
     * // => ['barney', 'fred']
     */
    function initial(array, callback, thisArg) {
      var n = 0,
          length = array ? array.length : 0;

      if (typeof callback != 'number' && callback != null) {
        var index = length;
        callback = lodash.createCallback(callback, thisArg, 3);
        while (index-- && callback(array[index], index, array)) {
          n++;
        }
      } else {
        n = (callback == null || thisArg) ? 1 : callback || n;
      }
      return slice(array, 0, nativeMin(nativeMax(0, length - n), length));
    }

    /**
     * Creates an array of unique values present in all provided arrays using
     * strict equality for comparisons, i.e. `===`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {...Array} [array] The arrays to inspect.
     * @returns {Array} Returns an array of shared values.
     * @example
     *
     * _.intersection([1, 2, 3], [5, 2, 1, 4], [2, 1]);
     * // => [1, 2]
     */
    function intersection() {
      var args = [],
          argsIndex = -1,
          argsLength = arguments.length,
          caches = getArray(),
          indexOf = getIndexOf(),
          trustIndexOf = indexOf === baseIndexOf,
          seen = getArray();

      while (++argsIndex < argsLength) {
        var value = arguments[argsIndex];
        if (isArray(value) || isArguments(value)) {
          args.push(value);
          caches.push(trustIndexOf && value.length >= largeArraySize &&
            createCache(argsIndex ? args[argsIndex] : seen));
        }
      }
      var array = args[0],
          index = -1,
          length = array ? array.length : 0,
          result = [];

      outer:
      while (++index < length) {
        var cache = caches[0];
        value = array[index];

        if ((cache ? cacheIndexOf(cache, value) : indexOf(seen, value)) < 0) {
          argsIndex = argsLength;
          (cache || seen).push(value);
          while (--argsIndex) {
            cache = caches[argsIndex];
            if ((cache ? cacheIndexOf(cache, value) : indexOf(args[argsIndex], value)) < 0) {
              continue outer;
            }
          }
          result.push(value);
        }
      }
      while (argsLength--) {
        cache = caches[argsLength];
        if (cache) {
          releaseObject(cache);
        }
      }
      releaseArray(caches);
      releaseArray(seen);
      return result;
    }

    /**
     * Gets the last element or last `n` elements of an array. If a callback is
     * provided elements at the end of the array are returned as long as the
     * callback returns truey. The callback is bound to `thisArg` and invoked
     * with three arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to query.
     * @param {Function|Object|number|string} [callback] The function called
     *  per element or the number of elements to return. If a property name or
     *  object is provided it will be used to create a "_.pluck" or "_.where"
     *  style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {*} Returns the last element(s) of `array`.
     * @example
     *
     * _.last([1, 2, 3]);
     * // => 3
     *
     * _.last([1, 2, 3], 2);
     * // => [2, 3]
     *
     * _.last([1, 2, 3], function(num) {
     *   return num > 1;
     * });
     * // => [2, 3]
     *
     * var characters = [
     *   { 'name': 'barney',  'blocked': false, 'employer': 'slate' },
     *   { 'name': 'fred',    'blocked': true,  'employer': 'slate' },
     *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.pluck(_.last(characters, 'blocked'), 'name');
     * // => ['fred', 'pebbles']
     *
     * // using "_.where" callback shorthand
     * _.last(characters, { 'employer': 'na' });
     * // => [{ 'name': 'pebbles', 'blocked': true, 'employer': 'na' }]
     */
    function last(array, callback, thisArg) {
      var n = 0,
          length = array ? array.length : 0;

      if (typeof callback != 'number' && callback != null) {
        var index = length;
        callback = lodash.createCallback(callback, thisArg, 3);
        while (index-- && callback(array[index], index, array)) {
          n++;
        }
      } else {
        n = callback;
        if (n == null || thisArg) {
          return array ? array[length - 1] : undefined;
        }
      }
      return slice(array, nativeMax(0, length - n));
    }

    /**
     * Gets the index at which the last occurrence of `value` is found using strict
     * equality for comparisons, i.e. `===`. If `fromIndex` is negative, it is used
     * as the offset from the end of the collection.
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to search.
     * @param {*} value The value to search for.
     * @param {number} [fromIndex=array.length-1] The index to search from.
     * @returns {number} Returns the index of the matched value or `-1`.
     * @example
     *
     * _.lastIndexOf([1, 2, 3, 1, 2, 3], 2);
     * // => 4
     *
     * _.lastIndexOf([1, 2, 3, 1, 2, 3], 2, 3);
     * // => 1
     */
    function lastIndexOf(array, value, fromIndex) {
      var index = array ? array.length : 0;
      if (typeof fromIndex == 'number') {
        index = (fromIndex < 0 ? nativeMax(0, index + fromIndex) : nativeMin(fromIndex, index - 1)) + 1;
      }
      while (index--) {
        if (array[index] === value) {
          return index;
        }
      }
      return -1;
    }

    /**
     * Removes all provided values from the given array using strict equality for
     * comparisons, i.e. `===`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to modify.
     * @param {...*} [value] The values to remove.
     * @returns {Array} Returns `array`.
     * @example
     *
     * var array = [1, 2, 3, 1, 2, 3];
     * _.pull(array, 2, 3);
     * console.log(array);
     * // => [1, 1]
     */
    function pull(array) {
      var args = arguments,
          argsIndex = 0,
          argsLength = args.length,
          length = array ? array.length : 0;

      while (++argsIndex < argsLength) {
        var index = -1,
            value = args[argsIndex];
        while (++index < length) {
          if (array[index] === value) {
            splice.call(array, index--, 1);
            length--;
          }
        }
      }
      return array;
    }

    /**
     * Creates an array of numbers (positive and/or negative) progressing from
     * `start` up to but not including `end`. If `start` is less than `stop` a
     * zero-length range is created unless a negative `step` is specified.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {number} [start=0] The start of the range.
     * @param {number} end The end of the range.
     * @param {number} [step=1] The value to increment or decrement by.
     * @returns {Array} Returns a new range array.
     * @example
     *
     * _.range(4);
     * // => [0, 1, 2, 3]
     *
     * _.range(1, 5);
     * // => [1, 2, 3, 4]
     *
     * _.range(0, 20, 5);
     * // => [0, 5, 10, 15]
     *
     * _.range(0, -4, -1);
     * // => [0, -1, -2, -3]
     *
     * _.range(1, 4, 0);
     * // => [1, 1, 1]
     *
     * _.range(0);
     * // => []
     */
    function range(start, end, step) {
      start = +start || 0;
      step = typeof step == 'number' ? step : (+step || 1);

      if (end == null) {
        end = start;
        start = 0;
      }
      // use `Array(length)` so engines like Chakra and V8 avoid slower modes
      // http://youtu.be/XAqIpGU8ZZk#t=17m25s
      var index = -1,
          length = nativeMax(0, ceil((end - start) / (step || 1))),
          result = Array(length);

      while (++index < length) {
        result[index] = start;
        start += step;
      }
      return result;
    }

    /**
     * Removes all elements from an array that the callback returns truey for
     * and returns an array of removed elements. The callback is bound to `thisArg`
     * and invoked with three arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to modify.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a new array of removed elements.
     * @example
     *
     * var array = [1, 2, 3, 4, 5, 6];
     * var evens = _.remove(array, function(num) { return num % 2 == 0; });
     *
     * console.log(array);
     * // => [1, 3, 5]
     *
     * console.log(evens);
     * // => [2, 4, 6]
     */
    function remove(array, callback, thisArg) {
      var index = -1,
          length = array ? array.length : 0,
          result = [];

      callback = lodash.createCallback(callback, thisArg, 3);
      while (++index < length) {
        var value = array[index];
        if (callback(value, index, array)) {
          result.push(value);
          splice.call(array, index--, 1);
          length--;
        }
      }
      return result;
    }

    /**
     * The opposite of `_.initial` this method gets all but the first element or
     * first `n` elements of an array. If a callback function is provided elements
     * at the beginning of the array are excluded from the result as long as the
     * callback returns truey. The callback is bound to `thisArg` and invoked
     * with three arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias drop, tail
     * @category Arrays
     * @param {Array} array The array to query.
     * @param {Function|Object|number|string} [callback=1] The function called
     *  per element or the number of elements to exclude. If a property name or
     *  object is provided it will be used to create a "_.pluck" or "_.where"
     *  style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a slice of `array`.
     * @example
     *
     * _.rest([1, 2, 3]);
     * // => [2, 3]
     *
     * _.rest([1, 2, 3], 2);
     * // => [3]
     *
     * _.rest([1, 2, 3], function(num) {
     *   return num < 3;
     * });
     * // => [3]
     *
     * var characters = [
     *   { 'name': 'barney',  'blocked': true,  'employer': 'slate' },
     *   { 'name': 'fred',    'blocked': false,  'employer': 'slate' },
     *   { 'name': 'pebbles', 'blocked': true, 'employer': 'na' }
     * ];
     *
     * // using "_.pluck" callback shorthand
     * _.pluck(_.rest(characters, 'blocked'), 'name');
     * // => ['fred', 'pebbles']
     *
     * // using "_.where" callback shorthand
     * _.rest(characters, { 'employer': 'slate' });
     * // => [{ 'name': 'pebbles', 'blocked': true, 'employer': 'na' }]
     */
    function rest(array, callback, thisArg) {
      if (typeof callback != 'number' && callback != null) {
        var n = 0,
            index = -1,
            length = array ? array.length : 0;

        callback = lodash.createCallback(callback, thisArg, 3);
        while (++index < length && callback(array[index], index, array)) {
          n++;
        }
      } else {
        n = (callback == null || thisArg) ? 1 : nativeMax(0, callback);
      }
      return slice(array, n);
    }

    /**
     * Uses a binary search to determine the smallest index at which a value
     * should be inserted into a given sorted array in order to maintain the sort
     * order of the array. If a callback is provided it will be executed for
     * `value` and each element of `array` to compute their sort ranking. The
     * callback is bound to `thisArg` and invoked with one argument; (value).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to inspect.
     * @param {*} value The value to evaluate.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {number} Returns the index at which `value` should be inserted
     *  into `array`.
     * @example
     *
     * _.sortedIndex([20, 30, 50], 40);
     * // => 2
     *
     * // using "_.pluck" callback shorthand
     * _.sortedIndex([{ 'x': 20 }, { 'x': 30 }, { 'x': 50 }], { 'x': 40 }, 'x');
     * // => 2
     *
     * var dict = {
     *   'wordToNumber': { 'twenty': 20, 'thirty': 30, 'fourty': 40, 'fifty': 50 }
     * };
     *
     * _.sortedIndex(['twenty', 'thirty', 'fifty'], 'fourty', function(word) {
     *   return dict.wordToNumber[word];
     * });
     * // => 2
     *
     * _.sortedIndex(['twenty', 'thirty', 'fifty'], 'fourty', function(word) {
     *   return this.wordToNumber[word];
     * }, dict);
     * // => 2
     */
    function sortedIndex(array, value, callback, thisArg) {
      var low = 0,
          high = array ? array.length : low;

      // explicitly reference `identity` for better inlining in Firefox
      callback = callback ? lodash.createCallback(callback, thisArg, 1) : identity;
      value = callback(value);

      while (low < high) {
        var mid = (low + high) >>> 1;
        (callback(array[mid]) < value)
          ? low = mid + 1
          : high = mid;
      }
      return low;
    }

    /**
     * Creates an array of unique values, in order, of the provided arrays using
     * strict equality for comparisons, i.e. `===`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {...Array} [array] The arrays to inspect.
     * @returns {Array} Returns an array of combined values.
     * @example
     *
     * _.union([1, 2, 3], [5, 2, 1, 4], [2, 1]);
     * // => [1, 2, 3, 5, 4]
     */
    function union() {
      return baseUniq(baseFlatten(arguments, true, true));
    }

    /**
     * Creates a duplicate-value-free version of an array using strict equality
     * for comparisons, i.e. `===`. If the array is sorted, providing
     * `true` for `isSorted` will use a faster algorithm. If a callback is provided
     * each element of `array` is passed through the callback before uniqueness
     * is computed. The callback is bound to `thisArg` and invoked with three
     * arguments; (value, index, array).
     *
     * If a property name is provided for `callback` the created "_.pluck" style
     * callback will return the property value of the given element.
     *
     * If an object is provided for `callback` the created "_.where" style callback
     * will return `true` for elements that have the properties of the given object,
     * else `false`.
     *
     * @static
     * @memberOf _
     * @alias unique
     * @category Arrays
     * @param {Array} array The array to process.
     * @param {boolean} [isSorted=false] A flag to indicate that `array` is sorted.
     * @param {Function|Object|string} [callback=identity] The function called
     *  per iteration. If a property name or object is provided it will be used
     *  to create a "_.pluck" or "_.where" style callback, respectively.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns a duplicate-value-free array.
     * @example
     *
     * _.uniq([1, 2, 1, 3, 1]);
     * // => [1, 2, 3]
     *
     * _.uniq([1, 1, 2, 2, 3], true);
     * // => [1, 2, 3]
     *
     * _.uniq(['A', 'b', 'C', 'a', 'B', 'c'], function(letter) { return letter.toLowerCase(); });
     * // => ['A', 'b', 'C']
     *
     * _.uniq([1, 2.5, 3, 1.5, 2, 3.5], function(num) { return this.floor(num); }, Math);
     * // => [1, 2.5, 3]
     *
     * // using "_.pluck" callback shorthand
     * _.uniq([{ 'x': 1 }, { 'x': 2 }, { 'x': 1 }], 'x');
     * // => [{ 'x': 1 }, { 'x': 2 }]
     */
    function uniq(array, isSorted, callback, thisArg) {
      // juggle arguments
      if (typeof isSorted != 'boolean' && isSorted != null) {
        thisArg = callback;
        callback = (typeof isSorted != 'function' && thisArg && thisArg[isSorted] === array) ? null : isSorted;
        isSorted = false;
      }
      if (callback != null) {
        callback = lodash.createCallback(callback, thisArg, 3);
      }
      return baseUniq(array, isSorted, callback);
    }

    /**
     * Creates an array excluding all provided values using strict equality for
     * comparisons, i.e. `===`.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {Array} array The array to filter.
     * @param {...*} [value] The values to exclude.
     * @returns {Array} Returns a new array of filtered values.
     * @example
     *
     * _.without([1, 2, 1, 0, 3, 1, 4], 0, 1);
     * // => [2, 3, 4]
     */
    function without(array) {
      return baseDifference(array, slice(arguments, 1));
    }

    /**
     * Creates an array that is the symmetric difference of the provided arrays.
     * See http://en.wikipedia.org/wiki/Symmetric_difference.
     *
     * @static
     * @memberOf _
     * @category Arrays
     * @param {...Array} [array] The arrays to inspect.
     * @returns {Array} Returns an array of values.
     * @example
     *
     * _.xor([1, 2, 3], [5, 2, 1, 4]);
     * // => [3, 5, 4]
     *
     * _.xor([1, 2, 5], [2, 3, 5], [3, 4, 5]);
     * // => [1, 4, 5]
     */
    function xor() {
      var index = -1,
          length = arguments.length;

      while (++index < length) {
        var array = arguments[index];
        if (isArray(array) || isArguments(array)) {
          var result = result
            ? baseUniq(baseDifference(result, array).concat(baseDifference(array, result)))
            : array;
        }
      }
      return result || [];
    }

    /**
     * Creates an array of grouped elements, the first of which contains the first
     * elements of the given arrays, the second of which contains the second
     * elements of the given arrays, and so on.
     *
     * @static
     * @memberOf _
     * @alias unzip
     * @category Arrays
     * @param {...Array} [array] Arrays to process.
     * @returns {Array} Returns a new array of grouped elements.
     * @example
     *
     * _.zip(['fred', 'barney'], [30, 40], [true, false]);
     * // => [['fred', 30, true], ['barney', 40, false]]
     */
    function zip() {
      var array = arguments.length > 1 ? arguments : arguments[0],
          index = -1,
          length = array ? max(pluck(array, 'length')) : 0,
          result = Array(length < 0 ? 0 : length);

      while (++index < length) {
        result[index] = pluck(array, index);
      }
      return result;
    }

    /**
     * Creates an object composed from arrays of `keys` and `values`. Provide
     * either a single two dimensional array, i.e. `[[key1, value1], [key2, value2]]`
     * or two arrays, one of `keys` and one of corresponding `values`.
     *
     * @static
     * @memberOf _
     * @alias object
     * @category Arrays
     * @param {Array} keys The array of keys.
     * @param {Array} [values=[]] The array of values.
     * @returns {Object} Returns an object composed of the given keys and
     *  corresponding values.
     * @example
     *
     * _.zipObject(['fred', 'barney'], [30, 40]);
     * // => { 'fred': 30, 'barney': 40 }
     */
    function zipObject(keys, values) {
      var index = -1,
          length = keys ? keys.length : 0,
          result = {};

      if (!values && length && !isArray(keys[0])) {
        values = [];
      }
      while (++index < length) {
        var key = keys[index];
        if (values) {
          result[key] = values[index];
        } else if (key) {
          result[key[0]] = key[1];
        }
      }
      return result;
    }

    /*--------------------------------------------------------------------------*/

    /**
     * Creates a function that executes `func`, with  the `this` binding and
     * arguments of the created function, only after being called `n` times.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {number} n The number of times the function must be called before
     *  `func` is executed.
     * @param {Function} func The function to restrict.
     * @returns {Function} Returns the new restricted function.
     * @example
     *
     * var saves = ['profile', 'settings'];
     *
     * var done = _.after(saves.length, function() {
     *   console.log('Done saving!');
     * });
     *
     * _.forEach(saves, function(type) {
     *   asyncSave({ 'type': type, 'complete': done });
     * });
     * // => logs 'Done saving!', after all saves have completed
     */
    function after(n, func) {
      if (!isFunction(func)) {
        throw new TypeError;
      }
      return function() {
        if (--n < 1) {
          return func.apply(this, arguments);
        }
      };
    }

    /**
     * Creates a function that, when called, invokes `func` with the `this`
     * binding of `thisArg` and prepends any additional `bind` arguments to those
     * provided to the bound function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to bind.
     * @param {*} [thisArg] The `this` binding of `func`.
     * @param {...*} [arg] Arguments to be partially applied.
     * @returns {Function} Returns the new bound function.
     * @example
     *
     * var func = function(greeting) {
     *   return greeting + ' ' + this.name;
     * };
     *
     * func = _.bind(func, { 'name': 'fred' }, 'hi');
     * func();
     * // => 'hi fred'
     */
    function bind(func, thisArg) {
      return arguments.length > 2
        ? createWrapper(func, 17, slice(arguments, 2), null, thisArg)
        : createWrapper(func, 1, null, null, thisArg);
    }

    /**
     * Binds methods of an object to the object itself, overwriting the existing
     * method. Method names may be specified as individual arguments or as arrays
     * of method names. If no method names are provided all the function properties
     * of `object` will be bound.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Object} object The object to bind and assign the bound methods to.
     * @param {...string} [methodName] The object method names to
     *  bind, specified as individual method names or arrays of method names.
     * @returns {Object} Returns `object`.
     * @example
     *
     * var view = {
     *   'label': 'docs',
     *   'onClick': function() { console.log('clicked ' + this.label); }
     * };
     *
     * _.bindAll(view);
     * jQuery('#docs').on('click', view.onClick);
     * // => logs 'clicked docs', when the button is clicked
     */
    function bindAll(object) {
      var funcs = arguments.length > 1 ? baseFlatten(arguments, true, false, 1) : functions(object),
          index = -1,
          length = funcs.length;

      while (++index < length) {
        var key = funcs[index];
        object[key] = createWrapper(object[key], 1, null, null, object);
      }
      return object;
    }

    /**
     * Creates a function that, when called, invokes the method at `object[key]`
     * and prepends any additional `bindKey` arguments to those provided to the bound
     * function. This method differs from `_.bind` by allowing bound functions to
     * reference methods that will be redefined or don't yet exist.
     * See http://michaux.ca/articles/lazy-function-definition-pattern.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Object} object The object the method belongs to.
     * @param {string} key The key of the method.
     * @param {...*} [arg] Arguments to be partially applied.
     * @returns {Function} Returns the new bound function.
     * @example
     *
     * var object = {
     *   'name': 'fred',
     *   'greet': function(greeting) {
     *     return greeting + ' ' + this.name;
     *   }
     * };
     *
     * var func = _.bindKey(object, 'greet', 'hi');
     * func();
     * // => 'hi fred'
     *
     * object.greet = function(greeting) {
     *   return greeting + 'ya ' + this.name + '!';
     * };
     *
     * func();
     * // => 'hiya fred!'
     */
    function bindKey(object, key) {
      return arguments.length > 2
        ? createWrapper(key, 19, slice(arguments, 2), null, object)
        : createWrapper(key, 3, null, null, object);
    }

    /**
     * Creates a function that is the composition of the provided functions,
     * where each function consumes the return value of the function that follows.
     * For example, composing the functions `f()`, `g()`, and `h()` produces `f(g(h()))`.
     * Each function is executed with the `this` binding of the composed function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {...Function} [func] Functions to compose.
     * @returns {Function} Returns the new composed function.
     * @example
     *
     * var realNameMap = {
     *   'pebbles': 'penelope'
     * };
     *
     * var format = function(name) {
     *   name = realNameMap[name.toLowerCase()] || name;
     *   return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
     * };
     *
     * var greet = function(formatted) {
     *   return 'Hiya ' + formatted + '!';
     * };
     *
     * var welcome = _.compose(greet, format);
     * welcome('pebbles');
     * // => 'Hiya Penelope!'
     */
    function compose() {
      var funcs = arguments,
          length = funcs.length;

      while (length--) {
        if (!isFunction(funcs[length])) {
          throw new TypeError;
        }
      }
      return function() {
        var args = arguments,
            length = funcs.length;

        while (length--) {
          args = [funcs[length].apply(this, args)];
        }
        return args[0];
      };
    }

    /**
     * Creates a function which accepts one or more arguments of `func` that when
     * invoked either executes `func` returning its result, if all `func` arguments
     * have been provided, or returns a function that accepts one or more of the
     * remaining `func` arguments, and so on. The arity of `func` can be specified
     * if `func.length` is not sufficient.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to curry.
     * @param {number} [arity=func.length] The arity of `func`.
     * @returns {Function} Returns the new curried function.
     * @example
     *
     * var curried = _.curry(function(a, b, c) {
     *   console.log(a + b + c);
     * });
     *
     * curried(1)(2)(3);
     * // => 6
     *
     * curried(1, 2)(3);
     * // => 6
     *
     * curried(1, 2, 3);
     * // => 6
     */
    function curry(func, arity) {
      arity = typeof arity == 'number' ? arity : (+arity || func.length);
      return createWrapper(func, 4, null, null, null, arity);
    }

    /**
     * Creates a function that will delay the execution of `func` until after
     * `wait` milliseconds have elapsed since the last time it was invoked.
     * Provide an options object to indicate that `func` should be invoked on
     * the leading and/or trailing edge of the `wait` timeout. Subsequent calls
     * to the debounced function will return the result of the last `func` call.
     *
     * Note: If `leading` and `trailing` options are `true` `func` will be called
     * on the trailing edge of the timeout only if the the debounced function is
     * invoked more than once during the `wait` timeout.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to debounce.
     * @param {number} wait The number of milliseconds to delay.
     * @param {Object} [options] The options object.
     * @param {boolean} [options.leading=false] Specify execution on the leading edge of the timeout.
     * @param {number} [options.maxWait] The maximum time `func` is allowed to be delayed before it's called.
     * @param {boolean} [options.trailing=true] Specify execution on the trailing edge of the timeout.
     * @returns {Function} Returns the new debounced function.
     * @example
     *
     * // avoid costly calculations while the window size is in flux
     * var lazyLayout = _.debounce(calculateLayout, 150);
     * jQuery(window).on('resize', lazyLayout);
     *
     * // execute `sendMail` when the click event is fired, debouncing subsequent calls
     * jQuery('#postbox').on('click', _.debounce(sendMail, 300, {
     *   'leading': true,
     *   'trailing': false
     * });
     *
     * // ensure `batchLog` is executed once after 1 second of debounced calls
     * var source = new EventSource('/stream');
     * source.addEventListener('message', _.debounce(batchLog, 250, {
     *   'maxWait': 1000
     * }, false);
     */
    function debounce(func, wait, options) {
      var args,
          maxTimeoutId,
          result,
          stamp,
          thisArg,
          timeoutId,
          trailingCall,
          lastCalled = 0,
          maxWait = false,
          trailing = true;

      if (!isFunction(func)) {
        throw new TypeError;
      }
      wait = nativeMax(0, wait) || 0;
      if (options === true) {
        var leading = true;
        trailing = false;
      } else if (isObject(options)) {
        leading = options.leading;
        maxWait = 'maxWait' in options && (nativeMax(wait, options.maxWait) || 0);
        trailing = 'trailing' in options ? options.trailing : trailing;
      }
      var delayed = function() {
        var remaining = wait - (now() - stamp);
        if (remaining <= 0) {
          if (maxTimeoutId) {
            clearTimeout(maxTimeoutId);
          }
          var isCalled = trailingCall;
          maxTimeoutId = timeoutId = trailingCall = undefined;
          if (isCalled) {
            lastCalled = now();
            result = func.apply(thisArg, args);
            if (!timeoutId && !maxTimeoutId) {
              args = thisArg = null;
            }
          }
        } else {
          timeoutId = setTimeout(delayed, remaining);
        }
      };

      var maxDelayed = function() {
        if (timeoutId) {
          clearTimeout(timeoutId);
        }
        maxTimeoutId = timeoutId = trailingCall = undefined;
        if (trailing || (maxWait !== wait)) {
          lastCalled = now();
          result = func.apply(thisArg, args);
          if (!timeoutId && !maxTimeoutId) {
            args = thisArg = null;
          }
        }
      };

      return function() {
        args = arguments;
        stamp = now();
        thisArg = this;
        trailingCall = trailing && (timeoutId || !leading);

        if (maxWait === false) {
          var leadingCall = leading && !timeoutId;
        } else {
          if (!maxTimeoutId && !leading) {
            lastCalled = stamp;
          }
          var remaining = maxWait - (stamp - lastCalled),
              isCalled = remaining <= 0;

          if (isCalled) {
            if (maxTimeoutId) {
              maxTimeoutId = clearTimeout(maxTimeoutId);
            }
            lastCalled = stamp;
            result = func.apply(thisArg, args);
          }
          else if (!maxTimeoutId) {
            maxTimeoutId = setTimeout(maxDelayed, remaining);
          }
        }
        if (isCalled && timeoutId) {
          timeoutId = clearTimeout(timeoutId);
        }
        else if (!timeoutId && wait !== maxWait) {
          timeoutId = setTimeout(delayed, wait);
        }
        if (leadingCall) {
          isCalled = true;
          result = func.apply(thisArg, args);
        }
        if (isCalled && !timeoutId && !maxTimeoutId) {
          args = thisArg = null;
        }
        return result;
      };
    }

    /**
     * Defers executing the `func` function until the current call stack has cleared.
     * Additional arguments will be provided to `func` when it is invoked.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to defer.
     * @param {...*} [arg] Arguments to invoke the function with.
     * @returns {number} Returns the timer id.
     * @example
     *
     * _.defer(function(text) { console.log(text); }, 'deferred');
     * // logs 'deferred' after one or more milliseconds
     */
    function defer(func) {
      if (!isFunction(func)) {
        throw new TypeError;
      }
      var args = slice(arguments, 1);
      return setTimeout(function() { func.apply(undefined, args); }, 1);
    }

    /**
     * Executes the `func` function after `wait` milliseconds. Additional arguments
     * will be provided to `func` when it is invoked.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to delay.
     * @param {number} wait The number of milliseconds to delay execution.
     * @param {...*} [arg] Arguments to invoke the function with.
     * @returns {number} Returns the timer id.
     * @example
     *
     * _.delay(function(text) { console.log(text); }, 1000, 'later');
     * // => logs 'later' after one second
     */
    function delay(func, wait) {
      if (!isFunction(func)) {
        throw new TypeError;
      }
      var args = slice(arguments, 2);
      return setTimeout(function() { func.apply(undefined, args); }, wait);
    }

    /**
     * Creates a function that memoizes the result of `func`. If `resolver` is
     * provided it will be used to determine the cache key for storing the result
     * based on the arguments provided to the memoized function. By default, the
     * first argument provided to the memoized function is used as the cache key.
     * The `func` is executed with the `this` binding of the memoized function.
     * The result cache is exposed as the `cache` property on the memoized function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to have its output memoized.
     * @param {Function} [resolver] A function used to resolve the cache key.
     * @returns {Function} Returns the new memoizing function.
     * @example
     *
     * var fibonacci = _.memoize(function(n) {
     *   return n < 2 ? n : fibonacci(n - 1) + fibonacci(n - 2);
     * });
     *
     * fibonacci(9)
     * // => 34
     *
     * var data = {
     *   'fred': { 'name': 'fred', 'age': 40 },
     *   'pebbles': { 'name': 'pebbles', 'age': 1 }
     * };
     *
     * // modifying the result cache
     * var get = _.memoize(function(name) { return data[name]; }, _.identity);
     * get('pebbles');
     * // => { 'name': 'pebbles', 'age': 1 }
     *
     * get.cache.pebbles.name = 'penelope';
     * get('pebbles');
     * // => { 'name': 'penelope', 'age': 1 }
     */
    function memoize(func, resolver) {
      if (!isFunction(func)) {
        throw new TypeError;
      }
      var memoized = function() {
        var cache = memoized.cache,
            key = resolver ? resolver.apply(this, arguments) : keyPrefix + arguments[0];

        return hasOwnProperty.call(cache, key)
          ? cache[key]
          : (cache[key] = func.apply(this, arguments));
      }
      memoized.cache = {};
      return memoized;
    }

    /**
     * Creates a function that is restricted to execute `func` once. Repeat calls to
     * the function will return the value of the first call. The `func` is executed
     * with the `this` binding of the created function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to restrict.
     * @returns {Function} Returns the new restricted function.
     * @example
     *
     * var initialize = _.once(createApplication);
     * initialize();
     * initialize();
     * // `initialize` executes `createApplication` once
     */
    function once(func) {
      var ran,
          result;

      if (!isFunction(func)) {
        throw new TypeError;
      }
      return function() {
        if (ran) {
          return result;
        }
        ran = true;
        result = func.apply(this, arguments);

        // clear the `func` variable so the function may be garbage collected
        func = null;
        return result;
      };
    }

    /**
     * Creates a function that, when called, invokes `func` with any additional
     * `partial` arguments prepended to those provided to the new function. This
     * method is similar to `_.bind` except it does **not** alter the `this` binding.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to partially apply arguments to.
     * @param {...*} [arg] Arguments to be partially applied.
     * @returns {Function} Returns the new partially applied function.
     * @example
     *
     * var greet = function(greeting, name) { return greeting + ' ' + name; };
     * var hi = _.partial(greet, 'hi');
     * hi('fred');
     * // => 'hi fred'
     */
    function partial(func) {
      return createWrapper(func, 16, slice(arguments, 1));
    }

    /**
     * This method is like `_.partial` except that `partial` arguments are
     * appended to those provided to the new function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to partially apply arguments to.
     * @param {...*} [arg] Arguments to be partially applied.
     * @returns {Function} Returns the new partially applied function.
     * @example
     *
     * var defaultsDeep = _.partialRight(_.merge, _.defaults);
     *
     * var options = {
     *   'variable': 'data',
     *   'imports': { 'jq': $ }
     * };
     *
     * defaultsDeep(options, _.templateSettings);
     *
     * options.variable
     * // => 'data'
     *
     * options.imports
     * // => { '_': _, 'jq': $ }
     */
    function partialRight(func) {
      return createWrapper(func, 32, null, slice(arguments, 1));
    }

    /**
     * Creates a function that, when executed, will only call the `func` function
     * at most once per every `wait` milliseconds. Provide an options object to
     * indicate that `func` should be invoked on the leading and/or trailing edge
     * of the `wait` timeout. Subsequent calls to the throttled function will
     * return the result of the last `func` call.
     *
     * Note: If `leading` and `trailing` options are `true` `func` will be called
     * on the trailing edge of the timeout only if the the throttled function is
     * invoked more than once during the `wait` timeout.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {Function} func The function to throttle.
     * @param {number} wait The number of milliseconds to throttle executions to.
     * @param {Object} [options] The options object.
     * @param {boolean} [options.leading=true] Specify execution on the leading edge of the timeout.
     * @param {boolean} [options.trailing=true] Specify execution on the trailing edge of the timeout.
     * @returns {Function} Returns the new throttled function.
     * @example
     *
     * // avoid excessively updating the position while scrolling
     * var throttled = _.throttle(updatePosition, 100);
     * jQuery(window).on('scroll', throttled);
     *
     * // execute `renewToken` when the click event is fired, but not more than once every 5 minutes
     * jQuery('.interactive').on('click', _.throttle(renewToken, 300000, {
     *   'trailing': false
     * }));
     */
    function throttle(func, wait, options) {
      var leading = true,
          trailing = true;

      if (!isFunction(func)) {
        throw new TypeError;
      }
      if (options === false) {
        leading = false;
      } else if (isObject(options)) {
        leading = 'leading' in options ? options.leading : leading;
        trailing = 'trailing' in options ? options.trailing : trailing;
      }
      debounceOptions.leading = leading;
      debounceOptions.maxWait = wait;
      debounceOptions.trailing = trailing;

      return debounce(func, wait, debounceOptions);
    }

    /**
     * Creates a function that provides `value` to the wrapper function as its
     * first argument. Additional arguments provided to the function are appended
     * to those provided to the wrapper function. The wrapper is executed with
     * the `this` binding of the created function.
     *
     * @static
     * @memberOf _
     * @category Functions
     * @param {*} value The value to wrap.
     * @param {Function} wrapper The wrapper function.
     * @returns {Function} Returns the new function.
     * @example
     *
     * var p = _.wrap(_.escape, function(func, text) {
     *   return '<p>' + func(text) + '</p>';
     * });
     *
     * p('Fred, Wilma, & Pebbles');
     * // => '<p>Fred, Wilma, &amp; Pebbles</p>'
     */
    function wrap(value, wrapper) {
      return createWrapper(wrapper, 16, [value]);
    }

    /*--------------------------------------------------------------------------*/

    /**
     * Creates a function that returns `value`.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {*} value The value to return from the new function.
     * @returns {Function} Returns the new function.
     * @example
     *
     * var object = { 'name': 'fred' };
     * var getter = _.constant(object);
     * getter() === object;
     * // => true
     */
    function constant(value) {
      return function() {
        return value;
      };
    }

    /**
     * Produces a callback bound to an optional `thisArg`. If `func` is a property
     * name the created callback will return the property value for a given element.
     * If `func` is an object the created callback will return `true` for elements
     * that contain the equivalent object properties, otherwise it will return `false`.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {*} [func=identity] The value to convert to a callback.
     * @param {*} [thisArg] The `this` binding of the created callback.
     * @param {number} [argCount] The number of arguments the callback accepts.
     * @returns {Function} Returns a callback function.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * // wrap to create custom callback shorthands
     * _.createCallback = _.wrap(_.createCallback, function(func, callback, thisArg) {
     *   var match = /^(.+?)__([gl]t)(.+)$/.exec(callback);
     *   return !match ? func(callback, thisArg) : function(object) {
     *     return match[2] == 'gt' ? object[match[1]] > match[3] : object[match[1]] < match[3];
     *   };
     * });
     *
     * _.filter(characters, 'age__gt38');
     * // => [{ 'name': 'fred', 'age': 40 }]
     */
    function createCallback(func, thisArg, argCount) {
      var type = typeof func;
      if (func == null || type == 'function') {
        return baseCreateCallback(func, thisArg, argCount);
      }
      // handle "_.pluck" style callback shorthands
      if (type != 'object') {
        return property(func);
      }
      var props = keys(func),
          key = props[0],
          a = func[key];

      // handle "_.where" style callback shorthands
      if (props.length == 1 && a === a && !isObject(a)) {
        // fast path the common case of providing an object with a single
        // property containing a primitive value
        return function(object) {
          var b = object[key];
          return a === b && (a !== 0 || (1 / a == 1 / b));
        };
      }
      return function(object) {
        var length = props.length,
            result = false;

        while (length--) {
          if (!(result = baseIsEqual(object[props[length]], func[props[length]], null, true))) {
            break;
          }
        }
        return result;
      };
    }

    /**
     * Converts the characters `&`, `<`, `>`, `"`, and `'` in `string` to their
     * corresponding HTML entities.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} string The string to escape.
     * @returns {string} Returns the escaped string.
     * @example
     *
     * _.escape('Fred, Wilma, & Pebbles');
     * // => 'Fred, Wilma, &amp; Pebbles'
     */
    function escape(string) {
      return string == null ? '' : String(string).replace(reUnescapedHtml, escapeHtmlChar);
    }

    /**
     * This method returns the first argument provided to it.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {*} value Any value.
     * @returns {*} Returns `value`.
     * @example
     *
     * var object = { 'name': 'fred' };
     * _.identity(object) === object;
     * // => true
     */
    function identity(value) {
      return value;
    }

    /**
     * Adds function properties of a source object to the destination object.
     * If `object` is a function methods will be added to its prototype as well.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {Function|Object} [object=lodash] object The destination object.
     * @param {Object} source The object of functions to add.
     * @param {Object} [options] The options object.
     * @param {boolean} [options.chain=true] Specify whether the functions added are chainable.
     * @example
     *
     * function capitalize(string) {
     *   return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
     * }
     *
     * _.mixin({ 'capitalize': capitalize });
     * _.capitalize('fred');
     * // => 'Fred'
     *
     * _('fred').capitalize().value();
     * // => 'Fred'
     *
     * _.mixin({ 'capitalize': capitalize }, { 'chain': false });
     * _('fred').capitalize();
     * // => 'Fred'
     */
    function mixin(object, source, options) {
      var chain = true,
          methodNames = source && functions(source);

      if (!source || (!options && !methodNames.length)) {
        if (options == null) {
          options = source;
        }
        ctor = lodashWrapper;
        source = object;
        object = lodash;
        methodNames = functions(source);
      }
      if (options === false) {
        chain = false;
      } else if (isObject(options) && 'chain' in options) {
        chain = options.chain;
      }
      var ctor = object,
          isFunc = isFunction(ctor);

      forEach(methodNames, function(methodName) {
        var func = object[methodName] = source[methodName];
        if (isFunc) {
          ctor.prototype[methodName] = function() {
            var chainAll = this.__chain__,
                value = this.__wrapped__,
                args = [value];

            push.apply(args, arguments);
            var result = func.apply(object, args);
            if (chain || chainAll) {
              if (value === result && isObject(result)) {
                return this;
              }
              result = new ctor(result);
              result.__chain__ = chainAll;
            }
            return result;
          };
        }
      });
    }

    /**
     * Reverts the '_' variable to its previous value and returns a reference to
     * the `lodash` function.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @returns {Function} Returns the `lodash` function.
     * @example
     *
     * var lodash = _.noConflict();
     */
    function noConflict() {
      context._ = oldDash;
      return this;
    }

    /**
     * A no-operation function.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @example
     *
     * var object = { 'name': 'fred' };
     * _.noop(object) === undefined;
     * // => true
     */
    function noop() {
      // no operation performed
    }

    /**
     * Gets the number of milliseconds that have elapsed since the Unix epoch
     * (1 January 1970 00:00:00 UTC).
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @example
     *
     * var stamp = _.now();
     * _.defer(function() { console.log(_.now() - stamp); });
     * // => logs the number of milliseconds it took for the deferred function to be called
     */
    var now = isNative(now = Date.now) && now || function() {
      return new Date().getTime();
    };

    /**
     * Converts the given value into an integer of the specified radix.
     * If `radix` is `undefined` or `0` a `radix` of `10` is used unless the
     * `value` is a hexadecimal, in which case a `radix` of `16` is used.
     *
     * Note: This method avoids differences in native ES3 and ES5 `parseInt`
     * implementations. See http://es5.github.io/#E.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} value The value to parse.
     * @param {number} [radix] The radix used to interpret the value to parse.
     * @returns {number} Returns the new integer value.
     * @example
     *
     * _.parseInt('08');
     * // => 8
     */
    var parseInt = nativeParseInt(whitespace + '08') == 8 ? nativeParseInt : function(value, radix) {
      // Firefox < 21 and Opera < 15 follow the ES3 specified implementation of `parseInt`
      return nativeParseInt(isString(value) ? value.replace(reLeadingSpacesAndZeros, '') : value, radix || 0);
    };

    /**
     * Creates a "_.pluck" style function, which returns the `key` value of a
     * given object.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} key The name of the property to retrieve.
     * @returns {Function} Returns the new function.
     * @example
     *
     * var characters = [
     *   { 'name': 'fred',   'age': 40 },
     *   { 'name': 'barney', 'age': 36 }
     * ];
     *
     * var getName = _.property('name');
     *
     * _.map(characters, getName);
     * // => ['barney', 'fred']
     *
     * _.sortBy(characters, getName);
     * // => [{ 'name': 'barney', 'age': 36 }, { 'name': 'fred',   'age': 40 }]
     */
    function property(key) {
      return function(object) {
        return object[key];
      };
    }

    /**
     * Produces a random number between `min` and `max` (inclusive). If only one
     * argument is provided a number between `0` and the given number will be
     * returned. If `floating` is truey or either `min` or `max` are floats a
     * floating-point number will be returned instead of an integer.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {number} [min=0] The minimum possible value.
     * @param {number} [max=1] The maximum possible value.
     * @param {boolean} [floating=false] Specify returning a floating-point number.
     * @returns {number} Returns a random number.
     * @example
     *
     * _.random(0, 5);
     * // => an integer between 0 and 5
     *
     * _.random(5);
     * // => also an integer between 0 and 5
     *
     * _.random(5, true);
     * // => a floating-point number between 0 and 5
     *
     * _.random(1.2, 5.2);
     * // => a floating-point number between 1.2 and 5.2
     */
    function random(min, max, floating) {
      var noMin = min == null,
          noMax = max == null;

      if (floating == null) {
        if (typeof min == 'boolean' && noMax) {
          floating = min;
          min = 1;
        }
        else if (!noMax && typeof max == 'boolean') {
          floating = max;
          noMax = true;
        }
      }
      if (noMin && noMax) {
        max = 1;
      }
      min = +min || 0;
      if (noMax) {
        max = min;
        min = 0;
      } else {
        max = +max || 0;
      }
      if (floating || min % 1 || max % 1) {
        var rand = nativeRandom();
        return nativeMin(min + (rand * (max - min + parseFloat('1e-' + ((rand +'').length - 1)))), max);
      }
      return baseRandom(min, max);
    }

    /**
     * Resolves the value of property `key` on `object`. If `key` is a function
     * it will be invoked with the `this` binding of `object` and its result returned,
     * else the property value is returned. If `object` is falsey then `undefined`
     * is returned.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {Object} object The object to inspect.
     * @param {string} key The name of the property to resolve.
     * @returns {*} Returns the resolved value.
     * @example
     *
     * var object = {
     *   'cheese': 'crumpets',
     *   'stuff': function() {
     *     return 'nonsense';
     *   }
     * };
     *
     * _.result(object, 'cheese');
     * // => 'crumpets'
     *
     * _.result(object, 'stuff');
     * // => 'nonsense'
     */
    function result(object, key) {
      if (object) {
        var value = object[key];
        return isFunction(value) ? object[key]() : value;
      }
    }

    /**
     * A micro-templating method that handles arbitrary delimiters, preserves
     * whitespace, and correctly escapes quotes within interpolated code.
     *
     * Note: In the development build, `_.template` utilizes sourceURLs for easier
     * debugging. See http://www.html5rocks.com/en/tutorials/developertools/sourcemaps/#toc-sourceurl
     *
     * For more information on precompiling templates see:
     * https://lodash.com/custom-builds
     *
     * For more information on Chrome extension sandboxes see:
     * http://developer.chrome.com/stable/extensions/sandboxingEval.html
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} text The template text.
     * @param {Object} data The data object used to populate the text.
     * @param {Object} [options] The options object.
     * @param {RegExp} [options.escape] The "escape" delimiter.
     * @param {RegExp} [options.evaluate] The "evaluate" delimiter.
     * @param {Object} [options.imports] An object to import into the template as local variables.
     * @param {RegExp} [options.interpolate] The "interpolate" delimiter.
     * @param {string} [sourceURL] The sourceURL of the template's compiled source.
     * @param {string} [variable] The data object variable name.
     * @returns {Function|string} Returns a compiled function when no `data` object
     *  is given, else it returns the interpolated text.
     * @example
     *
     * // using the "interpolate" delimiter to create a compiled template
     * var compiled = _.template('hello <%= name %>');
     * compiled({ 'name': 'fred' });
     * // => 'hello fred'
     *
     * // using the "escape" delimiter to escape HTML in data property values
     * _.template('<b><%- value %></b>', { 'value': '<script>' });
     * // => '<b>&lt;script&gt;</b>'
     *
     * // using the "evaluate" delimiter to generate HTML
     * var list = '<% _.forEach(people, function(name) { %><li><%- name %></li><% }); %>';
     * _.template(list, { 'people': ['fred', 'barney'] });
     * // => '<li>fred</li><li>barney</li>'
     *
     * // using the ES6 delimiter as an alternative to the default "interpolate" delimiter
     * _.template('hello ${ name }', { 'name': 'pebbles' });
     * // => 'hello pebbles'
     *
     * // using the internal `print` function in "evaluate" delimiters
     * _.template('<% print("hello " + name); %>!', { 'name': 'barney' });
     * // => 'hello barney!'
     *
     * // using a custom template delimiters
     * _.templateSettings = {
     *   'interpolate': /{{([\s\S]+?)}}/g
     * };
     *
     * _.template('hello {{ name }}!', { 'name': 'mustache' });
     * // => 'hello mustache!'
     *
     * // using the `imports` option to import jQuery
     * var list = '<% jq.each(people, function(name) { %><li><%- name %></li><% }); %>';
     * _.template(list, { 'people': ['fred', 'barney'] }, { 'imports': { 'jq': jQuery } });
     * // => '<li>fred</li><li>barney</li>'
     *
     * // using the `sourceURL` option to specify a custom sourceURL for the template
     * var compiled = _.template('hello <%= name %>', null, { 'sourceURL': '/basic/greeting.jst' });
     * compiled(data);
     * // => find the source of "greeting.jst" under the Sources tab or Resources panel of the web inspector
     *
     * // using the `variable` option to ensure a with-statement isn't used in the compiled template
     * var compiled = _.template('hi <%= data.name %>!', null, { 'variable': 'data' });
     * compiled.source;
     * // => function(data) {
     *   var __t, __p = '', __e = _.escape;
     *   __p += 'hi ' + ((__t = ( data.name )) == null ? '' : __t) + '!';
     *   return __p;
     * }
     *
     * // using the `source` property to inline compiled templates for meaningful
     * // line numbers in error messages and a stack trace
     * fs.writeFileSync(path.join(cwd, 'jst.js'), '\
     *   var JST = {\
     *     "main": ' + _.template(mainText).source + '\
     *   };\
     * ');
     */
    function template(text, data, options) {
      // based on John Resig's `tmpl` implementation
      // http://ejohn.org/blog/javascript-micro-templating/
      // and Laura Doktorova's doT.js
      // https://github.com/olado/doT
      var settings = lodash.templateSettings;
      text = String(text || '');

      // avoid missing dependencies when `iteratorTemplate` is not defined
      options = defaults({}, options, settings);

      var imports = defaults({}, options.imports, settings.imports),
          importsKeys = keys(imports),
          importsValues = values(imports);

      var isEvaluating,
          index = 0,
          interpolate = options.interpolate || reNoMatch,
          source = "__p += '";

      // compile the regexp to match each delimiter
      var reDelimiters = RegExp(
        (options.escape || reNoMatch).source + '|' +
        interpolate.source + '|' +
        (interpolate === reInterpolate ? reEsTemplate : reNoMatch).source + '|' +
        (options.evaluate || reNoMatch).source + '|$'
      , 'g');

      text.replace(reDelimiters, function(match, escapeValue, interpolateValue, esTemplateValue, evaluateValue, offset) {
        interpolateValue || (interpolateValue = esTemplateValue);

        // escape characters that cannot be included in string literals
        source += text.slice(index, offset).replace(reUnescapedString, escapeStringChar);

        // replace delimiters with snippets
        if (escapeValue) {
          source += "' +\n__e(" + escapeValue + ") +\n'";
        }
        if (evaluateValue) {
          isEvaluating = true;
          source += "';\n" + evaluateValue + ";\n__p += '";
        }
        if (interpolateValue) {
          source += "' +\n((__t = (" + interpolateValue + ")) == null ? '' : __t) +\n'";
        }
        index = offset + match.length;

        // the JS engine embedded in Adobe products requires returning the `match`
        // string in order to produce the correct `offset` value
        return match;
      });

      source += "';\n";

      // if `variable` is not specified, wrap a with-statement around the generated
      // code to add the data object to the top of the scope chain
      var variable = options.variable,
          hasVariable = variable;

      if (!hasVariable) {
        variable = 'obj';
        source = 'with (' + variable + ') {\n' + source + '\n}\n';
      }
      // cleanup code by stripping empty strings
      source = (isEvaluating ? source.replace(reEmptyStringLeading, '') : source)
        .replace(reEmptyStringMiddle, '$1')
        .replace(reEmptyStringTrailing, '$1;');

      // frame code as the function body
      source = 'function(' + variable + ') {\n' +
        (hasVariable ? '' : variable + ' || (' + variable + ' = {});\n') +
        "var __t, __p = '', __e = _.escape" +
        (isEvaluating
          ? ', __j = Array.prototype.join;\n' +
            "function print() { __p += __j.call(arguments, '') }\n"
          : ';\n'
        ) +
        source +
        'return __p\n}';

      // Use a sourceURL for easier debugging.
      // http://www.html5rocks.com/en/tutorials/developertools/sourcemaps/#toc-sourceurl
      var sourceURL = '\n/*\n//# sourceURL=' + (options.sourceURL || '/lodash/template/source[' + (templateCounter++) + ']') + '\n*/';

      try {
        var result = Function(importsKeys, 'return ' + source + sourceURL).apply(undefined, importsValues);
      } catch(e) {
        e.source = source;
        throw e;
      }
      if (data) {
        return result(data);
      }
      // provide the compiled function's source by its `toString` method, in
      // supported environments, or the `source` property as a convenience for
      // inlining compiled templates during the build process
      result.source = source;
      return result;
    }

    /**
     * Executes the callback `n` times, returning an array of the results
     * of each callback execution. The callback is bound to `thisArg` and invoked
     * with one argument; (index).
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {number} n The number of times to execute the callback.
     * @param {Function} callback The function called per iteration.
     * @param {*} [thisArg] The `this` binding of `callback`.
     * @returns {Array} Returns an array of the results of each `callback` execution.
     * @example
     *
     * var diceRolls = _.times(3, _.partial(_.random, 1, 6));
     * // => [3, 6, 4]
     *
     * _.times(3, function(n) { mage.castSpell(n); });
     * // => calls `mage.castSpell(n)` three times, passing `n` of `0`, `1`, and `2` respectively
     *
     * _.times(3, function(n) { this.cast(n); }, mage);
     * // => also calls `mage.castSpell(n)` three times
     */
    function times(n, callback, thisArg) {
      n = (n = +n) > -1 ? n : 0;
      var index = -1,
          result = Array(n);

      callback = baseCreateCallback(callback, thisArg, 1);
      while (++index < n) {
        result[index] = callback(index);
      }
      return result;
    }

    /**
     * The inverse of `_.escape` this method converts the HTML entities
     * `&amp;`, `&lt;`, `&gt;`, `&quot;`, and `&#39;` in `string` to their
     * corresponding characters.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} string The string to unescape.
     * @returns {string} Returns the unescaped string.
     * @example
     *
     * _.unescape('Fred, Barney &amp; Pebbles');
     * // => 'Fred, Barney & Pebbles'
     */
    function unescape(string) {
      return string == null ? '' : String(string).replace(reEscapedHtml, unescapeHtmlChar);
    }

    /**
     * Generates a unique ID. If `prefix` is provided the ID will be appended to it.
     *
     * @static
     * @memberOf _
     * @category Utilities
     * @param {string} [prefix] The value to prefix the ID with.
     * @returns {string} Returns the unique ID.
     * @example
     *
     * _.uniqueId('contact_');
     * // => 'contact_104'
     *
     * _.uniqueId();
     * // => '105'
     */
    function uniqueId(prefix) {
      var id = ++idCounter;
      return String(prefix == null ? '' : prefix) + id;
    }

    /*--------------------------------------------------------------------------*/

    /**
     * Creates a `lodash` object that wraps the given value with explicit
     * method chaining enabled.
     *
     * @static
     * @memberOf _
     * @category Chaining
     * @param {*} value The value to wrap.
     * @returns {Object} Returns the wrapper object.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney',  'age': 36 },
     *   { 'name': 'fred',    'age': 40 },
     *   { 'name': 'pebbles', 'age': 1 }
     * ];
     *
     * var youngest = _.chain(characters)
     *     .sortBy('age')
     *     .map(function(chr) { return chr.name + ' is ' + chr.age; })
     *     .first()
     *     .value();
     * // => 'pebbles is 1'
     */
    function chain(value) {
      value = new lodashWrapper(value);
      value.__chain__ = true;
      return value;
    }

    /**
     * Invokes `interceptor` with the `value` as the first argument and then
     * returns `value`. The purpose of this method is to "tap into" a method
     * chain in order to perform operations on intermediate results within
     * the chain.
     *
     * @static
     * @memberOf _
     * @category Chaining
     * @param {*} value The value to provide to `interceptor`.
     * @param {Function} interceptor The function to invoke.
     * @returns {*} Returns `value`.
     * @example
     *
     * _([1, 2, 3, 4])
     *  .tap(function(array) { array.pop(); })
     *  .reverse()
     *  .value();
     * // => [3, 2, 1]
     */
    function tap(value, interceptor) {
      interceptor(value);
      return value;
    }

    /**
     * Enables explicit method chaining on the wrapper object.
     *
     * @name chain
     * @memberOf _
     * @category Chaining
     * @returns {*} Returns the wrapper object.
     * @example
     *
     * var characters = [
     *   { 'name': 'barney', 'age': 36 },
     *   { 'name': 'fred',   'age': 40 }
     * ];
     *
     * // without explicit chaining
     * _(characters).first();
     * // => { 'name': 'barney', 'age': 36 }
     *
     * // with explicit chaining
     * _(characters).chain()
     *   .first()
     *   .pick('age')
     *   .value();
     * // => { 'age': 36 }
     */
    function wrapperChain() {
      this.__chain__ = true;
      return this;
    }

    /**
     * Produces the `toString` result of the wrapped value.
     *
     * @name toString
     * @memberOf _
     * @category Chaining
     * @returns {string} Returns the string result.
     * @example
     *
     * _([1, 2, 3]).toString();
     * // => '1,2,3'
     */
    function wrapperToString() {
      return String(this.__wrapped__);
    }

    /**
     * Extracts the wrapped value.
     *
     * @name valueOf
     * @memberOf _
     * @alias value
     * @category Chaining
     * @returns {*} Returns the wrapped value.
     * @example
     *
     * _([1, 2, 3]).valueOf();
     * // => [1, 2, 3]
     */
    function wrapperValueOf() {
      return this.__wrapped__;
    }

    /*--------------------------------------------------------------------------*/

    // add functions that return wrapped values when chaining
    lodash.after = after;
    lodash.assign = assign;
    lodash.at = at;
    lodash.bind = bind;
    lodash.bindAll = bindAll;
    lodash.bindKey = bindKey;
    lodash.chain = chain;
    lodash.compact = compact;
    lodash.compose = compose;
    lodash.constant = constant;
    lodash.countBy = countBy;
    lodash.create = create;
    lodash.createCallback = createCallback;
    lodash.curry = curry;
    lodash.debounce = debounce;
    lodash.defaults = defaults;
    lodash.defer = defer;
    lodash.delay = delay;
    lodash.difference = difference;
    lodash.filter = filter;
    lodash.flatten = flatten;
    lodash.forEach = forEach;
    lodash.forEachRight = forEachRight;
    lodash.forIn = forIn;
    lodash.forInRight = forInRight;
    lodash.forOwn = forOwn;
    lodash.forOwnRight = forOwnRight;
    lodash.functions = functions;
    lodash.groupBy = groupBy;
    lodash.indexBy = indexBy;
    lodash.initial = initial;
    lodash.intersection = intersection;
    lodash.invert = invert;
    lodash.invoke = invoke;
    lodash.keys = keys;
    lodash.map = map;
    lodash.mapValues = mapValues;
    lodash.max = max;
    lodash.memoize = memoize;
    lodash.merge = merge;
    lodash.min = min;
    lodash.omit = omit;
    lodash.once = once;
    lodash.pairs = pairs;
    lodash.partial = partial;
    lodash.partialRight = partialRight;
    lodash.pick = pick;
    lodash.pluck = pluck;
    lodash.property = property;
    lodash.pull = pull;
    lodash.range = range;
    lodash.reject = reject;
    lodash.remove = remove;
    lodash.rest = rest;
    lodash.shuffle = shuffle;
    lodash.sortBy = sortBy;
    lodash.tap = tap;
    lodash.throttle = throttle;
    lodash.times = times;
    lodash.toArray = toArray;
    lodash.transform = transform;
    lodash.union = union;
    lodash.uniq = uniq;
    lodash.values = values;
    lodash.where = where;
    lodash.without = without;
    lodash.wrap = wrap;
    lodash.xor = xor;
    lodash.zip = zip;
    lodash.zipObject = zipObject;

    // add aliases
    lodash.collect = map;
    lodash.drop = rest;
    lodash.each = forEach;
    lodash.eachRight = forEachRight;
    lodash.extend = assign;
    lodash.methods = functions;
    lodash.object = zipObject;
    lodash.select = filter;
    lodash.tail = rest;
    lodash.unique = uniq;
    lodash.unzip = zip;

    // add functions to `lodash.prototype`
    mixin(lodash);

    /*--------------------------------------------------------------------------*/

    // add functions that return unwrapped values when chaining
    lodash.clone = clone;
    lodash.cloneDeep = cloneDeep;
    lodash.contains = contains;
    lodash.escape = escape;
    lodash.every = every;
    lodash.find = find;
    lodash.findIndex = findIndex;
    lodash.findKey = findKey;
    lodash.findLast = findLast;
    lodash.findLastIndex = findLastIndex;
    lodash.findLastKey = findLastKey;
    lodash.has = has;
    lodash.identity = identity;
    lodash.indexOf = indexOf;
    lodash.isArguments = isArguments;
    lodash.isArray = isArray;
    lodash.isBoolean = isBoolean;
    lodash.isDate = isDate;
    lodash.isElement = isElement;
    lodash.isEmpty = isEmpty;
    lodash.isEqual = isEqual;
    lodash.isFinite = isFinite;
    lodash.isFunction = isFunction;
    lodash.isNaN = isNaN;
    lodash.isNull = isNull;
    lodash.isNumber = isNumber;
    lodash.isObject = isObject;
    lodash.isPlainObject = isPlainObject;
    lodash.isRegExp = isRegExp;
    lodash.isString = isString;
    lodash.isUndefined = isUndefined;
    lodash.lastIndexOf = lastIndexOf;
    lodash.mixin = mixin;
    lodash.noConflict = noConflict;
    lodash.noop = noop;
    lodash.now = now;
    lodash.parseInt = parseInt;
    lodash.random = random;
    lodash.reduce = reduce;
    lodash.reduceRight = reduceRight;
    lodash.result = result;
    lodash.runInContext = runInContext;
    lodash.size = size;
    lodash.some = some;
    lodash.sortedIndex = sortedIndex;
    lodash.template = template;
    lodash.unescape = unescape;
    lodash.uniqueId = uniqueId;

    // add aliases
    lodash.all = every;
    lodash.any = some;
    lodash.detect = find;
    lodash.findWhere = find;
    lodash.foldl = reduce;
    lodash.foldr = reduceRight;
    lodash.include = contains;
    lodash.inject = reduce;

    mixin(function() {
      var source = {}
      forOwn(lodash, function(func, methodName) {
        if (!lodash.prototype[methodName]) {
          source[methodName] = func;
        }
      });
      return source;
    }(), false);

    /*--------------------------------------------------------------------------*/

    // add functions capable of returning wrapped and unwrapped values when chaining
    lodash.first = first;
    lodash.last = last;
    lodash.sample = sample;

    // add aliases
    lodash.take = first;
    lodash.head = first;

    forOwn(lodash, function(func, methodName) {
      var callbackable = methodName !== 'sample';
      if (!lodash.prototype[methodName]) {
        lodash.prototype[methodName]= function(n, guard) {
          var chainAll = this.__chain__,
              result = func(this.__wrapped__, n, guard);

          return !chainAll && (n == null || (guard && !(callbackable && typeof n == 'function')))
            ? result
            : new lodashWrapper(result, chainAll);
        };
      }
    });

    /*--------------------------------------------------------------------------*/

    /**
     * The semantic version number.
     *
     * @static
     * @memberOf _
     * @type string
     */
    lodash.VERSION = '2.4.2';

    // add "Chaining" functions to the wrapper
    lodash.prototype.chain = wrapperChain;
    lodash.prototype.toString = wrapperToString;
    lodash.prototype.value = wrapperValueOf;
    lodash.prototype.valueOf = wrapperValueOf;

    // add `Array` functions that return unwrapped values
    forEach(['join', 'pop', 'shift'], function(methodName) {
      var func = arrayRef[methodName];
      lodash.prototype[methodName] = function() {
        var chainAll = this.__chain__,
            result = func.apply(this.__wrapped__, arguments);

        return chainAll
          ? new lodashWrapper(result, chainAll)
          : result;
      };
    });

    // add `Array` functions that return the existing wrapped value
    forEach(['push', 'reverse', 'sort', 'unshift'], function(methodName) {
      var func = arrayRef[methodName];
      lodash.prototype[methodName] = function() {
        func.apply(this.__wrapped__, arguments);
        return this;
      };
    });

    // add `Array` functions that return new wrapped values
    forEach(['concat', 'slice', 'splice'], function(methodName) {
      var func = arrayRef[methodName];
      lodash.prototype[methodName] = function() {
        return new lodashWrapper(func.apply(this.__wrapped__, arguments), this.__chain__);
      };
    });

    return lodash;
  }

  /*--------------------------------------------------------------------------*/

  // expose Lo-Dash
  var _ = runInContext();

  // some AMD build optimizers like r.js check for condition patterns like the following:
  if (typeof define == 'function' && typeof define.amd == 'object' && define.amd) {
    // Expose Lo-Dash to the global object even when an AMD loader is present in
    // case Lo-Dash is loaded with a RequireJS shim config.
    // See http://requirejs.org/docs/api.html#config-shim
    root._ = _;

    // define as an anonymous module so, through path mapping, it can be
    // referenced as the "underscore" module
    define(function() {
      return _;
    });
  }
  // check for `exports` after `define` in case a build optimizer adds an `exports` object
  else if (freeExports && freeModule) {
    // in Node.js or RingoJS
    if (moduleExports) {
      (freeModule.exports = _)._ = _;
    }
    // in Narwhal or Rhino -require
    else {
      freeExports._ = _;
    }
  }
  else {
    // in a browser or Rhino
    root._ = _;
  }
}.call(this));

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],44:[function(require,module,exports){
(function (global){
//! moment.js
//! version : 2.8.4
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

(function (undefined) {
    /************************************
        Constants
    ************************************/

    var moment,
        VERSION = '2.8.4',
        // the global-scope this is NOT the global object in Node.js
        globalScope = typeof global !== 'undefined' ? global : this,
        oldGlobalMoment,
        round = Math.round,
        hasOwnProperty = Object.prototype.hasOwnProperty,
        i,

        YEAR = 0,
        MONTH = 1,
        DATE = 2,
        HOUR = 3,
        MINUTE = 4,
        SECOND = 5,
        MILLISECOND = 6,

        // internal storage for locale config files
        locales = {},

        // extra moment internal properties (plugins register props here)
        momentProperties = [],

        // check for nodeJS
        hasModule = (typeof module !== 'undefined' && module && module.exports),

        // ASP.NET json date format regex
        aspNetJsonRegex = /^\/?Date\((\-?\d+)/i,
        aspNetTimeSpanJsonRegex = /(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/,

        // from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
        // somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
        isoDurationRegex = /^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/,

        // format tokens
        formattingTokens = /(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Q|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|S{1,4}|x|X|zz?|ZZ?|.)/g,
        localFormattingTokens = /(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g,

        // parsing token regexes
        parseTokenOneOrTwoDigits = /\d\d?/, // 0 - 99
        parseTokenOneToThreeDigits = /\d{1,3}/, // 0 - 999
        parseTokenOneToFourDigits = /\d{1,4}/, // 0 - 9999
        parseTokenOneToSixDigits = /[+\-]?\d{1,6}/, // -999,999 - 999,999
        parseTokenDigits = /\d+/, // nonzero number of digits
        parseTokenWord = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i, // any word (or two) characters or numbers including two/three word month in arabic.
        parseTokenTimezone = /Z|[\+\-]\d\d:?\d\d/gi, // +00:00 -00:00 +0000 -0000 or Z
        parseTokenT = /T/i, // T (ISO separator)
        parseTokenOffsetMs = /[\+\-]?\d+/, // 1234567890123
        parseTokenTimestampMs = /[\+\-]?\d+(\.\d{1,3})?/, // 123456789 123456789.123

        //strict parsing regexes
        parseTokenOneDigit = /\d/, // 0 - 9
        parseTokenTwoDigits = /\d\d/, // 00 - 99
        parseTokenThreeDigits = /\d{3}/, // 000 - 999
        parseTokenFourDigits = /\d{4}/, // 0000 - 9999
        parseTokenSixDigits = /[+-]?\d{6}/, // -999,999 - 999,999
        parseTokenSignedNumber = /[+-]?\d+/, // -inf - inf

        // iso 8601 regex
        // 0000-00-00 0000-W00 or 0000-W00-0 + T + 00 or 00:00 or 00:00:00 or 00:00:00.000 + +00:00 or +0000 or +00)
        isoRegex = /^\s*(?:[+-]\d{6}|\d{4})-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d+)?)?)?)?([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/,

        isoFormat = 'YYYY-MM-DDTHH:mm:ssZ',

        isoDates = [
            ['YYYYYY-MM-DD', /[+-]\d{6}-\d{2}-\d{2}/],
            ['YYYY-MM-DD', /\d{4}-\d{2}-\d{2}/],
            ['GGGG-[W]WW-E', /\d{4}-W\d{2}-\d/],
            ['GGGG-[W]WW', /\d{4}-W\d{2}/],
            ['YYYY-DDD', /\d{4}-\d{3}/]
        ],

        // iso time formats and regexes
        isoTimes = [
            ['HH:mm:ss.SSSS', /(T| )\d\d:\d\d:\d\d\.\d+/],
            ['HH:mm:ss', /(T| )\d\d:\d\d:\d\d/],
            ['HH:mm', /(T| )\d\d:\d\d/],
            ['HH', /(T| )\d\d/]
        ],

        // timezone chunker '+10:00' > ['10', '00'] or '-1530' > ['-15', '30']
        parseTimezoneChunker = /([\+\-]|\d\d)/gi,

        // getter and setter names
        proxyGettersAndSetters = 'Date|Hours|Minutes|Seconds|Milliseconds'.split('|'),
        unitMillisecondFactors = {
            'Milliseconds' : 1,
            'Seconds' : 1e3,
            'Minutes' : 6e4,
            'Hours' : 36e5,
            'Days' : 864e5,
            'Months' : 2592e6,
            'Years' : 31536e6
        },

        unitAliases = {
            ms : 'millisecond',
            s : 'second',
            m : 'minute',
            h : 'hour',
            d : 'day',
            D : 'date',
            w : 'week',
            W : 'isoWeek',
            M : 'month',
            Q : 'quarter',
            y : 'year',
            DDD : 'dayOfYear',
            e : 'weekday',
            E : 'isoWeekday',
            gg: 'weekYear',
            GG: 'isoWeekYear'
        },

        camelFunctions = {
            dayofyear : 'dayOfYear',
            isoweekday : 'isoWeekday',
            isoweek : 'isoWeek',
            weekyear : 'weekYear',
            isoweekyear : 'isoWeekYear'
        },

        // format function strings
        formatFunctions = {},

        // default relative time thresholds
        relativeTimeThresholds = {
            s: 45,  // seconds to minute
            m: 45,  // minutes to hour
            h: 22,  // hours to day
            d: 26,  // days to month
            M: 11   // months to year
        },

        // tokens to ordinalize and pad
        ordinalizeTokens = 'DDD w W M D d'.split(' '),
        paddedTokens = 'M D H h m s w W'.split(' '),

        formatTokenFunctions = {
            M    : function () {
                return this.month() + 1;
            },
            MMM  : function (format) {
                return this.localeData().monthsShort(this, format);
            },
            MMMM : function (format) {
                return this.localeData().months(this, format);
            },
            D    : function () {
                return this.date();
            },
            DDD  : function () {
                return this.dayOfYear();
            },
            d    : function () {
                return this.day();
            },
            dd   : function (format) {
                return this.localeData().weekdaysMin(this, format);
            },
            ddd  : function (format) {
                return this.localeData().weekdaysShort(this, format);
            },
            dddd : function (format) {
                return this.localeData().weekdays(this, format);
            },
            w    : function () {
                return this.week();
            },
            W    : function () {
                return this.isoWeek();
            },
            YY   : function () {
                return leftZeroFill(this.year() % 100, 2);
            },
            YYYY : function () {
                return leftZeroFill(this.year(), 4);
            },
            YYYYY : function () {
                return leftZeroFill(this.year(), 5);
            },
            YYYYYY : function () {
                var y = this.year(), sign = y >= 0 ? '+' : '-';
                return sign + leftZeroFill(Math.abs(y), 6);
            },
            gg   : function () {
                return leftZeroFill(this.weekYear() % 100, 2);
            },
            gggg : function () {
                return leftZeroFill(this.weekYear(), 4);
            },
            ggggg : function () {
                return leftZeroFill(this.weekYear(), 5);
            },
            GG   : function () {
                return leftZeroFill(this.isoWeekYear() % 100, 2);
            },
            GGGG : function () {
                return leftZeroFill(this.isoWeekYear(), 4);
            },
            GGGGG : function () {
                return leftZeroFill(this.isoWeekYear(), 5);
            },
            e : function () {
                return this.weekday();
            },
            E : function () {
                return this.isoWeekday();
            },
            a    : function () {
                return this.localeData().meridiem(this.hours(), this.minutes(), true);
            },
            A    : function () {
                return this.localeData().meridiem(this.hours(), this.minutes(), false);
            },
            H    : function () {
                return this.hours();
            },
            h    : function () {
                return this.hours() % 12 || 12;
            },
            m    : function () {
                return this.minutes();
            },
            s    : function () {
                return this.seconds();
            },
            S    : function () {
                return toInt(this.milliseconds() / 100);
            },
            SS   : function () {
                return leftZeroFill(toInt(this.milliseconds() / 10), 2);
            },
            SSS  : function () {
                return leftZeroFill(this.milliseconds(), 3);
            },
            SSSS : function () {
                return leftZeroFill(this.milliseconds(), 3);
            },
            Z    : function () {
                var a = -this.zone(),
                    b = '+';
                if (a < 0) {
                    a = -a;
                    b = '-';
                }
                return b + leftZeroFill(toInt(a / 60), 2) + ':' + leftZeroFill(toInt(a) % 60, 2);
            },
            ZZ   : function () {
                var a = -this.zone(),
                    b = '+';
                if (a < 0) {
                    a = -a;
                    b = '-';
                }
                return b + leftZeroFill(toInt(a / 60), 2) + leftZeroFill(toInt(a) % 60, 2);
            },
            z : function () {
                return this.zoneAbbr();
            },
            zz : function () {
                return this.zoneName();
            },
            x    : function () {
                return this.valueOf();
            },
            X    : function () {
                return this.unix();
            },
            Q : function () {
                return this.quarter();
            }
        },

        deprecations = {},

        lists = ['months', 'monthsShort', 'weekdays', 'weekdaysShort', 'weekdaysMin'];

    // Pick the first defined of two or three arguments. dfl comes from
    // default.
    function dfl(a, b, c) {
        switch (arguments.length) {
            case 2: return a != null ? a : b;
            case 3: return a != null ? a : b != null ? b : c;
            default: throw new Error('Implement me');
        }
    }

    function hasOwnProp(a, b) {
        return hasOwnProperty.call(a, b);
    }

    function defaultParsingFlags() {
        // We need to deep clone this object, and es5 standard is not very
        // helpful.
        return {
            empty : false,
            unusedTokens : [],
            unusedInput : [],
            overflow : -2,
            charsLeftOver : 0,
            nullInput : false,
            invalidMonth : null,
            invalidFormat : false,
            userInvalidated : false,
            iso: false
        };
    }

    function printMsg(msg) {
        if (moment.suppressDeprecationWarnings === false &&
                typeof console !== 'undefined' && console.warn) {
            console.warn('Deprecation warning: ' + msg);
        }
    }

    function deprecate(msg, fn) {
        var firstTime = true;
        return extend(function () {
            if (firstTime) {
                printMsg(msg);
                firstTime = false;
            }
            return fn.apply(this, arguments);
        }, fn);
    }

    function deprecateSimple(name, msg) {
        if (!deprecations[name]) {
            printMsg(msg);
            deprecations[name] = true;
        }
    }

    function padToken(func, count) {
        return function (a) {
            return leftZeroFill(func.call(this, a), count);
        };
    }
    function ordinalizeToken(func, period) {
        return function (a) {
            return this.localeData().ordinal(func.call(this, a), period);
        };
    }

    while (ordinalizeTokens.length) {
        i = ordinalizeTokens.pop();
        formatTokenFunctions[i + 'o'] = ordinalizeToken(formatTokenFunctions[i], i);
    }
    while (paddedTokens.length) {
        i = paddedTokens.pop();
        formatTokenFunctions[i + i] = padToken(formatTokenFunctions[i], 2);
    }
    formatTokenFunctions.DDDD = padToken(formatTokenFunctions.DDD, 3);


    /************************************
        Constructors
    ************************************/

    function Locale() {
    }

    // Moment prototype object
    function Moment(config, skipOverflow) {
        if (skipOverflow !== false) {
            checkOverflow(config);
        }
        copyConfig(this, config);
        this._d = new Date(+config._d);
    }

    // Duration Constructor
    function Duration(duration) {
        var normalizedInput = normalizeObjectUnits(duration),
            years = normalizedInput.year || 0,
            quarters = normalizedInput.quarter || 0,
            months = normalizedInput.month || 0,
            weeks = normalizedInput.week || 0,
            days = normalizedInput.day || 0,
            hours = normalizedInput.hour || 0,
            minutes = normalizedInput.minute || 0,
            seconds = normalizedInput.second || 0,
            milliseconds = normalizedInput.millisecond || 0;

        // representation for dateAddRemove
        this._milliseconds = +milliseconds +
            seconds * 1e3 + // 1000
            minutes * 6e4 + // 1000 * 60
            hours * 36e5; // 1000 * 60 * 60
        // Because of dateAddRemove treats 24 hours as different from a
        // day when working around DST, we need to store them separately
        this._days = +days +
            weeks * 7;
        // It is impossible translate months into days without knowing
        // which months you are are talking about, so we have to store
        // it separately.
        this._months = +months +
            quarters * 3 +
            years * 12;

        this._data = {};

        this._locale = moment.localeData();

        this._bubble();
    }

    /************************************
        Helpers
    ************************************/


    function extend(a, b) {
        for (var i in b) {
            if (hasOwnProp(b, i)) {
                a[i] = b[i];
            }
        }

        if (hasOwnProp(b, 'toString')) {
            a.toString = b.toString;
        }

        if (hasOwnProp(b, 'valueOf')) {
            a.valueOf = b.valueOf;
        }

        return a;
    }

    function copyConfig(to, from) {
        var i, prop, val;

        if (typeof from._isAMomentObject !== 'undefined') {
            to._isAMomentObject = from._isAMomentObject;
        }
        if (typeof from._i !== 'undefined') {
            to._i = from._i;
        }
        if (typeof from._f !== 'undefined') {
            to._f = from._f;
        }
        if (typeof from._l !== 'undefined') {
            to._l = from._l;
        }
        if (typeof from._strict !== 'undefined') {
            to._strict = from._strict;
        }
        if (typeof from._tzm !== 'undefined') {
            to._tzm = from._tzm;
        }
        if (typeof from._isUTC !== 'undefined') {
            to._isUTC = from._isUTC;
        }
        if (typeof from._offset !== 'undefined') {
            to._offset = from._offset;
        }
        if (typeof from._pf !== 'undefined') {
            to._pf = from._pf;
        }
        if (typeof from._locale !== 'undefined') {
            to._locale = from._locale;
        }

        if (momentProperties.length > 0) {
            for (i in momentProperties) {
                prop = momentProperties[i];
                val = from[prop];
                if (typeof val !== 'undefined') {
                    to[prop] = val;
                }
            }
        }

        return to;
    }

    function absRound(number) {
        if (number < 0) {
            return Math.ceil(number);
        } else {
            return Math.floor(number);
        }
    }

    // left zero fill a number
    // see http://jsperf.com/left-zero-filling for performance comparison
    function leftZeroFill(number, targetLength, forceSign) {
        var output = '' + Math.abs(number),
            sign = number >= 0;

        while (output.length < targetLength) {
            output = '0' + output;
        }
        return (sign ? (forceSign ? '+' : '') : '-') + output;
    }

    function positiveMomentsDifference(base, other) {
        var res = {milliseconds: 0, months: 0};

        res.months = other.month() - base.month() +
            (other.year() - base.year()) * 12;
        if (base.clone().add(res.months, 'M').isAfter(other)) {
            --res.months;
        }

        res.milliseconds = +other - +(base.clone().add(res.months, 'M'));

        return res;
    }

    function momentsDifference(base, other) {
        var res;
        other = makeAs(other, base);
        if (base.isBefore(other)) {
            res = positiveMomentsDifference(base, other);
        } else {
            res = positiveMomentsDifference(other, base);
            res.milliseconds = -res.milliseconds;
            res.months = -res.months;
        }

        return res;
    }

    // TODO: remove 'name' arg after deprecation is removed
    function createAdder(direction, name) {
        return function (val, period) {
            var dur, tmp;
            //invert the arguments, but complain about it
            if (period !== null && !isNaN(+period)) {
                deprecateSimple(name, 'moment().' + name  + '(period, number) is deprecated. Please use moment().' + name + '(number, period).');
                tmp = val; val = period; period = tmp;
            }

            val = typeof val === 'string' ? +val : val;
            dur = moment.duration(val, period);
            addOrSubtractDurationFromMoment(this, dur, direction);
            return this;
        };
    }

    function addOrSubtractDurationFromMoment(mom, duration, isAdding, updateOffset) {
        var milliseconds = duration._milliseconds,
            days = duration._days,
            months = duration._months;
        updateOffset = updateOffset == null ? true : updateOffset;

        if (milliseconds) {
            mom._d.setTime(+mom._d + milliseconds * isAdding);
        }
        if (days) {
            rawSetter(mom, 'Date', rawGetter(mom, 'Date') + days * isAdding);
        }
        if (months) {
            rawMonthSetter(mom, rawGetter(mom, 'Month') + months * isAdding);
        }
        if (updateOffset) {
            moment.updateOffset(mom, days || months);
        }
    }

    // check if is an array
    function isArray(input) {
        return Object.prototype.toString.call(input) === '[object Array]';
    }

    function isDate(input) {
        return Object.prototype.toString.call(input) === '[object Date]' ||
            input instanceof Date;
    }

    // compare two arrays, return the number of differences
    function compareArrays(array1, array2, dontConvert) {
        var len = Math.min(array1.length, array2.length),
            lengthDiff = Math.abs(array1.length - array2.length),
            diffs = 0,
            i;
        for (i = 0; i < len; i++) {
            if ((dontConvert && array1[i] !== array2[i]) ||
                (!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
                diffs++;
            }
        }
        return diffs + lengthDiff;
    }

    function normalizeUnits(units) {
        if (units) {
            var lowered = units.toLowerCase().replace(/(.)s$/, '$1');
            units = unitAliases[units] || camelFunctions[lowered] || lowered;
        }
        return units;
    }

    function normalizeObjectUnits(inputObject) {
        var normalizedInput = {},
            normalizedProp,
            prop;

        for (prop in inputObject) {
            if (hasOwnProp(inputObject, prop)) {
                normalizedProp = normalizeUnits(prop);
                if (normalizedProp) {
                    normalizedInput[normalizedProp] = inputObject[prop];
                }
            }
        }

        return normalizedInput;
    }

    function makeList(field) {
        var count, setter;

        if (field.indexOf('week') === 0) {
            count = 7;
            setter = 'day';
        }
        else if (field.indexOf('month') === 0) {
            count = 12;
            setter = 'month';
        }
        else {
            return;
        }

        moment[field] = function (format, index) {
            var i, getter,
                method = moment._locale[field],
                results = [];

            if (typeof format === 'number') {
                index = format;
                format = undefined;
            }

            getter = function (i) {
                var m = moment().utc().set(setter, i);
                return method.call(moment._locale, m, format || '');
            };

            if (index != null) {
                return getter(index);
            }
            else {
                for (i = 0; i < count; i++) {
                    results.push(getter(i));
                }
                return results;
            }
        };
    }

    function toInt(argumentForCoercion) {
        var coercedNumber = +argumentForCoercion,
            value = 0;

        if (coercedNumber !== 0 && isFinite(coercedNumber)) {
            if (coercedNumber >= 0) {
                value = Math.floor(coercedNumber);
            } else {
                value = Math.ceil(coercedNumber);
            }
        }

        return value;
    }

    function daysInMonth(year, month) {
        return new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
    }

    function weeksInYear(year, dow, doy) {
        return weekOfYear(moment([year, 11, 31 + dow - doy]), dow, doy).week;
    }

    function daysInYear(year) {
        return isLeapYear(year) ? 366 : 365;
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    }

    function checkOverflow(m) {
        var overflow;
        if (m._a && m._pf.overflow === -2) {
            overflow =
                m._a[MONTH] < 0 || m._a[MONTH] > 11 ? MONTH :
                m._a[DATE] < 1 || m._a[DATE] > daysInMonth(m._a[YEAR], m._a[MONTH]) ? DATE :
                m._a[HOUR] < 0 || m._a[HOUR] > 24 ||
                    (m._a[HOUR] === 24 && (m._a[MINUTE] !== 0 ||
                                           m._a[SECOND] !== 0 ||
                                           m._a[MILLISECOND] !== 0)) ? HOUR :
                m._a[MINUTE] < 0 || m._a[MINUTE] > 59 ? MINUTE :
                m._a[SECOND] < 0 || m._a[SECOND] > 59 ? SECOND :
                m._a[MILLISECOND] < 0 || m._a[MILLISECOND] > 999 ? MILLISECOND :
                -1;

            if (m._pf._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
                overflow = DATE;
            }

            m._pf.overflow = overflow;
        }
    }

    function isValid(m) {
        if (m._isValid == null) {
            m._isValid = !isNaN(m._d.getTime()) &&
                m._pf.overflow < 0 &&
                !m._pf.empty &&
                !m._pf.invalidMonth &&
                !m._pf.nullInput &&
                !m._pf.invalidFormat &&
                !m._pf.userInvalidated;

            if (m._strict) {
                m._isValid = m._isValid &&
                    m._pf.charsLeftOver === 0 &&
                    m._pf.unusedTokens.length === 0 &&
                    m._pf.bigHour === undefined;
            }
        }
        return m._isValid;
    }

    function normalizeLocale(key) {
        return key ? key.toLowerCase().replace('_', '-') : key;
    }

    // pick the locale from the array
    // try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
    // substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
    function chooseLocale(names) {
        var i = 0, j, next, locale, split;

        while (i < names.length) {
            split = normalizeLocale(names[i]).split('-');
            j = split.length;
            next = normalizeLocale(names[i + 1]);
            next = next ? next.split('-') : null;
            while (j > 0) {
                locale = loadLocale(split.slice(0, j).join('-'));
                if (locale) {
                    return locale;
                }
                if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
                    //the next array item is better than a shallower substring of this one
                    break;
                }
                j--;
            }
            i++;
        }
        return null;
    }

    function loadLocale(name) {
        var oldLocale = null;
        if (!locales[name] && hasModule) {
            try {
                oldLocale = moment.locale();
                require('./locale/' + name);
                // because defineLocale currently also sets the global locale, we want to undo that for lazy loaded locales
                moment.locale(oldLocale);
            } catch (e) { }
        }
        return locales[name];
    }

    // Return a moment from input, that is local/utc/zone equivalent to model.
    function makeAs(input, model) {
        var res, diff;
        if (model._isUTC) {
            res = model.clone();
            diff = (moment.isMoment(input) || isDate(input) ?
                    +input : +moment(input)) - (+res);
            // Use low-level api, because this fn is low-level api.
            res._d.setTime(+res._d + diff);
            moment.updateOffset(res, false);
            return res;
        } else {
            return moment(input).local();
        }
    }

    /************************************
        Locale
    ************************************/


    extend(Locale.prototype, {

        set : function (config) {
            var prop, i;
            for (i in config) {
                prop = config[i];
                if (typeof prop === 'function') {
                    this[i] = prop;
                } else {
                    this['_' + i] = prop;
                }
            }
            // Lenient ordinal parsing accepts just a number in addition to
            // number + (possibly) stuff coming from _ordinalParseLenient.
            this._ordinalParseLenient = new RegExp(this._ordinalParse.source + '|' + /\d{1,2}/.source);
        },

        _months : 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_'),
        months : function (m) {
            return this._months[m.month()];
        },

        _monthsShort : 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_'),
        monthsShort : function (m) {
            return this._monthsShort[m.month()];
        },

        monthsParse : function (monthName, format, strict) {
            var i, mom, regex;

            if (!this._monthsParse) {
                this._monthsParse = [];
                this._longMonthsParse = [];
                this._shortMonthsParse = [];
            }

            for (i = 0; i < 12; i++) {
                // make the regex if we don't have it already
                mom = moment.utc([2000, i]);
                if (strict && !this._longMonthsParse[i]) {
                    this._longMonthsParse[i] = new RegExp('^' + this.months(mom, '').replace('.', '') + '$', 'i');
                    this._shortMonthsParse[i] = new RegExp('^' + this.monthsShort(mom, '').replace('.', '') + '$', 'i');
                }
                if (!strict && !this._monthsParse[i]) {
                    regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
                    this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
                }
                // test the regex
                if (strict && format === 'MMMM' && this._longMonthsParse[i].test(monthName)) {
                    return i;
                } else if (strict && format === 'MMM' && this._shortMonthsParse[i].test(monthName)) {
                    return i;
                } else if (!strict && this._monthsParse[i].test(monthName)) {
                    return i;
                }
            }
        },

        _weekdays : 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_'),
        weekdays : function (m) {
            return this._weekdays[m.day()];
        },

        _weekdaysShort : 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_'),
        weekdaysShort : function (m) {
            return this._weekdaysShort[m.day()];
        },

        _weekdaysMin : 'Su_Mo_Tu_We_Th_Fr_Sa'.split('_'),
        weekdaysMin : function (m) {
            return this._weekdaysMin[m.day()];
        },

        weekdaysParse : function (weekdayName) {
            var i, mom, regex;

            if (!this._weekdaysParse) {
                this._weekdaysParse = [];
            }

            for (i = 0; i < 7; i++) {
                // make the regex if we don't have it already
                if (!this._weekdaysParse[i]) {
                    mom = moment([2000, 1]).day(i);
                    regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
                    this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
                }
                // test the regex
                if (this._weekdaysParse[i].test(weekdayName)) {
                    return i;
                }
            }
        },

        _longDateFormat : {
            LTS : 'h:mm:ss A',
            LT : 'h:mm A',
            L : 'MM/DD/YYYY',
            LL : 'MMMM D, YYYY',
            LLL : 'MMMM D, YYYY LT',
            LLLL : 'dddd, MMMM D, YYYY LT'
        },
        longDateFormat : function (key) {
            var output = this._longDateFormat[key];
            if (!output && this._longDateFormat[key.toUpperCase()]) {
                output = this._longDateFormat[key.toUpperCase()].replace(/MMMM|MM|DD|dddd/g, function (val) {
                    return val.slice(1);
                });
                this._longDateFormat[key] = output;
            }
            return output;
        },

        isPM : function (input) {
            // IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
            // Using charAt should be more compatible.
            return ((input + '').toLowerCase().charAt(0) === 'p');
        },

        _meridiemParse : /[ap]\.?m?\.?/i,
        meridiem : function (hours, minutes, isLower) {
            if (hours > 11) {
                return isLower ? 'pm' : 'PM';
            } else {
                return isLower ? 'am' : 'AM';
            }
        },

        _calendar : {
            sameDay : '[Today at] LT',
            nextDay : '[Tomorrow at] LT',
            nextWeek : 'dddd [at] LT',
            lastDay : '[Yesterday at] LT',
            lastWeek : '[Last] dddd [at] LT',
            sameElse : 'L'
        },
        calendar : function (key, mom, now) {
            var output = this._calendar[key];
            return typeof output === 'function' ? output.apply(mom, [now]) : output;
        },

        _relativeTime : {
            future : 'in %s',
            past : '%s ago',
            s : 'a few seconds',
            m : 'a minute',
            mm : '%d minutes',
            h : 'an hour',
            hh : '%d hours',
            d : 'a day',
            dd : '%d days',
            M : 'a month',
            MM : '%d months',
            y : 'a year',
            yy : '%d years'
        },

        relativeTime : function (number, withoutSuffix, string, isFuture) {
            var output = this._relativeTime[string];
            return (typeof output === 'function') ?
                output(number, withoutSuffix, string, isFuture) :
                output.replace(/%d/i, number);
        },

        pastFuture : function (diff, output) {
            var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
            return typeof format === 'function' ? format(output) : format.replace(/%s/i, output);
        },

        ordinal : function (number) {
            return this._ordinal.replace('%d', number);
        },
        _ordinal : '%d',
        _ordinalParse : /\d{1,2}/,

        preparse : function (string) {
            return string;
        },

        postformat : function (string) {
            return string;
        },

        week : function (mom) {
            return weekOfYear(mom, this._week.dow, this._week.doy).week;
        },

        _week : {
            dow : 0, // Sunday is the first day of the week.
            doy : 6  // The week that contains Jan 1st is the first week of the year.
        },

        _invalidDate: 'Invalid date',
        invalidDate: function () {
            return this._invalidDate;
        }
    });

    /************************************
        Formatting
    ************************************/


    function removeFormattingTokens(input) {
        if (input.match(/\[[\s\S]/)) {
            return input.replace(/^\[|\]$/g, '');
        }
        return input.replace(/\\/g, '');
    }

    function makeFormatFunction(format) {
        var array = format.match(formattingTokens), i, length;

        for (i = 0, length = array.length; i < length; i++) {
            if (formatTokenFunctions[array[i]]) {
                array[i] = formatTokenFunctions[array[i]];
            } else {
                array[i] = removeFormattingTokens(array[i]);
            }
        }

        return function (mom) {
            var output = '';
            for (i = 0; i < length; i++) {
                output += array[i] instanceof Function ? array[i].call(mom, format) : array[i];
            }
            return output;
        };
    }

    // format date using native date object
    function formatMoment(m, format) {
        if (!m.isValid()) {
            return m.localeData().invalidDate();
        }

        format = expandFormat(format, m.localeData());

        if (!formatFunctions[format]) {
            formatFunctions[format] = makeFormatFunction(format);
        }

        return formatFunctions[format](m);
    }

    function expandFormat(format, locale) {
        var i = 5;

        function replaceLongDateFormatTokens(input) {
            return locale.longDateFormat(input) || input;
        }

        localFormattingTokens.lastIndex = 0;
        while (i >= 0 && localFormattingTokens.test(format)) {
            format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
            localFormattingTokens.lastIndex = 0;
            i -= 1;
        }

        return format;
    }


    /************************************
        Parsing
    ************************************/


    // get the regex to find the next token
    function getParseRegexForToken(token, config) {
        var a, strict = config._strict;
        switch (token) {
        case 'Q':
            return parseTokenOneDigit;
        case 'DDDD':
            return parseTokenThreeDigits;
        case 'YYYY':
        case 'GGGG':
        case 'gggg':
            return strict ? parseTokenFourDigits : parseTokenOneToFourDigits;
        case 'Y':
        case 'G':
        case 'g':
            return parseTokenSignedNumber;
        case 'YYYYYY':
        case 'YYYYY':
        case 'GGGGG':
        case 'ggggg':
            return strict ? parseTokenSixDigits : parseTokenOneToSixDigits;
        case 'S':
            if (strict) {
                return parseTokenOneDigit;
            }
            /* falls through */
        case 'SS':
            if (strict) {
                return parseTokenTwoDigits;
            }
            /* falls through */
        case 'SSS':
            if (strict) {
                return parseTokenThreeDigits;
            }
            /* falls through */
        case 'DDD':
            return parseTokenOneToThreeDigits;
        case 'MMM':
        case 'MMMM':
        case 'dd':
        case 'ddd':
        case 'dddd':
            return parseTokenWord;
        case 'a':
        case 'A':
            return config._locale._meridiemParse;
        case 'x':
            return parseTokenOffsetMs;
        case 'X':
            return parseTokenTimestampMs;
        case 'Z':
        case 'ZZ':
            return parseTokenTimezone;
        case 'T':
            return parseTokenT;
        case 'SSSS':
            return parseTokenDigits;
        case 'MM':
        case 'DD':
        case 'YY':
        case 'GG':
        case 'gg':
        case 'HH':
        case 'hh':
        case 'mm':
        case 'ss':
        case 'ww':
        case 'WW':
            return strict ? parseTokenTwoDigits : parseTokenOneOrTwoDigits;
        case 'M':
        case 'D':
        case 'd':
        case 'H':
        case 'h':
        case 'm':
        case 's':
        case 'w':
        case 'W':
        case 'e':
        case 'E':
            return parseTokenOneOrTwoDigits;
        case 'Do':
            return strict ? config._locale._ordinalParse : config._locale._ordinalParseLenient;
        default :
            a = new RegExp(regexpEscape(unescapeFormat(token.replace('\\', '')), 'i'));
            return a;
        }
    }

    function timezoneMinutesFromString(string) {
        string = string || '';
        var possibleTzMatches = (string.match(parseTokenTimezone) || []),
            tzChunk = possibleTzMatches[possibleTzMatches.length - 1] || [],
            parts = (tzChunk + '').match(parseTimezoneChunker) || ['-', 0, 0],
            minutes = +(parts[1] * 60) + toInt(parts[2]);

        return parts[0] === '+' ? -minutes : minutes;
    }

    // function to convert string input to date
    function addTimeToArrayFromToken(token, input, config) {
        var a, datePartArray = config._a;

        switch (token) {
        // QUARTER
        case 'Q':
            if (input != null) {
                datePartArray[MONTH] = (toInt(input) - 1) * 3;
            }
            break;
        // MONTH
        case 'M' : // fall through to MM
        case 'MM' :
            if (input != null) {
                datePartArray[MONTH] = toInt(input) - 1;
            }
            break;
        case 'MMM' : // fall through to MMMM
        case 'MMMM' :
            a = config._locale.monthsParse(input, token, config._strict);
            // if we didn't find a month name, mark the date as invalid.
            if (a != null) {
                datePartArray[MONTH] = a;
            } else {
                config._pf.invalidMonth = input;
            }
            break;
        // DAY OF MONTH
        case 'D' : // fall through to DD
        case 'DD' :
            if (input != null) {
                datePartArray[DATE] = toInt(input);
            }
            break;
        case 'Do' :
            if (input != null) {
                datePartArray[DATE] = toInt(parseInt(
                            input.match(/\d{1,2}/)[0], 10));
            }
            break;
        // DAY OF YEAR
        case 'DDD' : // fall through to DDDD
        case 'DDDD' :
            if (input != null) {
                config._dayOfYear = toInt(input);
            }

            break;
        // YEAR
        case 'YY' :
            datePartArray[YEAR] = moment.parseTwoDigitYear(input);
            break;
        case 'YYYY' :
        case 'YYYYY' :
        case 'YYYYYY' :
            datePartArray[YEAR] = toInt(input);
            break;
        // AM / PM
        case 'a' : // fall through to A
        case 'A' :
            config._isPm = config._locale.isPM(input);
            break;
        // HOUR
        case 'h' : // fall through to hh
        case 'hh' :
            config._pf.bigHour = true;
            /* falls through */
        case 'H' : // fall through to HH
        case 'HH' :
            datePartArray[HOUR] = toInt(input);
            break;
        // MINUTE
        case 'm' : // fall through to mm
        case 'mm' :
            datePartArray[MINUTE] = toInt(input);
            break;
        // SECOND
        case 's' : // fall through to ss
        case 'ss' :
            datePartArray[SECOND] = toInt(input);
            break;
        // MILLISECOND
        case 'S' :
        case 'SS' :
        case 'SSS' :
        case 'SSSS' :
            datePartArray[MILLISECOND] = toInt(('0.' + input) * 1000);
            break;
        // UNIX OFFSET (MILLISECONDS)
        case 'x':
            config._d = new Date(toInt(input));
            break;
        // UNIX TIMESTAMP WITH MS
        case 'X':
            config._d = new Date(parseFloat(input) * 1000);
            break;
        // TIMEZONE
        case 'Z' : // fall through to ZZ
        case 'ZZ' :
            config._useUTC = true;
            config._tzm = timezoneMinutesFromString(input);
            break;
        // WEEKDAY - human
        case 'dd':
        case 'ddd':
        case 'dddd':
            a = config._locale.weekdaysParse(input);
            // if we didn't get a weekday name, mark the date as invalid
            if (a != null) {
                config._w = config._w || {};
                config._w['d'] = a;
            } else {
                config._pf.invalidWeekday = input;
            }
            break;
        // WEEK, WEEK DAY - numeric
        case 'w':
        case 'ww':
        case 'W':
        case 'WW':
        case 'd':
        case 'e':
        case 'E':
            token = token.substr(0, 1);
            /* falls through */
        case 'gggg':
        case 'GGGG':
        case 'GGGGG':
            token = token.substr(0, 2);
            if (input) {
                config._w = config._w || {};
                config._w[token] = toInt(input);
            }
            break;
        case 'gg':
        case 'GG':
            config._w = config._w || {};
            config._w[token] = moment.parseTwoDigitYear(input);
        }
    }

    function dayOfYearFromWeekInfo(config) {
        var w, weekYear, week, weekday, dow, doy, temp;

        w = config._w;
        if (w.GG != null || w.W != null || w.E != null) {
            dow = 1;
            doy = 4;

            // TODO: We need to take the current isoWeekYear, but that depends on
            // how we interpret now (local, utc, fixed offset). So create
            // a now version of current config (take local/utc/offset flags, and
            // create now).
            weekYear = dfl(w.GG, config._a[YEAR], weekOfYear(moment(), 1, 4).year);
            week = dfl(w.W, 1);
            weekday = dfl(w.E, 1);
        } else {
            dow = config._locale._week.dow;
            doy = config._locale._week.doy;

            weekYear = dfl(w.gg, config._a[YEAR], weekOfYear(moment(), dow, doy).year);
            week = dfl(w.w, 1);

            if (w.d != null) {
                // weekday -- low day numbers are considered next week
                weekday = w.d;
                if (weekday < dow) {
                    ++week;
                }
            } else if (w.e != null) {
                // local weekday -- counting starts from begining of week
                weekday = w.e + dow;
            } else {
                // default to begining of week
                weekday = dow;
            }
        }
        temp = dayOfYearFromWeeks(weekYear, week, weekday, doy, dow);

        config._a[YEAR] = temp.year;
        config._dayOfYear = temp.dayOfYear;
    }

    // convert an array to a date.
    // the array should mirror the parameters below
    // note: all values past the year are optional and will default to the lowest possible value.
    // [year, month, day , hour, minute, second, millisecond]
    function dateFromConfig(config) {
        var i, date, input = [], currentDate, yearToUse;

        if (config._d) {
            return;
        }

        currentDate = currentDateArray(config);

        //compute day of the year from weeks and weekdays
        if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
            dayOfYearFromWeekInfo(config);
        }

        //if the day of the year is set, figure out what it is
        if (config._dayOfYear) {
            yearToUse = dfl(config._a[YEAR], currentDate[YEAR]);

            if (config._dayOfYear > daysInYear(yearToUse)) {
                config._pf._overflowDayOfYear = true;
            }

            date = makeUTCDate(yearToUse, 0, config._dayOfYear);
            config._a[MONTH] = date.getUTCMonth();
            config._a[DATE] = date.getUTCDate();
        }

        // Default to current date.
        // * if no year, month, day of month are given, default to today
        // * if day of month is given, default month and year
        // * if month is given, default only year
        // * if year is given, don't default anything
        for (i = 0; i < 3 && config._a[i] == null; ++i) {
            config._a[i] = input[i] = currentDate[i];
        }

        // Zero out whatever was not defaulted, including time
        for (; i < 7; i++) {
            config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
        }

        // Check for 24:00:00.000
        if (config._a[HOUR] === 24 &&
                config._a[MINUTE] === 0 &&
                config._a[SECOND] === 0 &&
                config._a[MILLISECOND] === 0) {
            config._nextDay = true;
            config._a[HOUR] = 0;
        }

        config._d = (config._useUTC ? makeUTCDate : makeDate).apply(null, input);
        // Apply timezone offset from input. The actual zone can be changed
        // with parseZone.
        if (config._tzm != null) {
            config._d.setUTCMinutes(config._d.getUTCMinutes() + config._tzm);
        }

        if (config._nextDay) {
            config._a[HOUR] = 24;
        }
    }

    function dateFromObject(config) {
        var normalizedInput;

        if (config._d) {
            return;
        }

        normalizedInput = normalizeObjectUnits(config._i);
        config._a = [
            normalizedInput.year,
            normalizedInput.month,
            normalizedInput.day || normalizedInput.date,
            normalizedInput.hour,
            normalizedInput.minute,
            normalizedInput.second,
            normalizedInput.millisecond
        ];

        dateFromConfig(config);
    }

    function currentDateArray(config) {
        var now = new Date();
        if (config._useUTC) {
            return [
                now.getUTCFullYear(),
                now.getUTCMonth(),
                now.getUTCDate()
            ];
        } else {
            return [now.getFullYear(), now.getMonth(), now.getDate()];
        }
    }

    // date from string and format string
    function makeDateFromStringAndFormat(config) {
        if (config._f === moment.ISO_8601) {
            parseISO(config);
            return;
        }

        config._a = [];
        config._pf.empty = true;

        // This array is used to make a Date, either with `new Date` or `Date.UTC`
        var string = '' + config._i,
            i, parsedInput, tokens, token, skipped,
            stringLength = string.length,
            totalParsedInputLength = 0;

        tokens = expandFormat(config._f, config._locale).match(formattingTokens) || [];

        for (i = 0; i < tokens.length; i++) {
            token = tokens[i];
            parsedInput = (string.match(getParseRegexForToken(token, config)) || [])[0];
            if (parsedInput) {
                skipped = string.substr(0, string.indexOf(parsedInput));
                if (skipped.length > 0) {
                    config._pf.unusedInput.push(skipped);
                }
                string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
                totalParsedInputLength += parsedInput.length;
            }
            // don't parse if it's not a known token
            if (formatTokenFunctions[token]) {
                if (parsedInput) {
                    config._pf.empty = false;
                }
                else {
                    config._pf.unusedTokens.push(token);
                }
                addTimeToArrayFromToken(token, parsedInput, config);
            }
            else if (config._strict && !parsedInput) {
                config._pf.unusedTokens.push(token);
            }
        }

        // add remaining unparsed input length to the string
        config._pf.charsLeftOver = stringLength - totalParsedInputLength;
        if (string.length > 0) {
            config._pf.unusedInput.push(string);
        }

        // clear _12h flag if hour is <= 12
        if (config._pf.bigHour === true && config._a[HOUR] <= 12) {
            config._pf.bigHour = undefined;
        }
        // handle am pm
        if (config._isPm && config._a[HOUR] < 12) {
            config._a[HOUR] += 12;
        }
        // if is 12 am, change hours to 0
        if (config._isPm === false && config._a[HOUR] === 12) {
            config._a[HOUR] = 0;
        }
        dateFromConfig(config);
        checkOverflow(config);
    }

    function unescapeFormat(s) {
        return s.replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
            return p1 || p2 || p3 || p4;
        });
    }

    // Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
    function regexpEscape(s) {
        return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    // date from string and array of format strings
    function makeDateFromStringAndArray(config) {
        var tempConfig,
            bestMoment,

            scoreToBeat,
            i,
            currentScore;

        if (config._f.length === 0) {
            config._pf.invalidFormat = true;
            config._d = new Date(NaN);
            return;
        }

        for (i = 0; i < config._f.length; i++) {
            currentScore = 0;
            tempConfig = copyConfig({}, config);
            if (config._useUTC != null) {
                tempConfig._useUTC = config._useUTC;
            }
            tempConfig._pf = defaultParsingFlags();
            tempConfig._f = config._f[i];
            makeDateFromStringAndFormat(tempConfig);

            if (!isValid(tempConfig)) {
                continue;
            }

            // if there is any input that was not parsed add a penalty for that format
            currentScore += tempConfig._pf.charsLeftOver;

            //or tokens
            currentScore += tempConfig._pf.unusedTokens.length * 10;

            tempConfig._pf.score = currentScore;

            if (scoreToBeat == null || currentScore < scoreToBeat) {
                scoreToBeat = currentScore;
                bestMoment = tempConfig;
            }
        }

        extend(config, bestMoment || tempConfig);
    }

    // date from iso format
    function parseISO(config) {
        var i, l,
            string = config._i,
            match = isoRegex.exec(string);

        if (match) {
            config._pf.iso = true;
            for (i = 0, l = isoDates.length; i < l; i++) {
                if (isoDates[i][1].exec(string)) {
                    // match[5] should be 'T' or undefined
                    config._f = isoDates[i][0] + (match[6] || ' ');
                    break;
                }
            }
            for (i = 0, l = isoTimes.length; i < l; i++) {
                if (isoTimes[i][1].exec(string)) {
                    config._f += isoTimes[i][0];
                    break;
                }
            }
            if (string.match(parseTokenTimezone)) {
                config._f += 'Z';
            }
            makeDateFromStringAndFormat(config);
        } else {
            config._isValid = false;
        }
    }

    // date from iso format or fallback
    function makeDateFromString(config) {
        parseISO(config);
        if (config._isValid === false) {
            delete config._isValid;
            moment.createFromInputFallback(config);
        }
    }

    function map(arr, fn) {
        var res = [], i;
        for (i = 0; i < arr.length; ++i) {
            res.push(fn(arr[i], i));
        }
        return res;
    }

    function makeDateFromInput(config) {
        var input = config._i, matched;
        if (input === undefined) {
            config._d = new Date();
        } else if (isDate(input)) {
            config._d = new Date(+input);
        } else if ((matched = aspNetJsonRegex.exec(input)) !== null) {
            config._d = new Date(+matched[1]);
        } else if (typeof input === 'string') {
            makeDateFromString(config);
        } else if (isArray(input)) {
            config._a = map(input.slice(0), function (obj) {
                return parseInt(obj, 10);
            });
            dateFromConfig(config);
        } else if (typeof(input) === 'object') {
            dateFromObject(config);
        } else if (typeof(input) === 'number') {
            // from milliseconds
            config._d = new Date(input);
        } else {
            moment.createFromInputFallback(config);
        }
    }

    function makeDate(y, m, d, h, M, s, ms) {
        //can't just apply() to create a date:
        //http://stackoverflow.com/questions/181348/instantiating-a-javascript-object-by-calling-prototype-constructor-apply
        var date = new Date(y, m, d, h, M, s, ms);

        //the date constructor doesn't accept years < 1970
        if (y < 1970) {
            date.setFullYear(y);
        }
        return date;
    }

    function makeUTCDate(y) {
        var date = new Date(Date.UTC.apply(null, arguments));
        if (y < 1970) {
            date.setUTCFullYear(y);
        }
        return date;
    }

    function parseWeekday(input, locale) {
        if (typeof input === 'string') {
            if (!isNaN(input)) {
                input = parseInt(input, 10);
            }
            else {
                input = locale.weekdaysParse(input);
                if (typeof input !== 'number') {
                    return null;
                }
            }
        }
        return input;
    }

    /************************************
        Relative Time
    ************************************/


    // helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
    function substituteTimeAgo(string, number, withoutSuffix, isFuture, locale) {
        return locale.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
    }

    function relativeTime(posNegDuration, withoutSuffix, locale) {
        var duration = moment.duration(posNegDuration).abs(),
            seconds = round(duration.as('s')),
            minutes = round(duration.as('m')),
            hours = round(duration.as('h')),
            days = round(duration.as('d')),
            months = round(duration.as('M')),
            years = round(duration.as('y')),

            args = seconds < relativeTimeThresholds.s && ['s', seconds] ||
                minutes === 1 && ['m'] ||
                minutes < relativeTimeThresholds.m && ['mm', minutes] ||
                hours === 1 && ['h'] ||
                hours < relativeTimeThresholds.h && ['hh', hours] ||
                days === 1 && ['d'] ||
                days < relativeTimeThresholds.d && ['dd', days] ||
                months === 1 && ['M'] ||
                months < relativeTimeThresholds.M && ['MM', months] ||
                years === 1 && ['y'] || ['yy', years];

        args[2] = withoutSuffix;
        args[3] = +posNegDuration > 0;
        args[4] = locale;
        return substituteTimeAgo.apply({}, args);
    }


    /************************************
        Week of Year
    ************************************/


    // firstDayOfWeek       0 = sun, 6 = sat
    //                      the day of the week that starts the week
    //                      (usually sunday or monday)
    // firstDayOfWeekOfYear 0 = sun, 6 = sat
    //                      the first week is the week that contains the first
    //                      of this day of the week
    //                      (eg. ISO weeks use thursday (4))
    function weekOfYear(mom, firstDayOfWeek, firstDayOfWeekOfYear) {
        var end = firstDayOfWeekOfYear - firstDayOfWeek,
            daysToDayOfWeek = firstDayOfWeekOfYear - mom.day(),
            adjustedMoment;


        if (daysToDayOfWeek > end) {
            daysToDayOfWeek -= 7;
        }

        if (daysToDayOfWeek < end - 7) {
            daysToDayOfWeek += 7;
        }

        adjustedMoment = moment(mom).add(daysToDayOfWeek, 'd');
        return {
            week: Math.ceil(adjustedMoment.dayOfYear() / 7),
            year: adjustedMoment.year()
        };
    }

    //http://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
    function dayOfYearFromWeeks(year, week, weekday, firstDayOfWeekOfYear, firstDayOfWeek) {
        var d = makeUTCDate(year, 0, 1).getUTCDay(), daysToAdd, dayOfYear;

        d = d === 0 ? 7 : d;
        weekday = weekday != null ? weekday : firstDayOfWeek;
        daysToAdd = firstDayOfWeek - d + (d > firstDayOfWeekOfYear ? 7 : 0) - (d < firstDayOfWeek ? 7 : 0);
        dayOfYear = 7 * (week - 1) + (weekday - firstDayOfWeek) + daysToAdd + 1;

        return {
            year: dayOfYear > 0 ? year : year - 1,
            dayOfYear: dayOfYear > 0 ?  dayOfYear : daysInYear(year - 1) + dayOfYear
        };
    }

    /************************************
        Top Level Functions
    ************************************/

    function makeMoment(config) {
        var input = config._i,
            format = config._f,
            res;

        config._locale = config._locale || moment.localeData(config._l);

        if (input === null || (format === undefined && input === '')) {
            return moment.invalid({nullInput: true});
        }

        if (typeof input === 'string') {
            config._i = input = config._locale.preparse(input);
        }

        if (moment.isMoment(input)) {
            return new Moment(input, true);
        } else if (format) {
            if (isArray(format)) {
                makeDateFromStringAndArray(config);
            } else {
                makeDateFromStringAndFormat(config);
            }
        } else {
            makeDateFromInput(config);
        }

        res = new Moment(config);
        if (res._nextDay) {
            // Adding is smart enough around DST
            res.add(1, 'd');
            res._nextDay = undefined;
        }

        return res;
    }

    moment = function (input, format, locale, strict) {
        var c;

        if (typeof(locale) === 'boolean') {
            strict = locale;
            locale = undefined;
        }
        // object construction must be done this way.
        // https://github.com/moment/moment/issues/1423
        c = {};
        c._isAMomentObject = true;
        c._i = input;
        c._f = format;
        c._l = locale;
        c._strict = strict;
        c._isUTC = false;
        c._pf = defaultParsingFlags();

        return makeMoment(c);
    };

    moment.suppressDeprecationWarnings = false;

    moment.createFromInputFallback = deprecate(
        'moment construction falls back to js Date. This is ' +
        'discouraged and will be removed in upcoming major ' +
        'release. Please refer to ' +
        'https://github.com/moment/moment/issues/1407 for more info.',
        function (config) {
            config._d = new Date(config._i + (config._useUTC ? ' UTC' : ''));
        }
    );

    // Pick a moment m from moments so that m[fn](other) is true for all
    // other. This relies on the function fn to be transitive.
    //
    // moments should either be an array of moment objects or an array, whose
    // first element is an array of moment objects.
    function pickBy(fn, moments) {
        var res, i;
        if (moments.length === 1 && isArray(moments[0])) {
            moments = moments[0];
        }
        if (!moments.length) {
            return moment();
        }
        res = moments[0];
        for (i = 1; i < moments.length; ++i) {
            if (moments[i][fn](res)) {
                res = moments[i];
            }
        }
        return res;
    }

    moment.min = function () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isBefore', args);
    };

    moment.max = function () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isAfter', args);
    };

    // creating with utc
    moment.utc = function (input, format, locale, strict) {
        var c;

        if (typeof(locale) === 'boolean') {
            strict = locale;
            locale = undefined;
        }
        // object construction must be done this way.
        // https://github.com/moment/moment/issues/1423
        c = {};
        c._isAMomentObject = true;
        c._useUTC = true;
        c._isUTC = true;
        c._l = locale;
        c._i = input;
        c._f = format;
        c._strict = strict;
        c._pf = defaultParsingFlags();

        return makeMoment(c).utc();
    };

    // creating with unix timestamp (in seconds)
    moment.unix = function (input) {
        return moment(input * 1000);
    };

    // duration
    moment.duration = function (input, key) {
        var duration = input,
            // matching against regexp is expensive, do it on demand
            match = null,
            sign,
            ret,
            parseIso,
            diffRes;

        if (moment.isDuration(input)) {
            duration = {
                ms: input._milliseconds,
                d: input._days,
                M: input._months
            };
        } else if (typeof input === 'number') {
            duration = {};
            if (key) {
                duration[key] = input;
            } else {
                duration.milliseconds = input;
            }
        } else if (!!(match = aspNetTimeSpanJsonRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            duration = {
                y: 0,
                d: toInt(match[DATE]) * sign,
                h: toInt(match[HOUR]) * sign,
                m: toInt(match[MINUTE]) * sign,
                s: toInt(match[SECOND]) * sign,
                ms: toInt(match[MILLISECOND]) * sign
            };
        } else if (!!(match = isoDurationRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            parseIso = function (inp) {
                // We'd normally use ~~inp for this, but unfortunately it also
                // converts floats to ints.
                // inp may be undefined, so careful calling replace on it.
                var res = inp && parseFloat(inp.replace(',', '.'));
                // apply sign while we're at it
                return (isNaN(res) ? 0 : res) * sign;
            };
            duration = {
                y: parseIso(match[2]),
                M: parseIso(match[3]),
                d: parseIso(match[4]),
                h: parseIso(match[5]),
                m: parseIso(match[6]),
                s: parseIso(match[7]),
                w: parseIso(match[8])
            };
        } else if (typeof duration === 'object' &&
                ('from' in duration || 'to' in duration)) {
            diffRes = momentsDifference(moment(duration.from), moment(duration.to));

            duration = {};
            duration.ms = diffRes.milliseconds;
            duration.M = diffRes.months;
        }

        ret = new Duration(duration);

        if (moment.isDuration(input) && hasOwnProp(input, '_locale')) {
            ret._locale = input._locale;
        }

        return ret;
    };

    // version number
    moment.version = VERSION;

    // default format
    moment.defaultFormat = isoFormat;

    // constant that refers to the ISO standard
    moment.ISO_8601 = function () {};

    // Plugins that add properties should also add the key here (null value),
    // so we can properly clone ourselves.
    moment.momentProperties = momentProperties;

    // This function will be called whenever a moment is mutated.
    // It is intended to keep the offset in sync with the timezone.
    moment.updateOffset = function () {};

    // This function allows you to set a threshold for relative time strings
    moment.relativeTimeThreshold = function (threshold, limit) {
        if (relativeTimeThresholds[threshold] === undefined) {
            return false;
        }
        if (limit === undefined) {
            return relativeTimeThresholds[threshold];
        }
        relativeTimeThresholds[threshold] = limit;
        return true;
    };

    moment.lang = deprecate(
        'moment.lang is deprecated. Use moment.locale instead.',
        function (key, value) {
            return moment.locale(key, value);
        }
    );

    // This function will load locale and then set the global locale.  If
    // no arguments are passed in, it will simply return the current global
    // locale key.
    moment.locale = function (key, values) {
        var data;
        if (key) {
            if (typeof(values) !== 'undefined') {
                data = moment.defineLocale(key, values);
            }
            else {
                data = moment.localeData(key);
            }

            if (data) {
                moment.duration._locale = moment._locale = data;
            }
        }

        return moment._locale._abbr;
    };

    moment.defineLocale = function (name, values) {
        if (values !== null) {
            values.abbr = name;
            if (!locales[name]) {
                locales[name] = new Locale();
            }
            locales[name].set(values);

            // backwards compat for now: also set the locale
            moment.locale(name);

            return locales[name];
        } else {
            // useful for testing
            delete locales[name];
            return null;
        }
    };

    moment.langData = deprecate(
        'moment.langData is deprecated. Use moment.localeData instead.',
        function (key) {
            return moment.localeData(key);
        }
    );

    // returns locale data
    moment.localeData = function (key) {
        var locale;

        if (key && key._locale && key._locale._abbr) {
            key = key._locale._abbr;
        }

        if (!key) {
            return moment._locale;
        }

        if (!isArray(key)) {
            //short-circuit everything else
            locale = loadLocale(key);
            if (locale) {
                return locale;
            }
            key = [key];
        }

        return chooseLocale(key);
    };

    // compare moment object
    moment.isMoment = function (obj) {
        return obj instanceof Moment ||
            (obj != null && hasOwnProp(obj, '_isAMomentObject'));
    };

    // for typechecking Duration objects
    moment.isDuration = function (obj) {
        return obj instanceof Duration;
    };

    for (i = lists.length - 1; i >= 0; --i) {
        makeList(lists[i]);
    }

    moment.normalizeUnits = function (units) {
        return normalizeUnits(units);
    };

    moment.invalid = function (flags) {
        var m = moment.utc(NaN);
        if (flags != null) {
            extend(m._pf, flags);
        }
        else {
            m._pf.userInvalidated = true;
        }

        return m;
    };

    moment.parseZone = function () {
        return moment.apply(null, arguments).parseZone();
    };

    moment.parseTwoDigitYear = function (input) {
        return toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
    };

    /************************************
        Moment Prototype
    ************************************/


    extend(moment.fn = Moment.prototype, {

        clone : function () {
            return moment(this);
        },

        valueOf : function () {
            return +this._d + ((this._offset || 0) * 60000);
        },

        unix : function () {
            return Math.floor(+this / 1000);
        },

        toString : function () {
            return this.clone().locale('en').format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ');
        },

        toDate : function () {
            return this._offset ? new Date(+this) : this._d;
        },

        toISOString : function () {
            var m = moment(this).utc();
            if (0 < m.year() && m.year() <= 9999) {
                if ('function' === typeof Date.prototype.toISOString) {
                    // native implementation is ~50x faster, use it when we can
                    return this.toDate().toISOString();
                } else {
                    return formatMoment(m, 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
                }
            } else {
                return formatMoment(m, 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
            }
        },

        toArray : function () {
            var m = this;
            return [
                m.year(),
                m.month(),
                m.date(),
                m.hours(),
                m.minutes(),
                m.seconds(),
                m.milliseconds()
            ];
        },

        isValid : function () {
            return isValid(this);
        },

        isDSTShifted : function () {
            if (this._a) {
                return this.isValid() && compareArrays(this._a, (this._isUTC ? moment.utc(this._a) : moment(this._a)).toArray()) > 0;
            }

            return false;
        },

        parsingFlags : function () {
            return extend({}, this._pf);
        },

        invalidAt: function () {
            return this._pf.overflow;
        },

        utc : function (keepLocalTime) {
            return this.zone(0, keepLocalTime);
        },

        local : function (keepLocalTime) {
            if (this._isUTC) {
                this.zone(0, keepLocalTime);
                this._isUTC = false;

                if (keepLocalTime) {
                    this.add(this._dateTzOffset(), 'm');
                }
            }
            return this;
        },

        format : function (inputString) {
            var output = formatMoment(this, inputString || moment.defaultFormat);
            return this.localeData().postformat(output);
        },

        add : createAdder(1, 'add'),

        subtract : createAdder(-1, 'subtract'),

        diff : function (input, units, asFloat) {
            var that = makeAs(input, this),
                zoneDiff = (this.zone() - that.zone()) * 6e4,
                diff, output, daysAdjust;

            units = normalizeUnits(units);

            if (units === 'year' || units === 'month') {
                // average number of days in the months in the given dates
                diff = (this.daysInMonth() + that.daysInMonth()) * 432e5; // 24 * 60 * 60 * 1000 / 2
                // difference in months
                output = ((this.year() - that.year()) * 12) + (this.month() - that.month());
                // adjust by taking difference in days, average number of days
                // and dst in the given months.
                daysAdjust = (this - moment(this).startOf('month')) -
                    (that - moment(that).startOf('month'));
                // same as above but with zones, to negate all dst
                daysAdjust -= ((this.zone() - moment(this).startOf('month').zone()) -
                        (that.zone() - moment(that).startOf('month').zone())) * 6e4;
                output += daysAdjust / diff;
                if (units === 'year') {
                    output = output / 12;
                }
            } else {
                diff = (this - that);
                output = units === 'second' ? diff / 1e3 : // 1000
                    units === 'minute' ? diff / 6e4 : // 1000 * 60
                    units === 'hour' ? diff / 36e5 : // 1000 * 60 * 60
                    units === 'day' ? (diff - zoneDiff) / 864e5 : // 1000 * 60 * 60 * 24, negate dst
                    units === 'week' ? (diff - zoneDiff) / 6048e5 : // 1000 * 60 * 60 * 24 * 7, negate dst
                    diff;
            }
            return asFloat ? output : absRound(output);
        },

        from : function (time, withoutSuffix) {
            return moment.duration({to: this, from: time}).locale(this.locale()).humanize(!withoutSuffix);
        },

        fromNow : function (withoutSuffix) {
            return this.from(moment(), withoutSuffix);
        },

        calendar : function (time) {
            // We want to compare the start of today, vs this.
            // Getting start-of-today depends on whether we're zone'd or not.
            var now = time || moment(),
                sod = makeAs(now, this).startOf('day'),
                diff = this.diff(sod, 'days', true),
                format = diff < -6 ? 'sameElse' :
                    diff < -1 ? 'lastWeek' :
                    diff < 0 ? 'lastDay' :
                    diff < 1 ? 'sameDay' :
                    diff < 2 ? 'nextDay' :
                    diff < 7 ? 'nextWeek' : 'sameElse';
            return this.format(this.localeData().calendar(format, this, moment(now)));
        },

        isLeapYear : function () {
            return isLeapYear(this.year());
        },

        isDST : function () {
            return (this.zone() < this.clone().month(0).zone() ||
                this.zone() < this.clone().month(5).zone());
        },

        day : function (input) {
            var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
            if (input != null) {
                input = parseWeekday(input, this.localeData());
                return this.add(input - day, 'd');
            } else {
                return day;
            }
        },

        month : makeAccessor('Month', true),

        startOf : function (units) {
            units = normalizeUnits(units);
            // the following switch intentionally omits break keywords
            // to utilize falling through the cases.
            switch (units) {
            case 'year':
                this.month(0);
                /* falls through */
            case 'quarter':
            case 'month':
                this.date(1);
                /* falls through */
            case 'week':
            case 'isoWeek':
            case 'day':
                this.hours(0);
                /* falls through */
            case 'hour':
                this.minutes(0);
                /* falls through */
            case 'minute':
                this.seconds(0);
                /* falls through */
            case 'second':
                this.milliseconds(0);
                /* falls through */
            }

            // weeks are a special case
            if (units === 'week') {
                this.weekday(0);
            } else if (units === 'isoWeek') {
                this.isoWeekday(1);
            }

            // quarters are also special
            if (units === 'quarter') {
                this.month(Math.floor(this.month() / 3) * 3);
            }

            return this;
        },

        endOf: function (units) {
            units = normalizeUnits(units);
            if (units === undefined || units === 'millisecond') {
                return this;
            }
            return this.startOf(units).add(1, (units === 'isoWeek' ? 'week' : units)).subtract(1, 'ms');
        },

        isAfter: function (input, units) {
            var inputMs;
            units = normalizeUnits(typeof units !== 'undefined' ? units : 'millisecond');
            if (units === 'millisecond') {
                input = moment.isMoment(input) ? input : moment(input);
                return +this > +input;
            } else {
                inputMs = moment.isMoment(input) ? +input : +moment(input);
                return inputMs < +this.clone().startOf(units);
            }
        },

        isBefore: function (input, units) {
            var inputMs;
            units = normalizeUnits(typeof units !== 'undefined' ? units : 'millisecond');
            if (units === 'millisecond') {
                input = moment.isMoment(input) ? input : moment(input);
                return +this < +input;
            } else {
                inputMs = moment.isMoment(input) ? +input : +moment(input);
                return +this.clone().endOf(units) < inputMs;
            }
        },

        isSame: function (input, units) {
            var inputMs;
            units = normalizeUnits(units || 'millisecond');
            if (units === 'millisecond') {
                input = moment.isMoment(input) ? input : moment(input);
                return +this === +input;
            } else {
                inputMs = +moment(input);
                return +(this.clone().startOf(units)) <= inputMs && inputMs <= +(this.clone().endOf(units));
            }
        },

        min: deprecate(
                 'moment().min is deprecated, use moment.min instead. https://github.com/moment/moment/issues/1548',
                 function (other) {
                     other = moment.apply(null, arguments);
                     return other < this ? this : other;
                 }
         ),

        max: deprecate(
                'moment().max is deprecated, use moment.max instead. https://github.com/moment/moment/issues/1548',
                function (other) {
                    other = moment.apply(null, arguments);
                    return other > this ? this : other;
                }
        ),

        // keepLocalTime = true means only change the timezone, without
        // affecting the local hour. So 5:31:26 +0300 --[zone(2, true)]-->
        // 5:31:26 +0200 It is possible that 5:31:26 doesn't exist int zone
        // +0200, so we adjust the time as needed, to be valid.
        //
        // Keeping the time actually adds/subtracts (one hour)
        // from the actual represented time. That is why we call updateOffset
        // a second time. In case it wants us to change the offset again
        // _changeInProgress == true case, then we have to adjust, because
        // there is no such time in the given timezone.
        zone : function (input, keepLocalTime) {
            var offset = this._offset || 0,
                localAdjust;
            if (input != null) {
                if (typeof input === 'string') {
                    input = timezoneMinutesFromString(input);
                }
                if (Math.abs(input) < 16) {
                    input = input * 60;
                }
                if (!this._isUTC && keepLocalTime) {
                    localAdjust = this._dateTzOffset();
                }
                this._offset = input;
                this._isUTC = true;
                if (localAdjust != null) {
                    this.subtract(localAdjust, 'm');
                }
                if (offset !== input) {
                    if (!keepLocalTime || this._changeInProgress) {
                        addOrSubtractDurationFromMoment(this,
                                moment.duration(offset - input, 'm'), 1, false);
                    } else if (!this._changeInProgress) {
                        this._changeInProgress = true;
                        moment.updateOffset(this, true);
                        this._changeInProgress = null;
                    }
                }
            } else {
                return this._isUTC ? offset : this._dateTzOffset();
            }
            return this;
        },

        zoneAbbr : function () {
            return this._isUTC ? 'UTC' : '';
        },

        zoneName : function () {
            return this._isUTC ? 'Coordinated Universal Time' : '';
        },

        parseZone : function () {
            if (this._tzm) {
                this.zone(this._tzm);
            } else if (typeof this._i === 'string') {
                this.zone(this._i);
            }
            return this;
        },

        hasAlignedHourOffset : function (input) {
            if (!input) {
                input = 0;
            }
            else {
                input = moment(input).zone();
            }

            return (this.zone() - input) % 60 === 0;
        },

        daysInMonth : function () {
            return daysInMonth(this.year(), this.month());
        },

        dayOfYear : function (input) {
            var dayOfYear = round((moment(this).startOf('day') - moment(this).startOf('year')) / 864e5) + 1;
            return input == null ? dayOfYear : this.add((input - dayOfYear), 'd');
        },

        quarter : function (input) {
            return input == null ? Math.ceil((this.month() + 1) / 3) : this.month((input - 1) * 3 + this.month() % 3);
        },

        weekYear : function (input) {
            var year = weekOfYear(this, this.localeData()._week.dow, this.localeData()._week.doy).year;
            return input == null ? year : this.add((input - year), 'y');
        },

        isoWeekYear : function (input) {
            var year = weekOfYear(this, 1, 4).year;
            return input == null ? year : this.add((input - year), 'y');
        },

        week : function (input) {
            var week = this.localeData().week(this);
            return input == null ? week : this.add((input - week) * 7, 'd');
        },

        isoWeek : function (input) {
            var week = weekOfYear(this, 1, 4).week;
            return input == null ? week : this.add((input - week) * 7, 'd');
        },

        weekday : function (input) {
            var weekday = (this.day() + 7 - this.localeData()._week.dow) % 7;
            return input == null ? weekday : this.add(input - weekday, 'd');
        },

        isoWeekday : function (input) {
            // behaves the same as moment#day except
            // as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
            // as a setter, sunday should belong to the previous week.
            return input == null ? this.day() || 7 : this.day(this.day() % 7 ? input : input - 7);
        },

        isoWeeksInYear : function () {
            return weeksInYear(this.year(), 1, 4);
        },

        weeksInYear : function () {
            var weekInfo = this.localeData()._week;
            return weeksInYear(this.year(), weekInfo.dow, weekInfo.doy);
        },

        get : function (units) {
            units = normalizeUnits(units);
            return this[units]();
        },

        set : function (units, value) {
            units = normalizeUnits(units);
            if (typeof this[units] === 'function') {
                this[units](value);
            }
            return this;
        },

        // If passed a locale key, it will set the locale for this
        // instance.  Otherwise, it will return the locale configuration
        // variables for this instance.
        locale : function (key) {
            var newLocaleData;

            if (key === undefined) {
                return this._locale._abbr;
            } else {
                newLocaleData = moment.localeData(key);
                if (newLocaleData != null) {
                    this._locale = newLocaleData;
                }
                return this;
            }
        },

        lang : deprecate(
            'moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.',
            function (key) {
                if (key === undefined) {
                    return this.localeData();
                } else {
                    return this.locale(key);
                }
            }
        ),

        localeData : function () {
            return this._locale;
        },

        _dateTzOffset : function () {
            // On Firefox.24 Date#getTimezoneOffset returns a floating point.
            // https://github.com/moment/moment/pull/1871
            return Math.round(this._d.getTimezoneOffset() / 15) * 15;
        }
    });

    function rawMonthSetter(mom, value) {
        var dayOfMonth;

        // TODO: Move this out of here!
        if (typeof value === 'string') {
            value = mom.localeData().monthsParse(value);
            // TODO: Another silent failure?
            if (typeof value !== 'number') {
                return mom;
            }
        }

        dayOfMonth = Math.min(mom.date(),
                daysInMonth(mom.year(), value));
        mom._d['set' + (mom._isUTC ? 'UTC' : '') + 'Month'](value, dayOfMonth);
        return mom;
    }

    function rawGetter(mom, unit) {
        return mom._d['get' + (mom._isUTC ? 'UTC' : '') + unit]();
    }

    function rawSetter(mom, unit, value) {
        if (unit === 'Month') {
            return rawMonthSetter(mom, value);
        } else {
            return mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value);
        }
    }

    function makeAccessor(unit, keepTime) {
        return function (value) {
            if (value != null) {
                rawSetter(this, unit, value);
                moment.updateOffset(this, keepTime);
                return this;
            } else {
                return rawGetter(this, unit);
            }
        };
    }

    moment.fn.millisecond = moment.fn.milliseconds = makeAccessor('Milliseconds', false);
    moment.fn.second = moment.fn.seconds = makeAccessor('Seconds', false);
    moment.fn.minute = moment.fn.minutes = makeAccessor('Minutes', false);
    // Setting the hour should keep the time, because the user explicitly
    // specified which hour he wants. So trying to maintain the same hour (in
    // a new timezone) makes sense. Adding/subtracting hours does not follow
    // this rule.
    moment.fn.hour = moment.fn.hours = makeAccessor('Hours', true);
    // moment.fn.month is defined separately
    moment.fn.date = makeAccessor('Date', true);
    moment.fn.dates = deprecate('dates accessor is deprecated. Use date instead.', makeAccessor('Date', true));
    moment.fn.year = makeAccessor('FullYear', true);
    moment.fn.years = deprecate('years accessor is deprecated. Use year instead.', makeAccessor('FullYear', true));

    // add plural methods
    moment.fn.days = moment.fn.day;
    moment.fn.months = moment.fn.month;
    moment.fn.weeks = moment.fn.week;
    moment.fn.isoWeeks = moment.fn.isoWeek;
    moment.fn.quarters = moment.fn.quarter;

    // add aliased format methods
    moment.fn.toJSON = moment.fn.toISOString;

    /************************************
        Duration Prototype
    ************************************/


    function daysToYears (days) {
        // 400 years have 146097 days (taking into account leap year rules)
        return days * 400 / 146097;
    }

    function yearsToDays (years) {
        // years * 365 + absRound(years / 4) -
        //     absRound(years / 100) + absRound(years / 400);
        return years * 146097 / 400;
    }

    extend(moment.duration.fn = Duration.prototype, {

        _bubble : function () {
            var milliseconds = this._milliseconds,
                days = this._days,
                months = this._months,
                data = this._data,
                seconds, minutes, hours, years = 0;

            // The following code bubbles up values, see the tests for
            // examples of what that means.
            data.milliseconds = milliseconds % 1000;

            seconds = absRound(milliseconds / 1000);
            data.seconds = seconds % 60;

            minutes = absRound(seconds / 60);
            data.minutes = minutes % 60;

            hours = absRound(minutes / 60);
            data.hours = hours % 24;

            days += absRound(hours / 24);

            // Accurately convert days to years, assume start from year 0.
            years = absRound(daysToYears(days));
            days -= absRound(yearsToDays(years));

            // 30 days to a month
            // TODO (iskren): Use anchor date (like 1st Jan) to compute this.
            months += absRound(days / 30);
            days %= 30;

            // 12 months -> 1 year
            years += absRound(months / 12);
            months %= 12;

            data.days = days;
            data.months = months;
            data.years = years;
        },

        abs : function () {
            this._milliseconds = Math.abs(this._milliseconds);
            this._days = Math.abs(this._days);
            this._months = Math.abs(this._months);

            this._data.milliseconds = Math.abs(this._data.milliseconds);
            this._data.seconds = Math.abs(this._data.seconds);
            this._data.minutes = Math.abs(this._data.minutes);
            this._data.hours = Math.abs(this._data.hours);
            this._data.months = Math.abs(this._data.months);
            this._data.years = Math.abs(this._data.years);

            return this;
        },

        weeks : function () {
            return absRound(this.days() / 7);
        },

        valueOf : function () {
            return this._milliseconds +
              this._days * 864e5 +
              (this._months % 12) * 2592e6 +
              toInt(this._months / 12) * 31536e6;
        },

        humanize : function (withSuffix) {
            var output = relativeTime(this, !withSuffix, this.localeData());

            if (withSuffix) {
                output = this.localeData().pastFuture(+this, output);
            }

            return this.localeData().postformat(output);
        },

        add : function (input, val) {
            // supports only 2.0-style add(1, 's') or add(moment)
            var dur = moment.duration(input, val);

            this._milliseconds += dur._milliseconds;
            this._days += dur._days;
            this._months += dur._months;

            this._bubble();

            return this;
        },

        subtract : function (input, val) {
            var dur = moment.duration(input, val);

            this._milliseconds -= dur._milliseconds;
            this._days -= dur._days;
            this._months -= dur._months;

            this._bubble();

            return this;
        },

        get : function (units) {
            units = normalizeUnits(units);
            return this[units.toLowerCase() + 's']();
        },

        as : function (units) {
            var days, months;
            units = normalizeUnits(units);

            if (units === 'month' || units === 'year') {
                days = this._days + this._milliseconds / 864e5;
                months = this._months + daysToYears(days) * 12;
                return units === 'month' ? months : months / 12;
            } else {
                // handle milliseconds separately because of floating point math errors (issue #1867)
                days = this._days + Math.round(yearsToDays(this._months / 12));
                switch (units) {
                    case 'week': return days / 7 + this._milliseconds / 6048e5;
                    case 'day': return days + this._milliseconds / 864e5;
                    case 'hour': return days * 24 + this._milliseconds / 36e5;
                    case 'minute': return days * 24 * 60 + this._milliseconds / 6e4;
                    case 'second': return days * 24 * 60 * 60 + this._milliseconds / 1000;
                    // Math.floor prevents floating point math errors here
                    case 'millisecond': return Math.floor(days * 24 * 60 * 60 * 1000) + this._milliseconds;
                    default: throw new Error('Unknown unit ' + units);
                }
            }
        },

        lang : moment.fn.lang,
        locale : moment.fn.locale,

        toIsoString : deprecate(
            'toIsoString() is deprecated. Please use toISOString() instead ' +
            '(notice the capitals)',
            function () {
                return this.toISOString();
            }
        ),

        toISOString : function () {
            // inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
            var years = Math.abs(this.years()),
                months = Math.abs(this.months()),
                days = Math.abs(this.days()),
                hours = Math.abs(this.hours()),
                minutes = Math.abs(this.minutes()),
                seconds = Math.abs(this.seconds() + this.milliseconds() / 1000);

            if (!this.asSeconds()) {
                // this is the same as C#'s (Noda) and python (isodate)...
                // but not other JS (goog.date)
                return 'P0D';
            }

            return (this.asSeconds() < 0 ? '-' : '') +
                'P' +
                (years ? years + 'Y' : '') +
                (months ? months + 'M' : '') +
                (days ? days + 'D' : '') +
                ((hours || minutes || seconds) ? 'T' : '') +
                (hours ? hours + 'H' : '') +
                (minutes ? minutes + 'M' : '') +
                (seconds ? seconds + 'S' : '');
        },

        localeData : function () {
            return this._locale;
        }
    });

    moment.duration.fn.toString = moment.duration.fn.toISOString;

    function makeDurationGetter(name) {
        moment.duration.fn[name] = function () {
            return this._data[name];
        };
    }

    for (i in unitMillisecondFactors) {
        if (hasOwnProp(unitMillisecondFactors, i)) {
            makeDurationGetter(i.toLowerCase());
        }
    }

    moment.duration.fn.asMilliseconds = function () {
        return this.as('ms');
    };
    moment.duration.fn.asSeconds = function () {
        return this.as('s');
    };
    moment.duration.fn.asMinutes = function () {
        return this.as('m');
    };
    moment.duration.fn.asHours = function () {
        return this.as('h');
    };
    moment.duration.fn.asDays = function () {
        return this.as('d');
    };
    moment.duration.fn.asWeeks = function () {
        return this.as('weeks');
    };
    moment.duration.fn.asMonths = function () {
        return this.as('M');
    };
    moment.duration.fn.asYears = function () {
        return this.as('y');
    };

    /************************************
        Default Locale
    ************************************/


    // Set default locale, other locale will inherit from English.
    moment.locale('en', {
        ordinalParse: /\d{1,2}(th|st|nd|rd)/,
        ordinal : function (number) {
            var b = number % 10,
                output = (toInt(number % 100 / 10) === 1) ? 'th' :
                (b === 1) ? 'st' :
                (b === 2) ? 'nd' :
                (b === 3) ? 'rd' : 'th';
            return number + output;
        }
    });

    /* EMBED_LOCALES */

    /************************************
        Exposing Moment
    ************************************/

    function makeGlobal(shouldDeprecate) {
        /*global ender:false */
        if (typeof ender !== 'undefined') {
            return;
        }
        oldGlobalMoment = globalScope.moment;
        if (shouldDeprecate) {
            globalScope.moment = deprecate(
                    'Accessing Moment through the global scope is ' +
                    'deprecated, and will be removed in an upcoming ' +
                    'release.',
                    moment);
        } else {
            globalScope.moment = moment;
        }
    }

    // CommonJS module is defined
    if (hasModule) {
        module.exports = moment;
    } else if (typeof define === 'function' && define.amd) {
        define('moment', function (require, exports, module) {
            if (module.config && module.config() && module.config().noGlobal === true) {
                // release the global variable
                globalScope.moment = oldGlobalMoment;
            }

            return moment;
        });
        makeGlobal(true);
    } else {
        makeGlobal();
    }
}).call(this);

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],45:[function(require,module,exports){

var indexOf = [].indexOf;

module.exports = function(arr, obj){
  if (indexOf) return arr.indexOf(obj);
  for (var i = 0; i < arr.length; ++i) {
    if (arr[i] === obj) return i;
  }
  return -1;
};
},{}],46:[function(require,module,exports){
//! moment.js
//! version : 2.10.6
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    global.moment = factory()
}(this, function () { 'use strict';

    var hookCallback;

    function utils_hooks__hooks () {
        return hookCallback.apply(null, arguments);
    }

    // This is done to register the method called with moment()
    // without creating circular dependencies.
    function setHookCallback (callback) {
        hookCallback = callback;
    }

    function isArray(input) {
        return Object.prototype.toString.call(input) === '[object Array]';
    }

    function isDate(input) {
        return input instanceof Date || Object.prototype.toString.call(input) === '[object Date]';
    }

    function map(arr, fn) {
        var res = [], i;
        for (i = 0; i < arr.length; ++i) {
            res.push(fn(arr[i], i));
        }
        return res;
    }

    function hasOwnProp(a, b) {
        return Object.prototype.hasOwnProperty.call(a, b);
    }

    function extend(a, b) {
        for (var i in b) {
            if (hasOwnProp(b, i)) {
                a[i] = b[i];
            }
        }

        if (hasOwnProp(b, 'toString')) {
            a.toString = b.toString;
        }

        if (hasOwnProp(b, 'valueOf')) {
            a.valueOf = b.valueOf;
        }

        return a;
    }

    function create_utc__createUTC (input, format, locale, strict) {
        return createLocalOrUTC(input, format, locale, strict, true).utc();
    }

    function defaultParsingFlags() {
        // We need to deep clone this object.
        return {
            empty           : false,
            unusedTokens    : [],
            unusedInput     : [],
            overflow        : -2,
            charsLeftOver   : 0,
            nullInput       : false,
            invalidMonth    : null,
            invalidFormat   : false,
            userInvalidated : false,
            iso             : false
        };
    }

    function getParsingFlags(m) {
        if (m._pf == null) {
            m._pf = defaultParsingFlags();
        }
        return m._pf;
    }

    function valid__isValid(m) {
        if (m._isValid == null) {
            var flags = getParsingFlags(m);
            m._isValid = !isNaN(m._d.getTime()) &&
                flags.overflow < 0 &&
                !flags.empty &&
                !flags.invalidMonth &&
                !flags.invalidWeekday &&
                !flags.nullInput &&
                !flags.invalidFormat &&
                !flags.userInvalidated;

            if (m._strict) {
                m._isValid = m._isValid &&
                    flags.charsLeftOver === 0 &&
                    flags.unusedTokens.length === 0 &&
                    flags.bigHour === undefined;
            }
        }
        return m._isValid;
    }

    function valid__createInvalid (flags) {
        var m = create_utc__createUTC(NaN);
        if (flags != null) {
            extend(getParsingFlags(m), flags);
        }
        else {
            getParsingFlags(m).userInvalidated = true;
        }

        return m;
    }

    var momentProperties = utils_hooks__hooks.momentProperties = [];

    function copyConfig(to, from) {
        var i, prop, val;

        if (typeof from._isAMomentObject !== 'undefined') {
            to._isAMomentObject = from._isAMomentObject;
        }
        if (typeof from._i !== 'undefined') {
            to._i = from._i;
        }
        if (typeof from._f !== 'undefined') {
            to._f = from._f;
        }
        if (typeof from._l !== 'undefined') {
            to._l = from._l;
        }
        if (typeof from._strict !== 'undefined') {
            to._strict = from._strict;
        }
        if (typeof from._tzm !== 'undefined') {
            to._tzm = from._tzm;
        }
        if (typeof from._isUTC !== 'undefined') {
            to._isUTC = from._isUTC;
        }
        if (typeof from._offset !== 'undefined') {
            to._offset = from._offset;
        }
        if (typeof from._pf !== 'undefined') {
            to._pf = getParsingFlags(from);
        }
        if (typeof from._locale !== 'undefined') {
            to._locale = from._locale;
        }

        if (momentProperties.length > 0) {
            for (i in momentProperties) {
                prop = momentProperties[i];
                val = from[prop];
                if (typeof val !== 'undefined') {
                    to[prop] = val;
                }
            }
        }

        return to;
    }

    var updateInProgress = false;

    // Moment prototype object
    function Moment(config) {
        copyConfig(this, config);
        this._d = new Date(config._d != null ? config._d.getTime() : NaN);
        // Prevent infinite loop in case updateOffset creates new moment
        // objects.
        if (updateInProgress === false) {
            updateInProgress = true;
            utils_hooks__hooks.updateOffset(this);
            updateInProgress = false;
        }
    }

    function isMoment (obj) {
        return obj instanceof Moment || (obj != null && obj._isAMomentObject != null);
    }

    function absFloor (number) {
        if (number < 0) {
            return Math.ceil(number);
        } else {
            return Math.floor(number);
        }
    }

    function toInt(argumentForCoercion) {
        var coercedNumber = +argumentForCoercion,
            value = 0;

        if (coercedNumber !== 0 && isFinite(coercedNumber)) {
            value = absFloor(coercedNumber);
        }

        return value;
    }

    function compareArrays(array1, array2, dontConvert) {
        var len = Math.min(array1.length, array2.length),
            lengthDiff = Math.abs(array1.length - array2.length),
            diffs = 0,
            i;
        for (i = 0; i < len; i++) {
            if ((dontConvert && array1[i] !== array2[i]) ||
                (!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
                diffs++;
            }
        }
        return diffs + lengthDiff;
    }

    function Locale() {
    }

    var locales = {};
    var globalLocale;

    function normalizeLocale(key) {
        return key ? key.toLowerCase().replace('_', '-') : key;
    }

    // pick the locale from the array
    // try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
    // substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
    function chooseLocale(names) {
        var i = 0, j, next, locale, split;

        while (i < names.length) {
            split = normalizeLocale(names[i]).split('-');
            j = split.length;
            next = normalizeLocale(names[i + 1]);
            next = next ? next.split('-') : null;
            while (j > 0) {
                locale = loadLocale(split.slice(0, j).join('-'));
                if (locale) {
                    return locale;
                }
                if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
                    //the next array item is better than a shallower substring of this one
                    break;
                }
                j--;
            }
            i++;
        }
        return null;
    }

    function loadLocale(name) {
        var oldLocale = null;
        // TODO: Find a better way to register and load all the locales in Node
        if (!locales[name] && typeof module !== 'undefined' &&
                module && module.exports) {
            try {
                oldLocale = globalLocale._abbr;
                require('./locale/' + name);
                // because defineLocale currently also sets the global locale, we
                // want to undo that for lazy loaded locales
                locale_locales__getSetGlobalLocale(oldLocale);
            } catch (e) { }
        }
        return locales[name];
    }

    // This function will load locale and then set the global locale.  If
    // no arguments are passed in, it will simply return the current global
    // locale key.
    function locale_locales__getSetGlobalLocale (key, values) {
        var data;
        if (key) {
            if (typeof values === 'undefined') {
                data = locale_locales__getLocale(key);
            }
            else {
                data = defineLocale(key, values);
            }

            if (data) {
                // moment.duration._locale = moment._locale = data;
                globalLocale = data;
            }
        }

        return globalLocale._abbr;
    }

    function defineLocale (name, values) {
        if (values !== null) {
            values.abbr = name;
            locales[name] = locales[name] || new Locale();
            locales[name].set(values);

            // backwards compat for now: also set the locale
            locale_locales__getSetGlobalLocale(name);

            return locales[name];
        } else {
            // useful for testing
            delete locales[name];
            return null;
        }
    }

    // returns locale data
    function locale_locales__getLocale (key) {
        var locale;

        if (key && key._locale && key._locale._abbr) {
            key = key._locale._abbr;
        }

        if (!key) {
            return globalLocale;
        }

        if (!isArray(key)) {
            //short-circuit everything else
            locale = loadLocale(key);
            if (locale) {
                return locale;
            }
            key = [key];
        }

        return chooseLocale(key);
    }

    var aliases = {};

    function addUnitAlias (unit, shorthand) {
        var lowerCase = unit.toLowerCase();
        aliases[lowerCase] = aliases[lowerCase + 's'] = aliases[shorthand] = unit;
    }

    function normalizeUnits(units) {
        return typeof units === 'string' ? aliases[units] || aliases[units.toLowerCase()] : undefined;
    }

    function normalizeObjectUnits(inputObject) {
        var normalizedInput = {},
            normalizedProp,
            prop;

        for (prop in inputObject) {
            if (hasOwnProp(inputObject, prop)) {
                normalizedProp = normalizeUnits(prop);
                if (normalizedProp) {
                    normalizedInput[normalizedProp] = inputObject[prop];
                }
            }
        }

        return normalizedInput;
    }

    function makeGetSet (unit, keepTime) {
        return function (value) {
            if (value != null) {
                get_set__set(this, unit, value);
                utils_hooks__hooks.updateOffset(this, keepTime);
                return this;
            } else {
                return get_set__get(this, unit);
            }
        };
    }

    function get_set__get (mom, unit) {
        return mom._d['get' + (mom._isUTC ? 'UTC' : '') + unit]();
    }

    function get_set__set (mom, unit, value) {
        return mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value);
    }

    // MOMENTS

    function getSet (units, value) {
        var unit;
        if (typeof units === 'object') {
            for (unit in units) {
                this.set(unit, units[unit]);
            }
        } else {
            units = normalizeUnits(units);
            if (typeof this[units] === 'function') {
                return this[units](value);
            }
        }
        return this;
    }

    function zeroFill(number, targetLength, forceSign) {
        var absNumber = '' + Math.abs(number),
            zerosToFill = targetLength - absNumber.length,
            sign = number >= 0;
        return (sign ? (forceSign ? '+' : '') : '-') +
            Math.pow(10, Math.max(0, zerosToFill)).toString().substr(1) + absNumber;
    }

    var formattingTokens = /(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Q|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|S{1,9}|x|X|zz?|ZZ?|.)/g;

    var localFormattingTokens = /(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g;

    var formatFunctions = {};

    var formatTokenFunctions = {};

    // token:    'M'
    // padded:   ['MM', 2]
    // ordinal:  'Mo'
    // callback: function () { this.month() + 1 }
    function addFormatToken (token, padded, ordinal, callback) {
        var func = callback;
        if (typeof callback === 'string') {
            func = function () {
                return this[callback]();
            };
        }
        if (token) {
            formatTokenFunctions[token] = func;
        }
        if (padded) {
            formatTokenFunctions[padded[0]] = function () {
                return zeroFill(func.apply(this, arguments), padded[1], padded[2]);
            };
        }
        if (ordinal) {
            formatTokenFunctions[ordinal] = function () {
                return this.localeData().ordinal(func.apply(this, arguments), token);
            };
        }
    }

    function removeFormattingTokens(input) {
        if (input.match(/\[[\s\S]/)) {
            return input.replace(/^\[|\]$/g, '');
        }
        return input.replace(/\\/g, '');
    }

    function makeFormatFunction(format) {
        var array = format.match(formattingTokens), i, length;

        for (i = 0, length = array.length; i < length; i++) {
            if (formatTokenFunctions[array[i]]) {
                array[i] = formatTokenFunctions[array[i]];
            } else {
                array[i] = removeFormattingTokens(array[i]);
            }
        }

        return function (mom) {
            var output = '';
            for (i = 0; i < length; i++) {
                output += array[i] instanceof Function ? array[i].call(mom, format) : array[i];
            }
            return output;
        };
    }

    // format date using native date object
    function formatMoment(m, format) {
        if (!m.isValid()) {
            return m.localeData().invalidDate();
        }

        format = expandFormat(format, m.localeData());
        formatFunctions[format] = formatFunctions[format] || makeFormatFunction(format);

        return formatFunctions[format](m);
    }

    function expandFormat(format, locale) {
        var i = 5;

        function replaceLongDateFormatTokens(input) {
            return locale.longDateFormat(input) || input;
        }

        localFormattingTokens.lastIndex = 0;
        while (i >= 0 && localFormattingTokens.test(format)) {
            format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
            localFormattingTokens.lastIndex = 0;
            i -= 1;
        }

        return format;
    }

    var match1         = /\d/;            //       0 - 9
    var match2         = /\d\d/;          //      00 - 99
    var match3         = /\d{3}/;         //     000 - 999
    var match4         = /\d{4}/;         //    0000 - 9999
    var match6         = /[+-]?\d{6}/;    // -999999 - 999999
    var match1to2      = /\d\d?/;         //       0 - 99
    var match1to3      = /\d{1,3}/;       //       0 - 999
    var match1to4      = /\d{1,4}/;       //       0 - 9999
    var match1to6      = /[+-]?\d{1,6}/;  // -999999 - 999999

    var matchUnsigned  = /\d+/;           //       0 - inf
    var matchSigned    = /[+-]?\d+/;      //    -inf - inf

    var matchOffset    = /Z|[+-]\d\d:?\d\d/gi; // +00:00 -00:00 +0000 -0000 or Z

    var matchTimestamp = /[+-]?\d+(\.\d{1,3})?/; // 123456789 123456789.123

    // any word (or two) characters or numbers including two/three word month in arabic.
    var matchWord = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i;

    var regexes = {};

    function isFunction (sth) {
        // https://github.com/moment/moment/issues/2325
        return typeof sth === 'function' &&
            Object.prototype.toString.call(sth) === '[object Function]';
    }


    function addRegexToken (token, regex, strictRegex) {
        regexes[token] = isFunction(regex) ? regex : function (isStrict) {
            return (isStrict && strictRegex) ? strictRegex : regex;
        };
    }

    function getParseRegexForToken (token, config) {
        if (!hasOwnProp(regexes, token)) {
            return new RegExp(unescapeFormat(token));
        }

        return regexes[token](config._strict, config._locale);
    }

    // Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
    function unescapeFormat(s) {
        return s.replace('\\', '').replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
            return p1 || p2 || p3 || p4;
        }).replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    var tokens = {};

    function addParseToken (token, callback) {
        var i, func = callback;
        if (typeof token === 'string') {
            token = [token];
        }
        if (typeof callback === 'number') {
            func = function (input, array) {
                array[callback] = toInt(input);
            };
        }
        for (i = 0; i < token.length; i++) {
            tokens[token[i]] = func;
        }
    }

    function addWeekParseToken (token, callback) {
        addParseToken(token, function (input, array, config, token) {
            config._w = config._w || {};
            callback(input, config._w, config, token);
        });
    }

    function addTimeToArrayFromToken(token, input, config) {
        if (input != null && hasOwnProp(tokens, token)) {
            tokens[token](input, config._a, config, token);
        }
    }

    var YEAR = 0;
    var MONTH = 1;
    var DATE = 2;
    var HOUR = 3;
    var MINUTE = 4;
    var SECOND = 5;
    var MILLISECOND = 6;

    function daysInMonth(year, month) {
        return new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
    }

    // FORMATTING

    addFormatToken('M', ['MM', 2], 'Mo', function () {
        return this.month() + 1;
    });

    addFormatToken('MMM', 0, 0, function (format) {
        return this.localeData().monthsShort(this, format);
    });

    addFormatToken('MMMM', 0, 0, function (format) {
        return this.localeData().months(this, format);
    });

    // ALIASES

    addUnitAlias('month', 'M');

    // PARSING

    addRegexToken('M',    match1to2);
    addRegexToken('MM',   match1to2, match2);
    addRegexToken('MMM',  matchWord);
    addRegexToken('MMMM', matchWord);

    addParseToken(['M', 'MM'], function (input, array) {
        array[MONTH] = toInt(input) - 1;
    });

    addParseToken(['MMM', 'MMMM'], function (input, array, config, token) {
        var month = config._locale.monthsParse(input, token, config._strict);
        // if we didn't find a month name, mark the date as invalid.
        if (month != null) {
            array[MONTH] = month;
        } else {
            getParsingFlags(config).invalidMonth = input;
        }
    });

    // LOCALES

    var defaultLocaleMonths = 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_');
    function localeMonths (m) {
        return this._months[m.month()];
    }

    var defaultLocaleMonthsShort = 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_');
    function localeMonthsShort (m) {
        return this._monthsShort[m.month()];
    }

    function localeMonthsParse (monthName, format, strict) {
        var i, mom, regex;

        if (!this._monthsParse) {
            this._monthsParse = [];
            this._longMonthsParse = [];
            this._shortMonthsParse = [];
        }

        for (i = 0; i < 12; i++) {
            // make the regex if we don't have it already
            mom = create_utc__createUTC([2000, i]);
            if (strict && !this._longMonthsParse[i]) {
                this._longMonthsParse[i] = new RegExp('^' + this.months(mom, '').replace('.', '') + '$', 'i');
                this._shortMonthsParse[i] = new RegExp('^' + this.monthsShort(mom, '').replace('.', '') + '$', 'i');
            }
            if (!strict && !this._monthsParse[i]) {
                regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
                this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
            }
            // test the regex
            if (strict && format === 'MMMM' && this._longMonthsParse[i].test(monthName)) {
                return i;
            } else if (strict && format === 'MMM' && this._shortMonthsParse[i].test(monthName)) {
                return i;
            } else if (!strict && this._monthsParse[i].test(monthName)) {
                return i;
            }
        }
    }

    // MOMENTS

    function setMonth (mom, value) {
        var dayOfMonth;

        // TODO: Move this out of here!
        if (typeof value === 'string') {
            value = mom.localeData().monthsParse(value);
            // TODO: Another silent failure?
            if (typeof value !== 'number') {
                return mom;
            }
        }

        dayOfMonth = Math.min(mom.date(), daysInMonth(mom.year(), value));
        mom._d['set' + (mom._isUTC ? 'UTC' : '') + 'Month'](value, dayOfMonth);
        return mom;
    }

    function getSetMonth (value) {
        if (value != null) {
            setMonth(this, value);
            utils_hooks__hooks.updateOffset(this, true);
            return this;
        } else {
            return get_set__get(this, 'Month');
        }
    }

    function getDaysInMonth () {
        return daysInMonth(this.year(), this.month());
    }

    function checkOverflow (m) {
        var overflow;
        var a = m._a;

        if (a && getParsingFlags(m).overflow === -2) {
            overflow =
                a[MONTH]       < 0 || a[MONTH]       > 11  ? MONTH :
                a[DATE]        < 1 || a[DATE]        > daysInMonth(a[YEAR], a[MONTH]) ? DATE :
                a[HOUR]        < 0 || a[HOUR]        > 24 || (a[HOUR] === 24 && (a[MINUTE] !== 0 || a[SECOND] !== 0 || a[MILLISECOND] !== 0)) ? HOUR :
                a[MINUTE]      < 0 || a[MINUTE]      > 59  ? MINUTE :
                a[SECOND]      < 0 || a[SECOND]      > 59  ? SECOND :
                a[MILLISECOND] < 0 || a[MILLISECOND] > 999 ? MILLISECOND :
                -1;

            if (getParsingFlags(m)._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
                overflow = DATE;
            }

            getParsingFlags(m).overflow = overflow;
        }

        return m;
    }

    function warn(msg) {
        if (utils_hooks__hooks.suppressDeprecationWarnings === false && typeof console !== 'undefined' && console.warn) {
            console.warn('Deprecation warning: ' + msg);
        }
    }

    function deprecate(msg, fn) {
        var firstTime = true;

        return extend(function () {
            if (firstTime) {
                warn(msg + '\n' + (new Error()).stack);
                firstTime = false;
            }
            return fn.apply(this, arguments);
        }, fn);
    }

    var deprecations = {};

    function deprecateSimple(name, msg) {
        if (!deprecations[name]) {
            warn(msg);
            deprecations[name] = true;
        }
    }

    utils_hooks__hooks.suppressDeprecationWarnings = false;

    var from_string__isoRegex = /^\s*(?:[+-]\d{6}|\d{4})-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d+)?)?)?)?([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/;

    var isoDates = [
        ['YYYYYY-MM-DD', /[+-]\d{6}-\d{2}-\d{2}/],
        ['YYYY-MM-DD', /\d{4}-\d{2}-\d{2}/],
        ['GGGG-[W]WW-E', /\d{4}-W\d{2}-\d/],
        ['GGGG-[W]WW', /\d{4}-W\d{2}/],
        ['YYYY-DDD', /\d{4}-\d{3}/]
    ];

    // iso time formats and regexes
    var isoTimes = [
        ['HH:mm:ss.SSSS', /(T| )\d\d:\d\d:\d\d\.\d+/],
        ['HH:mm:ss', /(T| )\d\d:\d\d:\d\d/],
        ['HH:mm', /(T| )\d\d:\d\d/],
        ['HH', /(T| )\d\d/]
    ];

    var aspNetJsonRegex = /^\/?Date\((\-?\d+)/i;

    // date from iso format
    function configFromISO(config) {
        var i, l,
            string = config._i,
            match = from_string__isoRegex.exec(string);

        if (match) {
            getParsingFlags(config).iso = true;
            for (i = 0, l = isoDates.length; i < l; i++) {
                if (isoDates[i][1].exec(string)) {
                    config._f = isoDates[i][0];
                    break;
                }
            }
            for (i = 0, l = isoTimes.length; i < l; i++) {
                if (isoTimes[i][1].exec(string)) {
                    // match[6] should be 'T' or space
                    config._f += (match[6] || ' ') + isoTimes[i][0];
                    break;
                }
            }
            if (string.match(matchOffset)) {
                config._f += 'Z';
            }
            configFromStringAndFormat(config);
        } else {
            config._isValid = false;
        }
    }

    // date from iso format or fallback
    function configFromString(config) {
        var matched = aspNetJsonRegex.exec(config._i);

        if (matched !== null) {
            config._d = new Date(+matched[1]);
            return;
        }

        configFromISO(config);
        if (config._isValid === false) {
            delete config._isValid;
            utils_hooks__hooks.createFromInputFallback(config);
        }
    }

    utils_hooks__hooks.createFromInputFallback = deprecate(
        'moment construction falls back to js Date. This is ' +
        'discouraged and will be removed in upcoming major ' +
        'release. Please refer to ' +
        'https://github.com/moment/moment/issues/1407 for more info.',
        function (config) {
            config._d = new Date(config._i + (config._useUTC ? ' UTC' : ''));
        }
    );

    function createDate (y, m, d, h, M, s, ms) {
        //can't just apply() to create a date:
        //http://stackoverflow.com/questions/181348/instantiating-a-javascript-object-by-calling-prototype-constructor-apply
        var date = new Date(y, m, d, h, M, s, ms);

        //the date constructor doesn't accept years < 1970
        if (y < 1970) {
            date.setFullYear(y);
        }
        return date;
    }

    function createUTCDate (y) {
        var date = new Date(Date.UTC.apply(null, arguments));
        if (y < 1970) {
            date.setUTCFullYear(y);
        }
        return date;
    }

    addFormatToken(0, ['YY', 2], 0, function () {
        return this.year() % 100;
    });

    addFormatToken(0, ['YYYY',   4],       0, 'year');
    addFormatToken(0, ['YYYYY',  5],       0, 'year');
    addFormatToken(0, ['YYYYYY', 6, true], 0, 'year');

    // ALIASES

    addUnitAlias('year', 'y');

    // PARSING

    addRegexToken('Y',      matchSigned);
    addRegexToken('YY',     match1to2, match2);
    addRegexToken('YYYY',   match1to4, match4);
    addRegexToken('YYYYY',  match1to6, match6);
    addRegexToken('YYYYYY', match1to6, match6);

    addParseToken(['YYYYY', 'YYYYYY'], YEAR);
    addParseToken('YYYY', function (input, array) {
        array[YEAR] = input.length === 2 ? utils_hooks__hooks.parseTwoDigitYear(input) : toInt(input);
    });
    addParseToken('YY', function (input, array) {
        array[YEAR] = utils_hooks__hooks.parseTwoDigitYear(input);
    });

    // HELPERS

    function daysInYear(year) {
        return isLeapYear(year) ? 366 : 365;
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    }

    // HOOKS

    utils_hooks__hooks.parseTwoDigitYear = function (input) {
        return toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
    };

    // MOMENTS

    var getSetYear = makeGetSet('FullYear', false);

    function getIsLeapYear () {
        return isLeapYear(this.year());
    }

    addFormatToken('w', ['ww', 2], 'wo', 'week');
    addFormatToken('W', ['WW', 2], 'Wo', 'isoWeek');

    // ALIASES

    addUnitAlias('week', 'w');
    addUnitAlias('isoWeek', 'W');

    // PARSING

    addRegexToken('w',  match1to2);
    addRegexToken('ww', match1to2, match2);
    addRegexToken('W',  match1to2);
    addRegexToken('WW', match1to2, match2);

    addWeekParseToken(['w', 'ww', 'W', 'WW'], function (input, week, config, token) {
        week[token.substr(0, 1)] = toInt(input);
    });

    // HELPERS

    // firstDayOfWeek       0 = sun, 6 = sat
    //                      the day of the week that starts the week
    //                      (usually sunday or monday)
    // firstDayOfWeekOfYear 0 = sun, 6 = sat
    //                      the first week is the week that contains the first
    //                      of this day of the week
    //                      (eg. ISO weeks use thursday (4))
    function weekOfYear(mom, firstDayOfWeek, firstDayOfWeekOfYear) {
        var end = firstDayOfWeekOfYear - firstDayOfWeek,
            daysToDayOfWeek = firstDayOfWeekOfYear - mom.day(),
            adjustedMoment;


        if (daysToDayOfWeek > end) {
            daysToDayOfWeek -= 7;
        }

        if (daysToDayOfWeek < end - 7) {
            daysToDayOfWeek += 7;
        }

        adjustedMoment = local__createLocal(mom).add(daysToDayOfWeek, 'd');
        return {
            week: Math.ceil(adjustedMoment.dayOfYear() / 7),
            year: adjustedMoment.year()
        };
    }

    // LOCALES

    function localeWeek (mom) {
        return weekOfYear(mom, this._week.dow, this._week.doy).week;
    }

    var defaultLocaleWeek = {
        dow : 0, // Sunday is the first day of the week.
        doy : 6  // The week that contains Jan 1st is the first week of the year.
    };

    function localeFirstDayOfWeek () {
        return this._week.dow;
    }

    function localeFirstDayOfYear () {
        return this._week.doy;
    }

    // MOMENTS

    function getSetWeek (input) {
        var week = this.localeData().week(this);
        return input == null ? week : this.add((input - week) * 7, 'd');
    }

    function getSetISOWeek (input) {
        var week = weekOfYear(this, 1, 4).week;
        return input == null ? week : this.add((input - week) * 7, 'd');
    }

    addFormatToken('DDD', ['DDDD', 3], 'DDDo', 'dayOfYear');

    // ALIASES

    addUnitAlias('dayOfYear', 'DDD');

    // PARSING

    addRegexToken('DDD',  match1to3);
    addRegexToken('DDDD', match3);
    addParseToken(['DDD', 'DDDD'], function (input, array, config) {
        config._dayOfYear = toInt(input);
    });

    // HELPERS

    //http://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
    function dayOfYearFromWeeks(year, week, weekday, firstDayOfWeekOfYear, firstDayOfWeek) {
        var week1Jan = 6 + firstDayOfWeek - firstDayOfWeekOfYear, janX = createUTCDate(year, 0, 1 + week1Jan), d = janX.getUTCDay(), dayOfYear;
        if (d < firstDayOfWeek) {
            d += 7;
        }

        weekday = weekday != null ? 1 * weekday : firstDayOfWeek;

        dayOfYear = 1 + week1Jan + 7 * (week - 1) - d + weekday;

        return {
            year: dayOfYear > 0 ? year : year - 1,
            dayOfYear: dayOfYear > 0 ?  dayOfYear : daysInYear(year - 1) + dayOfYear
        };
    }

    // MOMENTS

    function getSetDayOfYear (input) {
        var dayOfYear = Math.round((this.clone().startOf('day') - this.clone().startOf('year')) / 864e5) + 1;
        return input == null ? dayOfYear : this.add((input - dayOfYear), 'd');
    }

    // Pick the first defined of two or three arguments.
    function defaults(a, b, c) {
        if (a != null) {
            return a;
        }
        if (b != null) {
            return b;
        }
        return c;
    }

    function currentDateArray(config) {
        var now = new Date();
        if (config._useUTC) {
            return [now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate()];
        }
        return [now.getFullYear(), now.getMonth(), now.getDate()];
    }

    // convert an array to a date.
    // the array should mirror the parameters below
    // note: all values past the year are optional and will default to the lowest possible value.
    // [year, month, day , hour, minute, second, millisecond]
    function configFromArray (config) {
        var i, date, input = [], currentDate, yearToUse;

        if (config._d) {
            return;
        }

        currentDate = currentDateArray(config);

        //compute day of the year from weeks and weekdays
        if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
            dayOfYearFromWeekInfo(config);
        }

        //if the day of the year is set, figure out what it is
        if (config._dayOfYear) {
            yearToUse = defaults(config._a[YEAR], currentDate[YEAR]);

            if (config._dayOfYear > daysInYear(yearToUse)) {
                getParsingFlags(config)._overflowDayOfYear = true;
            }

            date = createUTCDate(yearToUse, 0, config._dayOfYear);
            config._a[MONTH] = date.getUTCMonth();
            config._a[DATE] = date.getUTCDate();
        }

        // Default to current date.
        // * if no year, month, day of month are given, default to today
        // * if day of month is given, default month and year
        // * if month is given, default only year
        // * if year is given, don't default anything
        for (i = 0; i < 3 && config._a[i] == null; ++i) {
            config._a[i] = input[i] = currentDate[i];
        }

        // Zero out whatever was not defaulted, including time
        for (; i < 7; i++) {
            config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
        }

        // Check for 24:00:00.000
        if (config._a[HOUR] === 24 &&
                config._a[MINUTE] === 0 &&
                config._a[SECOND] === 0 &&
                config._a[MILLISECOND] === 0) {
            config._nextDay = true;
            config._a[HOUR] = 0;
        }

        config._d = (config._useUTC ? createUTCDate : createDate).apply(null, input);
        // Apply timezone offset from input. The actual utcOffset can be changed
        // with parseZone.
        if (config._tzm != null) {
            config._d.setUTCMinutes(config._d.getUTCMinutes() - config._tzm);
        }

        if (config._nextDay) {
            config._a[HOUR] = 24;
        }
    }

    function dayOfYearFromWeekInfo(config) {
        var w, weekYear, week, weekday, dow, doy, temp;

        w = config._w;
        if (w.GG != null || w.W != null || w.E != null) {
            dow = 1;
            doy = 4;

            // TODO: We need to take the current isoWeekYear, but that depends on
            // how we interpret now (local, utc, fixed offset). So create
            // a now version of current config (take local/utc/offset flags, and
            // create now).
            weekYear = defaults(w.GG, config._a[YEAR], weekOfYear(local__createLocal(), 1, 4).year);
            week = defaults(w.W, 1);
            weekday = defaults(w.E, 1);
        } else {
            dow = config._locale._week.dow;
            doy = config._locale._week.doy;

            weekYear = defaults(w.gg, config._a[YEAR], weekOfYear(local__createLocal(), dow, doy).year);
            week = defaults(w.w, 1);

            if (w.d != null) {
                // weekday -- low day numbers are considered next week
                weekday = w.d;
                if (weekday < dow) {
                    ++week;
                }
            } else if (w.e != null) {
                // local weekday -- counting starts from begining of week
                weekday = w.e + dow;
            } else {
                // default to begining of week
                weekday = dow;
            }
        }
        temp = dayOfYearFromWeeks(weekYear, week, weekday, doy, dow);

        config._a[YEAR] = temp.year;
        config._dayOfYear = temp.dayOfYear;
    }

    utils_hooks__hooks.ISO_8601 = function () {};

    // date from string and format string
    function configFromStringAndFormat(config) {
        // TODO: Move this to another part of the creation flow to prevent circular deps
        if (config._f === utils_hooks__hooks.ISO_8601) {
            configFromISO(config);
            return;
        }

        config._a = [];
        getParsingFlags(config).empty = true;

        // This array is used to make a Date, either with `new Date` or `Date.UTC`
        var string = '' + config._i,
            i, parsedInput, tokens, token, skipped,
            stringLength = string.length,
            totalParsedInputLength = 0;

        tokens = expandFormat(config._f, config._locale).match(formattingTokens) || [];

        for (i = 0; i < tokens.length; i++) {
            token = tokens[i];
            parsedInput = (string.match(getParseRegexForToken(token, config)) || [])[0];
            if (parsedInput) {
                skipped = string.substr(0, string.indexOf(parsedInput));
                if (skipped.length > 0) {
                    getParsingFlags(config).unusedInput.push(skipped);
                }
                string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
                totalParsedInputLength += parsedInput.length;
            }
            // don't parse if it's not a known token
            if (formatTokenFunctions[token]) {
                if (parsedInput) {
                    getParsingFlags(config).empty = false;
                }
                else {
                    getParsingFlags(config).unusedTokens.push(token);
                }
                addTimeToArrayFromToken(token, parsedInput, config);
            }
            else if (config._strict && !parsedInput) {
                getParsingFlags(config).unusedTokens.push(token);
            }
        }

        // add remaining unparsed input length to the string
        getParsingFlags(config).charsLeftOver = stringLength - totalParsedInputLength;
        if (string.length > 0) {
            getParsingFlags(config).unusedInput.push(string);
        }

        // clear _12h flag if hour is <= 12
        if (getParsingFlags(config).bigHour === true &&
                config._a[HOUR] <= 12 &&
                config._a[HOUR] > 0) {
            getParsingFlags(config).bigHour = undefined;
        }
        // handle meridiem
        config._a[HOUR] = meridiemFixWrap(config._locale, config._a[HOUR], config._meridiem);

        configFromArray(config);
        checkOverflow(config);
    }


    function meridiemFixWrap (locale, hour, meridiem) {
        var isPm;

        if (meridiem == null) {
            // nothing to do
            return hour;
        }
        if (locale.meridiemHour != null) {
            return locale.meridiemHour(hour, meridiem);
        } else if (locale.isPM != null) {
            // Fallback
            isPm = locale.isPM(meridiem);
            if (isPm && hour < 12) {
                hour += 12;
            }
            if (!isPm && hour === 12) {
                hour = 0;
            }
            return hour;
        } else {
            // this is not supposed to happen
            return hour;
        }
    }

    function configFromStringAndArray(config) {
        var tempConfig,
            bestMoment,

            scoreToBeat,
            i,
            currentScore;

        if (config._f.length === 0) {
            getParsingFlags(config).invalidFormat = true;
            config._d = new Date(NaN);
            return;
        }

        for (i = 0; i < config._f.length; i++) {
            currentScore = 0;
            tempConfig = copyConfig({}, config);
            if (config._useUTC != null) {
                tempConfig._useUTC = config._useUTC;
            }
            tempConfig._f = config._f[i];
            configFromStringAndFormat(tempConfig);

            if (!valid__isValid(tempConfig)) {
                continue;
            }

            // if there is any input that was not parsed add a penalty for that format
            currentScore += getParsingFlags(tempConfig).charsLeftOver;

            //or tokens
            currentScore += getParsingFlags(tempConfig).unusedTokens.length * 10;

            getParsingFlags(tempConfig).score = currentScore;

            if (scoreToBeat == null || currentScore < scoreToBeat) {
                scoreToBeat = currentScore;
                bestMoment = tempConfig;
            }
        }

        extend(config, bestMoment || tempConfig);
    }

    function configFromObject(config) {
        if (config._d) {
            return;
        }

        var i = normalizeObjectUnits(config._i);
        config._a = [i.year, i.month, i.day || i.date, i.hour, i.minute, i.second, i.millisecond];

        configFromArray(config);
    }

    function createFromConfig (config) {
        var res = new Moment(checkOverflow(prepareConfig(config)));
        if (res._nextDay) {
            // Adding is smart enough around DST
            res.add(1, 'd');
            res._nextDay = undefined;
        }

        return res;
    }

    function prepareConfig (config) {
        var input = config._i,
            format = config._f;

        config._locale = config._locale || locale_locales__getLocale(config._l);

        if (input === null || (format === undefined && input === '')) {
            return valid__createInvalid({nullInput: true});
        }

        if (typeof input === 'string') {
            config._i = input = config._locale.preparse(input);
        }

        if (isMoment(input)) {
            return new Moment(checkOverflow(input));
        } else if (isArray(format)) {
            configFromStringAndArray(config);
        } else if (format) {
            configFromStringAndFormat(config);
        } else if (isDate(input)) {
            config._d = input;
        } else {
            configFromInput(config);
        }

        return config;
    }

    function configFromInput(config) {
        var input = config._i;
        if (input === undefined) {
            config._d = new Date();
        } else if (isDate(input)) {
            config._d = new Date(+input);
        } else if (typeof input === 'string') {
            configFromString(config);
        } else if (isArray(input)) {
            config._a = map(input.slice(0), function (obj) {
                return parseInt(obj, 10);
            });
            configFromArray(config);
        } else if (typeof(input) === 'object') {
            configFromObject(config);
        } else if (typeof(input) === 'number') {
            // from milliseconds
            config._d = new Date(input);
        } else {
            utils_hooks__hooks.createFromInputFallback(config);
        }
    }

    function createLocalOrUTC (input, format, locale, strict, isUTC) {
        var c = {};

        if (typeof(locale) === 'boolean') {
            strict = locale;
            locale = undefined;
        }
        // object construction must be done this way.
        // https://github.com/moment/moment/issues/1423
        c._isAMomentObject = true;
        c._useUTC = c._isUTC = isUTC;
        c._l = locale;
        c._i = input;
        c._f = format;
        c._strict = strict;

        return createFromConfig(c);
    }

    function local__createLocal (input, format, locale, strict) {
        return createLocalOrUTC(input, format, locale, strict, false);
    }

    var prototypeMin = deprecate(
         'moment().min is deprecated, use moment.min instead. https://github.com/moment/moment/issues/1548',
         function () {
             var other = local__createLocal.apply(null, arguments);
             return other < this ? this : other;
         }
     );

    var prototypeMax = deprecate(
        'moment().max is deprecated, use moment.max instead. https://github.com/moment/moment/issues/1548',
        function () {
            var other = local__createLocal.apply(null, arguments);
            return other > this ? this : other;
        }
    );

    // Pick a moment m from moments so that m[fn](other) is true for all
    // other. This relies on the function fn to be transitive.
    //
    // moments should either be an array of moment objects or an array, whose
    // first element is an array of moment objects.
    function pickBy(fn, moments) {
        var res, i;
        if (moments.length === 1 && isArray(moments[0])) {
            moments = moments[0];
        }
        if (!moments.length) {
            return local__createLocal();
        }
        res = moments[0];
        for (i = 1; i < moments.length; ++i) {
            if (!moments[i].isValid() || moments[i][fn](res)) {
                res = moments[i];
            }
        }
        return res;
    }

    // TODO: Use [].sort instead?
    function min () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isBefore', args);
    }

    function max () {
        var args = [].slice.call(arguments, 0);

        return pickBy('isAfter', args);
    }

    function Duration (duration) {
        var normalizedInput = normalizeObjectUnits(duration),
            years = normalizedInput.year || 0,
            quarters = normalizedInput.quarter || 0,
            months = normalizedInput.month || 0,
            weeks = normalizedInput.week || 0,
            days = normalizedInput.day || 0,
            hours = normalizedInput.hour || 0,
            minutes = normalizedInput.minute || 0,
            seconds = normalizedInput.second || 0,
            milliseconds = normalizedInput.millisecond || 0;

        // representation for dateAddRemove
        this._milliseconds = +milliseconds +
            seconds * 1e3 + // 1000
            minutes * 6e4 + // 1000 * 60
            hours * 36e5; // 1000 * 60 * 60
        // Because of dateAddRemove treats 24 hours as different from a
        // day when working around DST, we need to store them separately
        this._days = +days +
            weeks * 7;
        // It is impossible translate months into days without knowing
        // which months you are are talking about, so we have to store
        // it separately.
        this._months = +months +
            quarters * 3 +
            years * 12;

        this._data = {};

        this._locale = locale_locales__getLocale();

        this._bubble();
    }

    function isDuration (obj) {
        return obj instanceof Duration;
    }

    function offset (token, separator) {
        addFormatToken(token, 0, 0, function () {
            var offset = this.utcOffset();
            var sign = '+';
            if (offset < 0) {
                offset = -offset;
                sign = '-';
            }
            return sign + zeroFill(~~(offset / 60), 2) + separator + zeroFill(~~(offset) % 60, 2);
        });
    }

    offset('Z', ':');
    offset('ZZ', '');

    // PARSING

    addRegexToken('Z',  matchOffset);
    addRegexToken('ZZ', matchOffset);
    addParseToken(['Z', 'ZZ'], function (input, array, config) {
        config._useUTC = true;
        config._tzm = offsetFromString(input);
    });

    // HELPERS

    // timezone chunker
    // '+10:00' > ['10',  '00']
    // '-1530'  > ['-15', '30']
    var chunkOffset = /([\+\-]|\d\d)/gi;

    function offsetFromString(string) {
        var matches = ((string || '').match(matchOffset) || []);
        var chunk   = matches[matches.length - 1] || [];
        var parts   = (chunk + '').match(chunkOffset) || ['-', 0, 0];
        var minutes = +(parts[1] * 60) + toInt(parts[2]);

        return parts[0] === '+' ? minutes : -minutes;
    }

    // Return a moment from input, that is local/utc/zone equivalent to model.
    function cloneWithOffset(input, model) {
        var res, diff;
        if (model._isUTC) {
            res = model.clone();
            diff = (isMoment(input) || isDate(input) ? +input : +local__createLocal(input)) - (+res);
            // Use low-level api, because this fn is low-level api.
            res._d.setTime(+res._d + diff);
            utils_hooks__hooks.updateOffset(res, false);
            return res;
        } else {
            return local__createLocal(input).local();
        }
    }

    function getDateOffset (m) {
        // On Firefox.24 Date#getTimezoneOffset returns a floating point.
        // https://github.com/moment/moment/pull/1871
        return -Math.round(m._d.getTimezoneOffset() / 15) * 15;
    }

    // HOOKS

    // This function will be called whenever a moment is mutated.
    // It is intended to keep the offset in sync with the timezone.
    utils_hooks__hooks.updateOffset = function () {};

    // MOMENTS

    // keepLocalTime = true means only change the timezone, without
    // affecting the local hour. So 5:31:26 +0300 --[utcOffset(2, true)]-->
    // 5:31:26 +0200 It is possible that 5:31:26 doesn't exist with offset
    // +0200, so we adjust the time as needed, to be valid.
    //
    // Keeping the time actually adds/subtracts (one hour)
    // from the actual represented time. That is why we call updateOffset
    // a second time. In case it wants us to change the offset again
    // _changeInProgress == true case, then we have to adjust, because
    // there is no such time in the given timezone.
    function getSetOffset (input, keepLocalTime) {
        var offset = this._offset || 0,
            localAdjust;
        if (input != null) {
            if (typeof input === 'string') {
                input = offsetFromString(input);
            }
            if (Math.abs(input) < 16) {
                input = input * 60;
            }
            if (!this._isUTC && keepLocalTime) {
                localAdjust = getDateOffset(this);
            }
            this._offset = input;
            this._isUTC = true;
            if (localAdjust != null) {
                this.add(localAdjust, 'm');
            }
            if (offset !== input) {
                if (!keepLocalTime || this._changeInProgress) {
                    add_subtract__addSubtract(this, create__createDuration(input - offset, 'm'), 1, false);
                } else if (!this._changeInProgress) {
                    this._changeInProgress = true;
                    utils_hooks__hooks.updateOffset(this, true);
                    this._changeInProgress = null;
                }
            }
            return this;
        } else {
            return this._isUTC ? offset : getDateOffset(this);
        }
    }

    function getSetZone (input, keepLocalTime) {
        if (input != null) {
            if (typeof input !== 'string') {
                input = -input;
            }

            this.utcOffset(input, keepLocalTime);

            return this;
        } else {
            return -this.utcOffset();
        }
    }

    function setOffsetToUTC (keepLocalTime) {
        return this.utcOffset(0, keepLocalTime);
    }

    function setOffsetToLocal (keepLocalTime) {
        if (this._isUTC) {
            this.utcOffset(0, keepLocalTime);
            this._isUTC = false;

            if (keepLocalTime) {
                this.subtract(getDateOffset(this), 'm');
            }
        }
        return this;
    }

    function setOffsetToParsedOffset () {
        if (this._tzm) {
            this.utcOffset(this._tzm);
        } else if (typeof this._i === 'string') {
            this.utcOffset(offsetFromString(this._i));
        }
        return this;
    }

    function hasAlignedHourOffset (input) {
        input = input ? local__createLocal(input).utcOffset() : 0;

        return (this.utcOffset() - input) % 60 === 0;
    }

    function isDaylightSavingTime () {
        return (
            this.utcOffset() > this.clone().month(0).utcOffset() ||
            this.utcOffset() > this.clone().month(5).utcOffset()
        );
    }

    function isDaylightSavingTimeShifted () {
        if (typeof this._isDSTShifted !== 'undefined') {
            return this._isDSTShifted;
        }

        var c = {};

        copyConfig(c, this);
        c = prepareConfig(c);

        if (c._a) {
            var other = c._isUTC ? create_utc__createUTC(c._a) : local__createLocal(c._a);
            this._isDSTShifted = this.isValid() &&
                compareArrays(c._a, other.toArray()) > 0;
        } else {
            this._isDSTShifted = false;
        }

        return this._isDSTShifted;
    }

    function isLocal () {
        return !this._isUTC;
    }

    function isUtcOffset () {
        return this._isUTC;
    }

    function isUtc () {
        return this._isUTC && this._offset === 0;
    }

    var aspNetRegex = /(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/;

    // from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
    // somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
    var create__isoRegex = /^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/;

    function create__createDuration (input, key) {
        var duration = input,
            // matching against regexp is expensive, do it on demand
            match = null,
            sign,
            ret,
            diffRes;

        if (isDuration(input)) {
            duration = {
                ms : input._milliseconds,
                d  : input._days,
                M  : input._months
            };
        } else if (typeof input === 'number') {
            duration = {};
            if (key) {
                duration[key] = input;
            } else {
                duration.milliseconds = input;
            }
        } else if (!!(match = aspNetRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            duration = {
                y  : 0,
                d  : toInt(match[DATE])        * sign,
                h  : toInt(match[HOUR])        * sign,
                m  : toInt(match[MINUTE])      * sign,
                s  : toInt(match[SECOND])      * sign,
                ms : toInt(match[MILLISECOND]) * sign
            };
        } else if (!!(match = create__isoRegex.exec(input))) {
            sign = (match[1] === '-') ? -1 : 1;
            duration = {
                y : parseIso(match[2], sign),
                M : parseIso(match[3], sign),
                d : parseIso(match[4], sign),
                h : parseIso(match[5], sign),
                m : parseIso(match[6], sign),
                s : parseIso(match[7], sign),
                w : parseIso(match[8], sign)
            };
        } else if (duration == null) {// checks for null or undefined
            duration = {};
        } else if (typeof duration === 'object' && ('from' in duration || 'to' in duration)) {
            diffRes = momentsDifference(local__createLocal(duration.from), local__createLocal(duration.to));

            duration = {};
            duration.ms = diffRes.milliseconds;
            duration.M = diffRes.months;
        }

        ret = new Duration(duration);

        if (isDuration(input) && hasOwnProp(input, '_locale')) {
            ret._locale = input._locale;
        }

        return ret;
    }

    create__createDuration.fn = Duration.prototype;

    function parseIso (inp, sign) {
        // We'd normally use ~~inp for this, but unfortunately it also
        // converts floats to ints.
        // inp may be undefined, so careful calling replace on it.
        var res = inp && parseFloat(inp.replace(',', '.'));
        // apply sign while we're at it
        return (isNaN(res) ? 0 : res) * sign;
    }

    function positiveMomentsDifference(base, other) {
        var res = {milliseconds: 0, months: 0};

        res.months = other.month() - base.month() +
            (other.year() - base.year()) * 12;
        if (base.clone().add(res.months, 'M').isAfter(other)) {
            --res.months;
        }

        res.milliseconds = +other - +(base.clone().add(res.months, 'M'));

        return res;
    }

    function momentsDifference(base, other) {
        var res;
        other = cloneWithOffset(other, base);
        if (base.isBefore(other)) {
            res = positiveMomentsDifference(base, other);
        } else {
            res = positiveMomentsDifference(other, base);
            res.milliseconds = -res.milliseconds;
            res.months = -res.months;
        }

        return res;
    }

    function createAdder(direction, name) {
        return function (val, period) {
            var dur, tmp;
            //invert the arguments, but complain about it
            if (period !== null && !isNaN(+period)) {
                deprecateSimple(name, 'moment().' + name  + '(period, number) is deprecated. Please use moment().' + name + '(number, period).');
                tmp = val; val = period; period = tmp;
            }

            val = typeof val === 'string' ? +val : val;
            dur = create__createDuration(val, period);
            add_subtract__addSubtract(this, dur, direction);
            return this;
        };
    }

    function add_subtract__addSubtract (mom, duration, isAdding, updateOffset) {
        var milliseconds = duration._milliseconds,
            days = duration._days,
            months = duration._months;
        updateOffset = updateOffset == null ? true : updateOffset;

        if (milliseconds) {
            mom._d.setTime(+mom._d + milliseconds * isAdding);
        }
        if (days) {
            get_set__set(mom, 'Date', get_set__get(mom, 'Date') + days * isAdding);
        }
        if (months) {
            setMonth(mom, get_set__get(mom, 'Month') + months * isAdding);
        }
        if (updateOffset) {
            utils_hooks__hooks.updateOffset(mom, days || months);
        }
    }

    var add_subtract__add      = createAdder(1, 'add');
    var add_subtract__subtract = createAdder(-1, 'subtract');

    function moment_calendar__calendar (time, formats) {
        // We want to compare the start of today, vs this.
        // Getting start-of-today depends on whether we're local/utc/offset or not.
        var now = time || local__createLocal(),
            sod = cloneWithOffset(now, this).startOf('day'),
            diff = this.diff(sod, 'days', true),
            format = diff < -6 ? 'sameElse' :
                diff < -1 ? 'lastWeek' :
                diff < 0 ? 'lastDay' :
                diff < 1 ? 'sameDay' :
                diff < 2 ? 'nextDay' :
                diff < 7 ? 'nextWeek' : 'sameElse';
        return this.format(formats && formats[format] || this.localeData().calendar(format, this, local__createLocal(now)));
    }

    function clone () {
        return new Moment(this);
    }

    function isAfter (input, units) {
        var inputMs;
        units = normalizeUnits(typeof units !== 'undefined' ? units : 'millisecond');
        if (units === 'millisecond') {
            input = isMoment(input) ? input : local__createLocal(input);
            return +this > +input;
        } else {
            inputMs = isMoment(input) ? +input : +local__createLocal(input);
            return inputMs < +this.clone().startOf(units);
        }
    }

    function isBefore (input, units) {
        var inputMs;
        units = normalizeUnits(typeof units !== 'undefined' ? units : 'millisecond');
        if (units === 'millisecond') {
            input = isMoment(input) ? input : local__createLocal(input);
            return +this < +input;
        } else {
            inputMs = isMoment(input) ? +input : +local__createLocal(input);
            return +this.clone().endOf(units) < inputMs;
        }
    }

    function isBetween (from, to, units) {
        return this.isAfter(from, units) && this.isBefore(to, units);
    }

    function isSame (input, units) {
        var inputMs;
        units = normalizeUnits(units || 'millisecond');
        if (units === 'millisecond') {
            input = isMoment(input) ? input : local__createLocal(input);
            return +this === +input;
        } else {
            inputMs = +local__createLocal(input);
            return +(this.clone().startOf(units)) <= inputMs && inputMs <= +(this.clone().endOf(units));
        }
    }

    function diff (input, units, asFloat) {
        var that = cloneWithOffset(input, this),
            zoneDelta = (that.utcOffset() - this.utcOffset()) * 6e4,
            delta, output;

        units = normalizeUnits(units);

        if (units === 'year' || units === 'month' || units === 'quarter') {
            output = monthDiff(this, that);
            if (units === 'quarter') {
                output = output / 3;
            } else if (units === 'year') {
                output = output / 12;
            }
        } else {
            delta = this - that;
            output = units === 'second' ? delta / 1e3 : // 1000
                units === 'minute' ? delta / 6e4 : // 1000 * 60
                units === 'hour' ? delta / 36e5 : // 1000 * 60 * 60
                units === 'day' ? (delta - zoneDelta) / 864e5 : // 1000 * 60 * 60 * 24, negate dst
                units === 'week' ? (delta - zoneDelta) / 6048e5 : // 1000 * 60 * 60 * 24 * 7, negate dst
                delta;
        }
        return asFloat ? output : absFloor(output);
    }

    function monthDiff (a, b) {
        // difference in months
        var wholeMonthDiff = ((b.year() - a.year()) * 12) + (b.month() - a.month()),
            // b is in (anchor - 1 month, anchor + 1 month)
            anchor = a.clone().add(wholeMonthDiff, 'months'),
            anchor2, adjust;

        if (b - anchor < 0) {
            anchor2 = a.clone().add(wholeMonthDiff - 1, 'months');
            // linear across the month
            adjust = (b - anchor) / (anchor - anchor2);
        } else {
            anchor2 = a.clone().add(wholeMonthDiff + 1, 'months');
            // linear across the month
            adjust = (b - anchor) / (anchor2 - anchor);
        }

        return -(wholeMonthDiff + adjust);
    }

    utils_hooks__hooks.defaultFormat = 'YYYY-MM-DDTHH:mm:ssZ';

    function toString () {
        return this.clone().locale('en').format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ');
    }

    function moment_format__toISOString () {
        var m = this.clone().utc();
        if (0 < m.year() && m.year() <= 9999) {
            if ('function' === typeof Date.prototype.toISOString) {
                // native implementation is ~50x faster, use it when we can
                return this.toDate().toISOString();
            } else {
                return formatMoment(m, 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
            }
        } else {
            return formatMoment(m, 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
        }
    }

    function format (inputString) {
        var output = formatMoment(this, inputString || utils_hooks__hooks.defaultFormat);
        return this.localeData().postformat(output);
    }

    function from (time, withoutSuffix) {
        if (!this.isValid()) {
            return this.localeData().invalidDate();
        }
        return create__createDuration({to: this, from: time}).locale(this.locale()).humanize(!withoutSuffix);
    }

    function fromNow (withoutSuffix) {
        return this.from(local__createLocal(), withoutSuffix);
    }

    function to (time, withoutSuffix) {
        if (!this.isValid()) {
            return this.localeData().invalidDate();
        }
        return create__createDuration({from: this, to: time}).locale(this.locale()).humanize(!withoutSuffix);
    }

    function toNow (withoutSuffix) {
        return this.to(local__createLocal(), withoutSuffix);
    }

    function locale (key) {
        var newLocaleData;

        if (key === undefined) {
            return this._locale._abbr;
        } else {
            newLocaleData = locale_locales__getLocale(key);
            if (newLocaleData != null) {
                this._locale = newLocaleData;
            }
            return this;
        }
    }

    var lang = deprecate(
        'moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.',
        function (key) {
            if (key === undefined) {
                return this.localeData();
            } else {
                return this.locale(key);
            }
        }
    );

    function localeData () {
        return this._locale;
    }

    function startOf (units) {
        units = normalizeUnits(units);
        // the following switch intentionally omits break keywords
        // to utilize falling through the cases.
        switch (units) {
        case 'year':
            this.month(0);
            /* falls through */
        case 'quarter':
        case 'month':
            this.date(1);
            /* falls through */
        case 'week':
        case 'isoWeek':
        case 'day':
            this.hours(0);
            /* falls through */
        case 'hour':
            this.minutes(0);
            /* falls through */
        case 'minute':
            this.seconds(0);
            /* falls through */
        case 'second':
            this.milliseconds(0);
        }

        // weeks are a special case
        if (units === 'week') {
            this.weekday(0);
        }
        if (units === 'isoWeek') {
            this.isoWeekday(1);
        }

        // quarters are also special
        if (units === 'quarter') {
            this.month(Math.floor(this.month() / 3) * 3);
        }

        return this;
    }

    function endOf (units) {
        units = normalizeUnits(units);
        if (units === undefined || units === 'millisecond') {
            return this;
        }
        return this.startOf(units).add(1, (units === 'isoWeek' ? 'week' : units)).subtract(1, 'ms');
    }

    function to_type__valueOf () {
        return +this._d - ((this._offset || 0) * 60000);
    }

    function unix () {
        return Math.floor(+this / 1000);
    }

    function toDate () {
        return this._offset ? new Date(+this) : this._d;
    }

    function toArray () {
        var m = this;
        return [m.year(), m.month(), m.date(), m.hour(), m.minute(), m.second(), m.millisecond()];
    }

    function toObject () {
        var m = this;
        return {
            years: m.year(),
            months: m.month(),
            date: m.date(),
            hours: m.hours(),
            minutes: m.minutes(),
            seconds: m.seconds(),
            milliseconds: m.milliseconds()
        };
    }

    function moment_valid__isValid () {
        return valid__isValid(this);
    }

    function parsingFlags () {
        return extend({}, getParsingFlags(this));
    }

    function invalidAt () {
        return getParsingFlags(this).overflow;
    }

    addFormatToken(0, ['gg', 2], 0, function () {
        return this.weekYear() % 100;
    });

    addFormatToken(0, ['GG', 2], 0, function () {
        return this.isoWeekYear() % 100;
    });

    function addWeekYearFormatToken (token, getter) {
        addFormatToken(0, [token, token.length], 0, getter);
    }

    addWeekYearFormatToken('gggg',     'weekYear');
    addWeekYearFormatToken('ggggg',    'weekYear');
    addWeekYearFormatToken('GGGG',  'isoWeekYear');
    addWeekYearFormatToken('GGGGG', 'isoWeekYear');

    // ALIASES

    addUnitAlias('weekYear', 'gg');
    addUnitAlias('isoWeekYear', 'GG');

    // PARSING

    addRegexToken('G',      matchSigned);
    addRegexToken('g',      matchSigned);
    addRegexToken('GG',     match1to2, match2);
    addRegexToken('gg',     match1to2, match2);
    addRegexToken('GGGG',   match1to4, match4);
    addRegexToken('gggg',   match1to4, match4);
    addRegexToken('GGGGG',  match1to6, match6);
    addRegexToken('ggggg',  match1to6, match6);

    addWeekParseToken(['gggg', 'ggggg', 'GGGG', 'GGGGG'], function (input, week, config, token) {
        week[token.substr(0, 2)] = toInt(input);
    });

    addWeekParseToken(['gg', 'GG'], function (input, week, config, token) {
        week[token] = utils_hooks__hooks.parseTwoDigitYear(input);
    });

    // HELPERS

    function weeksInYear(year, dow, doy) {
        return weekOfYear(local__createLocal([year, 11, 31 + dow - doy]), dow, doy).week;
    }

    // MOMENTS

    function getSetWeekYear (input) {
        var year = weekOfYear(this, this.localeData()._week.dow, this.localeData()._week.doy).year;
        return input == null ? year : this.add((input - year), 'y');
    }

    function getSetISOWeekYear (input) {
        var year = weekOfYear(this, 1, 4).year;
        return input == null ? year : this.add((input - year), 'y');
    }

    function getISOWeeksInYear () {
        return weeksInYear(this.year(), 1, 4);
    }

    function getWeeksInYear () {
        var weekInfo = this.localeData()._week;
        return weeksInYear(this.year(), weekInfo.dow, weekInfo.doy);
    }

    addFormatToken('Q', 0, 0, 'quarter');

    // ALIASES

    addUnitAlias('quarter', 'Q');

    // PARSING

    addRegexToken('Q', match1);
    addParseToken('Q', function (input, array) {
        array[MONTH] = (toInt(input) - 1) * 3;
    });

    // MOMENTS

    function getSetQuarter (input) {
        return input == null ? Math.ceil((this.month() + 1) / 3) : this.month((input - 1) * 3 + this.month() % 3);
    }

    addFormatToken('D', ['DD', 2], 'Do', 'date');

    // ALIASES

    addUnitAlias('date', 'D');

    // PARSING

    addRegexToken('D',  match1to2);
    addRegexToken('DD', match1to2, match2);
    addRegexToken('Do', function (isStrict, locale) {
        return isStrict ? locale._ordinalParse : locale._ordinalParseLenient;
    });

    addParseToken(['D', 'DD'], DATE);
    addParseToken('Do', function (input, array) {
        array[DATE] = toInt(input.match(match1to2)[0], 10);
    });

    // MOMENTS

    var getSetDayOfMonth = makeGetSet('Date', true);

    addFormatToken('d', 0, 'do', 'day');

    addFormatToken('dd', 0, 0, function (format) {
        return this.localeData().weekdaysMin(this, format);
    });

    addFormatToken('ddd', 0, 0, function (format) {
        return this.localeData().weekdaysShort(this, format);
    });

    addFormatToken('dddd', 0, 0, function (format) {
        return this.localeData().weekdays(this, format);
    });

    addFormatToken('e', 0, 0, 'weekday');
    addFormatToken('E', 0, 0, 'isoWeekday');

    // ALIASES

    addUnitAlias('day', 'd');
    addUnitAlias('weekday', 'e');
    addUnitAlias('isoWeekday', 'E');

    // PARSING

    addRegexToken('d',    match1to2);
    addRegexToken('e',    match1to2);
    addRegexToken('E',    match1to2);
    addRegexToken('dd',   matchWord);
    addRegexToken('ddd',  matchWord);
    addRegexToken('dddd', matchWord);

    addWeekParseToken(['dd', 'ddd', 'dddd'], function (input, week, config) {
        var weekday = config._locale.weekdaysParse(input);
        // if we didn't get a weekday name, mark the date as invalid
        if (weekday != null) {
            week.d = weekday;
        } else {
            getParsingFlags(config).invalidWeekday = input;
        }
    });

    addWeekParseToken(['d', 'e', 'E'], function (input, week, config, token) {
        week[token] = toInt(input);
    });

    // HELPERS

    function parseWeekday(input, locale) {
        if (typeof input !== 'string') {
            return input;
        }

        if (!isNaN(input)) {
            return parseInt(input, 10);
        }

        input = locale.weekdaysParse(input);
        if (typeof input === 'number') {
            return input;
        }

        return null;
    }

    // LOCALES

    var defaultLocaleWeekdays = 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_');
    function localeWeekdays (m) {
        return this._weekdays[m.day()];
    }

    var defaultLocaleWeekdaysShort = 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_');
    function localeWeekdaysShort (m) {
        return this._weekdaysShort[m.day()];
    }

    var defaultLocaleWeekdaysMin = 'Su_Mo_Tu_We_Th_Fr_Sa'.split('_');
    function localeWeekdaysMin (m) {
        return this._weekdaysMin[m.day()];
    }

    function localeWeekdaysParse (weekdayName) {
        var i, mom, regex;

        this._weekdaysParse = this._weekdaysParse || [];

        for (i = 0; i < 7; i++) {
            // make the regex if we don't have it already
            if (!this._weekdaysParse[i]) {
                mom = local__createLocal([2000, 1]).day(i);
                regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
                this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
            }
            // test the regex
            if (this._weekdaysParse[i].test(weekdayName)) {
                return i;
            }
        }
    }

    // MOMENTS

    function getSetDayOfWeek (input) {
        var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
        if (input != null) {
            input = parseWeekday(input, this.localeData());
            return this.add(input - day, 'd');
        } else {
            return day;
        }
    }

    function getSetLocaleDayOfWeek (input) {
        var weekday = (this.day() + 7 - this.localeData()._week.dow) % 7;
        return input == null ? weekday : this.add(input - weekday, 'd');
    }

    function getSetISODayOfWeek (input) {
        // behaves the same as moment#day except
        // as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
        // as a setter, sunday should belong to the previous week.
        return input == null ? this.day() || 7 : this.day(this.day() % 7 ? input : input - 7);
    }

    addFormatToken('H', ['HH', 2], 0, 'hour');
    addFormatToken('h', ['hh', 2], 0, function () {
        return this.hours() % 12 || 12;
    });

    function meridiem (token, lowercase) {
        addFormatToken(token, 0, 0, function () {
            return this.localeData().meridiem(this.hours(), this.minutes(), lowercase);
        });
    }

    meridiem('a', true);
    meridiem('A', false);

    // ALIASES

    addUnitAlias('hour', 'h');

    // PARSING

    function matchMeridiem (isStrict, locale) {
        return locale._meridiemParse;
    }

    addRegexToken('a',  matchMeridiem);
    addRegexToken('A',  matchMeridiem);
    addRegexToken('H',  match1to2);
    addRegexToken('h',  match1to2);
    addRegexToken('HH', match1to2, match2);
    addRegexToken('hh', match1to2, match2);

    addParseToken(['H', 'HH'], HOUR);
    addParseToken(['a', 'A'], function (input, array, config) {
        config._isPm = config._locale.isPM(input);
        config._meridiem = input;
    });
    addParseToken(['h', 'hh'], function (input, array, config) {
        array[HOUR] = toInt(input);
        getParsingFlags(config).bigHour = true;
    });

    // LOCALES

    function localeIsPM (input) {
        // IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
        // Using charAt should be more compatible.
        return ((input + '').toLowerCase().charAt(0) === 'p');
    }

    var defaultLocaleMeridiemParse = /[ap]\.?m?\.?/i;
    function localeMeridiem (hours, minutes, isLower) {
        if (hours > 11) {
            return isLower ? 'pm' : 'PM';
        } else {
            return isLower ? 'am' : 'AM';
        }
    }


    // MOMENTS

    // Setting the hour should keep the time, because the user explicitly
    // specified which hour he wants. So trying to maintain the same hour (in
    // a new timezone) makes sense. Adding/subtracting hours does not follow
    // this rule.
    var getSetHour = makeGetSet('Hours', true);

    addFormatToken('m', ['mm', 2], 0, 'minute');

    // ALIASES

    addUnitAlias('minute', 'm');

    // PARSING

    addRegexToken('m',  match1to2);
    addRegexToken('mm', match1to2, match2);
    addParseToken(['m', 'mm'], MINUTE);

    // MOMENTS

    var getSetMinute = makeGetSet('Minutes', false);

    addFormatToken('s', ['ss', 2], 0, 'second');

    // ALIASES

    addUnitAlias('second', 's');

    // PARSING

    addRegexToken('s',  match1to2);
    addRegexToken('ss', match1to2, match2);
    addParseToken(['s', 'ss'], SECOND);

    // MOMENTS

    var getSetSecond = makeGetSet('Seconds', false);

    addFormatToken('S', 0, 0, function () {
        return ~~(this.millisecond() / 100);
    });

    addFormatToken(0, ['SS', 2], 0, function () {
        return ~~(this.millisecond() / 10);
    });

    addFormatToken(0, ['SSS', 3], 0, 'millisecond');
    addFormatToken(0, ['SSSS', 4], 0, function () {
        return this.millisecond() * 10;
    });
    addFormatToken(0, ['SSSSS', 5], 0, function () {
        return this.millisecond() * 100;
    });
    addFormatToken(0, ['SSSSSS', 6], 0, function () {
        return this.millisecond() * 1000;
    });
    addFormatToken(0, ['SSSSSSS', 7], 0, function () {
        return this.millisecond() * 10000;
    });
    addFormatToken(0, ['SSSSSSSS', 8], 0, function () {
        return this.millisecond() * 100000;
    });
    addFormatToken(0, ['SSSSSSSSS', 9], 0, function () {
        return this.millisecond() * 1000000;
    });


    // ALIASES

    addUnitAlias('millisecond', 'ms');

    // PARSING

    addRegexToken('S',    match1to3, match1);
    addRegexToken('SS',   match1to3, match2);
    addRegexToken('SSS',  match1to3, match3);

    var token;
    for (token = 'SSSS'; token.length <= 9; token += 'S') {
        addRegexToken(token, matchUnsigned);
    }

    function parseMs(input, array) {
        array[MILLISECOND] = toInt(('0.' + input) * 1000);
    }

    for (token = 'S'; token.length <= 9; token += 'S') {
        addParseToken(token, parseMs);
    }
    // MOMENTS

    var getSetMillisecond = makeGetSet('Milliseconds', false);

    addFormatToken('z',  0, 0, 'zoneAbbr');
    addFormatToken('zz', 0, 0, 'zoneName');

    // MOMENTS

    function getZoneAbbr () {
        return this._isUTC ? 'UTC' : '';
    }

    function getZoneName () {
        return this._isUTC ? 'Coordinated Universal Time' : '';
    }

    var momentPrototype__proto = Moment.prototype;

    momentPrototype__proto.add          = add_subtract__add;
    momentPrototype__proto.calendar     = moment_calendar__calendar;
    momentPrototype__proto.clone        = clone;
    momentPrototype__proto.diff         = diff;
    momentPrototype__proto.endOf        = endOf;
    momentPrototype__proto.format       = format;
    momentPrototype__proto.from         = from;
    momentPrototype__proto.fromNow      = fromNow;
    momentPrototype__proto.to           = to;
    momentPrototype__proto.toNow        = toNow;
    momentPrototype__proto.get          = getSet;
    momentPrototype__proto.invalidAt    = invalidAt;
    momentPrototype__proto.isAfter      = isAfter;
    momentPrototype__proto.isBefore     = isBefore;
    momentPrototype__proto.isBetween    = isBetween;
    momentPrototype__proto.isSame       = isSame;
    momentPrototype__proto.isValid      = moment_valid__isValid;
    momentPrototype__proto.lang         = lang;
    momentPrototype__proto.locale       = locale;
    momentPrototype__proto.localeData   = localeData;
    momentPrototype__proto.max          = prototypeMax;
    momentPrototype__proto.min          = prototypeMin;
    momentPrototype__proto.parsingFlags = parsingFlags;
    momentPrototype__proto.set          = getSet;
    momentPrototype__proto.startOf      = startOf;
    momentPrototype__proto.subtract     = add_subtract__subtract;
    momentPrototype__proto.toArray      = toArray;
    momentPrototype__proto.toObject     = toObject;
    momentPrototype__proto.toDate       = toDate;
    momentPrototype__proto.toISOString  = moment_format__toISOString;
    momentPrototype__proto.toJSON       = moment_format__toISOString;
    momentPrototype__proto.toString     = toString;
    momentPrototype__proto.unix         = unix;
    momentPrototype__proto.valueOf      = to_type__valueOf;

    // Year
    momentPrototype__proto.year       = getSetYear;
    momentPrototype__proto.isLeapYear = getIsLeapYear;

    // Week Year
    momentPrototype__proto.weekYear    = getSetWeekYear;
    momentPrototype__proto.isoWeekYear = getSetISOWeekYear;

    // Quarter
    momentPrototype__proto.quarter = momentPrototype__proto.quarters = getSetQuarter;

    // Month
    momentPrototype__proto.month       = getSetMonth;
    momentPrototype__proto.daysInMonth = getDaysInMonth;

    // Week
    momentPrototype__proto.week           = momentPrototype__proto.weeks        = getSetWeek;
    momentPrototype__proto.isoWeek        = momentPrototype__proto.isoWeeks     = getSetISOWeek;
    momentPrototype__proto.weeksInYear    = getWeeksInYear;
    momentPrototype__proto.isoWeeksInYear = getISOWeeksInYear;

    // Day
    momentPrototype__proto.date       = getSetDayOfMonth;
    momentPrototype__proto.day        = momentPrototype__proto.days             = getSetDayOfWeek;
    momentPrototype__proto.weekday    = getSetLocaleDayOfWeek;
    momentPrototype__proto.isoWeekday = getSetISODayOfWeek;
    momentPrototype__proto.dayOfYear  = getSetDayOfYear;

    // Hour
    momentPrototype__proto.hour = momentPrototype__proto.hours = getSetHour;

    // Minute
    momentPrototype__proto.minute = momentPrototype__proto.minutes = getSetMinute;

    // Second
    momentPrototype__proto.second = momentPrototype__proto.seconds = getSetSecond;

    // Millisecond
    momentPrototype__proto.millisecond = momentPrototype__proto.milliseconds = getSetMillisecond;

    // Offset
    momentPrototype__proto.utcOffset            = getSetOffset;
    momentPrototype__proto.utc                  = setOffsetToUTC;
    momentPrototype__proto.local                = setOffsetToLocal;
    momentPrototype__proto.parseZone            = setOffsetToParsedOffset;
    momentPrototype__proto.hasAlignedHourOffset = hasAlignedHourOffset;
    momentPrototype__proto.isDST                = isDaylightSavingTime;
    momentPrototype__proto.isDSTShifted         = isDaylightSavingTimeShifted;
    momentPrototype__proto.isLocal              = isLocal;
    momentPrototype__proto.isUtcOffset          = isUtcOffset;
    momentPrototype__proto.isUtc                = isUtc;
    momentPrototype__proto.isUTC                = isUtc;

    // Timezone
    momentPrototype__proto.zoneAbbr = getZoneAbbr;
    momentPrototype__proto.zoneName = getZoneName;

    // Deprecations
    momentPrototype__proto.dates  = deprecate('dates accessor is deprecated. Use date instead.', getSetDayOfMonth);
    momentPrototype__proto.months = deprecate('months accessor is deprecated. Use month instead', getSetMonth);
    momentPrototype__proto.years  = deprecate('years accessor is deprecated. Use year instead', getSetYear);
    momentPrototype__proto.zone   = deprecate('moment().zone is deprecated, use moment().utcOffset instead. https://github.com/moment/moment/issues/1779', getSetZone);

    var momentPrototype = momentPrototype__proto;

    function moment__createUnix (input) {
        return local__createLocal(input * 1000);
    }

    function moment__createInZone () {
        return local__createLocal.apply(null, arguments).parseZone();
    }

    var defaultCalendar = {
        sameDay : '[Today at] LT',
        nextDay : '[Tomorrow at] LT',
        nextWeek : 'dddd [at] LT',
        lastDay : '[Yesterday at] LT',
        lastWeek : '[Last] dddd [at] LT',
        sameElse : 'L'
    };

    function locale_calendar__calendar (key, mom, now) {
        var output = this._calendar[key];
        return typeof output === 'function' ? output.call(mom, now) : output;
    }

    var defaultLongDateFormat = {
        LTS  : 'h:mm:ss A',
        LT   : 'h:mm A',
        L    : 'MM/DD/YYYY',
        LL   : 'MMMM D, YYYY',
        LLL  : 'MMMM D, YYYY h:mm A',
        LLLL : 'dddd, MMMM D, YYYY h:mm A'
    };

    function longDateFormat (key) {
        var format = this._longDateFormat[key],
            formatUpper = this._longDateFormat[key.toUpperCase()];

        if (format || !formatUpper) {
            return format;
        }

        this._longDateFormat[key] = formatUpper.replace(/MMMM|MM|DD|dddd/g, function (val) {
            return val.slice(1);
        });

        return this._longDateFormat[key];
    }

    var defaultInvalidDate = 'Invalid date';

    function invalidDate () {
        return this._invalidDate;
    }

    var defaultOrdinal = '%d';
    var defaultOrdinalParse = /\d{1,2}/;

    function ordinal (number) {
        return this._ordinal.replace('%d', number);
    }

    function preParsePostFormat (string) {
        return string;
    }

    var defaultRelativeTime = {
        future : 'in %s',
        past   : '%s ago',
        s  : 'a few seconds',
        m  : 'a minute',
        mm : '%d minutes',
        h  : 'an hour',
        hh : '%d hours',
        d  : 'a day',
        dd : '%d days',
        M  : 'a month',
        MM : '%d months',
        y  : 'a year',
        yy : '%d years'
    };

    function relative__relativeTime (number, withoutSuffix, string, isFuture) {
        var output = this._relativeTime[string];
        return (typeof output === 'function') ?
            output(number, withoutSuffix, string, isFuture) :
            output.replace(/%d/i, number);
    }

    function pastFuture (diff, output) {
        var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
        return typeof format === 'function' ? format(output) : format.replace(/%s/i, output);
    }

    function locale_set__set (config) {
        var prop, i;
        for (i in config) {
            prop = config[i];
            if (typeof prop === 'function') {
                this[i] = prop;
            } else {
                this['_' + i] = prop;
            }
        }
        // Lenient ordinal parsing accepts just a number in addition to
        // number + (possibly) stuff coming from _ordinalParseLenient.
        this._ordinalParseLenient = new RegExp(this._ordinalParse.source + '|' + (/\d{1,2}/).source);
    }

    var prototype__proto = Locale.prototype;

    prototype__proto._calendar       = defaultCalendar;
    prototype__proto.calendar        = locale_calendar__calendar;
    prototype__proto._longDateFormat = defaultLongDateFormat;
    prototype__proto.longDateFormat  = longDateFormat;
    prototype__proto._invalidDate    = defaultInvalidDate;
    prototype__proto.invalidDate     = invalidDate;
    prototype__proto._ordinal        = defaultOrdinal;
    prototype__proto.ordinal         = ordinal;
    prototype__proto._ordinalParse   = defaultOrdinalParse;
    prototype__proto.preparse        = preParsePostFormat;
    prototype__proto.postformat      = preParsePostFormat;
    prototype__proto._relativeTime   = defaultRelativeTime;
    prototype__proto.relativeTime    = relative__relativeTime;
    prototype__proto.pastFuture      = pastFuture;
    prototype__proto.set             = locale_set__set;

    // Month
    prototype__proto.months       =        localeMonths;
    prototype__proto._months      = defaultLocaleMonths;
    prototype__proto.monthsShort  =        localeMonthsShort;
    prototype__proto._monthsShort = defaultLocaleMonthsShort;
    prototype__proto.monthsParse  =        localeMonthsParse;

    // Week
    prototype__proto.week = localeWeek;
    prototype__proto._week = defaultLocaleWeek;
    prototype__proto.firstDayOfYear = localeFirstDayOfYear;
    prototype__proto.firstDayOfWeek = localeFirstDayOfWeek;

    // Day of Week
    prototype__proto.weekdays       =        localeWeekdays;
    prototype__proto._weekdays      = defaultLocaleWeekdays;
    prototype__proto.weekdaysMin    =        localeWeekdaysMin;
    prototype__proto._weekdaysMin   = defaultLocaleWeekdaysMin;
    prototype__proto.weekdaysShort  =        localeWeekdaysShort;
    prototype__proto._weekdaysShort = defaultLocaleWeekdaysShort;
    prototype__proto.weekdaysParse  =        localeWeekdaysParse;

    // Hours
    prototype__proto.isPM = localeIsPM;
    prototype__proto._meridiemParse = defaultLocaleMeridiemParse;
    prototype__proto.meridiem = localeMeridiem;

    function lists__get (format, index, field, setter) {
        var locale = locale_locales__getLocale();
        var utc = create_utc__createUTC().set(setter, index);
        return locale[field](utc, format);
    }

    function list (format, index, field, count, setter) {
        if (typeof format === 'number') {
            index = format;
            format = undefined;
        }

        format = format || '';

        if (index != null) {
            return lists__get(format, index, field, setter);
        }

        var i;
        var out = [];
        for (i = 0; i < count; i++) {
            out[i] = lists__get(format, i, field, setter);
        }
        return out;
    }

    function lists__listMonths (format, index) {
        return list(format, index, 'months', 12, 'month');
    }

    function lists__listMonthsShort (format, index) {
        return list(format, index, 'monthsShort', 12, 'month');
    }

    function lists__listWeekdays (format, index) {
        return list(format, index, 'weekdays', 7, 'day');
    }

    function lists__listWeekdaysShort (format, index) {
        return list(format, index, 'weekdaysShort', 7, 'day');
    }

    function lists__listWeekdaysMin (format, index) {
        return list(format, index, 'weekdaysMin', 7, 'day');
    }

    locale_locales__getSetGlobalLocale('en', {
        ordinalParse: /\d{1,2}(th|st|nd|rd)/,
        ordinal : function (number) {
            var b = number % 10,
                output = (toInt(number % 100 / 10) === 1) ? 'th' :
                (b === 1) ? 'st' :
                (b === 2) ? 'nd' :
                (b === 3) ? 'rd' : 'th';
            return number + output;
        }
    });

    // Side effect imports
    utils_hooks__hooks.lang = deprecate('moment.lang is deprecated. Use moment.locale instead.', locale_locales__getSetGlobalLocale);
    utils_hooks__hooks.langData = deprecate('moment.langData is deprecated. Use moment.localeData instead.', locale_locales__getLocale);

    var mathAbs = Math.abs;

    function duration_abs__abs () {
        var data           = this._data;

        this._milliseconds = mathAbs(this._milliseconds);
        this._days         = mathAbs(this._days);
        this._months       = mathAbs(this._months);

        data.milliseconds  = mathAbs(data.milliseconds);
        data.seconds       = mathAbs(data.seconds);
        data.minutes       = mathAbs(data.minutes);
        data.hours         = mathAbs(data.hours);
        data.months        = mathAbs(data.months);
        data.years         = mathAbs(data.years);

        return this;
    }

    function duration_add_subtract__addSubtract (duration, input, value, direction) {
        var other = create__createDuration(input, value);

        duration._milliseconds += direction * other._milliseconds;
        duration._days         += direction * other._days;
        duration._months       += direction * other._months;

        return duration._bubble();
    }

    // supports only 2.0-style add(1, 's') or add(duration)
    function duration_add_subtract__add (input, value) {
        return duration_add_subtract__addSubtract(this, input, value, 1);
    }

    // supports only 2.0-style subtract(1, 's') or subtract(duration)
    function duration_add_subtract__subtract (input, value) {
        return duration_add_subtract__addSubtract(this, input, value, -1);
    }

    function absCeil (number) {
        if (number < 0) {
            return Math.floor(number);
        } else {
            return Math.ceil(number);
        }
    }

    function bubble () {
        var milliseconds = this._milliseconds;
        var days         = this._days;
        var months       = this._months;
        var data         = this._data;
        var seconds, minutes, hours, years, monthsFromDays;

        // if we have a mix of positive and negative values, bubble down first
        // check: https://github.com/moment/moment/issues/2166
        if (!((milliseconds >= 0 && days >= 0 && months >= 0) ||
                (milliseconds <= 0 && days <= 0 && months <= 0))) {
            milliseconds += absCeil(monthsToDays(months) + days) * 864e5;
            days = 0;
            months = 0;
        }

        // The following code bubbles up values, see the tests for
        // examples of what that means.
        data.milliseconds = milliseconds % 1000;

        seconds           = absFloor(milliseconds / 1000);
        data.seconds      = seconds % 60;

        minutes           = absFloor(seconds / 60);
        data.minutes      = minutes % 60;

        hours             = absFloor(minutes / 60);
        data.hours        = hours % 24;

        days += absFloor(hours / 24);

        // convert days to months
        monthsFromDays = absFloor(daysToMonths(days));
        months += monthsFromDays;
        days -= absCeil(monthsToDays(monthsFromDays));

        // 12 months -> 1 year
        years = absFloor(months / 12);
        months %= 12;

        data.days   = days;
        data.months = months;
        data.years  = years;

        return this;
    }

    function daysToMonths (days) {
        // 400 years have 146097 days (taking into account leap year rules)
        // 400 years have 12 months === 4800
        return days * 4800 / 146097;
    }

    function monthsToDays (months) {
        // the reverse of daysToMonths
        return months * 146097 / 4800;
    }

    function as (units) {
        var days;
        var months;
        var milliseconds = this._milliseconds;

        units = normalizeUnits(units);

        if (units === 'month' || units === 'year') {
            days   = this._days   + milliseconds / 864e5;
            months = this._months + daysToMonths(days);
            return units === 'month' ? months : months / 12;
        } else {
            // handle milliseconds separately because of floating point math errors (issue #1867)
            days = this._days + Math.round(monthsToDays(this._months));
            switch (units) {
                case 'week'   : return days / 7     + milliseconds / 6048e5;
                case 'day'    : return days         + milliseconds / 864e5;
                case 'hour'   : return days * 24    + milliseconds / 36e5;
                case 'minute' : return days * 1440  + milliseconds / 6e4;
                case 'second' : return days * 86400 + milliseconds / 1000;
                // Math.floor prevents floating point math errors here
                case 'millisecond': return Math.floor(days * 864e5) + milliseconds;
                default: throw new Error('Unknown unit ' + units);
            }
        }
    }

    // TODO: Use this.as('ms')?
    function duration_as__valueOf () {
        return (
            this._milliseconds +
            this._days * 864e5 +
            (this._months % 12) * 2592e6 +
            toInt(this._months / 12) * 31536e6
        );
    }

    function makeAs (alias) {
        return function () {
            return this.as(alias);
        };
    }

    var asMilliseconds = makeAs('ms');
    var asSeconds      = makeAs('s');
    var asMinutes      = makeAs('m');
    var asHours        = makeAs('h');
    var asDays         = makeAs('d');
    var asWeeks        = makeAs('w');
    var asMonths       = makeAs('M');
    var asYears        = makeAs('y');

    function duration_get__get (units) {
        units = normalizeUnits(units);
        return this[units + 's']();
    }

    function makeGetter(name) {
        return function () {
            return this._data[name];
        };
    }

    var milliseconds = makeGetter('milliseconds');
    var seconds      = makeGetter('seconds');
    var minutes      = makeGetter('minutes');
    var hours        = makeGetter('hours');
    var days         = makeGetter('days');
    var months       = makeGetter('months');
    var years        = makeGetter('years');

    function weeks () {
        return absFloor(this.days() / 7);
    }

    var round = Math.round;
    var thresholds = {
        s: 45,  // seconds to minute
        m: 45,  // minutes to hour
        h: 22,  // hours to day
        d: 26,  // days to month
        M: 11   // months to year
    };

    // helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
    function substituteTimeAgo(string, number, withoutSuffix, isFuture, locale) {
        return locale.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
    }

    function duration_humanize__relativeTime (posNegDuration, withoutSuffix, locale) {
        var duration = create__createDuration(posNegDuration).abs();
        var seconds  = round(duration.as('s'));
        var minutes  = round(duration.as('m'));
        var hours    = round(duration.as('h'));
        var days     = round(duration.as('d'));
        var months   = round(duration.as('M'));
        var years    = round(duration.as('y'));

        var a = seconds < thresholds.s && ['s', seconds]  ||
                minutes === 1          && ['m']           ||
                minutes < thresholds.m && ['mm', minutes] ||
                hours   === 1          && ['h']           ||
                hours   < thresholds.h && ['hh', hours]   ||
                days    === 1          && ['d']           ||
                days    < thresholds.d && ['dd', days]    ||
                months  === 1          && ['M']           ||
                months  < thresholds.M && ['MM', months]  ||
                years   === 1          && ['y']           || ['yy', years];

        a[2] = withoutSuffix;
        a[3] = +posNegDuration > 0;
        a[4] = locale;
        return substituteTimeAgo.apply(null, a);
    }

    // This function allows you to set a threshold for relative time strings
    function duration_humanize__getSetRelativeTimeThreshold (threshold, limit) {
        if (thresholds[threshold] === undefined) {
            return false;
        }
        if (limit === undefined) {
            return thresholds[threshold];
        }
        thresholds[threshold] = limit;
        return true;
    }

    function humanize (withSuffix) {
        var locale = this.localeData();
        var output = duration_humanize__relativeTime(this, !withSuffix, locale);

        if (withSuffix) {
            output = locale.pastFuture(+this, output);
        }

        return locale.postformat(output);
    }

    var iso_string__abs = Math.abs;

    function iso_string__toISOString() {
        // for ISO strings we do not use the normal bubbling rules:
        //  * milliseconds bubble up until they become hours
        //  * days do not bubble at all
        //  * months bubble up until they become years
        // This is because there is no context-free conversion between hours and days
        // (think of clock changes)
        // and also not between days and months (28-31 days per month)
        var seconds = iso_string__abs(this._milliseconds) / 1000;
        var days         = iso_string__abs(this._days);
        var months       = iso_string__abs(this._months);
        var minutes, hours, years;

        // 3600 seconds -> 60 minutes -> 1 hour
        minutes           = absFloor(seconds / 60);
        hours             = absFloor(minutes / 60);
        seconds %= 60;
        minutes %= 60;

        // 12 months -> 1 year
        years  = absFloor(months / 12);
        months %= 12;


        // inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
        var Y = years;
        var M = months;
        var D = days;
        var h = hours;
        var m = minutes;
        var s = seconds;
        var total = this.asSeconds();

        if (!total) {
            // this is the same as C#'s (Noda) and python (isodate)...
            // but not other JS (goog.date)
            return 'P0D';
        }

        return (total < 0 ? '-' : '') +
            'P' +
            (Y ? Y + 'Y' : '') +
            (M ? M + 'M' : '') +
            (D ? D + 'D' : '') +
            ((h || m || s) ? 'T' : '') +
            (h ? h + 'H' : '') +
            (m ? m + 'M' : '') +
            (s ? s + 'S' : '');
    }

    var duration_prototype__proto = Duration.prototype;

    duration_prototype__proto.abs            = duration_abs__abs;
    duration_prototype__proto.add            = duration_add_subtract__add;
    duration_prototype__proto.subtract       = duration_add_subtract__subtract;
    duration_prototype__proto.as             = as;
    duration_prototype__proto.asMilliseconds = asMilliseconds;
    duration_prototype__proto.asSeconds      = asSeconds;
    duration_prototype__proto.asMinutes      = asMinutes;
    duration_prototype__proto.asHours        = asHours;
    duration_prototype__proto.asDays         = asDays;
    duration_prototype__proto.asWeeks        = asWeeks;
    duration_prototype__proto.asMonths       = asMonths;
    duration_prototype__proto.asYears        = asYears;
    duration_prototype__proto.valueOf        = duration_as__valueOf;
    duration_prototype__proto._bubble        = bubble;
    duration_prototype__proto.get            = duration_get__get;
    duration_prototype__proto.milliseconds   = milliseconds;
    duration_prototype__proto.seconds        = seconds;
    duration_prototype__proto.minutes        = minutes;
    duration_prototype__proto.hours          = hours;
    duration_prototype__proto.days           = days;
    duration_prototype__proto.weeks          = weeks;
    duration_prototype__proto.months         = months;
    duration_prototype__proto.years          = years;
    duration_prototype__proto.humanize       = humanize;
    duration_prototype__proto.toISOString    = iso_string__toISOString;
    duration_prototype__proto.toString       = iso_string__toISOString;
    duration_prototype__proto.toJSON         = iso_string__toISOString;
    duration_prototype__proto.locale         = locale;
    duration_prototype__proto.localeData     = localeData;

    // Deprecations
    duration_prototype__proto.toIsoString = deprecate('toIsoString() is deprecated. Please use toISOString() instead (notice the capitals)', iso_string__toISOString);
    duration_prototype__proto.lang = lang;

    // Side effect imports

    addFormatToken('X', 0, 0, 'unix');
    addFormatToken('x', 0, 0, 'valueOf');

    // PARSING

    addRegexToken('x', matchSigned);
    addRegexToken('X', matchTimestamp);
    addParseToken('X', function (input, array, config) {
        config._d = new Date(parseFloat(input, 10) * 1000);
    });
    addParseToken('x', function (input, array, config) {
        config._d = new Date(toInt(input));
    });

    // Side effect imports


    utils_hooks__hooks.version = '2.10.6';

    setHookCallback(local__createLocal);

    utils_hooks__hooks.fn                    = momentPrototype;
    utils_hooks__hooks.min                   = min;
    utils_hooks__hooks.max                   = max;
    utils_hooks__hooks.utc                   = create_utc__createUTC;
    utils_hooks__hooks.unix                  = moment__createUnix;
    utils_hooks__hooks.months                = lists__listMonths;
    utils_hooks__hooks.isDate                = isDate;
    utils_hooks__hooks.locale                = locale_locales__getSetGlobalLocale;
    utils_hooks__hooks.invalid               = valid__createInvalid;
    utils_hooks__hooks.duration              = create__createDuration;
    utils_hooks__hooks.isMoment              = isMoment;
    utils_hooks__hooks.weekdays              = lists__listWeekdays;
    utils_hooks__hooks.parseZone             = moment__createInZone;
    utils_hooks__hooks.localeData            = locale_locales__getLocale;
    utils_hooks__hooks.isDuration            = isDuration;
    utils_hooks__hooks.monthsShort           = lists__listMonthsShort;
    utils_hooks__hooks.weekdaysMin           = lists__listWeekdaysMin;
    utils_hooks__hooks.defineLocale          = defineLocale;
    utils_hooks__hooks.weekdaysShort         = lists__listWeekdaysShort;
    utils_hooks__hooks.normalizeUnits        = normalizeUnits;
    utils_hooks__hooks.relativeTimeThreshold = duration_humanize__getSetRelativeTimeThreshold;

    var _moment = utils_hooks__hooks;

    return _moment;

}));
},{}],47:[function(require,module,exports){
(function (global){

var rng;

if (global.crypto && crypto.getRandomValues) {
  // WHATWG crypto-based RNG - http://wiki.whatwg.org/wiki/Crypto
  // Moderately fast, high quality
  var _rnds8 = new Uint8Array(16);
  rng = function whatwgRNG() {
    crypto.getRandomValues(_rnds8);
    return _rnds8;
  };
}

if (!rng) {
  // Math.random()-based (RNG)
  //
  // If all else fails, use Math.random().  It's fast, but is of unspecified
  // quality.
  var  _rnds = new Array(16);
  rng = function() {
    for (var i = 0, r; i < 16; i++) {
      if ((i & 0x03) === 0) r = Math.random() * 0x100000000;
      _rnds[i] = r >>> ((i & 0x03) << 3) & 0xff;
    }

    return _rnds;
  };
}

module.exports = rng;


}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],48:[function(require,module,exports){
//     uuid.js
//
//     Copyright (c) 2010-2012 Robert Kieffer
//     MIT License - http://opensource.org/licenses/mit-license.php

// Unique ID creation requires a high quality random # generator.  We feature
// detect to determine the best RNG source, normalizing to a function that
// returns 128-bits of randomness, since that's what's usually required
var _rng = require('./rng');

// Maps for number <-> hex string conversion
var _byteToHex = [];
var _hexToByte = {};
for (var i = 0; i < 256; i++) {
  _byteToHex[i] = (i + 0x100).toString(16).substr(1);
  _hexToByte[_byteToHex[i]] = i;
}

// **`parse()` - Parse a UUID into it's component bytes**
function parse(s, buf, offset) {
  var i = (buf && offset) || 0, ii = 0;

  buf = buf || [];
  s.toLowerCase().replace(/[0-9a-f]{2}/g, function(oct) {
    if (ii < 16) { // Don't overflow!
      buf[i + ii++] = _hexToByte[oct];
    }
  });

  // Zero out remaining bytes if string was short
  while (ii < 16) {
    buf[i + ii++] = 0;
  }

  return buf;
}

// **`unparse()` - Convert UUID byte array (ala parse()) into a string**
function unparse(buf, offset) {
  var i = offset || 0, bth = _byteToHex;
  return  bth[buf[i++]] + bth[buf[i++]] +
          bth[buf[i++]] + bth[buf[i++]] + '-' +
          bth[buf[i++]] + bth[buf[i++]] + '-' +
          bth[buf[i++]] + bth[buf[i++]] + '-' +
          bth[buf[i++]] + bth[buf[i++]] + '-' +
          bth[buf[i++]] + bth[buf[i++]] +
          bth[buf[i++]] + bth[buf[i++]] +
          bth[buf[i++]] + bth[buf[i++]];
}

// **`v1()` - Generate time-based UUID**
//
// Inspired by https://github.com/LiosK/UUID.js
// and http://docs.python.org/library/uuid.html

// random #'s we need to init node and clockseq
var _seedBytes = _rng();

// Per 4.5, create and 48-bit node id, (47 random bits + multicast bit = 1)
var _nodeId = [
  _seedBytes[0] | 0x01,
  _seedBytes[1], _seedBytes[2], _seedBytes[3], _seedBytes[4], _seedBytes[5]
];

// Per 4.2.2, randomize (14 bit) clockseq
var _clockseq = (_seedBytes[6] << 8 | _seedBytes[7]) & 0x3fff;

// Previous uuid creation time
var _lastMSecs = 0, _lastNSecs = 0;

// See https://github.com/broofa/node-uuid for API details
function v1(options, buf, offset) {
  var i = buf && offset || 0;
  var b = buf || [];

  options = options || {};

  var clockseq = options.clockseq !== undefined ? options.clockseq : _clockseq;

  // UUID timestamps are 100 nano-second units since the Gregorian epoch,
  // (1582-10-15 00:00).  JSNumbers aren't precise enough for this, so
  // time is handled internally as 'msecs' (integer milliseconds) and 'nsecs'
  // (100-nanoseconds offset from msecs) since unix epoch, 1970-01-01 00:00.
  var msecs = options.msecs !== undefined ? options.msecs : new Date().getTime();

  // Per 4.2.1.2, use count of uuid's generated during the current clock
  // cycle to simulate higher resolution clock
  var nsecs = options.nsecs !== undefined ? options.nsecs : _lastNSecs + 1;

  // Time since last uuid creation (in msecs)
  var dt = (msecs - _lastMSecs) + (nsecs - _lastNSecs)/10000;

  // Per 4.2.1.2, Bump clockseq on clock regression
  if (dt < 0 && options.clockseq === undefined) {
    clockseq = clockseq + 1 & 0x3fff;
  }

  // Reset nsecs if clock regresses (new clockseq) or we've moved onto a new
  // time interval
  if ((dt < 0 || msecs > _lastMSecs) && options.nsecs === undefined) {
    nsecs = 0;
  }

  // Per 4.2.1.2 Throw error if too many uuids are requested
  if (nsecs >= 10000) {
    throw new Error('uuid.v1(): Can\'t create more than 10M uuids/sec');
  }

  _lastMSecs = msecs;
  _lastNSecs = nsecs;
  _clockseq = clockseq;

  // Per 4.1.4 - Convert from unix epoch to Gregorian epoch
  msecs += 12219292800000;

  // `time_low`
  var tl = ((msecs & 0xfffffff) * 10000 + nsecs) % 0x100000000;
  b[i++] = tl >>> 24 & 0xff;
  b[i++] = tl >>> 16 & 0xff;
  b[i++] = tl >>> 8 & 0xff;
  b[i++] = tl & 0xff;

  // `time_mid`
  var tmh = (msecs / 0x100000000 * 10000) & 0xfffffff;
  b[i++] = tmh >>> 8 & 0xff;
  b[i++] = tmh & 0xff;

  // `time_high_and_version`
  b[i++] = tmh >>> 24 & 0xf | 0x10; // include version
  b[i++] = tmh >>> 16 & 0xff;

  // `clock_seq_hi_and_reserved` (Per 4.2.2 - include variant)
  b[i++] = clockseq >>> 8 | 0x80;

  // `clock_seq_low`
  b[i++] = clockseq & 0xff;

  // `node`
  var node = options.node || _nodeId;
  for (var n = 0; n < 6; n++) {
    b[i + n] = node[n];
  }

  return buf ? buf : unparse(b);
}

// **`v4()` - Generate random UUID**

// See https://github.com/broofa/node-uuid for API details
function v4(options, buf, offset) {
  // Deprecated - 'format' argument, as supported in v1.2
  var i = buf && offset || 0;

  if (typeof(options) == 'string') {
    buf = options == 'binary' ? new Array(16) : null;
    options = null;
  }
  options = options || {};

  var rnds = options.random || (options.rng || _rng)();

  // Per 4.4, set bits for version and `clock_seq_hi_and_reserved`
  rnds[6] = (rnds[6] & 0x0f) | 0x40;
  rnds[8] = (rnds[8] & 0x3f) | 0x80;

  // Copy bytes to buffer, if provided
  if (buf) {
    for (var ii = 0; ii < 16; ii++) {
      buf[i + ii] = rnds[ii];
    }
  }

  return buf || unparse(rnds);
}

// Export public API
var uuid = v4;
uuid.v1 = v1;
uuid.v4 = v4;
uuid.parse = parse;
uuid.unparse = unparse;

module.exports = uuid;

},{"./rng":47}],49:[function(require,module,exports){
var indexOf = require('indexof');

var Object_keys = function (obj) {
    if (Object.keys) return Object.keys(obj)
    else {
        var res = [];
        for (var key in obj) res.push(key)
        return res;
    }
};

var forEach = function (xs, fn) {
    if (xs.forEach) return xs.forEach(fn)
    else for (var i = 0; i < xs.length; i++) {
        fn(xs[i], i, xs);
    }
};

var defineProp = (function() {
    try {
        Object.defineProperty({}, '_', {});
        return function(obj, name, value) {
            Object.defineProperty(obj, name, {
                writable: true,
                enumerable: false,
                configurable: true,
                value: value
            })
        };
    } catch(e) {
        return function(obj, name, value) {
            obj[name] = value;
        };
    }
}());

var globals = ['Array', 'Boolean', 'Date', 'Error', 'EvalError', 'Function',
'Infinity', 'JSON', 'Math', 'NaN', 'Number', 'Object', 'RangeError',
'ReferenceError', 'RegExp', 'String', 'SyntaxError', 'TypeError', 'URIError',
'decodeURI', 'decodeURIComponent', 'encodeURI', 'encodeURIComponent', 'escape',
'eval', 'isFinite', 'isNaN', 'parseFloat', 'parseInt', 'undefined', 'unescape'];

function Context() {}
Context.prototype = {};

var Script = exports.Script = function NodeScript (code) {
    if (!(this instanceof Script)) return new Script(code);
    this.code = code;
};

Script.prototype.runInContext = function (context) {
    if (!(context instanceof Context)) {
        throw new TypeError("needs a 'context' argument.");
    }
    
    var iframe = document.createElement('iframe');
    if (!iframe.style) iframe.style = {};
    iframe.style.display = 'none';
    
    document.body.appendChild(iframe);
    
    var win = iframe.contentWindow;
    var wEval = win.eval, wExecScript = win.execScript;

    if (!wEval && wExecScript) {
        // win.eval() magically appears when this is called in IE:
        wExecScript.call(win, 'null');
        wEval = win.eval;
    }
    
    forEach(Object_keys(context), function (key) {
        win[key] = context[key];
    });
    forEach(globals, function (key) {
        if (context[key]) {
            win[key] = context[key];
        }
    });
    
    var winKeys = Object_keys(win);

    var res = wEval.call(win, this.code);
    
    forEach(Object_keys(win), function (key) {
        // Avoid copying circular objects like `top` and `window` by only
        // updating existing context properties or new properties in the `win`
        // that was only introduced after the eval.
        if (key in context || indexOf(winKeys, key) === -1) {
            context[key] = win[key];
        }
    });

    forEach(globals, function (key) {
        if (!(key in context)) {
            defineProp(context, key, win[key]);
        }
    });
    
    document.body.removeChild(iframe);
    
    return res;
};

Script.prototype.runInThisContext = function () {
    return eval(this.code); // maybe...
};

Script.prototype.runInNewContext = function (context) {
    var ctx = Script.createContext(context);
    var res = this.runInContext(ctx);

    forEach(Object_keys(ctx), function (key) {
        context[key] = ctx[key];
    });

    return res;
};

forEach(Object_keys(Script.prototype), function (name) {
    exports[name] = Script[name] = function (code) {
        var s = Script(code);
        return s[name].apply(s, [].slice.call(arguments, 1));
    };
});

exports.createScript = function (code) {
    return exports.Script(code);
};

exports.createContext = Script.createContext = function (context) {
    var copy = new Context();
    if(typeof context === 'object') {
        forEach(Object_keys(context), function (key) {
            copy[key] = context[key];
        });
    }
    return copy;
};

},{"indexof":45}],"blue-button-generate":[function(require,module,exports){
"use strict";

/*
This script converts CCDA data in JSON format (originally generated from a Continuity of Care Document (CCD) in
standard XML/CCDA format) back to XML/CCDA format.
*/

var bbu = require("blue-button-util");

var engine = require('./lib/engine');
var documentLevel = require('./lib/documentLevel');

var bbuo = bbu.object;

var html_renderer = require('./lib/htmlHeaders');

var createContext = (function () {
    var base = {
        nextReference: function (referenceKey) {
            var index = this.references[referenceKey] || 0;
            ++index;
            this.references[referenceKey] = index;
            return "#" + referenceKey + index;
        },
        sameReference: function (referenceKey) {
            var index = this.references[referenceKey] || 0;
            return "#" + referenceKey + index;
        }
    };

    return function (options) {
        var result = Object.create(base);
        result.references = {};
        if (options.meta && options.addUniqueIds) {
            result.rootId = bbuo.deepValue(options.meta, 'identifiers.0.identifier');
        } else {
            result.rootId = null;
        }
        result.preventNullFlavor = options.preventNullFlavor;

        return result;
    };
})();

var generate = exports.generate = function (template, input, options) {
    if (!options.html_renderer) {
        options.html_renderer = html_renderer;
    }

    var context = createContext(options);
    return engine.create(documentLevel.ccd2(options.html_renderer), input, context);
};

exports.generateCCD = function (input, options) {
    options = options || {};
    options.meta = input.meta;
    return generate(documentLevel.ccd, input, options);
};

exports.fieldLevel = require("./lib/fieldLevel");
exports.entryLevel = require("./lib/entryLevel");
exports.leafLevel = require('./lib/leafLevel');
exports.contentModifier = require("./lib/contentModifier");
exports.condition = require('./lib/condition');

},{"./lib/condition":2,"./lib/contentModifier":3,"./lib/documentLevel":4,"./lib/engine":5,"./lib/entryLevel":9,"./lib/fieldLevel":19,"./lib/htmlHeaders":21,"./lib/leafLevel":22,"blue-button-util":36}]},{},["blue-button-generate"]);
