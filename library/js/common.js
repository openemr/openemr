function tabbify()
{

    $('ul.tabNav a').click(function () {
        var curChildIndex = $(this).parent().prevAll().length + 1;
        $(this).parent().parent().children('.current').removeClass('current');
        $(this).parent().addClass('current');
        $(this).parent().parent().next('.tabContainer').children('.current').each(function () {
            $(this).removeClass('current');
            $(this).parent().children('div:nth-child(' + curChildIndex + ')').each(function () {
                $(this).addClass('current');
            });
        });
        return false;
    });

}

//----------------------------------------
function PreventIt(evt)//Specially for the browser chrome.
{
//When focus is on the text box and enter key is pressed the form gets submitted.TO prevent it this function is used.
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13) {//tab key,enter key
        if (evt.preventDefault) {
            evt.preventDefault();
        }
        if (evt.stopPropagation) {
            evt.stopPropagation();
        }
    }
}


// Onkeyup handler for policy number.  Allows only A-Z and 0-9.
function policykeyup(e)
{
    var v = e.value.toUpperCase();
    var filteredString = "";
    for (var i = 0; i < v.length; ++i) {
        var c = v.charAt(i);
        if (
          (c >= '0' && c <= '9') ||
          (c >= 'A' && c <= 'Z') ||
          (c == '*') ||
          (c == '-') ||
          (c == '_') ||
          (c == '(') ||
          (c == ')') ||
          (c == '#') ||
          // add for modifiers in fee sheet, Claim::x12clean() will remove from outgoing claims
          (c == ' ') ||
          (c == ':')
        ) {
            filteredString += c;
        }
    }
    e.value = filteredString;
    return;
}

