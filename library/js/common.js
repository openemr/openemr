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