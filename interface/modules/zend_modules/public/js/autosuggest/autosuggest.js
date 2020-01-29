/**
 * interface/modules/zend_modules/public/js/autosuggest/autosuggest.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/**
 * Auto suggest
 * autosuggest.js
 */

/**
 * Function PreventIt
 *
 * @param {type} evt Event
 * @returns {undefined}
 */
function PreventIt(evt) {
  evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 38 || charCode == 40) {
		if (evt.preventDefault) evt.preventDefault();
		if (evt.stopPropagation) evt.stopPropagation();
	 }
}

/**
   * Function lookup
   *
   * @param {string}  inputString   keyword
   * @param {string}  searchType    Patient, CPT, Diagnosis etc.
   * @param {int}     searchEleNo   more then one search box
   * @param {string}  searchMode    Single / Multiple
   * @returns {undefined}
   */
  function lookup(inputString, searchType, searchEleNo, searchMode) {
		if(inputString.length == 0) {
			$('#suggestions' + searchEleNo).hide();
		} else {
      if (searchEleNo == '') {
        searchEleNo = 0;
      }
			if (searchEleNo != '' || searchEleNo > 0) {
        $('#inputString' + searchEleNo).keyup(function() {
          $('#page').val("");
        });
        $('#inputString' + searchEleNo).keydown(function() {
          $('#page').val("");
        });
        var inputString       = $('#inputString' + searchEleNo).val();
        var inputStringCheck 	= $('#inputString' + searchEleNo).val();
      } else {
        $('#inputString').keyup(function() {
          $('#page').val("");
        });
        $('#inputString').keydown(function() {
          $('#page').val("");
        });
        var inputString       = $('#inputString').val();
        var inputStringCheck 	= $('#inputString').val();
      }

			var leading           = '%';
			var trailing           = '%';
      var page = $('#page').val();
      /** Path settings */
      var path = window.location;
      var arr = path.toString().split("public");
      var count = arr[1].split("/").length-1;
      var newPath = './';
      for(var i = 0; i < count; i++){
        newPath += '../';
      }
	    dataType: "html",
			$.post(newPath + "public/application/index/search", {
        queryString : inputString,
        leading     : leading,
        trailing    : trailing,
        page        : page,
        searchType  : searchType,
        searchEleNo : searchEleNo,
        searchMode  : searchMode,
      }, function(data){
				cache: false;
				if(data.length > 0) {
          if (searchEleNo > 0) {
            var inputStringValue = $('#inputString' + searchEleNo).val();
          } else {
            var inputStringValue = $('#inputString').val();
          }
					if (inputStringCheck == inputStringValue) {
						if (searchEleNo > 0) {
              $('#suggestions' + searchEleNo).show();
              $('#autoSuggestionsList' + searchEleNo).html(data);
            } else {
              $('#suggestions').show();
              $('#autoSuggestionsList').html(data);
            }
            // Focus to 1st Row
            if(document.getElementById('list_' + searchEleNo + '_1')) {
                $('#list_' + searchEleNo + '_1').focus();
                $('#list_' + searchEleNo + '_1').css("background-color","#659CD8");
            }
					}
				}
			});
		}
	}

/**
 * Key Board Controls
 *
 * @param {type} evt  Event
 * @param {type} row  Line No.
 * @param {type} id   ID for arrow key scroll
 * @param {type} no   Search Element no
 * @returns {undefined}
 */
function move(evt, row, id, no) {
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode == 38 || charCode == 40) {
    evt.stopPropagation();
    evt.preventDefault();
  }
	if (charCode == 38) {
		row--;
		if(document.getElementById(id + '_' + (row))) {
			if((row + 1)%2 == 1) {
        $('#' + id + '_' + (row + 1)).css("background-color", "#fff");
			} else {
        $('#' + id + '_' + (row + 1)).css("background-color", "#fff");
			}
			$('#' + id + '_' + (row)).css("background-color","#659CD8");
			document.getElementById(id + '_' + (row)).focus();
		}
	} else if (charCode == 40) {
		row++;
		if(document.getElementById(id + '_' + (row))) {
			if((row - 1)%2 == 1) {
        $('#' + id + '_' + (row - 1)).css("background-color", "#fff");
			} else {
        $('#' + id + '_' + (row - 1)).css("background-color", "#fff");
			}
			$('#' + id + '_' + (row)).css("background-color","#659CD8");
			document.getElementById(id + '_' + (row)).focus();
		}
	} else if (charCode == 13) {
		if (no > 0) {
			$('#fill' + id + '_' + (row)).trigger('click');
		} else {
			$('#fill' + id + '_' + (row)).trigger('click');
		}
	}  else if (charCode == 27) {
		if (no > 0) {
			$('#closeme' + no).trigger('click');
		} else {
			$('#closeme').trigger('click');
		}

	} else {
		if (no > 0) {
			document.getElementById('inputString' + no).focus();
		} else {
      $('#inputString').focus();
		}
	}
}

/**
 *
 * @param {type} params
 * @param {type} id
 * @param {type} no
 * @returns {undefined}
 */
function nextPage(params, id, no, mode) {
	$('#page').val(params);
	if (no != '' || no > 0) {
		lookup($('#inputString' + no).val(), id, no, mode);
	} else {
		lookup($('#inputString').val(), id, no, mode);
	}
}

function previousPage(params, id, no, mode) {
	$('#page').val(params);
	if (no != '' || no > 0) {
		lookup($('#inputString' + no).val(), id, no);
	} else {
		lookup($('#inputString').val(), id, no, mode);
	}
}

/**
 * Function Fill
 * Fill the selected value in the input box
 *
 * @param {string} thisValue  Name to be Filled in the box
 * @param {string} id         Search Type 'Patient, CPT, Diagnosis etc.'
 * @param {int}    no         search Element No more then one search box
 * @param {string} mode       Search Mode Single / Multiple
 * @returns {undefined}
 */
function fill(thisValue, id, no, mode) {
	//Multiple search entry is in case of feesheet.
	/*var multiplesearch = false;
	if(document.getElementById('multiplesearch')){
		multiplesearch = document.getElementById('multiplesearch').checked ;
	}*/
  $('#form_date').prop('disabled', true);
  $('#form_to_date').prop('disabled', true);
  $('.validatebox-text').prop('disabled', true);
  var arr = thisValue.split("|");
  if (mode == 'single') {
    if (no == '' || no == 0) {
      setTimeout("$('#suggestions').hide();", 100);
      $('#inputString').val(arr[0]);
      $('#string_id').val(arr[1]);
      //$('#string_id').attr('type', 'text');
      $('#string_id').get(0).type = 'text';
    } else {
      setTimeout("$('#suggestions" + no + "').hide();", 100);
      $('#inputString' + no).val(arr[0]);
      $('#string_id' + no).val(arr[1]);
      //$('#string_id').attr('type', 'text');
      $('#string_id' + no).get(0).type = 'text';
    }
  }
  if (mode == 'multiple') {
    if (no == '' || no == 0) {
      //$('#inputString').val(arr[0]);
      $('#inputString').val('').focus();
      if ($('#string_value').val().length > 0) {
        $('#string_value').val($('#string_value').val() + ';' + arr[0]);
      } else {
        $('#string_value').val(arr[0]);
      }
      if ($("#string_id").val().length > 0) {
        $('#string_id').val($('#string_id').val() + ';' + arr[1]);
      } else {
        $("#string_id").val(arr[1]);
      }

      var close_id = 'close|' + arr[0] + '|' + arr[1] + '|' + no;
      var stringValue = '<div id="'+ arr[1] + '" class="selected_item">'
              + arr[0]
              + '<span class="close" id="' + close_id + '">&times;</span></div>';
      $('#selected_search').append(stringValue);
      //$("#target").append('<div style="border:1px solid red">' + arr[0] + '</div>');
      setTimeout("$('#suggestions').hide();", 100);
    } else {
      $('#inputString' + no).val('').focus();
      if ($('#string_value' + no).val().length > 0) {
        $('#string_value' + no).val($('#string_value' + no).val() + ';' + arr[0]);
      } else {
        $('#string_value' + no).val(arr[0]);
      }
      if ($("#string_id" + no).val().length > 0) {
        $('#string_id' + no).val($('#string_id' + no).val() + ';' + arr[1]);
      } else {
        $("#string_id" + no).val(arr[1]);
      }

      var close_id = 'close|' + arr[0] + '|' + arr[1] + '|' + no;
      var stringValue = '<div id="'+ arr[1] + '" class="selected_item">'
              + arr[0]
              + '<span class="close" id="' + close_id + '">&times;</span></div>';
      $('#selected_search' + no).append(stringValue);
      //$("#target").append('<div style="border:1px solid red">' + arr[0] + '</div>');
      setTimeout("$('#suggestions" + no + "').hide();", 100);
    }
  }

	$('#page').val("");
}

/**
 * Remove Selected Item
 *
 */
$(document).on('click', '.close', function(e){
  var id = $(this).attr("id");
  var arrId = id.split('|');
  var no = arrId[3];
  // Remove from hidden string values
  if (no > 0) {
    var stringValue = $("#string_value" + no).val();
  } else {
    var stringValue = $("#string_value").val();
  }
  var arr = stringValue.split(';');
  var str = '';
  for (var i = 0; i < arr.length; i++) {
    if (arr[i] != arrId[1]) {
      str = str + arr[i] + ';';
    }
  }
  str = str.slice(0, -1);
  if (no > 0) {
    $("#string_value" + no).val(str);
  } else {
    $("#string_value").val(str);
  }

  // Remove hidden ids
  if (no > 0) {
    var stringId = $("#string_id" + no).val();
  } else {
    var stringId = $("#string_id").val();
  }
  var arr = stringId.split(';');
  var str = '';
  for (var i = 0; i < arr.length; i++) {
    if (arr[i] != arrId[2]) {
      str = str + arr[i] + ';';
    }
  }
  str = str.slice(0, -1);
  if (no > 0) {
    $("#string_id" + no).val(str);
  } else {
    $("#string_id").val(str);
  }
  $(this).parent().remove();
});

/**
 * Clear Input Values
 */
$(document).on('change click keyup', '.lookup', function(e){
  var targetId = e.target.id;
  var arr = targetId.split('inputString');
  var classname =e.target.classList[1];
  //$('#' + targetId).val("");
  if (e.type == 'change' || (e.type == 'keyup' && e.keyCode == 8)) {
    if (arr[1] != '') {
        if (classname != 'multiple') {  //do not reset values if mode is multiple
            $('#string_value' + arr[1]).val("");
            $('#string_id' + arr[1]).val("");
        }
    } else {
      $('#string_value').val("");
      $('#string_id').val("");
      $('#form_date').prop('disabled', false);
      $('#form_to_date').prop('disabled', false);
      $('.validatebox-text').prop('disabled', false);
    }
  }
  if (e.type == 'click') {
    if ($(e.target).closest('.suggestions').length === 0) {
      $('.suggestions').hide();
    }
  }
});


/*$(document).on('click', function(e){
  if ($(e.target).closest('.suggestions').length === 0) {
    $('.suggestions').hide();
  }
  if(this != $(".suggestions")[0]) {
    $(".suggestions").hide();
  }
  var a = $(".suggestions");
  bodyClick(a,e);
});*/

/**
 * Close Auto suggest List
 * On Body click
 */
$(document).mouseup(function(e){
  var a = $('.panel_content_wrapper');
  bodyClick(a,e);
});

// Hide Pop-up Mneu on Click outside
function bodyClick(container,e){
  if(!container.is(e.target) && container.has(e.target).length === 0){
    $(container).css("display","none");
  }
}



