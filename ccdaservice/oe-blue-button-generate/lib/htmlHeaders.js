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
// Replace with static table
/*var alllergiesTextHeaders = ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
var allergiesTextRow = [
    leafLevel.deepInputProperty("observation.allergen.name", ""),
    leafLevel.deepInputProperty("observation.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.reaction.name", ""),
    leafLevel.deepInputProperty("observation.reactions.0.severity.code.name", ""),
    leafLevel.deepInputProperty("observation.status.name", "")
];*/

//exports.allergiesSectionEntriesRequiredHtmlHeader = getText('allergies', alllergiesTextHeaders, allergiesTextRow);

exports.allergiesSectionEntriesRequiredHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("allergies"),

    content: [{
        key: "table",
        attributes: {
            width: "100%",
            border: "1"
        },
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ["Substance", "Overall Severity", "Reaction", "Reaction Severity", "Status"];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputProperty("observation.allergen.name",  nda),
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("severity")
                    },
                    text: leafLevel.deepInputProperty("observation.severity.code.name",  nda)
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("reaction")
                    },
                    text: leafLevel.deepInputProperty("observation.reactions.0.reaction.name",  nda)
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("severity")
                    },
                    text: leafLevel.deepInputProperty("observation.reactions.0.severity.code.name",  nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("observation.status.name",  nda)
                }]
            }],
            dataKey: 'allergies'
        }]
    }]
};

/*var medicationsTextHeaders = ["Medication Class", "# fills", "Last fill date"];
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
];*/
//exports.medicationsSectionEntriesRequiredHtmlHeader = getText('medications', medicationsTextHeaders, medicationsTextRow);

exports.medicationsSectionEntriesRequiredHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("medications"),

    content: [{
        key: "table",
        attributes: {
            width: "100%",
            border: "1"
        },
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ["Medication Class", "# fills", "Last fill date"];
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
                        ID: leafLevel.nextTableReference("medinfo")
                    },
                    text: function (input) {
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
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("supply.repeatNumber", ""),
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("supply.date_time.low", nda)
                }]
            }],
            dataKey: 'medications'
        }]
    }]
}

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
                        return ['Condition', 'Status'];
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
                        ID: leafLevel.nextTableReference("problem")
                    },
                    text: leafLevel.deepInputProperty("problem.code.name", nda)
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("healthStatus")
                    },
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
                    attributes: {
                        ID: leafLevel.nextTableReference("procedure")
                    },
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
                        return ['Test', 'Result', 'Units', 'Reference Range', 'Date', 'Source'];
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
                    attributes: {
                        ID: leafLevel.nextTableReference("result")
                    },
                    text: leafLevel.deepInputProperty("value", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("unit", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("reference_range.range", nda)
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
                    attributes: {
                        ID: leafLevel.nextTableReference("Encounter")
                    },
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

exports.immunizationsSectionEntriesOptionalHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("immunizations"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "3"
                    },
                    text: "Immunizations"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Vaccine', 'Date', 'Status'];
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
                        ID: leafLevel.nextTableReference("immunization")
                    },
                    text: leafLevel.deepInputProperty("product.product.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.point", nda),
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("status", nda)
                }]
            }],
            dataKey: 'immunizations'
        }]
    }]
};

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
                        return ['Program', 'Start Date', 'Status', 'Goals'];
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
                        ID: leafLevel.nextTableReference("problem")
                    },
                    text: leafLevel.deepInputProperty("plan.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.center", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("status.code", nda)
                },  {
                    key: "td",
                    text: leafLevel.deepInputProperty("goal.name", nda)
                }]
            }],
            dataKey: 'plan_of_care'
        }]
    }]
};

exports.socialHistorySectionHtmlHeader = {key: "text",
    existsWhen: condition.keyExists("social_history"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "3"
                    },
                    text: "Social History"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Social History Element', 'Description', 'Effective Dates'];
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
                        ID: leafLevel.nextTableReference("social")
                    },
                    text: leafLevel.deepInputProperty("code.name", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputProperty("value", nda)
                }, {
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.low", nda),
                }]
            }],
            dataKey: 'social_history'
        }]
    }]
};

exports.vitalSignsSectionEntriesOptionalHtmlHeader = {
    key: "text",
    existsWhen: condition.keyExists("vitals"),

    content: [{
        key: "table",
        content: [{
            key: "thead",
            content: [{
                key: "tr",
                content: {
                    key: "th",
                    attributes: {
                        colspan: "6"
                    },
                    text: "Vital Sign Observations"
                }
            }, {
                key: "tr",
                content: [{
                    key: "th",
                    text: leafLevel.input,
                    dataTransform: function () {
                        return ['Date','Systolic BP [90-140 mmHg]', 'Diastolic BP [60-90 mmHg]', 'Height', 'Weight Measured', 'BMI (Body Mass Index)'];
                    }
                }]
            }]
        }, {
            key: "tbody",
            content: [{
                key: "tr",
                content: [{
                    key: "td",
                    text: leafLevel.deepInputDate("date_time.point", nda)
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("vital")
                    },
                    text: leafLevel.deepInputProperty("vital_list.0.value", nda, "vital_list.0.unit")
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("vital")
                    },
                    text: leafLevel.deepInputProperty("vital_list.1.value", nda, "vital_list.1.unit")
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("vital")
                    },
                    text: leafLevel.deepInputProperty("vital_list.2.value", nda, "vital_list.2.unit")
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("vital")
                    },
                    text: leafLevel.deepInputProperty("vital_list.3.value", nda, "vital_list.3.unit")
                }, {
                    key: "td",
                    attributes: {
                        ID: leafLevel.nextTableReference("vital")
                    },
                    text: leafLevel.deepInputProperty("vital_list.4.value", nda, "vital_list.4.unit")
                }]
            }],
            dataKey: 'vitals'
        }]
    }]
};

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
