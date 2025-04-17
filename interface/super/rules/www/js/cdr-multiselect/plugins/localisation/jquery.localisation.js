/* http://keith-wood.name/localisation.html
   Localisation assistance for jQuery v1.0.4.
   Written by Keith Wood (kbwood{at}iinet.com.au) June 2007. 
   Dual licensed under the GPL (http://dev.jquery.com/browser/trunk/jquery/GPL-LICENSE.txt) and 
   MIT (http://dev.jquery.com/browser/trunk/jquery/MIT-LICENSE.txt) licenses. 
   Please attribute the author if you use it. */

(function($) { // Hide scope, no $ conflict

/* Load applicable localisation package(s) for one or more jQuery packages.
   Assumes that the localisations are named <base>-<lang>.js
   and loads them in order from least to most specific.
   For example, $.localise('mypackage');
   with the browser set to 'en-US' would attempt to load
   mypackage-en.js and mypackage-en-US.js.
   Also accepts an array of package names to process.
   Optionally specify whether or not to include the base file,
   the desired language, and/or the timeout period, e.g.
   $.localise(['mypackage1', 'yourpackage'], 
      {loadBase: true; language: 'en-AU', timeout: 300});
   @param  packages  (string or string[]) names of package(s) to load
   @param  settings  omit for the current browser language or
                     (string) code for the language to load (aa[-AA]) or
                     (object} options for the call with
					   language  (string) the code for the language
					   loadBase  (boolean) true to also load the base package or false (default) to not
                       path      (string or string[2]) the paths to the JavaScript,
                                 either as both or [base, localisations]
					   timeout   (number) the time period in milliseconds (default 500)
   @param  loadBase  (boolean, optional) true to also load the base package or false (default) to not -
                     omit this if settings is an object
   @param  path      (string or string[2], optional) the paths to the JavaScript,
                     either as both or [base, localisations] -
                     omit this if settings is an object
   @param  timeout   (number, optional) the time period in milliseconds (default 500) -
                     omit this if settings is an object */
$.localise = function(packages, settings, loadBase, path, timeout) {
	if (typeof settings != 'object' && typeof settings != 'string') {
		timeout = path;
		path = loadBase;
		loadBase = settings;
		settings = '';
	}
	if (typeof loadBase != 'boolean') {
		timeout = path;
		path = loadBase;
		loadBase = false;
	}
	if (typeof path != 'string' && !isArray(path)) {
		timeout = path;
		path = ['', ''];
	}
	var saveSettings = {async: $.ajaxSettings.async, timeout: $.ajaxSettings.timeout};
	settings = (typeof settings != 'string' ? settings || {} :
		{language: settings, loadBase: loadBase, path: path, timeout: timeout});
	var paths = (!settings.path ? ['', ''] :
		(isArray(settings.path) ? settings.path : [settings.path, settings.path]));
	$.ajaxSetup({async: false, timeout: (settings.timeout || 500)});
	var localiseOne = function(package, lang) {
		if (settings.loadBase) {
			$.getScript(paths[0] + package + '.js');
		}
		if (lang.length >= 2) {
			$.getScript(paths[1] + package + '-' + lang.substring(0, 2) + '.js');
		}
		if (lang.length >= 5) {
			$.getScript(paths[1] + package + '-' + lang.substring(0, 5) + '.js');
		}
	};
	var lang = normaliseLang(settings.language || $.localise.defaultLanguage);
	packages = (isArray(packages) ? packages : [packages]);
	for (i = 0; i < packages.length; i++) {
		localiseOne(packages[i], lang);
	}
	$.ajaxSetup(saveSettings);
};

// Localise it!
$.localize = $.localise;

/* Retrieve the default language set for the browser. */
$.localise.defaultLanguage = normaliseLang(navigator.language /* Mozilla */ ||
	navigator.userLanguage /* IE */);

/* Ensure language code is in the format aa-AA. */
function normaliseLang(lang) {
	lang = lang.replace(/_/, '-').toLowerCase();
	if (lang.length > 3) {
		lang = lang.substring(0, 3) + lang.substring(3).toUpperCase();
	}
	return lang;
}

/* Determine whether an object is an array. */
function isArray(a) {
	return (a && a.constructor == Array);
}

})(jQuery);