/**
 * view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// End Events
// eslint-disable-next-line no-var
var codes_choices_vm = {
    categories: ko.observableArray(),
    active_category: ko.observable(false),
    show_choices: ko.observable(false),
};

function toggle_code(data, event) {
    data.selected(!data.selected());
}

function codes_ok(data, event) {
    codes_choices_vm.show_choices(false);
    const f = document.forms[0];
    const choices = codes_choices_vm.active_category().codes();
    for (let i = 0; i < choices.length; i += 1) {
        if (choices[i].selected()) {
            if (f.newcodes.value) f.newcodes.value += '~';
            f.newcodes.value += choices[i].value();
            choices[i].selected(false);
        }
    }
    if (f.newcodes.value) {
        codeselect(null);
    }
    return false;
}

function codes_cancel(data, event) {
    codes_choices_vm.show_choices(false);
    return false;
}

// Events
function set_active_category(data, event) {
    codes_choices_vm.active_category(data);
    codes_choices_vm.show_choices(true);
}

function code_category(title) {
    const self = this;
    this.title = ko.observable(title);
    this.codes = ko.observableArray();
    return this;
}

function code_choice(description, value) {
    const self = this;
    this.description = ko.observable(description);
    this.value = ko.observable(value);
    this.selected = ko.observable(false);
    return this;
}

function populate_vm_categories(idx, elem) {
    const jqElem = $(elem);
    jqElem.hide();
    jqElem.parent().parent().hide(); // select is child of a td and a tr.
    const title = jqElem.find("option[value='']").text();

    const category = new code_category(title);
    codes_choices_vm.categories().push(category);

    const choices = jqElem.find("option:not([value=''])");
    choices.each(function (idx, elem) {
        const jqChoice = $(elem);
        const description = jqChoice.text();
        const value = jqChoice.attr('value');
        const choice = new code_choice(description, value);
        category.codes().push(choice);
    });
}

function add_code_template(elem) {
    const template = $('<div></div>');
    template.attr('data-bind', "template: {name: 'code-choice-options'}");
    template.addClass('code-choices');
    elem.before(template);
    ko.applyBindings(codes_choices_vm, template.get(0));
    codes_choices_vm.active_category(codes_choices_vm.categories()[1]);
}

function analyze_codes() {
    const code_table = $("table[width='95%']");
    const categories = code_table.find("td[width='50%'] > select");
    categories.each(populate_vm_categories);
    add_code_template(code_table);
}

analyze_codes();
