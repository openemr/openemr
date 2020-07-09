// namespaces
var dwvOemr = dwvOemr || {};
/** @namespace */
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Display a progress value.
 * @param {Number} percent The progress percentage.
 */
dwvOemr.gui.displayProgress = function (percent) {
    if (percent <= 100) {
        var elem = document.getElementById("progressbar");
        elem.style.width = percent + "%";
    }
};

/**
 * Focus the view on the image.
 */
dwvOemr.gui.focusImage = function () {
    // does nothing
};

/**
 * Refresh a HTML element.
 * @param {String} element The HTML element to refresh.
 */
dwvOemr.gui.refreshElement = function (/*element*/) {
    // does nothing
};

/**
 * Slider base gui.
 * @constructor
 */
dwvOemr.gui.Slider = function (app) {
    /**
     * Append the slider HTML.
     */
    this.append = function () {
        // nothing to do
    };

    /**
     * Initialise the slider HTML.
     */
    this.initialise = function () {
        var min = app.getImage().getDataRange().min;
        var max = app.getImage().getDataRange().max;

        // jquery-ui slider
        $(".thresholdLi").slider({
            range: true,
            min: min,
            max: max,
            values: [min, max],
            slide: function (event, ui) {
                app.setFilterMinMax(
                    {'min': ui.values[0], 'max': ui.values[1]});
            }
        });
    };

}; // class dwvOemr.gui.Slider
