/*
 *
 */
var rule_detail = function( args ) {

    var editable = args.editable;

    var fn_create_row = function( rowData ) {
        var clone = $('.rule_row.template').clone().removeClass('template');
        var anchor = clone.find('.rule_title a');
            anchor.text( rowData.title );
            anchor.attr('href', anchor.attr('href') + "&id=" + rowData.id);

            anchor = clone.find('.rule_type a');
            anchor.text( rowData.type );
            anchor.attr('href', anchor.attr('href') + "&id=" + rowData.id);
        $('.rule_container').append(clone);
        clone.show();
    }

    var fn_work = function( sort ) {
        if ( !editable ) {
            $(".action_link").hide();
            $(".left_col").hide();
        }
    }

    var fn_wire_events = function() {
        // todo
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