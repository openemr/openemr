var clickmap = function( args ) {

	var f = true;
	var counter = 0;

	var fn_buildMarker = function( x, y, pos, annotation ) {
	    var legendItem = $("<li class='legend-item'><b>" + pos + "</b> " + decodeURIComponent(annotation) + "</li>");
		$(".legend .body ul").append( legendItem );

		var marker = $(".marker-template").clone();
		    marker.attr("data-x", x).attr("data-y", y).attr("data-pos", pos).attr("id", new Date().getTime() ).attr("class", "marker")
                          .attr("style", "left:" + x + "px; top:" + y + "px;" )
			  .find("span.count").text( pos );
		    marker.mouseenter( function() { f = true; } )
			  .mouseleave( function() { f = false; } )
	    .attr("title", annotation ? decodeURIComponent(annotation) : "" )
			  .show()
		   	  .click( function() { $(this).remove(); legendItem.remove(); f = false; } );
		return marker;
	};

	var fn_isnumber = function(num) { return !isNaN( parseInt( num ) ); }
	
	var fn_clear = function() { 
		$(".marker").remove();
		$(".legend-item").remove();
		counter = 0; 
	};

	var fn_load = function( container, val ) {
		fn_clear();
                if ( !val ) return;
		var coordinates = val.split("}");
		for ( var i = 0; i < coordinates.length; i++ ) {
			var coordinate = coordinates[i];
			if ( coordinate ) {
				var info = coordinate.split("^");
				var x = info[0]; var y = info[1]; var label = info[2]; var detail = info[3]; 
				var marker = fn_buildMarker( x, y, label, detail );
				container.append(marker);
				if ( fn_isnumber(label) ) {
					counter = parseInt(label);
				}
			}
		}
	};

	var fn_save = function() {
		var val = "";
		$(".marker").each( function() {
			var marker = $(this);
			val += marker.attr("data-x") + "^" + marker.attr("data-y") + "^" + marker.attr("data-pos") + "^" + encodeURIComponent(marker.attr("title")) + "}";
		});
		$("#data").attr("value", val);
                $("#submitForm").submit();
	};
	

	//// main
	var dropdownOptions = args.dropdownOptions;
	var options = dropdownOptions.options;
	var optionsLabel = dropdownOptions.label;
	var container = args.container;
        var data = args.data;
        var hideNav = args.hideNav;

	container.mousemove( function() { f = true; });

        if ( !hideNav ) {
            container.click ( function(e) {
                    if ( !f ) return;
                    var x = e.pageX - this.offsetLeft - 5;
                    var y = e.pageY - this.offsetTop - 5;
                    var dialog = $( ".dialog-form" ).clone();
                    dialog.find(".label").val( counter + 1 );
                    var hasOptions = typeof(options) != "undefined";
                    if ( hasOptions ) {
                            dialog.find("label[for='options']").text( typeof(optionsLabel) != "undefined" ? optionsLabel : "Select one"  );
                            var select = dialog.find("select[name='options']");
                            for ( var attr in options ) {
                                    if ( options.hasOwnProperty(attr) ) {
                                            select.append("<option value='" + attr + "'>" + options[attr] + "</option>");
                                    }
                            }
                    } else {
                            dialog.find("label[for='options']").remove();
                            dialog.find("select[name='options']").remove();
                    }

                    var do_marker = function() {
                            if ( dialog.saved ) {
                                    var newcounter = dialog.find(".label").val();
                                    var notes = encodeURIComponent(dialog.find(".detail").val());
                                    var selectedOption = encodeURIComponent(dialog.find("select[name='options']").val());
                                    var combinedNotes = "";
                                    if ( selectedOption) {
                                            combinedNotes = options[selectedOption];
                                    }
                                    if ( selectedOption && notes ) {
                                            combinedNotes += "%3A%20";
                                    }
                                    if ( notes ) {
                                            combinedNotes += notes;
                                    }

                                    var marker = fn_buildMarker( x, y, newcounter, combinedNotes );
                                    container.append(marker);
                                    if ( fn_isnumber(newcounter) ) { counter++; }
                            }
                            dialog.remove();
                    };

                    dialog.dialog({
                            title: "Information",
                            autoOpen: false, height: hasOptions? 345 : 300, width: 350, modal:true,
                            open: function() { dialog.find(".detail").focus(); },
                            buttons: {
                                    "Save": function() { dialog.saved = true; $(this).dialog("close"); },
                                    "Cancel": function() { $(this).dialog("close"); }
                            },
                            close: do_marker
                    });
                    dialog.dialog("open");
            });

        }
	var btn_clear = $("#btn_clear");
	btn_clear.click( fn_clear );

	var btn_save = $("#btn_save");
	btn_save.click( fn_save );

	fn_load( container, data );

        if ( hideNav ) {
            $(".nav").hide();
        };
};


