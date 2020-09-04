/* eslint-disable no-use-before-define */
/* eslint-disable no-var */

// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Toolbox base gui.
 * @constructor
 */
dwvOemr.gui.Toolbox = function (app) {
    let toolGuis = {};

    /**
     * Setup the toolbox HTML.
     * @param {Object} list The tool list
     */
    this.setup = function (list) {
        // tool select
        const toolSelector = dwvOemr.html.createHtmlSelect('toolSelect', list, 'tool');
        toolSelector.onchange = function (event) {
            // tell the app
            app.setTool(event.currentTarget.value);
            // show tool gui
            for (const gui in toolGuis) {
                if (Object.prototype.hasOwnProperty.call(toolGuis, gui)) {
                    toolGuis[gui].display(false);
                }
            }
            toolGuis[event.currentTarget.value].display(true);
        };

        // tool list element
        const toolLi = document.createElement('li');
        toolLi.className = 'toolLi ui-block-a';
        toolLi.style.display = 'none';
        toolLi.appendChild(toolSelector);

        // tool ul
        const toolUl = document.createElement('ul');
        toolUl.appendChild(toolLi);
        toolUl.className = 'ui-grid-b';

        // node
        const node = app.getElement('toolList');
        // append
        node.appendChild(toolUl);
        // refresh
        dwvOemr.gui.refreshElement(node);

        // create tool gui and call setup
        toolGuis = [];
        for (const key in list) {
            if (Object.prototype.hasOwnProperty.call(list, key)) {
                const guiClass = key;
                let gui = null;
                if (guiClass === 'Livewire') {
                    gui = new dwvOemr.gui.ColourTool(app, 'lw');
                } else if (guiClass === 'Floodfill') {
                    gui = new dwvOemr.gui.ColourTool(app, 'ff');
                } else {
                    if (typeof dwvOemr.gui[guiClass] === 'undefined') {
                        console.warn(`Could not create unknown loader gui: ${guiClass}`);
                        continue;
                    }
                    gui = new dwvOemr.gui[guiClass](app);
                }

                if (guiClass === 'Filter' || guiClass === 'Draw') {
                    gui.setup(list[key].options);
                } else {
                    gui.setup();
                }
                // store
                toolGuis[guiClass] = gui;
            }
        }
    };

    /**
     * Display the toolbox HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // tool list element
        const node = app.getElement('toolLi');
        dwvOemr.html.displayElement(node, bool);
    };

    /**
     * Initialise the toolbox HTML.
     */
    this.initialise = function () {
        // tool select: reset selected option
        const selector = app.getElement('toolSelect');

        // propagate and check if tool can be displayed
        const displays = [];
        let first = true;
        for (const guiClass in toolGuis) {
            if (Object.prototype.hasOwnProperty.call(toolGuis, guiClass)) {
                toolGuis[guiClass].display(false);
                const canInit = toolGuis[guiClass].initialise();
                // activate first tool
                if (canInit && first) {
                    app.setTool(guiClass);
                    toolGuis[guiClass].display(true);
                    first = false;
                }
                // store state
                displays.push(canInit);
            }
        }

        // update list display according to gui states
        const { options } = selector;
        let selectedIndex = -1;
        for (let i = 0; i < options.length; i += 1) {
            if (!displays[i]) {
                options[i].style.display = 'none';
            } else {
                if (selectedIndex === -1) {
                    selectedIndex = i;
                }
                options[i].style.display = '';
            }
        }
        selector.selectedIndex = selectedIndex;

        // refresh
        dwvOemr.gui.refreshElement(selector);
    };
}; // dwvOemr.gui.Toolbox

function capitalizeFirstLetter(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * WindowLevel tool base gui.
 * @constructor
 */
dwvOemr.gui.WindowLevel = function (app) {
    /**
     * Setup the tool HTML.
     */
    this.setup = function () {
        // preset select
        const wlSelector = dwvOemr.html.createHtmlSelect('presetSelect', []);
        wlSelector.onchange = function (event) {
            app.setWindowLevelPreset(event.currentTarget.value);
        };
        // colour map select
        const cmSelector = dwvOemr.html.createHtmlSelect('colourMapSelect', dwv.tool.colourMaps, 'colourmap');
        cmSelector.onchange = function (event) {
            app.setColourMap(event.currentTarget.value);
        };

        // preset list element
        const wlLi = document.createElement('li');
        wlLi.className = 'wlLi ui-block-b';
        wlLi.style.display = 'none';
        wlLi.appendChild(wlSelector);
        // colour map list element
        const cmLi = document.createElement('li');
        cmLi.className = 'cmLi ui-block-c';
        cmLi.style.display = 'none';
        cmLi.appendChild(cmSelector);

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // append preset
        node.appendChild(wlLi);
        // append colour map
        node.appendChild(cmLi);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // presets list element
        let node = app.getElement('wlLi');
        dwvOemr.html.displayElement(node, bool);
        // colour map list element
        node = app.getElement('cmLi');
        dwvOemr.html.displayElement(node, bool);

        const onAddPreset = function (event) {
            const wlSelector = app.getElement('presetSelect');
            // add preset
            wlSelector.add(new Option(capitalizeFirstLetter(event.name), event.name));
            // set as selected
            wlSelector.selectedIndex = wlSelector.options.length - 1;
            // refresh
            dwvOemr.gui.refreshElement(wlSelector);
        };

        if (bool) {
            app.addEventListener('wl-preset-add', onAddPreset);
        } else {
            app.removeEventListener('wl-preset-add', onAddPreset);
        }
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        if (!app.canWindowLevel()) {
            return false;
        }

        // create new preset select
        const wlSelector = dwvOemr.html.createHtmlSelect('presetSelect',
            app.getViewController().getWindowLevelPresetsNames(), 'wl.presets', true);
        wlSelector.onchange = function (event) {
            app.setWindowLevelPreset(event.currentTarget.value);
        };
        wlSelector.title = 'Select w/l preset.';

        // copy html list
        const wlLi = app.getElement('wlLi');
        // clear node
        dwvOemr.html.cleanNode(wlLi);
        // add children
        wlLi.appendChild(wlSelector);
        // refresh
        dwvOemr.gui.refreshElement(wlLi);

        // colour map select
        const cmSelector = app.getElement('colourMapSelect');
        cmSelector.selectedIndex = 0;
        // special monochrome1 case
        if (app.getImage().getPhotometricInterpretation() === 'MONOCHROME1') {
            cmSelector.selectedIndex = 1;
        }
        // refresh
        dwvOemr.gui.refreshElement(cmSelector);

        return true;
    };
}; // class dwvOemr.gui.WindowLevel

/**
 * Draw tool base gui.
 * @constructor
 */
dwvOemr.gui.Draw = function (app) {
    // default colours
    const colours = ['Yellow', 'Red', 'White', 'Green', 'Blue', 'Lime', 'Fuchsia', 'Black'];
    /**
     * Get the default colour.
     */
    this.getDefaultColour = function () {
        if (dwvOemr.browser.hasInputColor()) {
            return '#FFFF80';
        }
        return colours[0];
    };

    /**
     * Setup the tool HTML.
     */
    this.setup = function (shapeList) {
        // shape select
        const shapeSelector = dwvOemr.html.createHtmlSelect('shapeSelect', shapeList, 'shape');
        shapeSelector.onchange = function (event) {
            app.setDrawShape(event.currentTarget.value);
        };
        // colour select
        let colourSelector = null;
        if (dwvOemr.browser.hasInputColor()) {
            colourSelector = document.createElement('input');
            colourSelector.className = 'colourSelect';
            colourSelector.type = 'color';
            colourSelector.value = '#FFFF80';
        } else {
            colourSelector = dwvOemr.html.createHtmlSelect('colourSelect', colours, 'colour');
        }
        colourSelector.onchange = function (event) {
            app.setDrawLineColour(event.currentTarget.value);
        };

        // shape list element
        const shapeLi = document.createElement('li');
        shapeLi.className = 'shapeLi ui-block-c';
        shapeLi.style.display = 'none';
        shapeLi.appendChild(shapeSelector);

        // colour list element
        const colourLi = document.createElement('li');
        colourLi.className = 'colourLi ui-block-b';
        colourLi.style.display = 'none';
        colourLi.appendChild(colourSelector);

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // apend shape
        node.appendChild(shapeLi);
        // append colour
        node.appendChild(colourLi);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // colour list element
        let node = app.getElement('colourLi');
        dwvOemr.html.displayElement(node, bool);
        // shape list element
        node = app.getElement('shapeLi');
        dwvOemr.html.displayElement(node, bool);

        // set selected shape
        if (bool) {
            const shapeSelector = app.getElement('shapeSelect');
            app.setDrawShape(shapeSelector.options[0].value);
        }
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        // shape select: reset selected option
        const shapeSelector = app.getElement('shapeSelect');
        shapeSelector.selectedIndex = 0;
        // refresh
        dwvOemr.gui.refreshElement(shapeSelector);

        // colour select: reset selected option
        const colourSelector = app.getElement('colourSelect');
        if (!dwvOemr.browser.hasInputColor()) {
            colourSelector.selectedIndex = 0;
        }
        // refresh
        dwvOemr.gui.refreshElement(colourSelector);

        return true;
    };
}; // class dwvOemr.gui.Draw

/**
 * Base gui for a tool with a colour setting.
 * @constructor
 */
dwvOemr.gui.ColourTool = function (app, prefix) {
    // default colours
    const colours = ['Yellow', 'Red', 'White', 'Green', 'Blue', 'Lime', 'Fuchsia', 'Black'];
    // colour selector class
    const colourSelectClassName = `${prefix}ColourSelect`;
    // colour selector class
    const colourLiClassName = `${prefix}ColourLi`;

    /**
     * Get the default colour.
     */
    this.getDefaultColour = function () {
        if (dwvOemr.browser.hasInputColor()) {
            return '#FFFF80';
        }
        return colours[0];
    };

    /**
     * Setup the tool HTML.
     */
    this.setup = function () {
        // colour select
        let colourSelector = null;
        if (dwvOemr.browser.hasInputColor()) {
            colourSelector = document.createElement('input');
            colourSelector.className = colourSelectClassName;
            colourSelector.type = 'color';
            colourSelector.value = '#FFFF80';
        } else {
            colourSelector = dwvOemr.html.createHtmlSelect(colourSelectClassName, colours, 'colour');
        }
        colourSelector.onchange = function (event) {
            app.setDrawLineColour(event.currentTarget.value);
        };

        // colour list element
        const colourLi = document.createElement('li');
        colourLi.className = `${colourLiClassName} ui-block-b`;
        colourLi.style.display = 'none';
        colourLi.appendChild(colourSelector);

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // apend colour
        node.appendChild(colourLi);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // colour list
        const node = app.getElement(colourLiClassName);
        dwvOemr.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        const colourSelector = app.getElement(colourSelectClassName);
        if (!dwvOemr.browser.hasInputColor()) {
            colourSelector.selectedIndex = 0;
        }
        dwvOemr.gui.refreshElement(colourSelector);

        return true;
    };
}; // class dwvOemr.gui.ColourTool

/**
 * ZoomAndPan tool base gui.
 * @constructor
 */
dwvOemr.gui.ZoomAndPan = function (app) {
    /**
     * Setup the tool HTML.
     */
    this.setup = function () {
        // reset button
        const button = document.createElement('button');
        button.className = 'zoomResetButton';
        button.name = 'zoomResetButton';
        button.onclick = function () {
            app.resetZoom();
        };
        button.setAttribute('style', 'width:100%; margin-top:0.5em;');
        button.setAttribute('class', 'ui-btn ui-btn-b');
        const text = document.createTextNode(dwv.i18n('basics.reset'));
        button.appendChild(text);

        // list element
        const liElement = document.createElement('li');
        liElement.className = 'zoomLi ui-block-c';
        liElement.style.display = 'none';
        liElement.appendChild(button);

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // append element
        node.appendChild(liElement);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // display list element
        const node = app.getElement('zoomLi');
        dwvOemr.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        return true;
    };
}; // class dwvOemr.gui.ZoomAndPan

/**
 * Scroll tool base gui.
 * @constructor
 */
dwvOemr.gui.Scroll = function (app) {
    /**
     * Setup the tool HTML.
     */
    this.setup = function () {
        // list element
        const liElement = document.createElement('li');
        liElement.className = 'scrollLi ui-block-c';
        liElement.style.display = 'none';

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // append element
        node.appendChild(liElement);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool) {
        // display list element
        const node = app.getElement('scrollLi');
        dwvOemr.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        return app.canScroll();
    };
}; // class dwvOemr.gui.Scroll
