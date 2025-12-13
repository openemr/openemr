// jshint node:true
// jshint shadow:true
module.exports = testAssertion;

function testAssertion(test, selected, select, xmlDoc, resourceDir, xmlSnippetMaxLength) {
    var results = [];
    
    for (var i = 0; i < selected.length; i++) {
        try {
            var result = select('boolean(' + test + ')', selected[i]);
            var lineNumber = null;
            var xmlSnippet = null;
            if (selected[i].lineNumber) {
                lineNumber = selected[i].lineNumber;
                xmlSnippet = selected[i].toString();
            }
            var maxLength = (xmlSnippetMaxLength || 1e308);
            if (xmlSnippet && xmlSnippet.length > maxLength) {
                xmlSnippet = xmlSnippet.slice(0, maxLength) + '...';
            }
            results.push({ result: result, line: lineNumber, path: getXPath(selected[i]), xml: xmlSnippet });
        }
        catch (err) {
            return { ignored: true, errorMessage: err.message };
        }
    }
    
    for (var i = 0; i < results.length; i++) {
        if (results[i].result !== true && results[i].result !== false) {
            return { ignored: true, errorMessage: 'Test returned non-boolean result' };
        }
    }
    return results;
}

function getXPath(node, path) {    
    var top = !path ? true : false;
    path = path || [];
    if (node.parentNode) {
        path = getXPath(node.parentNode, path);
    }

    var count = 1;
    if (node.previousSibling) {
        var sibling = node.previousSibling;
        do {
            if (sibling.nodeType === 1 && sibling.nodeName === node.nodeName) {
                count++;
            }
            sibling = sibling.previousSibling;
        } while (sibling);
        if (count === 1) {
            count = null;
        }
    }
    else if (node.nextSibling) {
        var sibling = node.nextSibling;
        do {
            if (sibling.nodeType === 1 && sibling.nodeName === node.nodeName) {
                var count = 1;
                sibling = null;
            } else {
                var count = null;
                sibling = sibling.previousSibling;
            }
        } while (sibling);
    }

    if (node.nodeType === 1) {
        path.push(node.nodeName + ('[' + (count || 1) + ']'));
    }
    return top ? '/' + path.join('/') : path;
}