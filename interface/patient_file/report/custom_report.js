/**
 *
 * Javascript extracted from Patient custom report.
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Tony McCormick <tony@mi-squared.com>
 * @link    https://www.open-emr.org
 */

// Search & highlight backed by library/js/searchHighlight.js
// (window.OpenEMRSearchHighlight). The previous SearchHighlight.js jQuery
// plugin and the hand-rolled mark_hilight() regex have been replaced by a
// single DOM-walking module that produces the same <mark class="hilite">
// output.

var res_id = 0;

function mark_hilight(form_id, form_dir, keys, case_sensitive) { // Adds <mark class="hilite"> tags
  var trimmed = keys.replace(/^\s+|\s+$/g, '');
  if (trimmed === '') {
    return;
  }
  var target = '#search_div_' + form_id + '_' + form_dir;
  var inserted = window.OpenEMRSearchHighlight.search(target, trimmed, {
    exact: 'partial',
    caseSensitive: case_sensitive === true || case_sensitive === 'true',
    className: 'hilite',
    tagName: 'mark'
  });
  for (var i = 0; i < inserted.length; i++) {
    res_id = res_id + 1;
    inserted[i].id = 'result_' + res_id;
  }
}

var forms_array;
var res_array   = Array();
function find_all(){ // for each report the function mark_hilight() is called
  case_sensitive = false;
  if ($('#search_case').attr('checked')) {
      case_sensitive = true;
  }
  var keys = document.getElementById('search_element').value;
  var match = null;
  match = keys.match(/[\^$.|?+()\\~`!@#%&+={}<>]{1,}/);
  if(match){
    document.getElementById('alert_msg').innerHTML = jsText(xl('Special characters are not allowed'));
    return;
  }
  else{
    document.getElementById('alert_msg').innerHTML='';
  }

  forms_arr = document.getElementById('forms_to_search');
  for (var i = 0; i < forms_arr.options.length; i++) {
   if(forms_arr.options[i].selected ==true){
        $('.class_'+forms_arr.options[i].value).each(function(){
        id_arr = this.id.split('search_div_');
        var re = new RegExp('_','i');
        new_id = id_arr[1].replace(re, "|");
        new_id_arr = new_id.split('|');
        form_id = new_id_arr[0];
        form_dir = new_id_arr[1];
        mark_hilight(form_id,form_dir,keys,case_sensitive);
      });

    }
  }
  if($('.hilite').length <1){
    if(keys != '')
    document.getElementById('alert_msg').innerHTML = jsText(xl('No results found'));
  }
  else{
    document.getElementById('alert_msg').innerHTML='';
    f_id = $('.hilite:first').attr('id');
    element = document.getElementById(f_id);
    element.scrollIntoView(false);
  }

}

function remove_mark_all(){ // clears previous search results if exists
  $('.report_search_div').each(function(){
    if (window.OpenEMRSearchHighlight) {
      window.OpenEMRSearchHighlight.unhighlight(this, 'hilite');
      window.OpenEMRSearchHighlight.unhighlight(this, 'hilite2');
    }
  });
  res_id = 0;
  res_array = [];
}
//
var last_visited = -1;
var last_clicked = "";
var cur_res =0;
function next(w_count){
  cur_res++;
  remove_mark_all();
  find_all();
  var index = -1;
  if(!($(".hilite")[0])) {
    return;
  }
  $('.hilite').each(function(){
    if($(this).is(":visible")){
      index = index+1;
      res_array[index] = this.id;
    }
  });
  $('.hilite').addClass("hilite2");
  $('.hilite').removeClass("hilite");
  var array_count = res_array.length;
  if(last_clicked == "prev"){
    last_visited = last_visited + (w_count-1);
   }
   last_clicked = "next";
  for(k=0;k<w_count;k++){
    last_visited ++;
      if(last_visited == array_count){
        cur_res = 0;
        last_visited = -1;
        next(w_count);
        return;
      }
      $("#"+res_array[last_visited]).addClass("next");
  }
  element = document.getElementById(res_array[last_visited]);
  element.scrollIntoView(false);

}

function prev(w_count){
  cur_res--;
  remove_mark_all();
  find_all();
  var index = -1;
  if(!($(".hilite")[0])) {
    return;
  }
  $('.hilite').each(function(){
    if($(this).is(":visible")){
      index = index+1;
      res_array[index] = this.id;
    }
  });
   $('.hilite').addClass("hilite2");
   $('.hilite').removeClass("hilite");
   var array_count = res_array.length;
   if(last_clicked == "next"){
    last_visited = last_visited - (w_count-1);
   }
   last_clicked = "prev";
  for(k=0;k<w_count;k++){
    last_visited --;
    if(last_visited < 0){
      cur_res = (array_count/w_count) + 1;
      last_visited = array_count;
      prev(w_count);
      return;
    }
  $("#"+res_array[last_visited]).addClass("next");

  }

  element = document.getElementById(res_array[last_visited]);
  element.scrollIntoView(false);
}
function clear_last_visit(){
  last_visited = -1;
  cur_res = 0;
  res_array = [];
  last_clicked = "";
}

function get_word_count(form_id, form_dir, keys, case_sensitive) {
  var trimmed = keys.replace(/^\s+|\s+$/g, '');
  if (trimmed === '') {
    return;
  }
  // w_count = number of search tokens so navigation steps past one full "result"
  // (one mark per token) at a time. Derived from the query, not from live DOM
  // state, so it stays correct after hilite→hilite2 class swaps in next()/prev().
  var tokens = trimmed.split(/[\s,]+/).filter(function(t) { return t.length > 0; });
  return tokens.length;
}

function next_prev(action){
  var w_count =0;
  case_sensitive = false;
  if ($('#search_case').attr('checked')) {
      case_sensitive = true;
  }
  var keys = document.getElementById('search_element').value;
  var match = null;
  match = keys.match(/[\^$.|?+()\\~`!@#%&+={}<>]{1,}/);
  if(match){
    document.getElementById('alert_msg').innerHTML = jsText(xl('Special characters are not allowed'));
    return;
  }
  else{
    document.getElementById('alert_msg').innerHTML='';
  }
  forms_arr = document.getElementById('forms_to_search');
  for (var i = 0; i < forms_arr.options.length; i++) {
   if(forms_arr.options[i].selected ==true){
        $('.class_'+forms_arr.options[i].value).each(function(){
        id_arr = this.id.split('search_div_');
        var re = new RegExp('_','i');
        new_id = id_arr[1].replace(re, "|");
        new_id_arr = new_id.split('|');
        form_id = new_id_arr[0];
        form_dir = new_id_arr[1];
        w_count = get_word_count(form_id,form_dir,keys,case_sensitive);
      });
      if(!isNaN(w_count)){
        break;
      }
    }
  }
  if(w_count <1){
    if(keys != '')
    document.getElementById('alert_msg').innerHTML = jsText(xl('No results found'));
  }
  else{
    document.getElementById('alert_msg').innerHTML='';
    if(action == 'next'){
     next(w_count);
    }
    else if (action == 'prev'){
     prev(w_count);
    }
    var tot_res = res_array.length/w_count;
  if(tot_res > 0){
	document.getElementById('alert_msg').innerHTML = jsText(xl('Showing result')) + ' ' + cur_res + ' ' + jsText(xl('of')) + ' ' + tot_res;
  }
  }

}
