/**
 * Basic javascript setup for the fee sheet review features
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var fee_sheet_new = webroot + "/interface/forms/fee_sheet/new.php";


var review_path = webroot + "/interface/forms/fee_sheet/review/";
var review_ajax = review_path + "fee_sheet_ajax.php";

var ajax_fee_sheet_options = review_path + "fee_sheet_options_ajax.php";
var justify_ajax = review_path + "fee_sheet_justify.php";

var ajax_fee_sheet_search = review_path + "fee_sheet_search_ajax.php";

var display_table_selector = "table[name='selected_codes']";

function add_review_button() {
    var review = $("<input type='button' class='btn btn-primary'/>");
    review.attr("value", review_tag);
    review.attr("data-bind", "click: review_event")
    var td = $("<td class='review_td'></td>");
    td.append(review)
    var template = $("<div class='review'></div>").appendTo(td);
    template.attr("data-bind", "template: {name: 'review-display', data: review}");
    // This makes the Review button first in the row.
    // $("[name='search_term']").parent().parent().prepend(td); // left the  original code alone
    $("#copay_review tr:first").append(td);
    return td;
}

function get_fee_sheet_options(level) {
    fee_sheet_options = [];
    var fso = $.ajax(ajax_fee_sheet_options, {
        type: "GET",
        data: {
            pricelevel: level
        },
        async: false,
        dataType: "json"
    });
    var json_options = JSON.parse(fso.responseText)['fee_sheet_options'];
    for (var idx = 0; idx < json_options.length; idx++) {
        var cur = json_options[idx];
        fee_sheet_options.push(new fee_sheet_option(cur.code, cur.code_type, cur.description, cur.price));
    }
    return fee_sheet_options;
}

var view_model;

function initialize_review() {
    var review = add_review_button();

    view_model = new fee_sheet_review_view_model();
    view_model.displayReview = ko.observable(false);
    get_fee_sheet_options('standard');
    ko.applyBindings(view_model, review.get(0));
}
$(initialize_review);
