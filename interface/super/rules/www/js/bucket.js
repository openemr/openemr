/*
 *
 */
var bucket = function( args ) {

    var fn_work = function() {
        //$('#change_category').trigger('click');
        //$('#change_item').trigger("click");
    }

    var fn_handle_change = function() {
        var that = $(this);
        var selected = that.val();
        var txt = this[this.selectedIndex].text;
        var textBox = that.parent().find("input[type='text']");
        textBox.val( txt );
        textBox.show();
        var hidden = $("#" + that.attr("data-hidden"));
        hidden.val( selected );
        that.remove();
    }

    var fn_prep_options = function( select, listType, hidden, showMe ) {
        select.append( "<option value=''></option>");
        top.restoreSession();
        var current = $("#"+hidden).val();
        var selected = '';
        $.getJSON('index.php?action=edit!' + listType,
            function(data) {
                $.each( data, function(i, item) {
                    if (item.code == current) {
                        selected="selected='selected'";
                    } else {
                        selected='';
                    }
                    select.append( "<option value='" + item.code + "' "+ selected +">" + item.lbl + "</option>");
                });
                //select.val('');
            }
        );
        select.attr("data-hidden", hidden );
        select.on("change", fn_handle_change );
        select.on("change", function() {
            showMe.show();
        });
    }

    var fn_wire_events = function() {
        $("[id^='change_category_']").on("click", function() {
            var type = this.id.match(/change_category_(.*)/)[1];
            $("#fld_category_lbl_"+type).hide();
            var select = $("<select class='form-control tight'></select>");
            $("#fld_category_lbl_"+type).parent().append( select );
            fn_prep_options( select, 'categories', 'fld_category_'+type, $(this) );
            $(this).hide();
        });
        
        $("[id^='change_item_']").on("click", function() {
            var type = this.id.match(/change_item_(.*)/)[1];
            $("#fld_item_lbl_"+type).hide();
            var select = $("<select class='form-control tight'></select>");
            $("#fld_item_lbl_"+type).parent().append( select );
            fn_prep_options( select, 'items', 'fld_item_'+type, $(this));
            $(this).hide();
        });
    }

    return {
            init: function() {
                $( document ).ready( function() {
                    fn_wire_events();
                    fn_work();
                });
            }
    };

}
