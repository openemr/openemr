/**
 * Basic javascript setup for the fee sheet review features
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// eslint-disable-next-line no-var
var fee_sheet_new = `${webroot}/interface/forms/fee_sheet/new.php`;
// eslint-disable-next-line no-var
var review_path = `${webroot}/interface/forms/fee_sheet/review/`;
// eslint-disable-next-line no-var
var review_ajax = `${review_path}fee_sheet_ajax.php`;
// eslint-disable-next-line no-var
var ajax_fee_sheet_options = `${review_path}fee_sheet_options_ajax.php`;
// eslint-disable-next-line no-var
var justify_ajax = `${review_path}fee_sheet_justify.php`;
// eslint-disable-next-line no-var
var ajax_fee_sheet_search = `${review_path}fee_sheet_search_ajax.php`;
// eslint-disable-next-line no-var
var display_table_selector = "table[name='selected_codes']";
// eslint-disable-next-line no-var
var view_model;

function add_review_button() {
    const review = $("<input type='button' class='btn btn-primary'/>");
    review.attr('value', review_tag);
    review.attr('data-bind', 'click: review_event');
    const td = $("<td class='review_td'></td>");
    td.append(review);
    const template = $("<div class='review'></div>").appendTo(td);
    template.attr('data-bind', "template: {name: 'review-display', data: review}");
    // This makes the Review button first in the row.
    // $("[name='search_term']").parent().parent().prepend(td); // left the  original code alone
    $('#copay_review tr:first').append(td);
    return td;
}

function get_fee_sheet_options(level) {
    const fee_sheet_options = [];
    const fso = $.ajax(ajax_fee_sheet_options, {
        type: 'GET',
        data: {
            pricelevel: level,
        },
        async: false,
        dataType: 'json',
    });
    const json_options = JSON.parse(fso.responseText).fee_sheet_options;
    for (let idx = 0; idx < json_options.length; idx += 1) {
        const cur = json_options[idx];
        fee_sheet_options.push(
            new fee_sheet_option(cur.code, cur.code_type, cur.description, cur.price),
        );
    }
    return fee_sheet_options;
}

function initialize_review() {
    const review = add_review_button();
    view_model = new fee_sheet_review_view_model();
    view_model.displayReview = ko.observable(false);
    get_fee_sheet_options('standard');
    ko.applyBindings(view_model, review.get(0));
}
$(initialize_review);
