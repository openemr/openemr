// namespaces
var dwvOemr = dwvOemr || {};
/** @namespace */
dwvOemr.html = dwvOemr.html || {};

/**
 * Append a cell to a given row.
 * @param {Object} row The row to append the cell to.
 * @param {Object} content The content of the cell.
 */
dwvOemr.html.appendCell = function (row, content)
{
    var cell = row.insertCell(-1);
    var str = content;
    // special care for arrays
    if ( content instanceof Array ||
            content instanceof Uint8Array || content instanceof Int8Array ||
            content instanceof Uint16Array || content instanceof Int16Array ||
            content instanceof Uint32Array ) {
        if ( content.length > 10 ) {
            content = Array.prototype.slice.call( content, 0, 10 );
            content[10] = "...";
        }
        str = Array.prototype.join.call( content, ', ' );
    } else if (dwv.utils.isObject(content)) {
        str = "";
        var keys = Object.keys(content);
        for (var i = 0; i < keys.length; ++i ) {
            var key = keys[i];
            if (str.length !== 0) {
                str += ", ";
            }
            str += key + ": " + content[key];
        }
    }
    // append
    cell.appendChild(document.createTextNode(str));
};

/**
 * Append a header cell to a given row.
 * @param {Object} row The row to append the header cell to.
 * @param {String} text The text of the header cell.
 */
dwvOemr.html.appendHCell = function (row, text)
{
    var cell = document.createElement("th");
    cell.appendChild(document.createTextNode(text));
    row.appendChild(cell);
};

/**
 * Append a row to an array.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwvOemr.html.appendRowForArray = function (table, input, level, maxLevel, rowHeader)
{
    var row = null;
    // loop through
    for ( var i=0; i<input.length; ++i ) {
        var value = input[i];
        // last level
        if ( typeof value === 'number' ||
                typeof value === 'string' ||
                value === null ||
                value === undefined ||
                level >= maxLevel ) {
            if ( !row ) {
                row = table.insertRow(-1);
            }
            dwvOemr.html.appendCell(row, value);
        }
        // more to come
        else {
            dwvOemr.html.appendRow(table, value, level+i, maxLevel, rowHeader);
        }
    }
};

/**
 * Append a row to an object.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwvOemr.html.appendRowForObject = function (table, input, level, maxLevel, rowHeader)
{
    var keys = Object.keys(input);
    var row = null;
    for ( var o=0; o<keys.length; ++o ) {
        var value = input[keys[o]];
        // last level
        if ( typeof value === 'number' ||
                typeof value === 'string' ||
                value === null ||
                value === undefined ||
                level >= maxLevel ) {
            if ( !row ) {
                row = table.insertRow(-1);
            }
            if ( o === 0 && rowHeader) {
                dwvOemr.html.appendCell(row, rowHeader);
            }
            dwvOemr.html.appendCell(row, value);
        }
        // more to come
        else {
            dwvOemr.html.appendRow(table, value, level+o, maxLevel, keys[o]);
        }
    }
    // header row
    // warn: need to create the header after the rest
    // otherwise the data will inserted in the thead...
    if ( level === 2 ) {
        var header = table.createTHead();
        var th = header.insertRow(-1);
        if ( rowHeader ) {
            dwvOemr.html.appendHCell(th, "");
        }
        for ( var k=0; k<keys.length; ++k ) {
            dwvOemr.html.appendHCell(th, keys[k]);
        }
    }
};

/**
 * Append a row to an object or an array.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwvOemr.html.appendRow = function (table, input, level, maxLevel, rowHeader)
{
    // array
    if ( input instanceof Array ) {
        dwvOemr.html.appendRowForArray(table, input, level+1, maxLevel, rowHeader);
    }
    // object
    else if ( typeof input === 'object') {
        dwvOemr.html.appendRowForObject(table, input, level+1, maxLevel, rowHeader);
    }
    else {
        throw new Error("Unsupported input data type.");
    }
};

/**
 * Converts the input to an HTML table.
 * @input {Mixed} input Allowed types are: array, array of object, object.
 * @return {Object} The created HTML table or null if the input is empty.
 * @warning Null is interpreted differently in browsers, firefox will not display it.
 */
dwvOemr.html.toTable = function (input)
{
    // check content
    if (input.length === 0) {
        return null;
    }

    var table = document.createElement('table');
    dwvOemr.html.appendRow(table, input, 0, 2);
    return table;
};

/**
 * Get an HTML search form.
 * @param {Object} htmlTableToSearch The table to do the search on.
 * @param {string} elementId The HTML element id.
 * @return {Object} The HTML search form.
 */
dwvOemr.html.getHtmlSearchForm = function (htmlTableToSearch, elementId)
{
    // input
    var input = document.createElement("input");
    input.id = elementId;
    // TODO Use new html5 search type
    //input.setAttribute("type", "search");
    input.onkeyup = function () {
        dwvOemr.html.filterTable(input, htmlTableToSearch);
    };
    // label
    var label = document.createElement("label");
    label.setAttribute("for", input.id);
    label.appendChild(document.createTextNode(dwv.i18n("basics.search") + ": "));
    // form
    var form = document.createElement("form");
    form.setAttribute("class", "filter");
    form.onsubmit = function (event) {
        event.preventDefault();
    };
    form.appendChild(label);
    form.appendChild(input);
    // return
    return form;
};

/**
 * Filter a table with a given parameter: sets the display css of rows to
 * true or false if it contains the term.
 * @param {String} term The term to filter the table with.
 * @param {Object} table The table to filter.
 */
dwvOemr.html.filterTable = function (term, table) {
    // de-highlight
    dwvOemr.html.dehighlight(table);
    // split search terms
    var terms = term.value.toLowerCase().split(" ");

    // search
    var text = 0;
    var display = 0;
    for (var r = 1; r < table.rows.length; ++r) {
        display = '';
        for (var i = 0; i < terms.length; ++i) {
            text = table.rows[r].innerHTML.replace(/<[^>]+>/g, "").toLowerCase();
            if (text.indexOf(terms[i]) < 0) {
                display = 'none';
            } else {
                if (terms[i].length) {
                    dwvOemr.html.highlight(terms[i], table.rows[r]);
                }
            }
            table.rows[r].style.display = display;
        }
    }
};

/**
 * Transform back each
 * 'preText <span class="highlighted">term</span> postText'
 * into its original 'preText term postText'.
 * @param {Object} container The container to de-highlight.
 */
dwvOemr.html.dehighlight = function (container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];

        if (node.attributes &&
                node.attributes['class'] &&
                node.attributes['class'].value === 'highlighted') {
            node.parentNode.parentNode.replaceChild(
                    document.createTextNode(
                        node.parentNode.innerHTML.replace(/<[^>]+>/g, "")),
                    node.parentNode);
            // Stop here and process next parent
            return;
        } else if (node.nodeType !== 3) {
            // Keep going onto other elements
            dwvOemr.html.dehighlight(node);
        }
    }
};

/**
 * Create a
 * 'preText <span class="highlighted">term</span> postText'
 * around each search term.
 * @param {String} term The term to highlight.
 * @param {Object} container The container where to highlight the term.
 */
dwvOemr.html.highlight = function (term, container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];

        if (node.nodeType === 3) {
            // Text node
            var data = node.data;
            var data_low = data.toLowerCase();
            if (data_low.indexOf(term) >= 0) {
                //term found!
                var new_node = document.createElement('span');
                node.parentNode.replaceChild(new_node, node);

                var result;
                while ((result = data_low.indexOf(term)) !== -1) {
                    // before term
                    new_node.appendChild(document.createTextNode(
                                data.substr(0, result)));
                    // term
                    new_node.appendChild(dwvOemr.html.createHighlightNode(
                                document.createTextNode(data.substr(
                                        result, term.length))));
                    // reduce search string
                    data = data.substr(result + term.length);
                    data_low = data_low.substr(result + term.length);
                }
                new_node.appendChild(document.createTextNode(data));
            }
        } else {
            // Keep going onto other elements
            dwvOemr.html.highlight(term, node);
        }
    }
};

/**
 * Highlight a HTML node.
 * @param {Object} child The child to highlight.
 * @return {Object} The created HTML node.
 */
dwvOemr.html.createHighlightNode = function (child) {
    var node = document.createElement('span');
    node.setAttribute('class', 'highlighted');
    node.attributes['class'].value = 'highlighted';
    node.appendChild(child);
    return node;
};

/**
 * Remove all children of a HTML node.
 * @param {Object} node The node to remove kids.
 */
dwvOemr.html.cleanNode = function (node) {
    // remove its children if node exists
    if ( !node ) {
        return;
    }
    while (node.hasChildNodes()) {
        node.removeChild(node.firstChild);
    }
};

/**
 * Remove a HTML node and all its children.
 * @param {String} nodeId The string id of the node to delete.
 */
dwvOemr.html.removeNode = function (node) {
    // check node
    if ( !node ) {
        return;
    }
    // remove its children
    dwvOemr.html.cleanNode(node);
    // remove it from its parent
    var top = node.parentNode;
    top.removeChild(node);
};

/**
 * Remove a list of HTML nodes and all their children.
 * @param {Array} nodes The list of nodes to delete.
 */
dwvOemr.html.removeNodes = function (nodes) {
    for ( var i = 0; i < nodes.length; ++i ) {
        dwvOemr.html.removeNode(nodes[i]);
    }
};

/**
 * Translate the content of an HTML row.
 * @param {Object} row The HTML row to parse.
 * @param {String} i18nPrefix The i18n prefix to use to find the translation.
 */
dwvOemr.html.translateTableRow = function (row, i18nPrefix) {
    var prefix = (typeof i18nPrefix === "undefined") ? "basics" : i18nPrefix;
    if (prefix.length !== 0) {
        prefix += ".";
    }
    var cells = row.cells;
    for (var c = 0; c < cells.length; ++c) {
        var text = cells[c].firstChild.data;
        cells[c].firstChild.data = dwv.i18n( prefix + text );
    }
};

/**
 * Translate the content of an HTML column.
 * @param {Object} table The HTML table to parse.
 * @param {Number} columnNumber The number of the column to translate.
 * @param {String} i18nPrefix The i18n prefix to use to find the translation.
 * @param {String} i18nSuffix The i18n suffix to use to find the translation.
 */
dwvOemr.html.translateTableColumn = function (table, columnNumber, i18nPrefix, i18nSuffix) {
    var prefix = (typeof i18nPrefix === "undefined") ? "basics" : i18nPrefix;
    if (prefix.length !== 0) {
        prefix += ".";
    }
    var suffix = (typeof i18nSuffix === "undefined") ? "" : i18nSuffix;
    if (suffix.length !== 0) {
        suffix = "." + suffix;
    }
    if (table.rows.length !== 0) {
        for (var r = 1; r < table.rows.length; ++r) {
            var cells = table.rows.item(r).cells;
            if (cells.length >= columnNumber) {
                var text = cells[columnNumber].firstChild.data;
                cells[columnNumber].firstChild.data = dwv.i18n( prefix + text + suffix );
            }
        }
    }
};

/**
 * Make a HTML table cell editable by putting its content inside an input element.
 * @param {Object} cell The cell to make editable.
 * @param {Function} onchange The callback to call when cell's content is changed.
 *    if set to null, the HTML input will be disabled.
 * @param {String} inputType The type of the HTML input, default to 'text'.
 */
dwvOemr.html.makeCellEditable = function (cell, onchange, inputType) {
    // check event
    if (typeof cell === "undefined" ) {
        console.warn("Cannot create input for non existing cell.");
        return;
    }
    // HTML input
    var input = document.createElement("input");
    // handle change
    if (onchange) {
        input.onchange = onchange;
    }
    else {
        input.disabled = true;
    }
    // set input value
    input.value = cell.firstChild.data;
    // input type
    if (typeof inputType === "undefined" ||
        (inputType === "color" && !dwvOemr.browser.hasInputColor() ) ) {
        input.type = "text";
    }
    else {
        input.type = inputType;
    }

    // clean cell
    dwvOemr.html.cleanNode(cell);

    // HTML form
    var form = document.createElement("form");
    form.onsubmit = function (event) {
        event.preventDefault();
    };
    form.appendChild(input);
    // add form to cell
    cell.appendChild(form);
};

/**
 * Set the document cursor to 'pointer'.
 */
dwvOemr.html.setCursorToPointer = function () {
    document.body.style.cursor = 'pointer';
};

/**
 * Set the document cursor to 'default'.
 */
dwvOemr.html.setCursorToDefault = function () {
    document.body.style.cursor = 'default';
};


/**
 * Create a HTML select from an input array of options.
 * The values of the options are the name of the option made lower case.
 * It is left to the user to set the 'onchange' method of the select.
 * @param {String} name The name of the HTML select.
 * @param {Mixed} list The list of options of the HTML select.
 * @param {String} i18nPrefix An optional namespace prefix to find the translation values.
 * @param {Bool} i18nSafe An optional flag to check translation existence.
 * @return {Object} The created HTML select.
 */
dwvOemr.html.createHtmlSelect = function (name, list, i18nPrefix, i18nSafe) {
    // select
    var select = document.createElement("select");
    //select.name = name;
    select.className = name;
    var prefix = (typeof i18nPrefix === "undefined") ? "" : i18nPrefix + ".";
    var safe = (typeof i18nSafe === "undefined") ? false : true;
    var getText = function(value) {
        var key = prefix + value + ".name";
        var text = "";
        if (safe) {
            if (dwv.i18nExists(key)) {
                text = dwv.i18n(key);
            }
            else {
                text = value;
            }
        }
        else {
            text = dwv.i18n(key);
        }
        return text;
    };
    // options
    var option;
    if ( list instanceof Array )
    {
        for ( var i in list ) {
            if (Object.prototype.hasOwnProperty.call(list, i)) {
                option = document.createElement("option");
                option.value = list[i];
                option.appendChild(document.createTextNode(getText(list[i])));
                select.appendChild(option);
            }
        }
    }
    else if ( typeof list === 'object')
    {
        for ( var item in list )
        {
            option = document.createElement("option");
            option.value = item;
            option.appendChild(document.createTextNode(getText(item)));
            select.appendChild(option);
        }
    }
    else
    {
        throw new Error("Unsupported input list type.");
    }
    return select;
};

/**
 * Display or not an element.
 * @param {Object} element The HTML element to display.
 * @param {Boolean} flag True to display the element.
 */
dwvOemr.html.displayElement = function (element, flag)
{
    element.style.display = flag ? "" : "none";
};

/**
 * Toggle the display of an element.
 * @param {Object} element The HTML element to display.
 */
dwvOemr.html.toggleDisplay = function (element)
{
    if ( element.style.display === "none" ) {
        element.style.display = '';
    }
    else {
        element.style.display = "none";
    }
};

/**
 * Append an element.
 * @param {Object} parent The HTML element to append to.
 * @param {Object} element The HTML element to append.
 */
dwvOemr.html.appendElement = function (parent, element)
{
    // append
    parent.appendChild(element);
    // refresh
    dwvOemr.gui.refreshElement(parent);
};

/**
 * Create an element.
 * @param {String} type The type of the elemnt.
 * @param {String} className The className of the element.
 */
dwvOemr.html.createHiddenElement = function (type, className)
{
    var element = document.createElement(type);
    element.className = className;
    // hide by default
    element.style.display = "none";
    // return
    return element;
};
