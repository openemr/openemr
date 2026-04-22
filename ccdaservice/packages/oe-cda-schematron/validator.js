// jshint node:true
// jshint shadow:true

module.exports = {
    validate: validate,
    clearCache: clearCache
};

var fs = require('fs');
var xpath = require('xpath');
const { DOMParser, XMLSerializer } = require('@xmldom/xmldom');
var crypto = require('crypto');

var parseSchematron = require('./parseSchematron');
var testAssertion = require('./testAssertion');
var includeExternalDocument = require('./includeExternalDocument');

// Parsed object cache
var parsedMap = {};

function clearCache() {
    parsedMap = {};
}

function validate(xml, schematron, options) {
    var contextMap = {};
    options = options || {};
    var includeWarnings = options.includeWarnings === undefined ? true : options.includeWarnings;
    var resourceDir = options.resourceDir || './';
    var xmlSnippetMaxLength = options.xmlSnippetMaxLength === undefined ? 200 : options.xmlSnippetMaxLength;

    if (xml.trim().indexOf('<')) {
        try {
            xml = fs.readFileSync(xml, 'utf-8').toString();
        } catch (err) {
            // intentionally empty
        }
    }

    var schematronPath = null;
    if (schematron.trim().indexOf('<')) {
        try {
            var temp = schematron;
            schematron = fs.readFileSync(schematron, 'utf-8').toString();
            schematronPath = temp;
        } catch (err) {
            // intentionally empty
        }
    }

    var xmlDoc = new DOMParser().parseFromString(xml, 'text/xml');

    var hash = crypto.createHash('md5').update(schematron).digest('hex');
    var s = parsedMap[hash];

    if (!s) {
        var schematronDoc = new DOMParser().parseFromString(schematron, 'text/xml');
        s = parseSchematron(schematronDoc);
        parsedMap[hash] = s;
    }

    var namespaceMap = s.namespaceMap;
    var patternRuleMap = s.patternRuleMap;
    var ruleAssertionMap = s.ruleAssertionMap;

    var select = xpath.useNamespaces(namespaceMap);

    var errors = [];
    var warnings = [];
    var ignored = [];

    for (var pattern in patternRuleMap) {
        if (Object.prototype.hasOwnProperty.call(patternRuleMap, pattern)) {
            var patternId = pattern;
            var rules = patternRuleMap[pattern];
            for (var i = 0; i < rules.length; i++) {
                if (!ruleAssertionMap[rules[i]].abstract) {
                    var ruleId = rules[i];
                    var context = ruleAssertionMap[rules[i]].context;
                    var assertionResults = checkRule(rules[i]);

                    if (!Array.isArray(assertionResults)) {
                        assertionResults = [assertionResults]; // Normalize single object return
                    }

                    for (var j = 0; j < assertionResults.length; j++) {
                        var resultObj = assertionResults[j] || {};
                        var type = resultObj.type;
                        var assertionId = resultObj.assertionId;
                        var test = resultObj.test;
                        var simplifiedTest = resultObj.simplifiedTest;
                        var description = resultObj.description;
                        var results = resultObj.results;

                        if (results && !results.ignored) {
                            for (var k = 0; k < results.length; k++) {
                                var result = results[k].result;
                                var line = results[k].line;
                                var path = results[k].path;
                                var xmlSnippet = results[k].xml;
                                if (!result) {
                                    var obj = {
                                        type: type,
                                        test: test,
                                        simplifiedTest: simplifiedTest,
                                        description: description,
                                        line: line,
                                        path: path,
                                        patternId: patternId,
                                        ruleId: ruleId,
                                        assertionId: assertionId,
                                        context: context,
                                        xml: xmlSnippet
                                    };
                                    if (type === 'error') {
                                        errors.push(obj);
                                    } else {
                                        warnings.push(obj);
                                    }
                                }
                            }
                        } else {
                            ignored.push({
                                errorMessage: resultObj.errorMessage || "Assertion skipped or malformed.",
                                type: type,
                                test: test,
                                simplifiedTest: simplifiedTest,
                                description: description,
                                patternId: patternId,
                                ruleId: ruleId,
                                assertionId: assertionId,
                                context: context
                            });
                        }
                    }
                }
            }
        }
    }

    return {
        errorCount: errors.length,
        warningCount: warnings.length,
        ignoredCount: ignored.length,
        errors: errors,
        warnings: warnings,
        ignored: ignored
    };

    function checkRule(rule, contextOverride) {
        var results = [];
        var assertionsAndExtensions = ruleAssertionMap[rule].assertionsAndExtensions;
        var context = contextOverride || ruleAssertionMap[rule].context;

        var selected = contextMap[context];
        var contextModified = context;
        if (!selected) {
            if (context) {
                if (context.indexOf('/')) {
                    contextModified = '//' + context;
                }
                selected = select(contextModified, xmlDoc);
            } else {
                selected = [xmlDoc];
            }
            contextMap[context] = selected;
        }

        for (var i = 0; i < assertionsAndExtensions.length; i++) {
            if (assertionsAndExtensions[i].type === 'assertion') {
                var type = assertionsAndExtensions[i].level;
                var test = assertionsAndExtensions[i].test;

                var originalTest = test;
                try {
                    test = includeExternalDocument(test, resourceDir);
                } catch (err) {
                    results.push({
                        type: type,
                        assertionId: assertionsAndExtensions[i].id,
                        test: originalTest,
                        simplifiedTest: null,
                        description: assertionsAndExtensions[i].description,
                        results: { ignored: true },
                        errorMessage: err.message
                    });
                    continue;
                }

                var simplifiedTest = null;
                if (originalTest !== test) {
                    simplifiedTest = test;
                }

                if (type === 'error' || includeWarnings) {
                    results.push({
                        type: type,
                        assertionId: assertionsAndExtensions[i].id,
                        test: originalTest,
                        simplifiedTest: simplifiedTest,
                        description: assertionsAndExtensions[i].description,
                        results: testAssertion(test, selected, select, xmlDoc, resourceDir, xmlSnippetMaxLength)
                    });
                }
            } else {
                results = results.concat(checkRule(assertionsAndExtensions[i].rule, context));
            }
        }
        return results;
    }
}
