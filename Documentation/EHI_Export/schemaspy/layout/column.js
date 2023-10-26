var filterBy = function(tableType) {
$.fn.dataTableExt.afnFiltering.length = 0;
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {        
        var type = data[1]; // use data for the Type column
 
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
    var table = $('#column_table').DataTable( {
        deferRender: true,
		data: tableData,
        columns: [
            { data: "tableName" },
            { data: "tableType" },
            { data: "name" },
            { data: "type" },
            { data: "length" },
            { data: "nullable" },
            { data: "autoUpdated" },
            { data: "defaultValue" },
            { data: "comments" }
        ],
        columnDefs: [
            {
                targets: 0,
                render: function ( data, type, row, meta ) {
                    return '<a href="tables/'+row.tableFileName+'.html" target="_top">'+data+'</a>';
                }
            },
            {
                targets: 2,
                createdCell: function(td, cellData, rowData, row, col) {
                    if (rowData.keyTitle.length > 0) {
                        $(td).prop('title', rowData.keyTitle);
                    }
                    if (rowData.keyClass.length > 0) {
                        $(td).addClass(rowData.keyClass);
                    }
                }
            },
            {
                targets: 5,
                createdCell: function(td, cellData, rowData, row, col) {
                    if (cellData == '√') {
                        $(td).prop('title', "nullable");
                    }
                }
            },
            {
                targets: 6,
                createdCell: function(td, cellData, rowData, row, col) {
                    if (cellData == '√') {
                        $(td).prop('title', "Automatically updated by the database");
                    }
                }
            }
        ],
        lengthChange: false,
		paging: config.pagination,
		pageLength: 50,
		autoWidth: true,
		order: [[ 2, "asc" ]],		
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
