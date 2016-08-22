/**
 * Copyright (C) 2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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

function toggle_code(data,event)
{
    data.selected(!data.selected());
}

function codes_ok(data,event)
{
    codes_choices_vm.show_choices(false);
        var f = document.forms[0];
        var choices=codes_choices_vm.active_category().codes();
        for (var i = 0; i < choices.length; ++i) {
          if (choices[i].selected()) {
            if (f.newcodes.value) f.newcodes.value += '~';
            f.newcodes.value += choices[i].value();
            choices[i].selected(false);
          }
        }
        if (f.newcodes.value) {
          // top.restoreSession();
          // f.submit();
          // This supports the option to immediately save:
          codeselect(null);
        }
    return false;
}

function codes_cancel(data,event)
{
    codes_choices_vm.show_choices(false);
    return false;
}

//Events
function set_active_category(data,event)
{
    codes_choices_vm.active_category(data);
    codes_choices_vm.show_choices(true);
}

//End Events
var codes_choices_vm={
    categories : ko.observableArray(),
    active_category:ko.observable(false),
    show_choices: ko.observable(false)
};

function code_category(title)
{
    var self=this;
    this.title=ko.observable(title);
    this.codes=ko.observableArray();
    return this;
}

function code_choice(description,value)
{
    var self=this;
    this.description=ko.observable(description);
    this.value=ko.observable(value);
    this.selected=ko.observable(false);
    return this;
}

function populate_vm_categories(idx,elem)
{
    var jqElem=$(elem);
    jqElem.hide();
    jqElem.parent().parent().hide(); // select is child of a td and a tr.
    var title=jqElem.find("option[value='']").text();
    
    var category=new code_category(title);
    codes_choices_vm.categories().push(category);
    
    var choices=jqElem.find("option:not([value=''])");
    choices.each(function(idx,elem)
        {
            var jqChoice=$(elem);
            var description=jqChoice.text();
            var value=jqChoice.attr("value");
            var choice=new code_choice(description,value);
            category.codes().push(choice);
        }
    );
}

function analyze_codes()
{
    var code_table=$("table[width='95%']");
    var categories=code_table.find("td[width='50%'] > select");
    categories.each(populate_vm_categories);
    add_code_template(code_table);
}

function add_code_template(elem)
{
    var template=$("<div></div>");
    template.attr("data-bind","template: {name: 'code-choice-options'}");
    template.addClass("code-choices");
    elem.before(template);
    ko.applyBindings(codes_choices_vm,template.get(0));
    codes_choices_vm.active_category(codes_choices_vm.categories()[1]);
}

analyze_codes();
