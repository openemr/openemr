
$("input.dontsave").after("<button class='save_and_print'>Save and Print</button>");
$("button.save_and_print").click(function()
{
    console.log("save and print saved");
    //***SANTI ADD We are going to append the visible strings to message
    var visible = $("#covid_choice div").filter(function() {
        return $(this).css('display') === "block";

    });
    let newMessage = $("#message").val() + visible.text();
    if($("#message").val() === "") {
        newMessage = newMessage.replace(/\n/g, " ");
    }

    $("#message").val(newMessage);
    $("button.save_and_print").after("<input name='print' value='print' type='hidden'></input>");
    top.restoreSession();
    $("#my_form").submit();
    var isiPad = navigator.userAgent.match(/iPad/i) != null;
    if(isiPad){

        PrintForm();
    }
    return false;
})
