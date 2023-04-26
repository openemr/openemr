/**
 * Application logic available globally throughout the app
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */
var app = {

	/** @var landmarks used to screen-scrape the html error output for a friendly message */
	errorLandmarkStart: '<!-- ERROR ',
	errorLandmarkEnd: ' /ERROR -->',

	/**
     * Display an alert message inside the element with the id containerId
     *
     * @param message
     * @param style
     * @param timeout
     * @param containerId
     */
	appendAlert: function(message, style, timeout,containerId) {
	    if (!message) {
	        return;
        }
        if (timeout < 1000) {
            timeout = 15000;
        }
        if (typeof signerAlertMsg !== 'undefined') {
            signerAlertMsg(message, timeout, style);
        } else {
            alert(message);
        }
	},
	/**
	 * show the progress bar
	 * @param the id of the element containing the progress bar
	 */
	showProgress: function(elementId)
	{
		$('#'+elementId).show();
		$('#'+elementId).animate({width:'150'},'fast');
	},

	/**
     * hide the progress bar
     * @param elementId
     */
	hideProgress: function(elementId)
	{
		setTimeout("$('#"+elementId+"').hide();",100);
		$('#'+elementId).animate({width:'0'},'fast');
	},

	/**
     * Escape unsafe HTML chars to prevent xss injection
     * @returns string safe value
     * @param unsafe
     */
	escapeHtml: function(unsafe) {
		return _.escape(unsafe);
	},
	/**
     * Accept string in the following format: 'YYYY-MM-DD hh:mm:ss' or 'YYYY-MM-DD'
     * If a date object is passed in, it will be returned as-is.  if a time-only
     * value is provided, then it will be given the date of 1970-01-01
     * @param str
     * @param defaultDate if the provided string can't be parsed, return this instead (default is Now)
     * @returns Date
     */
	parseDate: function(str, defaultDate) {

		// don't re-parse a date obj
		if (str instanceof Date) return str;

		if (typeof(defaultDate) == 'undefined') defaultDate = ''; //new Date();

		// if the value passed in was blank, default to today
		if (str === '' || typeof(str) === 'undefined') {
			if (console) {
			    console.log('app.parseDate: empty or undefined date value');
            }
			return defaultDate;
		}
		return str;

	},

	/**
	 * Convenience method for creating an option
	 */
	getOptionHtml: function(val,label,selected)	{
		return '<option value="' + _.escape(val) + '" ' + (selected ? 'selected="selected"' : '') +'>'
			+ _.escape(label)
			+ '</option>';
	},

	/**
     * A server error should contain json data, but if a fatal php error occurs it
     * may contain html.  the function will parse the return contents of an
     * error response and return the error message
     * @param resp
     */
	getErrorMessage: function(resp) {
	    if (!resp) {
	        return '';
        }

        msg = resp.responseText;
		return msg ? msg : '';
	},

	version: 1.1

}

