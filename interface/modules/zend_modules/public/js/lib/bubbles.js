$(function()  
{  
  var hideDelay = 500;    
  var currentID;  
  var hideTimer = null;  
  
  // One instance that's reused to show info for the current person  
  var container = $('<div id="personPopupContainer">'  
      + '  <div id="personPopupContent"></div>'  
      + '</div>');  
  //alert(container);
  //$('body').remove("#personPopupContainer");
  $('body').append(container);  
  
  $('.personPopupTrigger').live('focus', function()  
  {  
      // format of 'rel' tag: pageid,personguid  
      var reason = $(this).attr('rel');  
      var pos = $(this).offset();  
      var width = $(this).width();  
      container.css({  
          left: (pos.left + width+3) + 'px',  
          top: pos.top - 5 + 'px'  
      });  
      $('#personPopupContent').html(reason);  

  
      container.css('display', 'block');  
  //});  
  
  $('.personPopupTrigger').live('blur', function()  
  {  
      //////if (hideTimer)  
      //////    clearTimeout(hideTimer);  
      //////hideTimer = setTimeout(function()  
      //////{  
          container.css('display', 'none');  
      ////////}, hideDelay);  
  });  
  
  // Allow mouse over of details without hiding details  
  ////////$('#personPopupContainer').mouseover(function()  
  ////////{  
  ////////    if (hideTimer)  
  ////////        clearTimeout(hideTimer);  
  ////////});  
  
  // Hide after mouseout  
  //////////$('#personPopupContainer').mouseout(function()  
  //////////{  
  //////////    if (hideTimer)  
  //////////        clearTimeout(hideTimer);  
  //////////    hideTimer = setTimeout(function()  
  //////////    {  
  //////////        container.css('display', 'none');  
  //////////    }, hideDelay);  
  //////////});  
});  
});