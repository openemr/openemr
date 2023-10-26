$(function() {
     var pgurl = window.location.href.substr(window.location.href.lastIndexOf("/")+1);
     $("#navbar-collapse ul li a").each(function(){
          if($(this).attr("href") == pgurl || $(this).attr("href") == '' )
          $(this).parent().addClass("active");
     })
});

$(function() {
	var $imgs = $('img.diagram, object.diagram');
    $imgs.css("cursor", "move")
    $imgs.draggable();
});
