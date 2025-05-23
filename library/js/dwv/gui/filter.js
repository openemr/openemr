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
dwvOemr.gui.Filter = function (app)
{
    var filterGuis = {};

    /**
     * Setup the filter tool HTML.
     */
    this.setup = function (list)
    {
        // filter select
        var filterSelector = dwvOemr.html.createHtmlSelect("filterSelect", list, "filter");
        filterSelector.onchange = function (event) {
            // show filter gui
            for ( var filterGui in filterGuis ) {
                filterGuis[filterGui].display(false);
            }
            filterGuis[event.currentTarget.value].display(true);
            // tell the app
            app.setImageFilter(event.currentTarget.value);
        };

        // filter list element
        var filterLi = dwvOemr.html.createHiddenElement("li", "filterLi");
        filterLi.className += " ui-block-b";
        filterLi.appendChild(filterSelector);

        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwvOemr.html.appendElement(node, filterLi);

        // create tool gui and call setup
        for ( var key in list ) {
            var filterClass = list[key];
            var filterGui = new dwvOemr.gui[filterClass](app);
            filterGui.setup(this.filterList);
            filterGuis[filterClass] = filterGui;
        }

    };

    /**
     * Display the tool HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("filterLi");
        dwvOemr.html.displayElement(node, flag);

        // set selected filter
        var filterSelector = app.getElement("filterSelect");
        if (flag) {
            var firstFilter = filterSelector.options[0].value;
            filterGuis[firstFilter].display(true);
            app.setImageFilter(firstFilter);
        } else {
            var optionKeys = Object.keys(filterSelector.options);
            for (var i = 0; i < optionKeys.length; ++i) {
                var option = filterSelector.options[optionKeys[i]];
                filterGuis[option.value].display(false);
            }
        }
    };

    /**
     * Initialise the tool HTML.
     * @returns Boolean True if the tool can be shown.
     */
    this.initialise = function ()
    {
        // filter select: reset selected options
        var filterSelector = app.getElement("filterSelect");
        filterSelector.selectedIndex = 0;

        // propagate
        for ( var filterGui in filterGuis ) {
            filterGuis[filterGui].initialise();
            filterGuis[filterGui].display(false);
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
dwvOemr.gui.Threshold = function (app)
{
    /**
     * Threshold slider.
     * @private
     * @type Object
     */
    var slider = new dwvOemr.gui.Slider(app);

    /**
     * Setup the threshold filter HTML.
     */
    this.setup = function ()
    {
        // threshold list element
        var thresholdLi = dwvOemr.html.createHiddenElement("li", "thresholdLi");
        thresholdLi.className += " ui-block-c";

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
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
    this.display = function (flag)
    {
        // only initialise at display time
        // (avoids min/max calculation at startup)
        if (flag) {
            slider.initialise();
        }

        var node = app.getElement("thresholdLi");
        dwvOemr.html.displayElement(node, flag);
    };

    /**
     * Initialise the threshold filter HTML.
     */
    this.initialise = function ()
    {
        // nothing to do
    };

}; // class dwvOemr.gui.Threshold

/**
 * Create the apply filter button.
 */
dwvOemr.gui.filter.base.createFilterApplyButton = function (app)
{
    var button = document.createElement("button");
    button.id = "runFilterButton";
    button.onclick = function (/*event*/) {
        app.runImageFilter();
    };
    button.setAttribute("style","width:100%; margin-top:0.5em;");
    button.setAttribute("class","ui-btn ui-btn-b");
    button.appendChild(document.createTextNode(dwv.i18n("basics.apply")));
    return button;
};

/**
 * Sharpen filter base gui.
 * @constructor
 */
dwvOemr.gui.Sharpen = function (app)
{
    /**
     * Setup the sharpen filter HTML.
     */
    this.setup = function ()
    {
        // sharpen list element
        var sharpenLi = dwvOemr.html.createHiddenElement("li", "sharpenLi");
        sharpenLi.className += " ui-block-c";
        sharpenLi.appendChild( dwvOemr.gui.filter.base.createFilterApplyButton(app) );
        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwvOemr.html.appendElement(node, sharpenLi);
    };

    /**
     * Display the sharpen filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("sharpenLi");
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
dwvOemr.gui.Sobel = function (app)
{
    /**
     * Setup the sobel filter HTML.
     */
    this.setup = function ()
    {
        // sobel list element
        var sobelLi = dwvOemr.html.createHiddenElement("li", "sobelLi");
        sobelLi.className += " ui-block-c";
        sobelLi.appendChild( dwvOemr.gui.filter.base.createFilterApplyButton(app) );
        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwvOemr.html.appendElement(node, sobelLi);
    };

    /**
     * Display the sobel filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("sobelLi");
        dwvOemr.html.displayElement(node, flag);
    };

    this.initialise = function () {
        // nothing to do
    };

}; // class dwvOemr.gui.Sobel
