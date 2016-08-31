/*
 * Copyright Jerry Padgett <sjpadgett@gmail.com> 
 * 
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * Rewrite and modifications by sjpadgett@gmail.com Padgetts Consulting 2016.
 *
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com> 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */
 
var mapdiagram = function (args) {
    var f = true;
    var counter = 0;
    var gf = false;
    var txtmode = false;
    var isInput = false; var prx = 0; var pry = 0;
    var mode_obj = '';
    
    var mycontext = document.getElementById('main-img');
    var mode_styleEl = document.getElementById("mode").style;

    String.prototype.escapeSpecialChars = function() {
    	return this.replace(/\\/g, "\\\\").replace(/\n/g, "\\n").replace(/\r/g,
    			"\\r").replace(/\t/g, "\\t").replace(/\f/g, "\\f").replace(/"/g,
    			"\\\"").replace(/'/g, "\\\'").replace(/\&/g, "\\&");
    }
    if (mycontext.addEventListener) {
      mycontext.addEventListener('contextmenu', function(e) {
        var posX = e.clientX;
        var posY = e.clientY;
        menu(posX, posY);
        e.preventDefault();
      }, false);
      mycontext.addEventListener('click', function(e) {
       // mode_styleEl.opacity = "0";
        setTimeout(function() {
        //  mode_styleEl.visibility = "hidden";
        }, 501);
      }, false);
    } else {
      mycontext.attachEvent('oncontextmenu', function(e) {
        var posX = e.clientX;
        var posY = e.clientY;
        menu(posX, posY);
        e.preventDefault();
      });

    }

    function menu(x, y) {
      mode_styleEl.top = y + "px";
      mode_styleEl.left = x + "px";
      mode_styleEl.visibility = "visible";
      mode_styleEl.opacity = "1";
    }
    
    var fn_buildMarker = function (x, y, pos, annotation) {
        var legendItem = $("<li class='legend-item'><b>" + pos + "</b> " + decodeURIComponent(annotation) + "</li>");
        if (!isSpecial(pos))
            $(".legend .body ul").append(legendItem);
        var s = "";
        if ( !pos ) s = "background-color:yellow;";
        if ( pos < '~' && !txtmode ) {
            var marker = $(".marker-template").clone();
            marker.attr("data-x", x).attr("data-y", y).attr("data-pos", pos).attr("id", new Date().getTime()).attr("class", "marker").attr("style", "left:" + x + "px; top:" + y + "px;"+s).find("span.count").text(pos);
        }
        else {
            var marker = $(".xmark-template").clone();
            marker.attr("data-x", x).attr("data-y", y).attr("data-pos", pos).attr("id", new Date().getTime()).attr("class", "xmark").attr("style", "left:" + x + "px; top:" + y + "px;").find("span.xcnt").text(pos);
        }
        marker.mouseenter(function () { f = true; })
          .mouseleave(function () { f = false; }).attr("title", annotation ? decodeURIComponent(annotation) : "")
          .show().click(function () { $(this).remove(); legendItem.remove(); f = false; });
        return marker;
    };

    var fn_isnumber = function (num) { return !isNaN(parseInt(num)); }

    var fn_clear = function () {
        $(".marker").remove();
        $(".xmark").remove();
        $(".legend-item").remove();
        counter = 0;
    };
    var fn_load = function (container, val) {
        fn_clear();
        if (!val) return;
        val = val.replace(/\\"/g, '"');
        var coordinates = val.split("}");
        for (var i = 0; i < coordinates.length; i++) {
            var coordinate = coordinates[i];
            if (coordinate) {
                var info = coordinate.split("^");
                var x = info[0]; var y = info[1]; var label = info[2]; var detail = info[3];
                var marker = fn_buildMarker(x, y, label, detail);
                container.append(marker);
            }
        }
    };
    var fn_save = function () {
        var val = "";
        $(".marker").each(function () {
            var marker = $(this);
            val += marker.attr("data-x") + "^" + marker.attr("data-y") + "^" + marker.attr("data-pos") + "^" + marker.attr("title") + "}";
        });
        $(".xmark").each(function () {
            var marker = $(this);
            val += marker.attr("data-x") + "^" + marker.attr("data-y") + "^" + marker.attr("data-pos") + "^" + marker.attr("title") + "}";
        });
        var img = document.getElementById('main-img');
        var imgdata = img.getAttribute('src');
        
        $("#imagedata").attr("value", imgdata);
        $("#data").attr("value", val.escapeSpecialChars());
        $("#submitForm").submit();
    };
    var isSpecial = function (elem){
        if ( elem > '~' || txtmode || elem[0] == '\1' ) {
            return true;
        }
        else
            return false;
    };   
    var centerMarker = function(xpos,ypos,elval,tpos){// center marker on click coordinate 
        var elem = document.getElementById(elval);
        var style = window.getComputedStyle(elem, null).getPropertyValue('font-size');
        var fontsz = parseInt(style);
        var cpos = new Object();
        if( fontsz < 12) fontsz = 12;
        cpos[0] = xpos - (fontsz / 2)-1; 
        cpos[1] = ypos - ((fontsz+(fontsz / 3))/2);
        cpos[1] =  Math.round(cpos[1]);
        if(tpos == '░'){
            cpos[0] = cpos[0] + 2; 
            cpos[1] = cpos[1] + 4;
        }
        
        return cpos;
    };
    var fn_setMode = function () {
        if (gf){
            gf = false; isInput = false; prx=0; pry=0;
            txtmode = false;
            $(".dytxt").val("");
            $('.dytxt').hide();
            //$("#mode").attr("style", "display:none;");
            $("#btn_mode").attr("style", "color:black;font-weight:normal;");
            $("#btn_mode").text('Legend Mode');
            $(".mode-inline").text('Mode Label');
            $('#container').removeClass('symcursor');
            mode_styleEl.visibility = "hidden";
        }
        else{
            gf = true;
            //$('#rtxt').attr('checked', 'checked');
            //fn_setMarkval();
            $("#btn_mode").attr("style", "color:red;");
            //$("#mode").attr("style", "display:inline-block;");
            $("#btn_mode").text("Label Mode");
        }
        
    };
    var fn_setMarkval = function () {
    	$('.dytxt').hide();
    	mode_obj = $('input[name=modegrp]:checked').val();
    	
         if( mode_obj == "txtmode" ) {
        	$('#container').removeClass('symcursor');
            txtmode = true;
            mode_obj = "";
            }
        else{
        	$('#container').addClass('symcursor');
            txtmode = false; isInput = false;
            prx=0; pry=0; 
            }
        
        if(txtmode) $(".mode-inline").text('Mode Legend : Text');
    	else $(".mode-inline").text('Mode Legend : '+ mode_obj);
        if(!gf){ // in label mode
    		fn_setMode();
    	}
    };
    /* main */
    var dropdownOptions = args.dropdownOptions;
    var options = dropdownOptions.options;
    var optionsLabel = dropdownOptions.label;
    var container = args.container;
    var data = args.data;
    var hideNav = args.hideNav;

    container.mousemove( function (){ f = true; } );
    $('.dytxt').blur( function(){ isInput = true; } );
 
    if (!hideNav) {
        container.click(function (e) {
            if (!f) return;
               
            var x = e.pageX - this.offsetLeft - 5;
            var y = e.pageY - this.offsetTop - 5;
            var dialog = $(".dialog-form").clone();
            dialog.find(".label").val(counter + 1);
            var hasOptions = typeof (options) != "undefined";
            if (hasOptions) {
                dialog.find("label[for='options']").text(typeof (optionsLabel) != "undefined" ? optionsLabel : "Select one");
                var select = dialog.find("select[name='options']");
                for (var attr in options) {
                    if (options.hasOwnProperty(attr)) {
                        select.append("<option value='" + attr + "'>" + options[attr] + "</option>");
                    }
                }
            } else {
                dialog.find("label[for='options']").remove();
                dialog.find("select[name='options']").remove();
            }
            
            var do_marker = function () {
                if (dialog.saved) {
                    var newcounter = dialog.find(".label").val();
                    var notes = encodeURIComponent(dialog.find(".detail").val());
                    var selectedOption = encodeURIComponent(dialog.find("select[name='options']").val());
                    var xobservation = encodeURIComponent(dialog.find(".optxtra").val());
                    var lt = dialog.find(".legendtext").val();
                    if( lt ) xobservation = lt;
                    var combinedNotes = "";
                    if (selectedOption) {
                        combinedNotes = options[selectedOption];
                    }
                    if (xobservation) {
                        if (combinedNotes > "") {
                            combinedNotes += " ";
                        }
                        combinedNotes += xobservation;
                    }
                    if ((selectedOption || xobservation) && notes) {
                        combinedNotes += "%3A%20";
                    }
                    if (notes) {
                        combinedNotes += notes;
                    }
                    if (newcounter  > '~') {
                        var cpos = centerMarker(x,y,"xcnt",newcounter);
                    }
                    else{
                        var cpos = centerMarker(x,y,"count",newcounter);
                    }
                    x = cpos[0];y = cpos[1];
                    var marker = fn_buildMarker(x, y, newcounter, combinedNotes);
                    container.append(marker);
                    if (fn_isnumber(newcounter)) { counter++; }
                }
                dialog.remove();
            };
            if( !gf ){
                dialog.dialog({ title:"Annotations",autoOpen: false, height:hasOptions ? 'auto' :300, width:350, modal:true,
                open: function () { dialog.find(".detail").focus(); },
                buttons: {
                    "Save": function () { dialog.saved = true;$(this).dialog("close"); },
                    "Cancel": function () { $(this).dialog("close"); }
                },
                close: do_marker});
            }
            if (!gf){
                $(".legendgrp").attr("style", "display:hidden");
                $(".legendtext").attr("value", "");
                $(".labelgrp").attr("style", "display:block");
                dialog.dialog("open");
            }
            else{
                if( txtmode ){
                    mode_obj = "";
                    var dt_xy = centerMarker(x,y,"xcnt","");
                    dt_xy[0] += 8;dt_xy[0] -= 2;
                    $(".dytxt").attr("style","display:inline;left:" + dt_xy[0] + "px; top:" + dt_xy[1] + "px;");
                    $(".dytxt").focus();
                    if( isInput ){
                        var tmpx = x; var tmpy = y;
                        x = prx; y = pry;
                        var v = $(".dytxt").val();
                        if(  v != ""){
                            mode_obj = '\1' + v; // delim SOH txt for report
                            dialog.find(".label").val(mode_obj);
                            dialog.saved = true;
                            x += 8;
                            do_marker();
                            mode_obj = "";
                            isInput = false;
                            $(".dytxt").val("");
                            $('.dytxt').hide();
                        }
                        x = tmpx; y = tmpy; prx = x; pry = y; isInput = false; //allows for double click emulation
                    }
                    else{
                        prx = x; pry = y;
                    }
                } // txt mode
                else{
                    dialog.find(".label").val(mode_obj);
                    dialog.saved = true;
                    do_marker();
                }
            } // is gf
        }); // container
    } // in edit mode
    // add top and bottom nav as convience for larger diagrams
    var btn_cleartop = $("#btn_cleartop"); btn_cleartop.click(fn_clear);
    var btn_savetop = $("#btn_savetop"); btn_savetop.click(fn_save);
    var btn_mode = $("#btn_mode"); btn_mode.click(fn_setMode);
    var radio_grp = $("#legend_grp"); radio_grp.click(fn_setMarkval);
    var btn_clear = $("#btn_clear"); btn_clear.click(fn_clear);
    var btn_save = $("#btn_save"); btn_save.click(fn_save);
       
    fn_load(container, data);
    txtmode = true;
    fn_setMode();
    if (hideNav) {
        $(".navtop").hide();
        $(".nav").hide();
    };
};