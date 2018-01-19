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
	 * @param string message to display
	 * @param string style: '', 'alert-error', 'alert-success' or 'alert-info'
	 * @param int timeout for message to hide itself
	 * @param string containerId (default = 'alert')
	 */
	appendAlert: function(message,style, timeout,containerId) {

		if (!style) style = '';
		if (!timeout) timeout = 0;
		if (!containerId) containerId = 'alert';

		var id = _.uniqueId('alert_');

		var html = '<div id="'+id+'" class="alert '+ this.escapeHtml(style) +'" style="display: none;">'
			+ '<a class="close" data-dismiss="alert">&times;</a>'
			+ '<span>'+ this.escapeHtml(message) +'</span>'
			+ '</div>';

		// scroll the alert message into view
		var container = $('#' + containerId);
		container.append(html);
		container.parent().animate({
			scrollTop: container.offset().top - container.parent().offset().top + container.parent().scrollTop() - 10 // (10 is for top padding)
		});

		$('#'+id).slideDown('fast');

		if (timeout > 0) {
			setTimeout("app.removeAlert('"+id+"')",timeout);
		}
	},

	/**
	 * Remove an alert that has been previously shown
	 * @param string element id
	 */
	removeAlert: function(id) {

		$("#"+id).slideUp('fast', function(){
			$("#"+id).remove();
		});
	},

	/**
	 * show the progress bar
	 * @param the id of the element containing the progress bar
	 */
	showProgress: function(elementId)
	{
		$('#'+elementId).show();
		// $('#'+elementId).animate({width:'150'},'fast');
	},

	/**
	 * hide the progress bar
	 * @param the id of the element containing the progress bar
	 */
	hideProgress: function(elementId)
	{
		setTimeout("$('#"+elementId+"').hide();",100);
		// $('#'+elementId).animate({width:'0'},'fast');
	},

	/**
	 * Escape unsafe HTML chars to prevent xss injection
	 * @param string potentially unsafe value
	 * @returns string safe value
	 */
	escapeHtml: function(unsafe) {
		return _.escape(unsafe);
	},

	/**
	 * return true if user interface should be limited based on browser support
	 * @returns bool
	 */
	browserSucks: function() {
		isIE6 = navigator.userAgent.match(/msie [6]/i) && !window.XMLHttpRequest;
		isIE7 = navigator.userAgent.match(/msie [7]/i);
		isIE8 = navigator.userAgent.match(/msie [8]/i);
		return isIE6 || isIE7 || isIE8;
	},

	/**
	 * Accept string in the following format: 'YYYY-MM-DD hh:mm:ss' or 'YYYY-MM-DD'
	 * If a date object is passed in, it will be returned as-is.  if a time-only
	 * value is provided, then it will be given the date of 1970-01-01
	 * @param string | date:
	 * @param defaultDate if the provided string can't be parsed, return this instead (default is Now)
	 * @returns Date
	 */
	parseDate: function(str, defaultDate) {

		// don't re-parse a date obj
		if (str instanceof Date) return str;

		if (typeof(defaultDate) == 'undefined') defaultDate = ''; //new Date();

		// if the value passed in was blank, default to today
		if (str == '' || typeof(str) == 'undefined') {
			if (console) console.log('app.parseDate: empty or undefined date value');
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
	 * @param server response
	 */
	getErrorMessage: function(resp) {

		var msg = 'An unknown error occured';
		try	{
			var json = $.parseJSON(resp.responseText);
			msg = json.message;
		} catch (error)	{
			// TODO: possibly use regex or some other more robust way to get details...?
			var parts = resp.responseText.split(app.errorLandmarkStart);

			if (parts.length > 1) {
				var parts2 = parts[1].split(app.errorLandmarkEnd);
				msg = parts2[0];
			}
		}

		return msg ? msg : 'Unknown server error';
	},

	version: 1.1

}