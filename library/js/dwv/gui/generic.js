// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Ask some text to the user.
 * @param {String} message Text to display to the user.
 * @param {String} defaultText Default value displayed in the text input field.
 * @return {String} Text entered by the user.
 */
dwvOemr.gui.prompt = function (message, defaultText)
{
    return prompt(message, defaultText);
};

/**
 * Post process a HTML table.
 * @param {Object} table The HTML table to process.
 * @return The processed HTML table.
 */
dwvOemr.gui.postProcessTable = function (table)
{
    var tableClass = table.className;
    // css
    table.className += " table-stripe ui-responsive";
    // add columntoggle
    table.setAttribute("data-role", "table");
    table.setAttribute("data-mode", "columntoggle");
    table.setAttribute("data-column-btn-text", dwv.i18n("basics.columns") + "...");
    // add priority columns for columntoggle
    var addDataPriority = function (cell) {
        var text = cell.firstChild.data;
        if ( tableClass === "tagsTable" ) {
            if ( text !== "value" && text !== "name" ) {
                cell.setAttribute("data-priority", "5");
            }
        }
        else if ( tableClass === "drawsTable" ) {
            if ( text === "description" ) {
                cell.setAttribute("data-priority", "1");
            }
            else if ( text === "frame" || text === "slice" ) {
                cell.setAttribute("data-priority", "5");
            }

        }
    };
    if (table.rows.length !== 0) {
        var hCells = table.rows.item(0).cells;
        for (var c = 0; c < hCells.length; ++c) {
            addDataPriority(hCells[c]);
        }
    }
    // return
    return table;
};

/**
 * Get a HTML element associated to a container div.
 * @param {Number} containerDivId The id of the container div.
 * @param {String} name The name or id to find.
 * @return {Object} The found element or null.
 */
dwvOemr.gui.getElement = function (containerDivId, name)
{
    // get by class in the container div
    var parent = document.getElementById(containerDivId);
    if ( !parent ) {
        return null;
    }
    var elements = parent.getElementsByClassName(name);
    // getting the last element since some libraries (ie jquery-mobile) create
    // span in front of regular tags (such as select)...
    var element = elements[elements.length-1];
    // if not found get by id with 'containerDivId-className'
    if ( typeof element === "undefined" ) {
        element = document.getElementById(containerDivId + '-' + name);
    }
    return element;
 };

/**
 * Set the selected item of a HTML select.
 * @param {String} element The HTML select element.
 * @param {String} value The value of the option to mark as selected.
 */
dwvOemr.gui.setSelected = function (element, value)
{
    if ( element ) {
        var index = 0;
        for( index in element.options){
            if( element.options[index].value === value ) {
                break;
            }
        }
        element.selectedIndex = index;
        dwvOemr.gui.refreshElement(element);
    }
};

/**
 * MetaData base gui: shows DICOM tags or file meta data.
 * @param {Object} app The associated application.
 * @constructor
 */
dwvOemr.gui.MetaData = function (app)
{
    /**
     * Update the DICOM tags table with the input info.
     * @param {Object} dataInfo The data information.
     */
    this.update = function (dataInfo)
    {
        // remove locally create meta data
        if (typeof dataInfo.InstanceNumber !== "undefined") {
            delete dataInfo.InstanceNumber;
        }

        var dataInfoArray = dataInfo;
        if (dwv.utils.isObject(dataInfo) &&
            !dwv.utils.isArray(dataInfo)) {
            dataInfoArray = dwv.utils.objectToArray(dataInfo);
        }

        // HTML node
        var node = app.getElement("tags");
        if( node === null ) {
            console.warn("Cannot find a node to append the meta data.");
            return;
        }
        // remove possible previous
        while (node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }

        // exit if no tags
        if (dataInfoArray.length === 0) {
            console.warn("No meta data tags to show.");
            return;
        }

        // tags HTML table
        var table = dwvOemr.html.toTable(dataInfoArray);
        table.className = "tagsTable";

        // optional gui specific table post process
        dwvOemr.gui.postProcessTable(table);

        // check processed table
        if (table.rows.length === 0) {
            console.warn("The processed table does not contain data.");
            return;
        }

        // translate first row
        dwvOemr.html.translateTableRow(table.rows.item(0));

        // append search form
        node.appendChild(dwvOemr.html.getHtmlSearchForm(table, "metadata-search"));
        // append tags table
        node.appendChild(table);

        // refresh
        dwvOemr.gui.refreshElement(node);
    };

}; // class dwvOemr.gui.DicomTags

/**
 * Drawing list base gui.
 * @param {Object} app The associated application.
 * @constructor
 */
dwvOemr.gui.DrawList = function (app)
{
    /**
     * Closure to self.
     */
    //var self = this;

    /**
     * Initialise.
     */
    this.init = function () {
        app.addEventListener("draw-create", update);
        app.addEventListener("draw-change", update);
        app.addEventListener("draw-delete", update);
    };

    /**
     * Update the draw list html element
     * @param {Object} event A change event, decides if the table is editable or not.
     */
    function update(event)
    {
        var isEditable = false;
        if (typeof event.editable !== "undefined") {
            isEditable = event.editable;
        }

        // HTML node
        var node = app.getElement("drawList");
        if( node === null ) {
            console.warn("Cannot find a node to append the drawing list.");
            return;
        }
        // remove possible previous
        while (node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }

        // drawing details
        var drawDisplayDetails = app.getDrawDisplayDetails();

        // exit if no details
        if (drawDisplayDetails.length === 0) {
            return;
        }

        // tags HTML table
        var table = dwvOemr.html.toTable(drawDisplayDetails);
        table.className = "drawsTable";

        // optional gui specific table post process
        dwvOemr.gui.postProcessTable(table);

        // check processed table
        if (table.rows.length === 0) {
            console.warn("The processed table does not contain data.");
            return;
        }

        // translate first row
        dwvOemr.html.translateTableRow(table.rows.item(0));

        // translate shape names
        dwvOemr.html.translateTableColumn(table, 3, "shape", "name");

        // create a color onkeyup handler
        var createColorOnKeyUp = function (details) {
            return function () {
                details.color = this.value;
                app.updateDraw(details);
            };
        };
        // create a text onkeyup handler
        var createTextOnKeyUp = function (details) {
            return function () {
                details.label = this.value;
                app.updateDraw(details);
            };
        };
        // create a long text onkeyup handler
        var createLongTextOnKeyUp = function (details) {
            return function () {
                details.description = this.value;
                app.updateDraw(details);
            };
        };
        // create a row onclick handler
        var createRowOnClick = function (slice, frame) {
            return function () {
                // update slice
                var pos = app.getViewController().getCurrentPosition();
                pos.k = slice;
                app.getViewController().setCurrentPosition(pos);
                // update frame
                app.getViewController().setCurrentFrame(frame);
                // focus on the image
                dwvOemr.gui.focusImage();
            };
        };
        // create visibility handler
        var createVisibleOnClick = function (details) {
            return function () {
                app.toogleGroupVisibility(details);
            };
        };

        // append visible column to the header row
        var row0 = table.rows.item(0);
        var cell00 = row0.insertCell(0);
        cell00.outerHTML = "<th>" + dwv.i18n("basics.visible") + "</th>";

        // loop through rows
        for (var r = 1; r < table.rows.length; ++r) {
            var drawId = r - 1;
            var drawDetails = drawDisplayDetails[drawId];
            var row = table.rows.item(r);
            var cells = row.cells;

            // loop through cells
            for (var c = 0; c < cells.length; ++c) {
                // show short ID
                if (c === 0) {
                    cells[c].firstChild.data = cells[c].firstChild.data.substring(0, 5);
                }

                if (isEditable) {
                    // color
                    if (c === 4) {
                        dwvOemr.html.makeCellEditable(cells[c], createColorOnKeyUp(drawDetails), "color");
                    }
                    // text
                    else if (c === 5) {
                        dwvOemr.html.makeCellEditable(cells[c], createTextOnKeyUp(drawDetails));
                    }
                    // long text
                    else if (c === 6) {
                        dwvOemr.html.makeCellEditable(cells[c], createLongTextOnKeyUp(drawDetails));
                    }
                }
                else {
                    // id: link to image
                    cells[0].onclick = createRowOnClick(
                        cells[1].firstChild.data,
                        cells[2].firstChild.data);
                    cells[0].onmouseover = dwvOemr.html.setCursorToPointer;
                    cells[0].onmouseout = dwvOemr.html.setCursorToDefault;
                    // color: just display the input color with no callback
                    if (c === 4) {
                        dwvOemr.html.makeCellEditable(cells[c], null, "color");
                    }
                }
            }

            // append visible column
            var cell0 = row.insertCell(0);
            var input = document.createElement("input");
            input.setAttribute("type", "checkbox");
            input.checked = app.isGroupVisible(drawDetails);
            input.onclick = createVisibleOnClick(drawDetails);
            cell0.appendChild(input);
        }

        // editable checkbox
        var tickBox = document.createElement("input");
        tickBox.setAttribute("type", "checkbox");
        tickBox.id = "checkbox-editable";
        tickBox.checked = isEditable;
        tickBox.onclick = function () { update({"editable": this.checked}); };
        // checkbox label
        var tickLabel = document.createElement("label");
        tickLabel.setAttribute( "for", tickBox.id );
        tickLabel.setAttribute( "class", "inline" );
        tickLabel.appendChild( document.createTextNode( dwv.i18n("basics.editMode") ) );
        // checkbox div
        var tickDiv = document.createElement("div");
        tickDiv.appendChild(tickLabel);
        tickDiv.appendChild(tickBox);

        // search form
        node.appendChild(dwvOemr.html.getHtmlSearchForm(table, "draw-search"));
        // tick form
        node.appendChild(tickDiv);

        // draw list table
        node.appendChild(table);

        // delete draw button
        var deleteButton = document.createElement("button");
        deleteButton.onclick = function () { app.deleteDraws(); };
        deleteButton.setAttribute( "class", "ui-btn ui-btn-inline" );
        deleteButton.appendChild( document.createTextNode( dwv.i18n("basics.deleteDraws") ) );
        if (!isEditable) {
            deleteButton.style.display = "none";
        }
        node.appendChild(deleteButton);

        // refresh
        dwvOemr.gui.refreshElement(node);
    }

}; // class dwvOemr.gui.DrawList
