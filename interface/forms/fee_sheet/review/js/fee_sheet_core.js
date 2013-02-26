/**
 * Core javascript functions for the fee sheet review features
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

function save_fee_form(callback)
{
    top.restoreSession();
    var form_data=$("form").serialize()+"&bn_save='Save'";
    $.post(fee_sheet_new,form_data,callback);
}

function refresh_codes()
{
    top.restoreSession();
    $.post(fee_sheet_new,{},function(data){
        update_display_table(data);
    });
}

function update_display_table(data)
{
    var new_info=$(data);
    var new_table=new_info.find(display_table_selector);
    $(display_table_selector).replaceWith(new_table);  

    // need refresh the diagnosis list
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
}

// This function is used force an immediate save when choosing codes
function codeselect_and_save(selobj)
{
 var i = selobj.selectedIndex;
 if (i > 0) {
    top.restoreSession();
    var f = document.forms[0];
    f.newcodes.value = selobj.options[i].value;
    top.restoreSession();

    // Submit the newly selected code.
    var form_data=$("form").serialize();
    $.post(fee_sheet_new,form_data,
        function(data)
        {
            f.newcodes.value="";
            // Clear the selection
            $(selobj).find("option:selected").prop("selected",false);
            update_display_table(data);
            save_fee_form(refresh_codes);
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

function justify_start(evt)
{
    var jqElem=$(this);
    var parent=jqElem.parent()
    var template_div=parent.find("div.justify_template");
    if(template_div.length==0)
    {
        var template_div=$("<div class='justify_template'></div>");
        template_div.attr("data-bind","template: {name: 'justify-display', data: justify}");
        jqElem.after(template_div);      
    }
    $(".cancel_dialog").click();
    var current_justify_choices=parse_row_justify(parent.parent());
    var justify_model=new fee_sheet_justify_view_model(parent.attr("billing_id"),enc,pid,current_justify_choices);
    ko.applyBindings(justify_model,template_div.get(0));
}
function tag_justify_rows(display)
{
    var justify_selectors=display.find("select[onchange^='setJustify']").parent();
    var justify_rows=justify_selectors.parent("tr")
    var justify_td=justify_rows.children("td:first-child").addClass("has_justify");
    justify_td.each(function(idx,elem){
        // This code takes the label text and "wraps it around a span for e"
        var jqElem=$(elem);
        if(jqElem.find("a.justify_label").length==0)
        {
            var label=jqElem.text();
            var html=jqElem.html().substr(label.length);
            jqElem.html(html);
            $("<a class='justify_label'>"+label+"</a>").appendTo(jqElem).on({click:justify_start}).attr("title",justify_click_title);;        
        }
    });
    var id_fields=justify_rows.find("input[type='hidden'][name$='[id]']");
    id_fields.each(function(idx,elem){
        var jqElem=$(elem);
        var td=jqElem.parent();
        td.addClass("has_id");
        td.attr("billing_id",jqElem.attr("value"));
    });
    
}


function setup_core()
{
    codeselect=codeselect_and_save;
    tag_justify_rows($(display_table_selector));
}

setup_core();