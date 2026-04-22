// jshint node:true
// jshint shadow:true
module.exports = modifyTest;

var fs = require('fs');
var xpath = require('xpath');
const { DOMParser, XMLSerializer } = require('@xmldom/xmldom');
var path_module = require('path');

var loadedExternalDocuments = {};

function modifyTest(test, resourceDir) {
    var matches = /=document(('[-_.A-Za-z0-9]+'|"[-_.A-Za-z0-9]+"))/.exec(test);
    while (matches) {

        // String processing to select the non-regular predicate expression
        var equalInd = test.indexOf(matches[0]);
        var start = equalInd;
        var bracketDepth = 0;
        for (var i = equalInd; i >= 0; i--) {
            if (!bracketDepth && (test[i] === '[' || test[i] === ' ')) {
                start = i + 1;
                break;
            }
            if (test[i] === ']') {
                bracketDepth++;
            } else if (test[i] === '[') {
                bracketDepth--;
            }
        }

        var end = test.length;
        bracketDepth = 0;
        for (var i = start + matches[0].length; i < test.length; i++) {
            if (!bracketDepth && (test[i] === ']' || test[i] === ' ')) {
                end = i;
                break;
            }
            if (test[i] === '[') {
                bracketDepth++;
            } else if (test[i] === ']') {
                bracketDepth--;
            }
        }

        var predicate = test.slice(start, end);

        // Load external doc (load from "cache" if already loaded)
        var filepath = matches[1].slice(1, -1);
        var externalDoc;
        if (!loadedExternalDocuments[filepath]) {
            var externalXml = null;
            try {
                externalXml = fs.readFileSync(path_module.join(resourceDir, filepath), 'utf-8').toString();
            } catch (err) {
                throw new Error("No such file '" + filepath + "'");
            }
            externalDoc = new DOMParser().parseFromString(externalXml, 'text/xml');
            loadedExternalDocuments[filepath] = externalDoc;
        } else {
            externalDoc = loadedExternalDocuments[filepath];
        }

        var externalXpath = test.slice(equalInd + matches[0].length, end);

        // Extract namespaces
        var match = /^([a-zA-Z_][\w\-.]*):/.exec(externalXpath);
        var defaultNamespaceKey = match ? match[1] : '';
        var externalNamespaceMap = externalDoc.lastChild._nsMap;
        var namespaceMap = {};
        for (var key in externalNamespaceMap) {
            if (Object.prototype.hasOwnProperty.call(externalNamespaceMap, key)) {
                if (key) {
                    namespaceMap[key] = externalNamespaceMap[key];
                }
            }
        }
        namespaceMap[defaultNamespaceKey] = externalNamespaceMap[''];

        var externalSelect = xpath.useNamespaces(namespaceMap);

        // Create new predicate from extracted values
        var values = [];
        var externalResults = externalSelect(externalXpath, externalDoc);
        for (var i = 0; i < externalResults.length; i++) {
            values.push(externalResults[i].value);
        }
        var lhv = predicate.slice(0, predicate.indexOf('=document('));
        var newPredicate = '(';
        for (var i = 0; i < values.length; i++) {
            newPredicate += lhv + "='" + values[i] + "'";
            if (i < values.length - 1) {
                newPredicate += ' or ';
            }
        }
        newPredicate += ')';

        // Replace test
        test = test.slice(0, start) + newPredicate + test.slice(end);

        matches = /@[^\\[\]]+=document(('[-_.A-Za-z0-9]+'|"[-_.A-Za-z0-9]+"))/.exec(test);
    }

    return test;
}
