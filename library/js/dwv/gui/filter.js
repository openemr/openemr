/* eslint-disable no-use-before-define */
/* eslint-disable no-var */

// namespaces
var dwvOemr = dwvOemr || {};
/** @namespace */
dwvOemr.gui = dwvOemr.gui || {};
/** @namespace */
dwvOemr.gui.filter = dwvOemr.gui.filter || {};
/** @namespace */
dwvOemr.gui.filter.base = dwvOemr.gui.filter.base || {};

/**
 * Filter tool base gui.
 * @constructor
 */
dwvOemr.gui.Filter = function (app) {
    const filterGuis = {};

    /**
     * Setup the filter tool HTML.
     */
    this.setup = function (list) {
        // filter select
        const filterSelector = dwvOemr.html.createHtmlSelect('filterSelect', list, 'filter');
        filterSelector.onchange = function (event) {
            // show filter gui
            for (const filterGui in filterGuis) {
                if (Object.prototype.hasOwnProperty.call(filterGuis, filterGui)) {
                    filterGuis[filterGui].display(false);
                }
            }
            filterGuis[event.currentTarget.value].display(true);
            // tell the app
            app.setImageFilter(event.currentTarget.value);
        };

        // filter list element
        const filterLi = dwvOemr.html.createHiddenElement('li', 'filterLi');
        filterLi.className += ' ui-block-b';
        filterLi.appendChild(filterSelector);

        // append element
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        dwvOemr.html.appendElement(node, filterLi);

        // create tool gui and call setup
        for (const key in list) {
            if (Object.prototype.hasOwnProperty.call(list, key)) {
                const filterClass = list[key];
                const filterGui = new dwvOemr.gui[filterClass](app);
                filterGui.setup(this.filterList);
                filterGuis[filterClass] = filterGui;
            }
        }
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag) {
        const node = app.getElement('filterLi');
        dwvOemr.html.displayElement(node, flag);

        // set selected filter
        const filterSelector = app.getElement('filterSelect');
        if (flag) {
            const firstFilter = filterSelector.options[0].value;
            filterGuis[firstFilter].display(true);
            app.setImageFilter(firstFilter);
        } else {
            const optionKeys = Object.keys(filterSelector.options);
            for (let i = 0; i < optionKeys.length; i += 1) {
                const option = filterSelector.options[optionKeys[i]];
                filterGuis[option.value].display(false);
            }
        }
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function () {
        // filter select: reset selected options
        const filterSelector = app.getElement('filterSelect');
        filterSelector.selectedIndex = 0;

        // propagate
        for (const filterGui in filterGuis) {
            if (Object.prototype.hasOwnProperty.call(filterGuis, filterGui)) {
                filterGuis[filterGui].initialise();
                filterGuis[filterGui].display(false);
            }
        }

        // refresh
        dwvOemr.gui.refreshElement(filterSelector);

        return true;
    };
}; // class dwvOemr.gui.Filter

/**
 * Threshold filter base gui.
 * @constructor
 */
dwvOemr.gui.Threshold = function (app) {
    /**
     * Threshold slider.
     * @private
     * @type Object
     */
    const slider = new dwvOemr.gui.Slider(app);

    /**
     * Setup the threshold filter HTML.
     */
    this.setup = function () {
        // threshold list element
        const thresholdLi = dwvOemr.html.createHiddenElement('li', 'thresholdLi');
        thresholdLi.className += ' ui-block-c';

        // node
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        // append threshold
        node.appendChild(thresholdLi);
        // threshold slider
        slider.append();
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Clear the threshold filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag) {
        // only initialise at display time
        // (avoids min/max calculation at startup)
        if (flag) {
            slider.initialise();
        }

        const node = app.getElement('thresholdLi');
        dwvOemr.html.displayElement(node, flag);
    };

    /**
     * Initialise the threshold filter HTML.
     */
    this.initialise = function () {
        // nothing to do
    };
}; // class dwvOemr.gui.Threshold

/**
 * Create the apply filter button.
 */
dwvOemr.gui.filter.base.createFilterApplyButton = function (app) {
    const button = document.createElement('button');
    button.id = 'runFilterButton';
    button.onclick = function () {
        app.runImageFilter();
    };
    button.setAttribute('style', 'width:100%; margin-top:0.5em;');
    button.setAttribute('class', 'ui-btn ui-btn-b');
    button.appendChild(document.createTextNode(dwv.i18n('basics.apply')));
    return button;
};

/**
 * Sharpen filter base gui.
 * @constructor
 */
dwvOemr.gui.Sharpen = function (app) {
    /**
     * Setup the sharpen filter HTML.
     */
    this.setup = function () {
        // sharpen list element
        const sharpenLi = dwvOemr.html.createHiddenElement('li', 'sharpenLi');
        sharpenLi.className += ' ui-block-c';
        sharpenLi.appendChild(dwvOemr.gui.filter.base.createFilterApplyButton(app));
        // append element
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        dwvOemr.html.appendElement(node, sharpenLi);
    };

    /**
     * Display the sharpen filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag) {
        const node = app.getElement('sharpenLi');
        dwvOemr.html.displayElement(node, flag);
    };

    this.initialise = function () {
        // nothing to do
    };
}; // class dwvOemr.gui.Sharpen

/**
 * Sobel filter base gui.
 * @constructor
 */
dwvOemr.gui.Sobel = function (app) {
    /**
     * Setup the sobel filter HTML.
     */
    this.setup = function () {
        // sobel list element
        const sobelLi = dwvOemr.html.createHiddenElement('li', 'sobelLi');
        sobelLi.className += ' ui-block-c';
        sobelLi.appendChild(dwvOemr.gui.filter.base.createFilterApplyButton(app));
        // append element
        const node = app.getElement('toolList').getElementsByTagName('ul')[0];
        dwvOemr.html.appendElement(node, sobelLi);
    };

    /**
     * Display the sobel filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag) {
        const node = app.getElement('sobelLi');
        dwvOemr.html.displayElement(node, flag);
    };

    this.initialise = function () {
        // nothing to do
    };
}; // class dwvOemr.gui.Sobel
