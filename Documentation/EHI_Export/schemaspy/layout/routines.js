var filterBy = function(functionType) {
    $.fn.dataTableExt.afnFiltering.length = 0;
    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var type = data[1]; // use data for the Type column

            if ( type.toUpperCase() == functionType || functionType == 'All' )
            {
                return true;
            }
            return false;
        }
    );
}

$(document).ready(function() {
	var activeObject;
    var table = $('#routine_table').DataTable( {
        lengthChange: false,
		ordering: true,
		paging: config.pagination,
		pageLength: 50,
		autoWidth: true,
		processing: true,
		order: [[ 0, "asc" ]],
        buttons: [
            {
                text: 'All',
                action: function ( e, dt, node, config ) {
                    filterBy('All');
                    if (activeObject != null) {
                        activeObject.active(false);
                    }
                    table.draw();
                }
            },
            {
                text: 'Functions',
                action: function ( e, dt, node, config ) {
                    filterBy('FUNCTION');
                    if (activeObject != null) {
                        activeObject.active(false);
                    }
                    this.active( !this.active() );
                    activeObject = this;
                    table.draw();
                }
            },
            {
                text: 'Procedures',
                action: function ( e, dt, node, config ) {
                    filterBy('PROCEDURE');
                    if (activeObject != null) {
                        activeObject.active(false);
                    }
                    this.active( !this.active() );
                    activeObject = this;
                    table.draw();
                }
            },
            {
                extend: 'columnsToggle',
                columns: '.toggle'
            }
        ]
    } );

    //schemaSpy.js
    dataTableExportButtons(table);
} );
