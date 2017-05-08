/****  FUNCTIONS RELATED TO NAVIGATION *****/
    // Process click to pop up the edit window.
function doRecallclick_edit(goHere) {
    top.restoreSession();
    dlgopen('messages.php?nomenu=1&go='+goHere, '_blank', 900, 400);
}
function goReminderRecall(choice) {
    tabYourIt('recall','main/messages/messages.php?go=' + choice);
}
function goMessages() {
    R = 'messages.php?showall=no&sortby=users.lname&sortorder=asc&begin=0&task=addnew&form_active=1';
    top.restoreSession();
    location.href = R;
}
function goMedEx() {
    location.href = 'https://medexbank.com/cart/upload/index.php?route=information/campaigns';
}
function tabYourIt(tabNAME,url) {
    parent.left_nav.loadFrame('1',tabNAME,url);
}

$(document).ready(function(){
                  
                  //bootstrap menu functions
                  $('.dropdown').hover(function() {
                                       $(".dropdown").removeClass('open');
                                       $(this).addClass('open');
                                       // $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
                                       }, function() {
                                       // $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideUp();
                                       $("[class='dropdown']").removeClass('open');
                                       $(this).parent().removeClass('open');
                                       });
                  
                  $("[class='dropdown-toggle']").hover(function(){
                                                       $(".dropdown").removeClass('open');
                                                       $(this).parent().addClass('open');
                                                       //$(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
                                                       });
                  
                  
                  });
                  
