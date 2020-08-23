// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};
dwvOemr.gui.info = dwvOemr.gui.info || {};

/**
 * Plot info layer.
 * @constructor
 * @param {Object} div The HTML element to add colourMap info to.
 * @param {Object} app The associated application.
 */
dwvOemr.gui.info.Plot = function (div, app) {
    /**
     * Plot some data in a given div using the Flot jquery plugin.
     * @param {Object} div The HTML element to add WindowLevel info to.
     * @param {Array} data The data array to plot.
     * @param {Object} options Plot options.
     */
    function plot(div, data, options) {
        const plotOptions = {
            bars: { show: true },
            grid: { backgroundcolor: null },
            xaxis: { show: true },
            yaxis: { show: false },
        };
        if (typeof options !== 'undefined'
            && typeof options.markings !== 'undefined') {
            plotOptions.grid.markings = options.markings;
        }
        // call flot
        $.plot(div, [data], plotOptions);
    }

    /**
     * Create the plot info.
     */
    this.create = function () {
        // clean div
        if (div) {
            dwvOemr.html.cleanNode(div);
        }
        // plot
        plot(div, app.getImage().getHistogram());
    };

    /**
     * Update plot.
     * @param {Object} event The windowing change event containing the new values.
     * Warning: expects the plot to exist (use after createPlot).
     */
    this.update = function (event) {
        const { wc, ww } = event;

        const half = parseInt((ww - 1) / 2, 10);
        const center = parseInt((wc - 0.5), 10);
        const min = center - half;
        const max = center + half;

        const markings = [
            { color: '#faa', lineWidth: 1, xaxis: { from: min, to: min } },
            { color: '#aaf', lineWidth: 1, xaxis: { from: max, to: max } },
        ];

        // plot
        plot(div, app.getImage().getHistogram(), {
            markings,
        });
    };
}; // class dwvOemr.gui.info.Plot
