/*
 *
 */
var custom = function( args ) {

    var selectedColumn = args.selectedColumn;

    var fn_work = function() {
        var selected = $('#fld_table').val();

        if ( selected ) {
            var colSelect = $("#fld_column");
            fn_fill_columns( selected, function() {
                colSelect.val( selectedColumn )
            });
        }
    }

    var fn_fill_columns = function( table, callback ) {
        var colSelect = $("#fld_column");
        colSelect.find(".populated").remove();
        top.restoreSession();
        $.getJSON('index.php?action=edit!columns&table=' + table,
            function(data) {
                $.each( data, function(i, item) {
                    colSelect.append( "<option class='populated' value='" + item + "'>" + item + "</option>");
                });
                callback();
            }
        );
    }

    var fn_wire_events = function() {
        $('#fld_table').on("change", function() {
            fn_work();
        });
    }

    return {
            init: function() {
                $(function () {
                    fn_wire_events();
                    fn_work();
                });
            }
    };

}
