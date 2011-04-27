/*
 * 
 */
var bucket = function( args ) {

    var fn_work = function() {
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
        $.getJSON('index.php?action=edit!' + listType,
            function(data) {
                $.each( data, function(i, item) {
                    select.append( "<option value='" + item.code + "'>" + item.lbl + "</option>");
                });
                select.val('');
            }
        );
        select.attr("data-hidden", hidden );
        select.change( fn_handle_change );
        select.change( function() {
            showMe.show();
        });
    }

    var fn_wire_events = function() {
        $('#change_category').click( function() {
            $("#fld_category_lbl").hide();
            var select = $("<select></select");
            $("#fld_category_lbl").parent().append( select );
            fn_prep_options( select, 'categories', 'fld_category', $(this) );
            $(this).hide();
        });
        
        $('#change_item').click( function() {
            $("#fld_item_lbl").hide();
            var select = $("<select></select");
            $("#fld_item_lbl").parent().append( select );
            fn_prep_options( select, 'items', 'fld_item', $(this));
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
