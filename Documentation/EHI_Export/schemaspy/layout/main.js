var filterBy = function(tableType) {
$.fn.dataTableExt.afnFiltering.length = 0;
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        var type = data[data.length - 2]; // use data for the Type column, which is the second to last

        if ( type == tableType || tableType=='All' )
        {
            return true;
        }
        return false;
    }
);
}

$(document).ready(function() {
	var activeObject;
    var table = $('#database_objects').DataTable( {
        lengthChange: false,
        paging: config.pagination,
		pageLength: 50,
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
							text: 'Tables',
							action: function ( e, dt, node, config ) {
								filterBy('Table');
								if (activeObject != null) {
									activeObject.active(false);
								}
								this.active( !this.active() );
								activeObject = this;
								table.draw();
							}
						},
						{
							text: 'Views',
							action: function ( e, dt, node, config ) {
								filterBy('View');
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