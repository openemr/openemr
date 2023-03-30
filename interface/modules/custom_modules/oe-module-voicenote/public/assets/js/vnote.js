(function(window, oemr_voicenote, bootstrap, jQuery) {
	/**
     * @type {string} The path of where the module is installed at.  In a multisite we pull this from the server configuration, otherwise we default here
     */
    //let moduleLocation = oemr_voicenote.settings.modulePath || '/interface/modules/custom_modules/oe-module-voicenote/';
    $(document).ready(function() {
        $('textarea, input[type="text"]').click(function() {
            top.activenote = $(this)[0];
        });
    });

    window.oemr_voicenote = oemr_voicenote;
})(window, window.oemr_voicenote || {}, bootstrap, $, window.dlgopen || function() {});