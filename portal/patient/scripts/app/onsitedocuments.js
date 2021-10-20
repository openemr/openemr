/**
 * View logic for OnsiteDocuments
 *
 * application logic specific to the OnsiteDocument listing page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var page = {
    onsiteDocuments: new model.OnsiteDocumentCollection(),
    collectionView: null,
    onsiteDocument: null,
    modelView: null,
    isInitialized: false,
    isInitializing: false,
    isSaved: true,
    fetchParams: {filter: '', orderBy: '', orderDesc: '', page: 1, patientId: cpid, recid: recid},
    fetchInProgress: false,
    dialogIsOpen: false,
    isLocked: false,
    isCharted: false,
    isDashboard: (!isModule && !isPortal),
    lbfFormId: 0,
    isFrameForm: 0,
    lbfFormName: "",
    formOrigin: 0, // default portal

    init: function () {
        // ensure initialization only occurs once
        if (page.isInitialized || page.isInitializing) {
            return;
        }
        page.isInitializing = true;

        if (!$.isReady && console) {
            console.warn('page was initialized before dom is ready.  views may not render properly.');
        }

        // make the new button clickable
        $("#newOnsiteDocumentButton").click(function (e) {
            e.preventDefault();
            page.showDetailDialog();
        });

        // initialize the collection view
        this.collectionView = new view.CollectionView({
            el: $("#onsiteDocumentCollectionContainer"),
            templateEl: $("#onsiteDocumentCollectionTemplate"),
            collection: page.onsiteDocuments
        });

        // initialize the search filter
        $('#filter').change(function (obj) {
            page.fetchParams.filter = $('#filter').val();
            page.fetchParams.page = 1;
            page.fetchOnsiteDocuments(page.fetchParams);
        });

        // make the rows clickable ('rendered' is a custom event, not a standard backbone event)
        this.collectionView.on('rendered', function () {
            if (page.isDashboard) {
                $("#topnav").hide();
            }
            // No dups - turn off buttons if doc exist
            this.collection.each(function (model, index, list) {
                var tplname = model.get('docType')
                if (model.get('denialReason') != 'Locked') {
                    let parsed = tplname.split(/.*[\/|\\]/)[1];
                    if (typeof parsed === 'undefined') {
                        parsed = tplname;
                    }
                    $('#' + parsed.slice(0, -4)).hide();
                }
            });
            // attach click handler to the table rows for editing
            $('table.collection tbody tr').click(function (e) {
                e.preventDefault();
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
                var m = page.onsiteDocuments.get(this.id);
                page.showDetailDialog(m);
            });
            // make the headers clickable for sorting
            $('table.collection thead tr th').unbind().on('click', function (e) {
                e.preventDefault();
                var prop = this.id.replace('header_', '');
                // toggle the ascending/descending before we change the sort prop
                page.fetchParams.orderDesc = (prop == page.fetchParams.orderBy && !page.fetchParams.orderDesc) ? '1' : '';
                page.fetchParams.orderBy = prop;
                page.fetchParams.page = 1;
                page.fetchOnsiteDocuments(page.fetchParams);
            });
            // attach click handlers to the pagination controls
            $('.pageButton').click(function (e) {
                e.preventDefault();
                page.fetchParams.page = this.id.substr(5);
                page.fetchOnsiteDocuments(page.fetchParams);
            });
            // Let's scroll to document editor on selection.
            $('.history-btn').unbind().on('click', function (e) {
                /*e.preventDefault();
                var m = page.onsiteDocuments.get(this.offsetParent.parentElement.id);
                page.showDetailDialog(m);
                $('html,body').animate({scrollTop:0},500);*/
            });

            page.isInitialized = true;
            page.isInitializing = false;
            // if dashboard let's open first doc for review.
            if (page.isDashboard) {
                $('table.collection tbody tr:first').click();
            }
        });
// ---------  Get Collection ------------------------//
        this.fetchOnsiteDocuments(page.fetchParams);

        // initialize the model view
        this.modelView = new view.ModelView({
            el: $("#onsiteDocumentModelContainer")
        });

        // tell the model view where it's template is located
        this.modelView.templateEl = $("#onsiteDocumentModelTemplate");

        // template rendered ready
        this.modelView.on('rendered', function () {
            $('#templatecontent').on('focus', ".datepicker:not(.hasDatepicker)", function () {
                $(".datepicker").datetimepicker({
                    i18n: {
                        en: {
                            months: datepicker_xlMonths,
                            dayOfWeekShort: datepicker_xlDayofwkshort,
                            dayOfWeek: datepicker_xlDayofwk
                        },
                    },
                    yearStart: datepicker_yearStart,
                    rtl: datepicker_rtl,
                    format: datepicker_format,
                    scrollInput: datepicker_scrollInput,
                    scrollMonth: datepicker_scrollMonth,
                    timepicker: false
                });
            })

            $("#templatecontent").on('focus', ".datetimepicker:not(.hasDatetimepicker)", function () {
                $(".datetimepicker").datetimepicker({
                    i18n: {
                        en: {
                            months: datetimepicker_xlMonths,
                            dayOfWeekShort: datetimepicker_xlDayofwkshort,
                            dayOfWeek: datetimepicker_xlDayofwk
                        },
                    },
                    yearStart: datetimepicker_yearStart,
                    rtl: datetimepicker_rtl,
                    format: datetimepicker_format,
                    step: datetimepicker_step,
                    scrollInput: datepicker_scrollInput,
                    scrollMonth: datepicker_scrollMonth,
                    timepicker: true
                });
            });

            docid = page.onsiteDocument.get('docType');
            page.isLocked = (page.onsiteDocument.get('denialReason') === 'Locked');
            (page.isLocked) ? $("#printTemplate").show() : $("#printTemplate").hide();
            $("#chartHistory").hide();

            page.getDocument(page.onsiteDocument.get('docType'), cpid);
            if (page.isDashboard) { // review
                flattenDocument();
            }
            pageAudit.fetchParams.doc = page.onsiteDocument.get('id');
            pageAudit.fetchOnsitePortalActivities(pageAudit.fetchParams);

            if (page.isLocked) {
                $('#patientSignature').off();
                $('#patientSignature').css('cursor', 'default');
                $('#adminSignature').off();
                $('#adminSignature').css('cursor', 'default');
            } else if (!isModule) {
                // disable signatures in appropriate views
                if (!isPortal) {
                    $('#patientSignature').css('cursor', 'default');
                    $('#patientSignature').off();
                } else {
                    $('#adminSignature').css('cursor', 'default');
                    $('#adminSignature').off();
                }
            }
            if (!isPortal) {
                $("#signTemplate").hide();
                $("#Help").hide();
                if (page.isCharted || page.isLocked) {
                    $("#chartTemplate").hide();
                    $("#chartHistory").hide();
                    page.lbfFormName = '';
                    page.isFrameForm = 0;
                } else {
                    $("#chartTemplate").show();
                }
                isModule ? $("#printTemplate").show() : $("#printTemplate").hide();
                $("#submitTemplate").hide();
                $("#sendTemplate").hide();
                $("#downloadTemplate").show();
                isModule ? $("#dismissOnsiteDocumentButton").show() : $("#dismissOnsiteDocumentButton").hide();
                ((isModule || page.isFrameForm) && !page.isLocked) ? $("#saveTemplate").show() : $("#saveTemplate").hide();
                isModule ? $("#homeTemplate").show() : $("#homeTemplate").hide();
                (page.lbfFormName === 'HIS' && !page.isLocked) ? $("#chartHistory").show() : $("#chartHistory").hide();

                $("#chartTemplate").unbind().on('click', function (e) {
                    e.preventDefault();
                    if (page.isFrameForm) {
                        let formFrame = document.getElementById('lbfForm');
                        $(window).one("message onmessage", (e) => {
                            if (event.origin !== window.location.origin) {
                                signerAlertMsg("Remote is not same origin!)", 15000);
                                return false;
                            }
                            if (isModule || page.isFrameForm) {
                                model.reloadCollectionOnModelUpdate = false;
                            }
                            page.lbfFormId = e.originalEvent.data.formid;
                            page.onsiteDocument.set('encounter', page.lbfFormId);
                            let url = webroot_url +
                                "/interface/forms/LBF/printable.php?return_content=" +
                                "&formname=" + encodeURIComponent(page.lbfFormName) +
                                "&formid=" + encodeURIComponent(page.lbfFormId) +
                                "&visitid=0&patientid=" + encodeURIComponent(cpid);
                            fetch(url).then(response => {
                                if (!response.ok) {
                                    throw new Error('Network Error.');
                                }
                                return response.json()
                            }).then(content => {
                                flattenDocument();
                                let templateContents = document.getElementById('templatecontent').innerHTML;
                                templateContents = templateContents.replace(/<script.*?>.*?<\/script>/ig, '');
                                templateContents = templateContents.replace(/(<\/iframe>)/g, '');
                                content = templateContents.replace(/(<iframe[^>]+>)/g, content);
                                if (content) {
                                    page.chartTemplate(content);
                                }
                            }).catch(error => {
                                console.error('Error:', error);
                                alert(error);
                            });
                        });
                        // request a save for lbf
                        formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                    } else {
                        page.chartTemplate('', 'flat');
                    }
                });

                $("#downloadTemplate").unbind().on('click', function (e) {
                    // just render the existing model and not save template.
                    // For downloads we just want to give user a chance to dispose/view rendered
                    // document and leave template intact for further edits before charting.
                    // I'm still unsure how useful a download is when to finish a review, charting document
                    // is necessary. I know eventually, I can do better:)
                    e.preventDefault();
                    if (page.isFrameForm) {
                        let formFrame = document.getElementById('lbfForm');
                        let frameDocument = formFrame.contentDocument || formFrame.contentWindow.document;
                        // we don't want events piling up so this is a one shot.
                        $(window).one("message onmessage", (e) => {
                            if (event.origin !== window.location.origin) {
                                signerAlertMsg("Remote is not same origin!)", 15000);
                                return false;
                            }
                            if (isModule || page.isFrameForm) {
                                model.reloadCollectionOnModelUpdate = false;
                            }
                            page.lbfFormId = e.originalEvent.data.formid;
                            page.onsiteDocument.set('encounter', page.lbfFormId);
                            let url = webroot_url +
                                "/interface/forms/LBF/printable.php?return_content=" +
                                "&formname=" + encodeURIComponent(page.lbfFormName) +
                                "&formid=" + encodeURIComponent(page.lbfFormId) +
                                "&visitid=0&patientid=" + encodeURIComponent(cpid);
                            fetch(url).then(response => {
                                if (!response.ok) {
                                    throw new Error('Network Error LBF Render.');
                                }
                                return response.json();
                            }).then(documentContents => {
                                if (documentContents) {
                                    page.updateModel();
                                    flattenDocument();
                                    $("#cpid").val(cpid);
                                    $("#docid").val(docid);
                                    $("#handler").val('download');
                                    $("#status").val('downloaded');

                                    let templateContents = document.getElementById('templatecontent').innerHTML;
                                    templateContents = templateContents.replace(/(<\/iframe>)/g, '')
                                    documentContents = templateContents.replace(/(<iframe[^>]+>)/g, documentContents);
                                    $("#content").val(documentContents);
                                    signerAlertMsg("Waiting for Download.", 6500, "info");
                                    $("#template").submit();

                                    page.renderModelView(false);
                                }
                            }).catch(error => {
                                alert(error);
                                console.error('Error:', error);
                            });
                        });
                        // request a save for lbf
                        formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                    } else {
                        // don't save let charting do that.
                        flattenDocument();
                        let documentContents = document.getElementById('templatecontent').innerHTML;
                        $("#content").val(documentContents);
                        $("#template").submit();
                        signerAlertMsg(xl('Downloading Document!'), 1000, 'success', 'lg' );

                        page.renderModelView(false);
                    }
                });
            } else {
                $("#downloadTemplate").hide();
                $("#chartTemplate").hide();
                $("#chartHistory").hide();
                page.isLocked ? $("#saveTemplate").hide() : $("#saveTemplate").show();
                page.isLocked ? $("#sendTemplate").hide() : $("#sendTemplate").show();
                page.isLocked ? $("#submitTemplate").show() : $("#submitTemplate").hide();
            }

            $("#saveTemplate").unbind().on('click', function (e) {
                e.preventDefault();
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('lbfForm');
                    page.lbfFormId = 0;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            signerAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        model.reloadCollectionOnModelUpdate = false;
                        page.lbfFormId = e.originalEvent.data.formid;
                        page.onsiteDocument.set('encounter', page.lbfFormId);
                        if (page.onsiteDocument.get('denialReason') === 'In Review') {
                            pageAudit.onsitePortalActivity.set('status', 'waiting');
                        } else {
                            pageAudit.onsitePortalActivity.set('status', 'editing');
                        }
                        // save lbf iframe template
                        page.updateModel();
                    });
                    // post to submit and save content remote form.
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                } else {
                    if (page.onsiteDocument.get('denialReason') === 'In Review') {
                        pageAudit.onsitePortalActivity.set('status', 'waiting');
                    } else {
                        pageAudit.onsitePortalActivity.set('status', 'editing');
                    }
                    page.updateModel();
                }
            });

            // send to review and save current
            $("#sendTemplate").unbind().on('click', function (e) {
                e.preventDefault();
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('lbfForm');
                    let frameDocument = formFrame.contentDocument || formFrame.contentWindow.document;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            signerAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        model.reloadCollectionOnModelUpdate = false;
                        page.lbfFormId = e.originalEvent.data.formid;
                        page.onsiteDocument.set('encounter', page.lbfFormId);
                        pageAudit.onsitePortalActivity.set('status', 'waiting');
                        page.onsiteDocument.set('denialReason', 'In Review');
                        // save lbf iframe template
                        page.updateModel(true);
                    });
                    // post to submit and save content remote form.
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                } else {
                    model.reloadCollectionOnModelUpdate = false;
                    var documentContents = document.getElementById('templatecontent').innerHTML;
                    $("#content").val(documentContents);
                    pageAudit.onsitePortalActivity.set('status', 'waiting');
                    page.onsiteDocument.set('denialReason', 'In Review');
                    page.updateModel(true);
                }
            });

            // download from portal
            $("#submitTemplate").unbind().on('click', function () {
                if (page.onsiteDocument.get('denialReason') === 'In Review') {
                    pageAudit.onsitePortalActivity.set('status', 'waiting');
                } else {
                    pageAudit.onsitePortalActivity.set('status', 'editing');
                    flattenDocument();
                }
                var documentContents = document.getElementById('templatecontent').innerHTML;
                $("#docid").val(docid);
                $("#content").val(documentContents);

                $("#template").submit();

                page.updateModel();
            });

            $("#chartHistory").unbind().on('click', function () {
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('lbfForm');
                    page.lbfFormId = 0;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            signerAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        // cool it just in case then save history to chart.
                        setTimeout("page.chartHistory();", 1000);
                    });
                    // post to submit
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                }
            });
        });

        if (newFilename) { // auto load new on init. once only.
            page.newDocument(cpid, cuser, newFilename);
            newFilename = '';
        }

        page.formOrigin = isPortal ? 0 : isModule ? 2 : 1;
    },
// page scoped functions
    chartHistory: function () {
        let formFrame = document.getElementById('lbfForm');
        formFrame.contentWindow.postMessage({submitForm: 'history'}, window.location.origin);
    },
    chartTemplate: function (documentContents = '', type = '') {
        if (type === 'flat') {
            flattenDocument();
            documentContents = document.getElementById('templatecontent').innerHTML;
        }
        $("#docid").val(docid);
        $("#handler").val('chart');
        $("#status").val('charted');

        signerAlertMsg(alertMsg1, 4000, "warning");
        let posting = $.post("./../lib/doc_lib.php", {
            cpid: cpid,
            docid: docid,
            catid: catid,
            content: documentContents,
            handler: "chart"
        });
        posting.done(function (rtn) {
            if (rtn.indexOf("ERROR") !== -1) {
                alert(rtn);
                return false;
            }
            page.isCharted = true;
            page.isLocked = true;
            $("#chartTemplate").hide();
            $("#chartHistory").hide();
            $("#saveTemplate").hide();
            page.isFrameForm = false;
            page.lbfFormName = '';
            if (isModule || page.isFrameForm) {
                model.reloadCollectionOnModelUpdate = false;
            }
            $('#templatecontent').html(documentContents);
            page.updateModel();
        });
    },
    /**
     * Fetch the collection data from the server
     * @param object params passed through to collection.fetch
     * @param bool true to hide the loading animation
     */
    fetchOnsiteDocuments: function (params, hideLoader) {
        // persist the params so that paging/sorting/filtering will play together nicely
        page.fetchParams = params;
        if (page.fetchInProgress) {
            if (console) {
                console.log('supressing fetch because it is already in progress');
            }
        }
        page.fetchInProgress = true;
        if (!hideLoader) {
            app.showProgress('loader');
        }

        page.onsiteDocuments.fetch({
            data: params,
            success: function () {
                if (page.onsiteDocuments.collectionHasChanged) {
                }
                app.hideProgress('loader');
                page.fetchInProgress = false;
            },
            error: function (m, r) {
                app.appendAlert(app.getErrorMessage(r), 'alert-error', 0, 'collectionAlert');
                app.hideProgress('loader');
                page.fetchInProgress = false;
            }
        });
    },

    newDocument: function (pid, user, templateName) {
        docid = templateName;
        cuser = user;
        cpid = pid;
        isNewDoc = true;
        m = new model.OnsiteDocumentModel();
        m.set('docType', docid);
        m.set('denialReason', 'New');
        $('#docid').val('docid');
        $('#status').val('New');
        page.showDetailDialog(m); // saved in rendered event
    },

    getDocument: function (templateName, pid) {
        $(".helpHide").removeClass("d-none");
        let currentName = page.onsiteDocument.get('docType');
        let currentNameStyled = currentName.substr(0, currentName.lastIndexOf('.')) || currentName;
        currentNameStyled = currentNameStyled.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, ' ');
        page.isFrameForm = 0;
        page.lbfFormId = 0;
        page.lbfFormName = '';
        if (currentName === templateName && currentName && !isNewDoc) {
            // update form for any submits.(downloads and prints)
            $("#docid").val(currentName);
            // get document template
            let templateContents = page.onsiteDocument.get('fullDocument');
            page.lbfFormId = page.onsiteDocument.get("encounter") ?? 0;
            page.isFrameForm = templateContents.includes("</iframe>");
            if (page.isFrameForm) {
                $("#saveTemplate").show();
                // @todo v6.0 add form name to table on create
                const regex = /^.*(page.lbfFormName)="(\w+)/s;
                let m;
                if ((m = regex.exec(templateContents)) !== null) {
                    page.lbfFormName = m[2];
                } else {
                    signerAlertMsg("There is an issue loading document. Missing Name Error.");
                    return false;
                }
                templateContents = templateContents.replace(/(isPortal=)\d/, "isPortal=" + isPortal);
                templateContents = templateContents.replace(/(formOrigin=)\d/, "formOrigin=" + page.formOrigin);
            }
            // init editor. if a frame, will use iframe src href.
            $('#templatecontent').html(templateContents);
            // normal text/html template directives are still valid with visit LBF.
            restoreDocumentEdits();
            $('.signature').each(function () {
                // set/reset cursor default for all
                $(this).css('cursor', 'pointer');
                if (isModule) {
                    // Make sure current user witness signature
                    $(this).attr('data-user', cuser);
                }
            });
            initSignerApi();
        } else { // this makes it a new template
            var liburl = webRoot + '/portal/lib/download_template.php';
            $.ajax({
                type: "POST",
                url: liburl,
                data: {docid: templateName, pid: pid, isModule: isModule},
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error: Get Document");
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    $("#docid").val(templateName);
                    $('#templatecontent').html(templateHtml);
                    if (isNewDoc) {
                        isNewDoc = false;
                        page.isSaved = false;
                        $("#printTemplate").hide();
                        $("#submitTemplate").hide();
                        //$("#sendTemplate").hide();
                        page.onsiteDocument.set('fullDocument', templateHtml);
                        if (isPortal) {
                            $('#adminSignature').css('cursor', 'default').off();
                        } else if (!isModule) {
                            $('#patientSignature').css('cursor', 'default').off();
                        }
                        bindFetch();
                        // new encounter form
                        // lbf has own signer instance. no binding here.
                        // page.lbfFormName & page.isFrameForm is set from template directive
                        $(function () {
                            // an iframe in <form><iframe src=???></iframe> this page.
                            if (page.isFrameForm) {
                                // a layout form
                                if (page.lbfFormName) {
                                    // iframe from template directive {EncounterDocument:LBFxxxxx}
                                    let url = webRoot + "/interface/forms/LBF/new.php" + "" +
                                        "?isPortal=" + encodeURIComponent(isPortal ? 1 : 0) +
                                        "&formOrigin=" + encodeURIComponent(page.formOrigin) +
                                        "&formname=" + encodeURIComponent(page.lbfFormName) + "&id=0";

                                    document.getElementById('lbfForm').src = url;
                                }
                            }
                        });
                    }
                }
            });
        }
        let cdate = page.onsiteDocument.get('createDate');
        let status = page.onsiteDocument.get('denialReason');
        let cnt = cdate.toString().indexOf("GMT");
        if (cnt !== -1) {
            cdate = cdate.toString().substring(0, cnt);
        }
        $('#docPanelHeader').append('&nbsp;<span class="bg-light text-dark px-2">' + jsText(currentNameStyled) + '</span>&nbsp;' +
            jsText(' Dated: ' + cdate + ' Status: ' + status));
        //$('#docTitle').html(jsText(currentNameStyled));
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
    }
    ,
    /**
     * show the doc for editing
     * @param model
     */
    showDetailDialog: function (m) {
        page.onsiteDocument = m ? m : new model.OnsiteDocumentModel();
        page.modelView.model = page.onsiteDocument;
        page.dialogIsOpen = true;
        if (page.onsiteDocument.id === null || page.onsiteDocument.id === '') {
            page.renderModelView(false);
        } else {
            page.onsiteDocument.fetch({
                success: function () {
                    if (page.isDashboard || page.onsiteDocument.get('denialReason') === 'Locked') {
                        page.renderModelView(false); // @todo TBD when should delete be allowed?
                    } else {
                        page.renderModelView(true);
                    }
                },
                error: function (m, r) {
                    app.appendAlert(app.getErrorMessage(r), 'alert-error', 0, 'modelAlert');
                }
            });
        }
    },

    /**
     * Render the model template in the container
     * @param bool show the delete button
     */
    renderModelView: function (showDeleteButton) {
        page.modelView.render();
        app.hideProgress('modelLoader');

        // initialize any special controls
        if (showDeleteButton) {
            // attach click handlers to the delete buttons
            $('#confirmDeleteOnsiteDocumentContainer').hide('fast');
            $('#deleteOnsiteDocumentButtonContainer').show();
            $('#deleteOnsiteDocumentButton').click(function (e) {
                e.preventDefault();
                $('#confirmDeleteOnsiteDocumentContainer').show('fast');
            });

            $('#cancelDeleteOnsiteDocumentButton').click(function (e) {
                e.preventDefault();
                $('#confirmDeleteOnsiteDocumentContainer').hide('fast');
            });

            $('#confirmDeleteOnsiteDocumentButton').click(function (e) {
                e.preventDefault();
                page.deleteModel();
            });
        } else {
            // no point in initializing the click handlers if we don't show the button
            $('#deleteOnsiteDocumentButtonContainer').hide();
        }

    },

    /**
     * update the model that is currently displayed in the dialog
     */
    updateModel: function (reload = false) {
        // reset any previous errors
        $('#modelAlert').html('');
        $('.control-group').removeClass('error');
        $('.help-inline').html('');
        if (page.isCharted) {
            page.onsiteDocument.set('denialReason', 'Locked');
            page.onsiteDocument.set('authorizingSignator', cuser);
        }
        // if this is new then on success we need to add it to the collection
        var isNew = page.onsiteDocument.isNew();
        var s = page.onsiteDocument.get('denialReason');
        if (!isNew && s == 'New' && s != 'In Review') {
            page.onsiteDocument.set('denialReason', 'Open');
            app.showProgress('modelLoader');
        }
        let isLink = $('#patientSignature').attr('src') ? $('#patientSignature').attr('src').indexOf('signhere') : -1;
        if (isLink !== -1) {
            $('#patientSignature').attr('src', signhere);
        }
        var ptsignature = $('#patientSignature').attr('src');
        if (ptsignature == signhere) {
            ptsignature = "";
        }

        page.formOrigin = isPortal ? 0 : 1;
        page.formOrigin = isModule ? 2 : page.formOrigin;
        let templateContent = document.getElementById('templatecontent').innerHTML;
        if (page.lbfFormName && page.lbfFormId) {
            // lbf templates are saved as iframe tag with src url for fetch content on doc load.
            // no frame content is maintained in onsite document activity but template directives are.
            templateContent = templateContent.replace("id=0", "id=" + page.lbfFormId);
        }
        page.onsiteDocument.save({
            'pid': cpid,
            'facility': page.formOrigin, /* 0 portal, 1 dashboard, 2 patient documents */
            'provider': page.onsiteDocument.get('provider'),
            'encounter': page.onsiteDocument.get('encounter'),
            'createDate': page.onsiteDocument.get('createDate'),
            'docType': page.onsiteDocument.get('docType'),
            'patientSignedStatus': ptsignature ? '1' : '0',
            'patientSignedTime': ptsignature ? new Date() : '0000-00-00',
            'authorizeSignedTime': page.onsiteDocument.get('authorizeSignedTime'),
            'acceptSignedStatus': page.onsiteDocument.get('acceptSignedStatus'),
            'authorizingSignator': page.onsiteDocument.get('authorizingSignator'),
            'reviewDate': (!isPortal) ? new Date() : '0000-00-00',
            'denialReason': page.onsiteDocument.get('denialReason'),
            'authorizedSignature': page.onsiteDocument.get('authorizedSignature'),
            'patientSignature': ptsignature,
            'fullDocument': templateContent,
            'fileName': page.onsiteDocument.get('fileName'),
            'filePath': page.onsiteDocument.get('filePath')
        }, {
            wait: true,
            success: function () {
                app.hideProgress('modelLoader');
                if (page.isCharted) {
                    pageAudit.onsitePortalActivity.set('status', 'closed');
                    pageAudit.onsitePortalActivity.set('pendingAction', 'completed');
                    pageAudit.onsitePortalActivity.set('actionUser', cuser);
                }
                pageAudit.onsitePortalActivity.set('date', page.onsiteDocument.get('createDate'));
                pageAudit.onsitePortalActivity.set('activity', 'document');
                pageAudit.onsitePortalActivity.set('patientId', cpid);
                pageAudit.onsitePortalActivity.set('tableAction', 'update');
                pageAudit.onsitePortalActivity.set('tableArgs', page.onsiteDocument.get('id'));
                pageAudit.onsitePortalActivity.set('narrative', page.onsiteDocument.get('docType'));
                pageAudit.onsitePortalActivity.set('actionTakenTime', new Date());
                pageAudit.updateModel();
                if (isNew) {
                    $('#confirmDeleteOnsiteDocumentContainer').hide('fast');
                    $('#deleteOnsiteDocumentButtonContainer').show();
                    //$("#printTemplate").show();
                    if (isPortal) {
                        $("#submitTemplate").show();
                        $("#sendTemplate").show();
                    } else {
                        $("#submitTemplate").hide();
                        $("#sendTemplate").hide();
                    }
                    isNewDoc = false;
                    page.onsiteDocuments.add(page.onsiteDocument)
                }
                if (model.reloadCollectionOnModelUpdate) {
                    page.fetchOnsiteDocuments(page.fetchParams, true);
                    page.showDetailDialog(page.onsiteDocument);
                }
                signerAlertMsg(msgSuccess, 3000, 'success');
                if (page.isCharted && isModule) {
                    $("#a_docReturn").click();
                    return;
                }
                if (reload) {
                    setTimeout("location.reload(true);", 4000);
                }
            },
            error: function (model, response, scope) {
                app.hideProgress('modelLoader');
                app.appendAlert(app.getErrorMessage(response), 'alert-error', 0, 'modelAlert');
            }
        });
    },
    /**
     * delete the model that is currently displayed in the dialog
     */
    deleteModel: function () {
        // reset any previous errors
        $('#modelAlert').html('');

        app.showProgress('modelLoader');

        page.onsiteDocument.destroy({
            wait: true,
            success: function () {
                signerAlertMsg(msgDelete, 4000, 'success');
                app.hideProgress('modelLoader');
                pageAudit.onsitePortalActivity.set('status', 'deleted');
                pageAudit.onsitePortalActivity.set('pendingAction', 'none');
                pageAudit.onsitePortalActivity.set('activity', 'document');

                pageAudit.onsitePortalActivity.set('pid', cpid);
                pageAudit.onsitePortalActivity.set('tableAction', 'delete');
                pageAudit.onsitePortalActivity.set('tableArgs', page.onsiteDocument.get('id'));
                pageAudit.onsitePortalActivity.set('narrative', 'Patient deleted un-charted template');
                pageAudit.updateModel()
                //pageAudit.onsitePortalActivity.destroy();

                if (model.reloadCollectionOnModelUpdate) {
                    // re-fetch and render the collection after the model has been updated
                    page.fetchOnsiteDocuments(page.fetchParams, true);
                    setTimeout("location.reload(true);", 3000);
                }
            },
            error: function (model, response, scope) {
                app.appendAlert(app.getErrorMessage(response), 'alert-error', 0, 'modelAlert');
                app.hideProgress('modelLoader');
            }
        });
    }
};
