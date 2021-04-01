/**
 * view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function populate_contraception_products(data)
{
    var category = new code_category("Products:" + data.method);
    var products = data['products'];
    for(var idx = 0; idx < products.length; idx++) {
        var cur_product = products[idx];
        var product_code = "PROD|" + cur_product.drug_id + "|" + cur_product.selector;
        // Per CV 2018-12-13, internal product ID should not be displayed.
        // var title=cur_product.drug_id + ":" + cur_product.selector;
        var title = cur_product.selector;
        if(cur_product.name !== cur_product.selector) {
            title += " " + cur_product.name;
        }
        var choice = new code_choice(title, product_code);
        category.codes.push(choice);
    }
    if(products!==null) {
        codes_choices_vm.categories.push(category);
    }
}

function lookup_contraception_products()
{
    var conmeth = $("input[name='ippfconmeth']");
    var conmethcode = conmeth.val();
    var methods_elements = $("tr > td.billcell > input[type='hidden'][name$='[method]']");
    var methods = [];
    methods_elements.each(function(idx, elem) {
        methods.push(elem.value);
    });
    conmethcode = methods_elements.get(0).value;
    if (methods.length != 0) {
        $.ajax(webroot+"/interface/forms/fee_sheet/contraception_products/ajax/find_contraception_products.php",
        {
            type: "POST",
            dataType: "json",
            data: {
                methods:methods
            },
            success: function(data)
            {
                for(var idx = 0; idx < data.length; idx++) {
                    populate_contraception_products(data[idx]);
                }
            }
        });
    }
}

lookup_contraception_products();
