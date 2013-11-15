/**
 * jQuery plugin to enable esign functionality on a DOM object.
 * Pass in a selector to enable esign on the objects that match    
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
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
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

(function( $ ) {
	
	$.fn.esign = function( customSettings, customEvents ) {
		
		// Initialize settings
		var settings = $.extend({
			
			// These are the defaults.
			module : "default",
			baseUrl : "/interface/esign/index.php",
			logViewAction : "/interface/esign/index.php?method=esign_log_view",
			formViewAction : "/interface/esign/index.php?method=esign_form_view",
			formSubmitAction : "/interface/esign/index.php?method=esign_form_submit"
		}, customSettings );
			
		var events = $.extend({
			
			afterFormSuccess : function( response ) {
				var logId = "esign-signature-log-"+response.formDir+"-"+response.formId;
				$.post( settings.logViewAction, response, function( html ) {
					$("#"+logId).replaceWith( html );
				});
				var editButtonId = "form-edit-button-"+response.formDir+"-"+response.formId;
				$("#"+editButtonId).replaceWith( response.editButtonHtml );
			}
		}, customEvents );
		
		// Set up the page with the masking div and form container
		$('body').append( "<div id='esign-mask-content' class='window rounded'></div>" );
		$('body').append( "<div id='esign-mask'></div>" );
		
		// Initialize mask event handlers
		$(document).on( 'click', '#esign-mask', function() {
			$(this).hide();
			$('.window').hide();
			return false;
		});			

		$(window).resize( function() {
			
	 		var box = $('.window');
	 
	        //Get the screen height and width
	        var maskHeight = $(document).height();
	        var maskWidth = $(window).width();
	      
	        //Set height and width to mask to fill up the whole screen
	        $('#esign-mask').css({'width':maskWidth,'height':maskHeight});
	        
	        //Get the window height and width
			var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;

	        //Set the popup window to center
	        box.css('top',  y/2 - box.height()/2);
	        box.css('left', x/2 - box.width()/2);
		 
	        return false;
		});
		
		// Initialize the signature form event handlers
	    $(document).on( 'click', '#esign-back-button', function( e ) {
	    	
	    	e.preventDefault();
	    	$(".window").hide();
	        $("#esign-mask").hide();
	        
	        return false;
	    });
	
	    $(document).on( 'click', '#esign-sign-button-'+settings.module, function( e ) {
	    	
	        e.preventDefault();
	        var formData = $('#esign-signature-form').serialize();
	        $.post( 
	        	settings.formSubmitAction,
	        	formData,
	        	function( response ) {
	        		if ( response.status != 'success' ) { 
	        			$("#esign-form-error-container").remove();
	        			$("#esign-mask-content").find("form").append("<div id='esign-form-error-container' class='error'>"+response.message+"</div>");
	        		} else {
	        			// Close the form and refresh the log if it's on the screen
	        			$(".window").hide();
	        	        $("#esign-mask").hide();
	        	        events.afterFormSuccess( response );
	        		}
	        	},
	        	'json'
	        );
	    	
	    	return false;
	    });

		function initElement( element ) {
			
			// Override the anchor
			element.attr( 'href', '#esign-mask-content' );
			
			element.click( function( e ) {

				e.preventDefault();

				//Get the screen height and width
				var maskHeight = $(document).height();
				var maskWidth = $(window).width();
			
				// Set heigth and width to mask to fill up the whole screen
				$('#esign-mask').css({'width':maskWidth,'height':maskHeight});
				
				//transition effect		
				$('#esign-mask').fadeIn(200);	
				$('#esign-mask').fadeTo("fast",0.5);	
			
				// Get the window height (y) and width (x)
				var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
						                  
				//Set the popup window to center
				$('#esign-mask-content').css( 'top', Math.min( y/4-$('#esign-mask-content').height()/4, 100 ) );
				$('#esign-mask-content').css( 'left', x/2-$('#esign-mask-content').width()/2 );
				
				// Get a list of all the data-* attributes
				var params = element.data(); 
				
				// Load the form
				$.post( settings.formViewAction, params, function( response ) {
					$('#esign-mask-content').html( response );
				});
			
				//transition effect
				$('#esign-mask-content').fadeIn(200); 
		    });
			
			return false;
		}
		
		return this.each( function() {
			var element = $(this);
        	initElement( element );
        });
	};
	
}( jQuery ));
