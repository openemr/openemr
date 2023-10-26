$(function() {
     var pgurl = window.location.href.substr(window.location.href.lastIndexOf("/")+1);
     $("#navbar-collapse ul li a").each(function(){
          if($(this).attr("href") == pgurl || $(this).attr("href") == '' )
          $(this).parent().addClass("active");
     })
});

function dataTableExportButtons(table) {
    $("<div class=\"row\">\n" +
        "    <div id=\"button_group_one\" class=\"col-md-6 col-sm-6\"></div>\n" +
        "    <div id=\"button_group_two\" class=\"col-md-2 col-sm-4 pull-right text-right\"></div>\n" +
        "</div>").prependTo('#' + table.table().container().id);
   new $.fn.dataTable.Buttons( table, {
       name: 'exports',
       buttons: [
           {
               extend:    'copyHtml5',
               text:      '<i class="fa fa-files-o"></i>',
               titleAttr: 'Copy'
           },
           {
               extend:    'excelHtml5',
               text:      '<i class="fa fa-file-excel-o"></i>',
               titleAttr: 'Excel'
           },
           {
               extend:    'csvHtml5',
               text:      '<i class="fa fa-file-text-o"></i>',
               titleAttr: 'CSV'
           },
           {
               extend:    'pdfHtml5',
               text:      '<i class="fa fa-file-pdf-o"></i>',
               orientation: 'landscape',
               titleAttr: 'PDF'
           }
       ]
   } );

    table.buttons().container().appendTo( '#' + table.table().container().id + ' #button_group_one' );
    table.buttons( 'exports', null ).container().appendTo( '#' + table.table().container().id + ' #button_group_two' );
}

 