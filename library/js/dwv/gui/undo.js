// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Undo base gui.
 * @constructor
 */
dwvOemr.gui.Undo = function (app)
{
    /**
     * Setup the undo HTML.
     */
    this.setup = function ()
    {
        var paragraph = document.createElement("p");
        paragraph.appendChild(document.createTextNode("History:"));
        paragraph.appendChild(document.createElement("br"));

        var select = document.createElement("select");
        select.className = "history_list";
        select.name = "history_list";
        select.multiple = "multiple";
        paragraph.appendChild(select);

        // node
        var node = app.getElement("history");
        // clear it
        while(node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }
        // append
        node.appendChild(paragraph);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Clear the command list of the undo HTML.
     */
    this.initialise = function ()
    {
        var select = app.getElement("history_list");
        if ( select && select.length !== 0 ) {
            for( var i = select.length - 1; i >= 0; --i)
            {
                select.remove(i);
            }
        }
        // refresh
        dwvOemr.gui.refreshElement(select);
    };

    /**
     * Add a command to the undo HTML.
     * @param {String} commandName The name of the command to add.
     */
    this.addCommandToUndoHtml = function (commandName)
    {
        var select = app.getElement("history_list");
        // remove undone commands
        var count = select.length - (select.selectedIndex+1);
        if( count > 0 )
        {
            for( var i = 0; i < count; ++i)
            {
                select.remove(select.length-1);
            }
        }
        // add new option
        var option = document.createElement("option");
        option.text = commandName;
        option.value = commandName;
        select.add(option);
        // increment selected index
        select.selectedIndex++;
        // refresh
        dwvOemr.gui.refreshElement(select);
    };

    /**
     * Enable the last command of the undo HTML.
     * @param {Boolean} enable Flag to enable or disable the command.
     */
    this.enableLastInUndoHtml = function (enable)
    {
        var select = app.getElement("history_list");
        // enable or not (order is important)
        var option;
        if( enable )
        {
            // increment selected index
            select.selectedIndex++;
            // enable option
            option = select.options[select.selectedIndex];
            option.disabled = false;
        }
        else
        {
            // disable option
            option = select.options[select.selectedIndex];
            option.disabled = true;
            // decrement selected index
            select.selectedIndex--;
        }
        // refresh
        dwvOemr.gui.refreshElement(select);
    };

}; // class dwvOemr.gui.Undo
