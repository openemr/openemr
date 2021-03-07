"use strict";

var bbu = require("../../oe-blue-button-util");

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
