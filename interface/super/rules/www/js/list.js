/* 
 */
var list_rules = function( args ) {
    
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
        if ( !sort ) {
            sort = 'title';
        }

        $.getJSON('index.php?action=browse!getrows',
            function(data) {
                data.sort(function(a, b) {
                   if ( sort == 'title' ) {
                       return (a.title < b.title) ? -1 : (a.title > b.title) ? 1 : 0;
                   } else if ( sort == 'type' ) {
                       return (a.type < b.type) ? -1 : (a.type > b.type) ? 1 : 0;
                   }
                });

                for ( i in data ) {
                    fn_create_row( data[i]);
                }
            }
        );
    }

    var fn_sort = function( field ) {
        $('.rule_row.data').sortElements( function(a,b) {
            var x = $(a).find('.' + field).text();
            var y = $(b).find('.' + field).text();
            return (x < y) ? -1 : (x > y) ? 1 : 0;
        });
    }

    var fn_wire_events = function() {
        $('.header_title').click( function() {
            fn_sort( 'rule_title' );
        });

        $('.header_type').click( function() {
            fn_sort( 'rule_type' );
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