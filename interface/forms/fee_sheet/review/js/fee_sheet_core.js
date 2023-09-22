/**
 * Core javascript functions for the fee sheet review features
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2013-2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function refresh_codes()
{
    top.restoreSession();
    $.post(fee_sheet_new,{"running_as_ajax": "1"},function(data) {
        update_display_table(data);
    });
}

function update_display_table(data)
{
    var rc = true; // this will be our return value

    // This creates a jquery object representing new DOM elements built from
    // HTML returned by the AJAX call to new.php.
    // Note trim() is necessary to avoid crashing when there are leading spaces...
    // that took me (Rod) a long time to figure out!
    var new_info=$(data.trim());

    // This finds the element "table[cellspacing='5']".
    // That is, the table element whose cellspacing attribute has a value of 5.
    // This happens to be the main table with all the line items in it.
    // Would be much better to assign it an ID and find by that.
    var new_table=new_info.find(display_table_selector);

    // Use this to replace the contents of this table in the current document.
    $(display_table_selector).replaceWith(new_table);

    // Copy in the latest form_checksum value.
    var new_checksum = new_info.find('input[name="form_checksum"]').val();
    $('input[name="form_checksum"]').val(new_checksum);

    // Show alertmsg if there is one. In that case we'll return false to indicate an error.
    var new_alertmsg = new_info.find('input[name="form_alertmsg"]').val();
    if (new_alertmsg) {
        alert(new_alertmsg);
        rc = false;
    }

    // need refresh the diagnosis list
    /* eslint-disable-next-line no-control-regex */
    var diag_regex=new RegExp("diags.push(.*);\n","g");
    var diags_matches=data.match(diag_regex);
    if(diags_matches!=null)
    {
        diags=new Array(); // clear the existing diags array
        for(var i=0;i<diags_matches.length;i++)
            {
                eval(diags_matches[i]);
            }
    }
    var justifications=$("select[onchange='setJustify(this)']");
    justifications.change();

    tag_justify_rows($(display_table_selector));

    return rc;
}

// This function is used to force an immediate save when choosing codes.
function codeselect_and_save(selobj)
{
  var i = selobj ? selobj.selectedIndex : -1;
  if (i) {
    var f = document.forms[0];
    if (selobj) f.newcodes.value = selobj.options[i].value;
    // Submit the newly selected code.
    top.restoreSession();
    var form_data=$("form").serialize() + "&running_as_ajax=1";
    $.post(fee_sheet_new,form_data,
      function(data) {
        // "data" here is the complete newly generated fee sheet HTML.
        f.newcodes.value = "";
        // Clear the selection
        if (selobj) $(selobj).find("option:selected").prop("selected",false);
        // We do a refresh and then save because refresh does not save the new line item.
        // Note the save is skipped if refresh returned an error.
        if (update_display_table(data)) {
          // Save the newly selected code. Parameter running_as_ajax tells new.php
          // to regenerate the form, including its checksum, after saving.
          var form_data = $("form").serialize() + "&bn_save=Save&running_as_ajax=1";
          $.post(fee_sheet_new, form_data,
            function(data) {
              update_display_table(data);
            }
          );
        }
      }
    );
  }
}

function parse_row_justify(row)
{
    var codes=row.find("select[onchange^='setJustify']").val().split(",");
    var retval=new Array();
    for(var idx=0;idx<codes.length;idx++)
        {
            var cur_code_string=codes[idx];
            if(cur_code_string.length>0)
                {
                    var code_parts=cur_code_string.split("|");
                    var justify=new code_entry({description:"",code:code_parts[1], code_type:code_parts[0]});
                    justify.priority(idx+1)
                    retval.push(justify);
                }
        }
    return retval;
}

function justify_start(evt) {
    const jqElem = $(this); // make sure is in scope after save.
    const wait = '<i id="wait" class="fa fa-sync fa-spin fa-1x"></i>';
    jqElem.after().append(wait); // for the slow pokes..
    let myForm = document.getElementById('fee_sheet_form');
    let formData = new FormData(myForm);
    formData.append('running_as_ajax', "1");
    formData.append('dx_update', "1");
    // save current form
    $.ajax({
        url: fee_sheet_new,
        processData: false,
        contentType: false,
        cache: false,
        type: 'POST',
        data: formData,
        beforeSend: function () {
            top.restoreSession();
        }
    }).done(function (data) {
        // now init justify
        let parent = jqElem.parent();
        let template_div = parent.find("div.justify_template");
        if (template_div.length == 0) {
            template_div = $("<div class='justify_template'></div>");
            template_div.attr("data-bind", "template: {name: 'justify-display', data: justify}");
            jqElem.after(template_div);
        }
        $(".cancel_dialog").click(); // this just ensures a dialog is not in view.
        $(display_table_selector).parent().css('min-height', '500px');
        let current_justify_choices = parse_row_justify(parent.parent());
        let justify_model = new fee_sheet_justify_view_model(parent.attr("billing_id"), enc, pid, current_justify_choices);
        ko.cleanNode(template_div.get(0));
        ko.applyBindings(justify_model, template_div.get(0));
        $("#wait").remove();
    });
}

function tag_justify_rows(display) {
    var justify_selectors = display.find("select[onchange^='setJustify']").parent();
    var justify_rows = justify_selectors.parent("tr");
    var justify_td = justify_rows.children("td:first-child").addClass("has_justify");
    justify_td.each(function (idx, elem) {
        // This code takes the label text and "wraps it around a span for e"
        var jqElem = $(elem);
        if (jqElem.find("a.justify_label").length == 0) {
            var pre_label = jqElem.html();
            if (pre_label.indexOf('<del>') !== -1) {
                // lets not add an anchor for justify if we are going to delete
                // procedure anyway so, continue onwards...
                return true;
            }
            var label = jqElem.text();
            var html = jqElem.html().substr(label.length);
            jqElem.html(html);
            $("<a class='justify_label'>" + label + "</a>").appendTo(jqElem).on({click: justify_start}).attr("title", justify_click_title);
        }
    });
    var id_fields = justify_rows.find("input[type='hidden'][name$='[id]']");
    id_fields.each(function (idx, elem) {
        var jqElem = $(elem);
        var td = jqElem.parent();
        td.addClass("has_id");
        td.attr("billing_id", jqElem.attr("value"));
    });

}

function setup_core()
{
  // KY on 2014-01-29 commented out this setup for the IPPF version. Not sure why.
  // I (Rod) made them conditional so we can share the same code.
  if (!ippf_specific) {
    codeselect=codeselect_and_save;
    tag_justify_rows($(display_table_selector));
  }
}

setup_core();
