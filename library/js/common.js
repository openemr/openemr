function tabbify() {

    $('ul.tabNav a').click(function() {
        var curChildIndex = $(this).parent().prevAll().length + 1;
        $(this).parent().parent().children('.current').removeClass('current');
        $(this).parent().addClass('current');
        $(this).parent().parent().next('.tabContainer').children('.current').each(function() {
            $(this).removeClass('current');
            $(this).parent().children('div:nth-child('+curChildIndex+')').each(function() {
                $(this).addClass('current');
            });
        });
        return false;
    });
    
}

function enable_modals() {

    // fancy box
	$(".iframe").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
        'centerOnScroll' : false
    });

}
//----------------------------------------
function PreventIt(evt)//Specially for the browser chrome.
 {//When focus is on the text box and enter key is pressed the form gets submitted.TO prevent it this function is used.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//tab key,enter key
	 {
		if (evt.preventDefault) evt.preventDefault();
		if (evt.stopPropagation) evt.stopPropagation();
	 }
}
