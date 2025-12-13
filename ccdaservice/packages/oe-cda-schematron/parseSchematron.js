// jshint node:true
// jshint shadow:true
module.exports = parseSchematron;

var xpath = require('xpath');

function parseSchematron(doc) {
    // Extract data from schematron
    var schematronData = extract(doc);
    
    return schematronData;
}

function extract(doc) {
    var namespaceMap = {};
    var patternLevelMap = {};
    var patternRuleMap = {};
    var ruleAssertionMap = {};
    
    //// Namespace mapping
    var namespaces = xpath.select('//*[local-name()="ns"]', doc);
    for (var i = 0; i < namespaces.length; i++) {
        namespaceMap[namespaces[i].getAttribute('prefix')] = namespaces[i].getAttribute('uri');
    }
    
    //// Pattern to level mapping
    
    // Find errors phases
    var errorPhase = xpath.select('//*[local-name()="phase" and @id="errors"]', doc);
    
    // Store error patterns
    if (errorPhase.length) {
        for (var i = 0; i < errorPhase[0].childNodes.length; i++) {
            if (errorPhase[0].childNodes[i].localName === 'active') {
                patternLevelMap[errorPhase[0].childNodes[i].getAttribute('pattern')] = 'error';
            }
        }
    }
        
    // Find errors phases
    var warningPhase = xpath.select('//*[local-name()="phase" and @id="warnings"]', doc);
    
    // Store warning patterns
    if (warningPhase.length) {
        for (var i = 0; i < warningPhase[0].childNodes.length; i++) {
            if (warningPhase[0].childNodes[i].localName === 'active') {
                patternLevelMap[warningPhase[0].childNodes[i].getAttribute('pattern')] = 'warning';
            }
        }
    }
    
    //// Pattern to rule and rule to assertion mapping
    
    // Find patterns
    var patterns = xpath.select('//*[local-name()="pattern"]', doc);
    
    // Map patterns to rules
    for (var i = 0; i < patterns.length; i++) {
        var patternId = patterns[i].getAttribute('id');
        var defaultLevel = patternLevelMap[patternId] || 'warning';
        patternRuleMap[patternId] = [];
        var rules = xpath.select('./*[local-name()="rule"]', patterns[i]);
        for (var j = 0; j < rules.length; j++) {
            patternRuleMap[patternId].push(rules[j].getAttribute('id'));
            ruleAssertionMap[rules[j].getAttribute('id')] = {
                abstract: parseAbstract(rules[j].getAttribute('abstract')),
                context: parseContext(rules[j].getAttribute('context')),
                assertionsAndExtensions: getAssertionsAndExtensions(rules[j], defaultLevel)
            };
        }        
    }
    
    return {
        namespaceMap: namespaceMap,
        patternRuleMap: patternRuleMap,
        ruleAssertionMap: ruleAssertionMap
    };
}

function getAssertionsAndExtensions(rule, defaultLevel) {
    var assertionsAndExtensions = [];
    
    // Find and store assertions
    var assertions = xpath.select('./*[local-name()="assert"]', rule);
    for (var i = 0; i < assertions.length; i++) {
        var description = assertions[i].childNodes[0] ? assertions[i].childNodes[0].data : '';
        var level = defaultLevel;
        if (description.indexOf('SHALL') !== -1
            && (description.indexOf('SHOULD') === -1 || description.indexOf('SHALL') < description.indexOf('SHOULD'))) {
            level = 'error';
        }
        assertionsAndExtensions.push({
            type: 'assertion',
            level: level,
            id: assertions[i].getAttribute('id'),
            test: assertions[i].getAttribute('test'),
            description: description
        });
    }
    
    // Find and store extensions
    var extensions = xpath.select('./*[local-name()="extends"]', rule);
    for (var i = 0; i < extensions.length; i++) {
        assertionsAndExtensions.push({
            type: 'extension',
            rule: extensions[i].getAttribute('rule'),
        });
    }
    
    return assertionsAndExtensions;
}

function parseAbstract(str) {
    if (str === 'true' || str === 'yes') {
        return true;
    }
    return false;
}

function parseContext(str) {
    return str || null;
}