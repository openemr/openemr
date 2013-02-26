/**
 * Basic javascript setup for the fee sheet review features
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
var fee_sheet_new=webroot+"/interface/forms/fee_sheet/new.php";


var review_path=webroot+"/interface/forms/fee_sheet/review/";
var review_ajax=review_path+"fee_sheet_ajax.php";

var ajax_fee_sheet_options=review_path+"fee_sheet_options_ajax.php";
var justify_ajax=review_path+"fee_sheet_justify.php";

var ajax_fee_sheet_search=review_path+"fee_sheet_search_ajax.php";

var display_table_selector="table[cellspacing='5']";

function add_review_button()
{

    var review=$("<input type='button'/>");
    review.attr("value",review_tag);
    review.attr("data-bind","click: review_event")
    var td=$("<td class='review_td'></td>");
    td.append(review)
    var template=$("<div class='review'></div>").appendTo(td);
    template.attr("data-bind","template: {name: 'review-display', data: review}");
    var copay=$("td > input:first").parent();
    copay.before(td);
    return td;
}


function get_fee_sheet_options(level)
{
    fee_sheet_options=[];
    var fso=$.ajax(ajax_fee_sheet_options,{type:"GET",data:{pricelevel: level},async:false,dataType:"json"});
    var json_options=JSON.parse(fso.responseText)['fee_sheet_options'];
    for(var idx=0;idx<json_options.length;idx++)
        {
            var cur=json_options[idx];
            fee_sheet_options.push(new fee_sheet_option(cur.code,cur.code_type,cur.description,cur.price));
        }
    return fee_sheet_options;
}


var view_model;
function initialize_review()
{
    var review=add_review_button();

    view_model=new fee_sheet_review_view_model();
    view_model.displayReview=ko.observable(false);
    get_fee_sheet_options('standard');
    ko.applyBindings(view_model,review.get(0));
}
$(document).ready(initialize_review);

