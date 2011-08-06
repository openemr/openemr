/* 
 */
var type_ahead = function( args ) {

    var url = args.url;
    var inputId = args.inputId;

    var fn_work = function() {
    }

    var fn_wire_events = function() {
        $(document).ready(function() {
            $( "#" + inputId ).autocomplete(
                url,
                {
                    delay:10,
                    minChars:2,
                    matchSubset:1,
                    matchContains:1,
                    cacheLength:10
                }
            );

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