$.fn.rowDetails = function(opts = {}) {
    let api = opts.api ? opts.api : null;
    let tformat = opts.format ? opts.format : () => {};
    let self = this;
    let rowDetailClass = opts.rowDetailClass ? opts.rowDetailClass : '';

    $(this).on('classChange', 'tbody', function() {
        let isShown = $(self).find('tbody tr.shown').length;
        let tr = $(self).find('thead tr th').closest('tr');

        if(isShown > 0) {
            tr.addClass('shown');
        } else {
            tr.removeClass('shown');
        }
    });

    this.expandAllRow = function(expand = true) {
        if(expand === true) {
            api.rows().every( function () {
                let tr = $(this.node());
                let row = api.row(tr);
                row.child(tformat(row.data()), 'no-padding row-details-tr ' + rowDetailClass).show();
                tr.addClass('shown').trigger('classChange');
            });
        } else {
            api.rows().every( function () {
                var tr = $(this.node());
                var row = api.row( tr );
                row.child.hide();
                tr.removeClass('shown').trigger('classChange');
            });
        }
    }

    $(this).on('click', 'tbody td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = api.row(tr);
        
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown').trigger('classChange');
        } else {
            // Open this row
            row.child(tformat(row.data()), 'no-padding row-details-tr ' + rowDetailClass).show();
            tr.addClass('shown').trigger('classChange');
        }
    });

    $(this).on('click', 'thead th .dt-control', function () {
        let tr = $(this).closest('tr');

        if(tr.hasClass( "shown" )) {
            //UnExpand Row Details
            self.expandAllRow(false);
            
        } else {
            //Expand Row Details
            self.expandAllRow(true);
        }
    });

    $(this).on('draw.dt', function () {
        //Expand Row Details
        self.expandAllRow(true);
    });

    return this;
}

$.fn.readmoretext = function(opts = {}) {
    $(this).each(function(i, pItem) {
        let p = pItem.querySelector('.content');
        if(Math.ceil(p.scrollHeight) > Math.ceil(p.offsetHeight)) {
            p.classList.add("truncated");
        } else {
            p.classList.remove("truncated");
        }
    });
}

if($.fn.dataTableExt != undefined) {
    $.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
        //redraw to account for filtering and sorting
        // concept here is that (for client side) there is a row got inserted at the end (for an add)
        // or when a record was modified it could be in the middle of the table
        // that is probably not supposed to be there - due to filtering / sorting
        // so we need to re process filtering and sorting
        // BUT - if it is server side - then this should be handled by the server - so skip this step
        if(oSettings.oFeatures.bServerSide === false){
            var before = oSettings._iDisplayStart;
            oSettings.oApi._fnReDraw(oSettings);
            //iDisplayStart has been reset to zero - so lets change it back
            oSettings._iDisplayStart = before;
            oSettings.oApi._fnCalculateEnd(oSettings);
        }
          
        //draw the 'current' page
        oSettings.oApi._fnDraw(oSettings);
    };
}

// Iframe readmore text
$.fn.textellipsis = function(opts = {}) {
    $(this).each(function(i, obj) {
        let eType = $(obj).data('ellipsis');

        if(eType == 'ellipsis') {
            let eParent = $(obj).parent();
            let eParentTag = $(eParent).prop("tagName");

            if(eParentTag == "TD") {
                $(eParent).addClass('ellipsisWrapText');
            }
        }
        
    });
}

// Iframe readmore text
$.fn.iframereadmoretext = function(opts = {}) {
    let self = this;
    let maxHeight =  opts['maxHeight'] ? opts['maxHeight'] : '80';
    let resetStatus =  opts['reset'] ? opts['reset'] : false;

    this.rmHandle = function(expand = true, obj) {
        let iframeWrapper = $(obj).parent().parent();
        let actionContainer = $(iframeWrapper).find('.actionContainer');

        let newHeight = obj.contentWindow.document.documentElement.scrollHeight;
        let rStatus = false;

        if(newHeight > maxHeight) {
            $(actionContainer).show();
            rStatus = true;
        } else {
            $(obj)[0].style.height = newHeight + 'px';
            $(actionContainer).hide();
            return;
        }

        if(rStatus === false) {
            return true;
        }

        if(expand == true) {
            $(obj)[0].style.height = newHeight + 'px';
            $(actionContainer).find('.rmBtn').hide();
            $(actionContainer).find('.rlBtn').show();
        } else if(expand == false) {
            $(obj)[0].style.height =  maxHeight + 'px';
            $(actionContainer).find('.rmBtn').show();
            $(actionContainer).find('.rlBtn').hide();
        }
    }

    this.init = function(obj) {
        if(obj.dataset.loaded !== '1') {
            obj.dataset.loaded = '1';
            obj.style.height = '0px';

            $(obj).wrap("<div class='iframeWrapper'></div>");
            $(obj).wrap("<div class='content'></div>");

            $($(obj).parent().parent()).append('<div class="actionContainer" style="display:none;"><a href="javascript:;" class="rmBtn" style="display:none;">(more)</a><a href="javascript:;"  class="rlBtn" style="display:none;">(less)</a></div>');

            $($(obj).parent().parent()).find('.content').append('<div class="loadingIcon"><span class="text"><i>Loading...</i></span></div>');
        }
    }

    this.afterInit = function(obj) {
        $($(obj).parent().parent()).find('.content .loadingIcon').remove();
        self.rmHandle(false, obj);
    }

    $(this).each(function(i, obj) {
        // Init
        self.init(obj);
        setTimeout(function(){
            if(obj.contentWindow.document.documentElement.scrollHeight > 9) {
                self.afterInit(obj);
            }
        },0);
    });

    if(resetStatus === true) {
        return true;
    }

    $(this).on("load", function() {
        self.afterInit($(this)[0]);
    });

    $($(this).parent().parent()).on('click', '.rmBtn', function() {
        self.rmHandle(true, $(this).parent().parent().find('iframe')[0]);
    });

    $($(this).parent().parent()).on('click', '.rlBtn', function() {
        self.rmHandle(false, $(this).parent().parent().find('iframe')[0]);
    });
}

function decodeHtmlString(text) {
    var map = {
        '&amp;': '&',
        '&#038;': "&",
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
        '&#8217;': "’",
        '&#8216;': "‘",
        '&#8211;': "–",
        '&#8212;': "—",
        '&#8230;': "…",
        '&#8221;': '”'
    };

    if(text != "" && text != null) {
        text = text.replace(/\\(.)/mg, "$1");
        text = text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
        return text;
    }

    return text;
};

function prepareColumns(columnList = []) {
    var colummsData = JSON.parse(columnList);
    var columns = []; 
    colummsData.forEach((item, index) => {
        if(item["name"]) {
            var item_data = item["data"] ? item["data"] : {};

            if(item["name"] == "dt_control") { 
                columns.push({ 
                    "data" : "dt_control",
                    ...item_data
                });
            } else {
                let colItem = { 
                    "data" : item["name"],
                    ...item_data
                };

                let needToRender = item_data['needToRender'] != undefined ? item_data['needToRender'] : true;
                if(needToRender !== false) {
                    colItem["render"] = function(data, type, row ) {
                        var defaultVal = item_data['defaultValue'] ? decodeHtmlString(item_data['defaultValue']) : "";
                        var colValue = decodeHtmlString(data);

                        return (colValue && colValue != "") ? colValue : defaultVal;
                    } 
                }

                columns.push(colItem);
            }
        }
    });

    return columns;
}