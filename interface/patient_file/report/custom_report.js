  /**
 *
 * Javascript extracted from Patient custom report.
 * Uses - jquery instance and SearchHighlight plug-in
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
 * @link    http://www.open-emr.org
 */

// Code for search & Highlight
function reset_highlight(form_id,form_dir,class_name) { // Removes <span class='hilite' id=''>VAL</span> with VAL
    $("."+class_name).each(function(){
    val = document.getElementById(this.id).innerHTML;
    $("#"+this.id).replaceWith(val);

  });
}
var res_id = 0;
function doSearch(form_id,form_dir,exact,class_name,keys,case_sensitive) { // Uses jquery SearchHighlight Plug in
  var options ={};
  var keys = keys.replace(/^\s+|\s+$/g, '') ;
  options = {
    exact     :exact,
    style_name :class_name,
    style_name_suffix:false,
    highlight:'#search_div_'+form_id+'_'+form_dir,
    keys      :keys,
    set_case_sensitive:case_sensitive
    }
    $(document).SearchHighlight(options);
      $('.'+class_name).each(function(){
      res_id = res_id+1;
      $(this).attr("id",'result_'+res_id);
    });
}

function remove_mark(form_id,form_dir){ // Removes all <mark> and </mark> tags
  var match1 = null;
  var src_str = document.getElementById('search_div_'+form_id+'_'+form_dir).innerHTML;
  var re = new RegExp('<mark>',"gi");
  var match2 = src_str.match(re);
  if(match2){
    src_str = src_str.replace(re,'');
  }
  var match2 = null;
  re = new RegExp('</mark>',"gi");
  if(match2){
    src_str = src_str.replace(re,'');
  }
  document.getElementById('search_div_'+form_id+'_'+form_dir).innerHTML=src_str;
}
function mark_hilight(form_id,form_dir,keys,case_sensitive){ // Adds <mark>match_val</mark> tags
  keys = keys.replace(/^\s+|\s+$/g, '') ;
  if(keys == '') return;
  var src_str = $('#search_div_'+form_id+'_'+form_dir).html();
  var term = keys;
  if((/\s+/).test(term) == true || (/['""-]{1,}/).test(term) == true){
    term = term.replace(/(\s+)/g,"(<[^>]+>)*$1(<[^>]+>)*");
    if(case_sensitive == true){
      var pattern = new RegExp("("+term+")", "g");
    }
    else{
      var pattern = new RegExp("("+term+")", "ig");
    }
    src_str = src_str.replace(/[\s\r\n]{1,}/g, ' '); // Replace text area newline or multiple spaces with single space
    src_str = src_str.replace(pattern, "<mark class='hilite'>$1</mark>");
    src_str = src_str.replace(/(<mark class=\'hilite\'>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/g,"$1</mark>$2<mark class='hilite'>$4");
    $('#search_div_'+form_id+'_'+form_dir).html(src_str);
      $('.hilite').each(function(){
      res_id = res_id+1;
      $(this).attr("id",'result_'+res_id);
    });
  }else{
    if(case_sensitive == true)
    doSearch(form_id,form_dir,'partial','hilite',keys,'true');
    else
    doSearch(form_id,form_dir,'partial','hilite',keys,'false');
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
  match = keys.match(/[\^\$\.\|\?\+\(\)\\~`\!@#%&\+={}<>]{1,}/);
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
    var id_arr = this.id.split('search_div_');
    var re = new RegExp('_','i');
    var new_id = id_arr[1].replace(re, "|");
    var new_id_arr = new_id.split('|');
    var form_id = new_id_arr[0];
    var form_dir = new_id_arr[1];
    reset_highlight(form_id,form_dir,'hilite');
    reset_highlight(form_id,form_dir,'hilite2');
    remove_mark(form_id,form_dir);
    res_id = 0;
    res_array =[];
  });
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

function get_word_count(form_id,form_dir,keys,case_sensitive){
  keys = keys.replace(/^\s+|\s+$/g, '') ;
  if(keys == '') return;
  var src_str = $('#search_div_'+form_id+'_'+form_dir).html();
  var term = keys;
  if((/\s+/).test(term) == true){
    term = term.replace(/(\s+)/g,"(<[^>]+>)*$1(<[^>]+>)*");
    if(case_sensitive == true){
      var pattern = new RegExp("("+term+")", "");
    }
    else{
      var pattern = new RegExp("("+term+")", "i");
    }
    src_str = src_str.replace(/[\s\r\n]{1,}/g, ' '); // Replace text area newline or multiple spaces with single space
    src_str = src_str.replace(pattern, "<mark class='hilite'>$1</mark>");
    src_str = src_str.replace(/(<mark class=\'hilite\'>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/,"$1</mark>$2<mark class='hilite'>$4");
    var res =[];
    res = src_str.match(/<mark class=\'hilite\'>/g);
    if(res != null){
      return res.length;
    }
  }else{
    return 1;
  }
}

function next_prev(action){
  var w_count =0;
  case_sensitive = false;
  if ($('#search_case').attr('checked')) {
      case_sensitive = true;
  }
  var keys = document.getElementById('search_element').value;
  var match = null;
  match = keys.match(/[\^\$\.\|\?\+\(\)\\~`\!@#%&\+={}<>]{1,}/);
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
