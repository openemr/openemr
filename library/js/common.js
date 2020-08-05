function tabbify() {
    $('ul.tabNav a').click(function () {
        const curChildIndex = $(this).parent().prevAll().length + 1;
        $(this).parent().parent().children('.current')
            .removeClass('current');
        $(this).parent().addClass('current');
        $(this).parent().parent().next('.tabContainer')
            .children('.current')
            .each(function () {
                $(this).removeClass('current');
                $(this).parent().children(`div:nth-child(${curChildIndex})`).each(function () {
                    $(this).addClass('current');
                });
            });
        return false;
    });
}

//----------------------------------------
// Specially for the browser chrome.
// When focus is on the text box and enter key is pressed the form gets submitted.
// TO prevent it this function is used.
function PreventIt(evt) {
    if (!evt) {
        evt = window.event;
    }
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    // tab key,enter key
    if (charCode === 13) {
        if (evt.preventDefault) {
            evt.preventDefault();
        }
        if (evt.stopPropagation) {
            evt.stopPropagation();
        }
    }
}
