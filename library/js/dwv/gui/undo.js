/* eslint-disable no-use-before-define */
/* eslint-disable no-var */

// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Undo base gui.
 * @constructor
 */
dwvOemr.gui.Undo = function (app) {
    /**
     * Setup the undo HTML.
     */
    this.setup = function () {
        const paragraph = document.createElement('p');
        paragraph.appendChild(document.createTextNode('History:'));
        paragraph.appendChild(document.createElement('br'));

        const select = document.createElement('select');
        select.className = 'history_list';
        select.name = 'history_list';
        select.multiple = 'multiple';
        paragraph.appendChild(select);

        // node
        const node = app.getElement('history');
        // clear it
        while (node.hasChildNodes()) {
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
    this.initialise = function () {
        const select = app.getElement('history_list');
        if (select && select.length !== 0) {
            for (let i = select.length - 1; i >= 0; i -= 1) {
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
    this.addCommandToUndoHtml = function (commandName) {
        const select = app.getElement('history_list');
        // remove undone commands
        const count = select.length - (select.selectedIndex + 1);
        if (count > 0) {
            for (let i = 0; i < count; i += 1) {
                select.remove(select.length - 1);
            }
        }
        // add new option
        const option = document.createElement('option');
        option.text = commandName;
        option.value = commandName;
        select.add(option);
        // increment selected index
        select.selectedIndex += 1;
        // refresh
        dwvOemr.gui.refreshElement(select);
    };

    /**
     * Enable the last command of the undo HTML.
     * @param {Boolean} enable Flag to enable or disable the command.
     */
    this.enableLastInUndoHtml = function (enable) {
        const select = app.getElement('history_list');
        // enable or not (order is important)
        let option;
        if (enable) {
            // increment selected index
            select.selectedIndex += 1;
            // enable option
            option = select.options[select.selectedIndex];
            option.disabled = false;
        } else {
            // disable option
            option = select.options[select.selectedIndex];
            option.disabled = true;
            // decrement selected index
            select.selectedIndex -= 1;
        }
        // refresh
        dwvOemr.gui.refreshElement(select);
    };
}; // class dwvOemr.gui.Undo
