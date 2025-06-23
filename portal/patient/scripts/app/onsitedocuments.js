/**
 * View logic for OnsiteDocuments
 *
 * application logic specific to the OnsiteDocument listing page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
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
    isNewDoc: false,
    fetchParams: {filter: '', orderBy: '', orderDesc: '', page: 1, patientId: cpid, recid: 0, showActive: false},
    fetchInProgress: false,
    dialogIsOpen: false,
    isLocked: false,
    isCharted: false,
    isDashboard: (!isModule && !isPortal),
    isQuestionnaire: '',
    encounterFormId: 0,
    isFrameForm: 0,
    encounterFormName: "",
    formOrigin: 0, // default portal
    presentPatientSignature: false,
    presentAdminSignature: false,
    presentWitnessSignature: false,
    signaturesRequired: false,
    isFlattened: false,
    version: '',
    currentName: '',
    init: function () {
        // ensure initialization only occurs once
        if (page.isInitialized || page.isInitializing) {
            return;
        }
        page.isInitializing = true;
        localStorage.setItem('showActive', 'false');

        if (page.isDashboard) {
            page.fetchParams.recid = recid;
        }

        if (!$.isReady && console) {
            console.warn('page was initialized before dom is ready.  views may not render properly.');
        }

        if (isModule) {
            $("#sendTemplate").hide();
            $("#saveTemplate").hide();
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
                $("#topNav").hide();
            }
            // attach click handler to the table rows for editing
            $('table.collection tbody tr').click(function (e) {
                e.preventDefault();
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
                let m = page.onsiteDocuments.get(this.id);
                page.showDetailDialog(m);
            });
            // make the headers clickable for sorting
            $('table.collection thead tr th').unbind().on('click', function (e) {
                e.preventDefault();
                let prop = this.id.replace('header_', '');
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
            $('.template-item').unbind().on('click', function (e) {
                if (!isModule) {
                    $("#topNav").hide();
                    parent.document.getElementById('topNav').classList.add('collapse');
                }
            });
            $(document).ready(function () {
                const showActive = localStorage.getItem('showActive') === 'true';
                $('#active-checkbox').prop('checked', showActive);
                page.fetchParams.showActive = showActive;
                $('#active-checkbox').unbind().on('click', '', function (e) {
                    const showActive = $(this).is(':checked');
                    localStorage.setItem('showActive', showActive);
                    page.fetchParams.showActive = showActive;
                    page.fetchOnsiteDocuments(page.fetchParams);
                });
            });
            page.isInitialized = true;
            page.isInitializing = false;
            // if dashboard let's open first doc for review.
            if (page.isDashboard) {
                $('table.collection tbody tr:first').click();
            }
        });
// ---------  Get Collection ------------------------//
        const showActive = localStorage.getItem('showActive') === 'true';
        $('#active-checkbox').prop('checked', showActive);
        page.fetchParams.showActive = showActive;
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

            page.getDocument(page.onsiteDocument.get('docType'), cpid, page.onsiteDocument.get('filePath'));
            if (page.isDashboard) { // review
                flattenDocument().then(r => {
                });
                page.isFlattened = true;
            }
            pageAudit.fetchParams.doc = page.onsiteDocument.get('id');
            pageAudit.fetchOnsitePortalActivities(pageAudit.fetchParams);

            if (page.isLocked) {
                $('#patientSignature').off();
                $('#patientSignature').css('cursor', 'default');
                $('#adminSignature').off();
                $('#adminSignature').css('cursor', 'default');
                $('#witnessSignature').css('cursor', 'default');
                $('#witnessSignature').off();
            } else if (!isModule) {
                // disable signatures in appropriate views
                if (!isPortal) {
                    $('#patientSignature').css('cursor', 'default');
                    $('#patientSignature').off();
                    $('#witnessSignature').css('cursor', 'default');
                    $('#witnessSignature').off();
                } else {
                    $('#adminSignature').css('cursor', 'default');
                    $('#adminSignature').off();
                }
            }
            if (!isPortal) {
                $("#signTemplate").hide();
                $("#Help").hide();
                $("#showNav").hide();
                if (page.isCharted || page.isLocked) {
                    $("#chartTemplate").hide();
                    $("#chartHistory").hide();
                    page.encounterFormName = '';
                    page.isFrameForm = 0;
                } else {
                    $("#chartTemplate").show();
                }
                (isModule || page.isDashboard) ? $("#printTemplate").show() : $("#printTemplate").hide();
                $("#submitTemplate").hide();
                $("#sendTemplate").hide();
                $("#downloadTemplate").hide();
                isModule ? $(".dismissOnsiteDocumentButton").show() : $(".dismissOnsiteDocumentButton").hide();
                if ((isModule || page.isFrameForm || page.isDashboard) && !page.isLocked && page.currentName !== 'Help') {
                    $("#saveTemplate").show()
                    $("#chartTemplate").show();
                } else {
                    $("#chartTemplate").hide();
                    $("#saveTemplate").hide();

                }
                isModule ? $("#homeTemplate").show() : $("#homeTemplate").hide();
                (page.encounterFormName === 'HIS' && !page.isLocked) ? $("#chartHistory").show() : $("#chartHistory").hide();

                $("#chartTemplate").unbind().on('click', function (e) {
                    e.preventDefault();
                    if (page.isFrameForm) {
                        let formFrame = document.getElementById('encounterForm');
                        $(window).one("message onmessage", (e) => {
                            if (event.origin !== window.location.origin) {
                                asyncAlertMsg("Remote is not same origin!", 15000);
                                return false;
                            }
                            if (isModule || page.isFrameForm) {
                                model.reloadCollectionOnModelUpdate = false;
                            }
                            page.encounterFormId = e.originalEvent.data.formid;
                            page.onsiteDocument.set('encounter', page.encounterFormId);
                            let url = '';
                            if (page.encounterFormName.startsWith('LBF') || page.encounterFormName.startsWith('HIS')) {
                                url = webroot_url +
                                    "/interface/forms/LBF/printable.php?return_content=" +
                                    "&formname=" + encodeURIComponent(page.encounterFormName) +
                                    "&formid=" + encodeURIComponent(page.encounterFormId) +
                                    "&visitid=0&patientid=" + encodeURIComponent(cpid);
                            } else {
                                // first, ensure form name is valid
                                if (!page.verifyValidEncounterForm(page.encounterFormName)) {
                                    asyncAlertMsg("There is an issue loading form. Form does not exist.");
                                    return false;
                                }
                                url = webroot_url +
                                    "/interface/forms/" + encodeURIComponent(page.encounterFormName) + "/patient_portal.php" +
                                    "?formid=" + encodeURIComponent(page.encounterFormId);
                                if (page.isQuestionnaire) {
                                    url = webroot_url +
                                        "/interface/forms/questionnaire_assessments/patient_portal.php" +
                                        "?formid=" + encodeURIComponent(page.encounterFormId);
                                }
                            }
                            fetch(url).then(response => {
                                if (!response.ok) {
                                    throw new Error('Network Error.');
                                }
                                return response.json()
                            }).then(content => {
                                flattenDocument().then(r => {
                                });
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
                        page.chartTemplate('', 'flatten');
                    }
                });

                $("#downloadTemplate").unbind().on('click', function (e) {
                    // just render the existing model and not save template.
                    // For downloads, we just want to give user a chance to dispose/view rendered
                    // document and leave template intact for further edits before charting.
                    // I'm still unsure how useful a download is as to when to finish a review, charting document
                    // is necessary. I know eventually, I can do better:)
                    e.preventDefault();
                    if (page.isFrameForm) {
                        let formFrame = document.getElementById('encounterForm');
                        let frameDocument = formFrame.contentDocument || formFrame.contentWindow.document;
                        // we don't want events piling up so this is a one shot.
                        $(window).one("message onmessage", (e) => {
                            if (event.origin !== window.location.origin) {
                                asyncAlertMsg("Remote is not same origin!)", 15000);
                                return false;
                            }
                            if (isModule || page.isFrameForm) {
                                model.reloadCollectionOnModelUpdate = false;
                            }
                            page.encounterFormId = e.originalEvent.data.formid;
                            page.onsiteDocument.set('encounter', page.encounterFormId);
                            let url = '';
                            if (page.encounterFormName.startsWith('LBF') || page.encounterFormName.startsWith('HIS')) {
                                url = webroot_url +
                                    "/interface/forms/LBF/printable.php?return_content=" +
                                    "&formname=" + encodeURIComponent(page.encounterFormName) +
                                    "&formid=" + encodeURIComponent(page.encounterFormId) +
                                    "&visitid=0&patientid=" + encodeURIComponent(cpid);
                            } else {
                                // first, ensure form name is valid
                                if (!page.verifyValidEncounterForm(page.encounterFormName)) {
                                    asyncAlertMsg("There is an issue loading form. Form does not exist.");
                                    return false;
                                }
                                url = webroot_url +
                                    "/interface/forms/" + encodeURIComponent(page.encounterFormName) + "/patient_portal.php" +
                                    "?formid=" + encodeURIComponent(page.encounterFormId);
                                if (page.isQuestionnaire) {
                                    url = webroot_url +
                                        "/interface/forms/questionnaire_assessments/patient_portal.php" +
                                        "?formid=" + encodeURIComponent(page.encounterFormId);
                                }
                            }
                            fetch(url).then(response => {
                                if (!response.ok) {
                                    throw new Error('Network Error LBF Render.');
                                }
                                return response.json();
                            }).then(documentContents => {
                                if (documentContents) {
                                    page.updateModel();
                                    // will flatten for download but form editing remains.
                                    flattenDocument().then(rv => {
                                    });
                                    $("#cpid").val(cpid);
                                    $("#docid").val(docid);
                                    $("#handler").val('download');
                                    $("#status").val('downloaded');
                                    let templateContents = document.getElementById('templatecontent').innerHTML;
                                    templateContents = templateContents.replace(/(<\/iframe>)/g, '')
                                    documentContents = templateContents.replace(/(<iframe[^>]+>)/g, documentContents);
                                    $("#content").val(documentContents);
                                    asyncAlertMsg("Waiting for Download.", 6500, "info");
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
                        flattenDocument().then(rv => {
                        });
                        let documentContents = document.getElementById('templatecontent').innerHTML;
                        $("#content").val(documentContents);
                        $("#template").submit();
                        asyncAlertMsg('Downloading Document!', 1000, 'success', 'lg');
                        page.renderModelView(false);
                    }
                });
            } else {
                $("#downloadTemplate").hide();
                $("#chartTemplate").hide();
                $("#chartHistory").hide();
                if (page.version === 'Legacy' || autoRender + auditRender > 0) {
                    $("#saveTemplate").hide();
                } else {
                    if (page.currentName !== 'Help') {
                        page.isLocked ? $("#saveTemplate").hide() : $("#saveTemplate").show();
                        page.isLocked ? $("#sendTemplate").hide() : $("#sendTemplate").show();
                        page.isLocked ? $("#submitTemplate").show() : $("#submitTemplate").hide();
                    }
                }
            }
            $("#saveTemplate").unbind().on('click', function (e) {
                e.preventDefault();
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('encounterForm');
                    page.encounterFormId = 0;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            asyncAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        model.reloadCollectionOnModelUpdate = false;
                        page.encounterFormId = e.originalEvent.data.formid;
                        page.onsiteDocument.set('encounter', page.encounterFormId);
                        if (page.onsiteDocument.get('denialReason') === 'In Review') {
                            pageAudit.onsitePortalActivity.set('status', 'waiting');
                        } else {
                            page.onsiteDocument.set('denialReason', 'Editing');
                            pageAudit.onsitePortalActivity.set('status', 'editing');
                            pageAudit.onsitePortalActivity.set('pendingAction', 'patient submission');
                        }
                        // save lbf iframe template
                        page.updateModel(true);
                    });
                    // post to submit and save content remote form.
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                } else {
                    if (page.onsiteDocument.get('denialReason') === 'In Review') {
                        pageAudit.onsitePortalActivity.set('status', 'waiting');
                    } else {
                        page.onsiteDocument.set('denialReason', 'Editing');
                        pageAudit.onsitePortalActivity.set('status', 'editing');
                        pageAudit.onsitePortalActivity.set('pendingAction', 'patient submission');
                    }
                    page.updateModel(true);
                }
            });
            // send to review and save current
            $("#sendTemplate").unbind().on('click', function (e) {
                e.preventDefault();
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('encounterForm');
                    let frameDocument = formFrame.contentDocument || formFrame.contentWindow.document;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            asyncAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        model.reloadCollectionOnModelUpdate = false;
                        page.encounterFormId = e.originalEvent.data.formid;
                        page.onsiteDocument.set('encounter', page.encounterFormId);
                        pageAudit.onsitePortalActivity.set('status', 'waiting');
                        page.onsiteDocument.set('denialReason', 'In Review');
                        // save lbf iframe template
                        page.updateModel(true);
                        if (autoRender + auditRender > 0) {
                            auditRender = 0;
                            autoRender = 0;
                            location.assign(webroot_url + "/portal/patient/onsitedocuments?pid=" + encodeURIComponent(cpid))
                        }
                    });
                    // post to submit and save content remote form.
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                } else {
                    model.reloadCollectionOnModelUpdate = false;
                    // @TODO only need is for downloads and pdf
                    // let documentContents = document.getElementById('templatecontent').innerHTML;
                    // $("#content").val(documentContents);
                    pageAudit.onsitePortalActivity.set('status', 'waiting');
                    page.onsiteDocument.set('denialReason', 'In Review');
                    page.updateModel(true);
                    if (autoRender + auditRender > 0) {
                        auditRender = 0;
                        autoRender = 0;
                        location.assign(webroot_url + "/portal/patient/onsitedocuments?pid=" + encodeURIComponent(cpid));
                    }

                }
            });
            // download from portal
            $("#submitTemplate").unbind().on('click', function () {
                if (page.onsiteDocument.get('denialReason') === 'In Review') {
                    pageAudit.onsitePortalActivity.set('status', 'waiting');
                } else {
                    pageAudit.onsitePortalActivity.set('status', 'editing');
                    flattenDocument().then(r => {
                    });
                }
                let documentContents = document.getElementById('templatecontent').innerHTML;
                $("#docid").val(docid);
                // @TODO PHP submit will use hidden content embedded in form object.
                $("#content").val(documentContents);
                $("#template").submit();
                page.updateModel();
            });
            $("#chartHistory").unbind().on('click', function () {
                if (page.isFrameForm) {
                    let formFrame = document.getElementById('encounterForm');
                    page.encounterFormId = 0;
                    $(window).one("message onmessage", (e) => {
                        if (event.origin !== window.location.origin) {
                            asyncAlertMsg("Remote is not same origin!)", 15000);
                            return false;
                        }
                        // cool it just in case then save history to chart.
                        setTimeout("page.chartHistory();", 1000);
                    });
                    // post to submit
                    formFrame.contentWindow.postMessage({submitForm: true}, window.location.origin);
                }
            });
            $('.navCollapse .dropdown-menu>a').on('click', function () {
                $('.navbar-collapse').collapse('hide');
            });
            $('.navCollapse li.nav-item>a').on('click', function () {
                $('.navbar-collapse').collapse('hide');
            });
            if (page.version === 'Legacy' && isPortal && !page.isLocked) {
                alert(page.onsiteDocument.get('docType') + " is available for one last edit." + "\n" +
                    "Then document must be deleted and a new document submitted or submit this document for review. This is due to our new document workflow.\n" +
                    "We appreciate your patience."
                )
            }
        });
        // These are set on init for save alerts
        page.isFlattened = false;
        page.isSaved = true;

        page.formOrigin = isPortal ? 0 : isModule ? 2 : 1;

       /* Broke in FF!
       $(window).bind('beforeunload', function () {
            if (!page.isSaved) {
                // You have unsaved changes auto browser popup
                event.preventDefault();
                event.returnValue = '';
            }
        });*/
    },
// page scoped functions
    verifyValidEncounterForm: function (form) {
        let formNameValid = false;
        if (page.isQuestionnaire) {
            form = 'questionnaire_assessments';
        }
        for (let k = 0; k < formNamesWhitelist.length; k++) {
            if (formNamesWhitelist[k] == form) {
                formNameValid = true;
            }
        }
        return formNameValid;
    },
    handleHistoryView: function () {
        let historyHide = $('.historyHide');
        historyHide.toggleClass('d-none');
        if (historyHide.hasClass('d-none')) {
            $('.modelContainer').removeClass("d-none");
        } else {
            $('.modelContainer').addClass("d-none");
        }
    },
    /**
     * Fetch the passed in document id in editing status
     * @param id the document id in edit mode from history
     * @param pid
     * @param user
     * @param templateName
     */
    editHistoryDocument: function (id, pid, user, templateName) {
        let m = page.onsiteDocuments.get(id);
        page.showDetailDialog(m);
    },
    chartHistory: function () {
        let formFrame = document.getElementById('encounterForm');
        formFrame.contentWindow.postMessage({submitForm: 'history'}, window.location.origin);
    },
    postTemplate: function (documentContents) {
        $("#docid").val(docid);
        $("#handler").val('chart');
        $("#status").val('charted');
        asyncAlertMsg(alertMsg1, 3000, "warning");
        let posting = $.post("./../lib/doc_lib.php", {
            csrf_token_form: csrfTokenDoclib,
            cpid: cpid,
            docid: docid,
            catid: catid || '',
            content: documentContents,
            type: type,
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
            page.encounterFormName = '';
            if (isModule || page.isFrameForm) {
                model.reloadCollectionOnModelUpdate = false;
            }
            $('#templatecontent').html(documentContents);
            page.updateModel();
        });
    },
    chartTemplate: function (documentContents = '', type = '') {
        if (type === 'flatten' || page.version === 'Legacy') {
            flattenDocument().then(r => {
                documentContents = document.getElementById('templatecontent').innerHTML;
                page.postTemplate(documentContents);
            });
        } else {
            page.postTemplate(documentContents);
        }
    },
    /**
     * Fetch the collection data from the server
     * @param params
     * @param hideLoader
     */
    fetchOnsiteDocuments: function (params, hideLoader) {
        // persist the params so that paging/sorting/filtering will play together nicely
        page.fetchParams = params;
        if (page.fetchInProgress) {
            if (console) {
                console.log('suppressing fetch because it is already in progress');
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

    newDocument: function (pid, user, templateName, template_id) {
        docid = templateName;
        cuser = cuser > '' ? cuser : user;
        cpid = cpid > '0' ? cpid : pid;
        page.isNewDoc = true;
        m = new model.OnsiteDocumentModel();
        m.set('docType', docid);
        m.set('filePath', template_id);
        m.set('denialReason', 'New');
        $('#docid').val('docid');
        $('#template_id').val('template_id');
        $('#status').val('New');
        page.isSaved = true;
        page.showDetailDialog(m); // saved in rendered event
    },

    getDocument: function (templateName, pid, template_id) {
        $(".helpHide").removeClass("d-none");
        $('.modelContainer').removeClass("d-none");
        $("#editorContainer").removeClass('w-auto').addClass('w-100');
        page.currentName = page.onsiteDocument.get('docType');
        if (page.onsiteDocument.get('fileName') === '') {
            page.onsiteDocument.set('fileName', page.currentName);
        }
        let currentNameStyled = page.currentName.substr(0, page.currentName.lastIndexOf('.')) || page.currentName;
        currentNameStyled = currentNameStyled.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, ' ');

        if (page.currentName === 'Help') {
            page.isSaved = true;
            $("#saveTemplate").hide();
            $("#sendTemplate").hide();
            $("#submitTemplate").hide();
            if (isPortal) {
                $('#idShow').removeClass('d-none');
            } else {
                $('#idShow').addClass('d-none');
            }
            $(".dismissOnsiteDocumentButton").addClass("d-none");
        } else {
            $('#idShow').addClass('d-none');
            $(".dismissOnsiteDocumentButton").removeClass("d-none");
        }
        page.isFrameForm = 0;
        page.encounterFormId = 0;
        page.encounterFormName = '';
        if (page.currentName !== 'Help') {
            $("#topNav").hide();
        }
        if (page.currentName === templateName && page.currentName && !page.isNewDoc) {
            // update form for any submits.(downloads and prints)
            $("#docid").val(page.currentName);
            // get document template
            let templateContents = page.onsiteDocument.get('fullDocument');
            page.encounterFormId = page.onsiteDocument.get("encounter") ?? 0;
            page.isFrameForm = templateContents.includes("</iframe>");
            if (page.isFrameForm) {
                $("#saveTemplate").show();
                // modify the iFrames url in embedded content
                const regex = /^.*(page.encounterFormName)="(\w+)/s;
                let m;
                if ((m = regex.exec(templateContents)) !== null) {
                    page.encounterFormName = m[2];
                } else {
                    asyncAlertMsg("There is an issue loading document. Missing Name Error.");
                    return false;
                }
                templateContents = templateContents.replace(/(isPortal=)\d/, "isPortal=" + isPortal);
                templateContents = templateContents.replace(/(formOrigin=)\d/, "formOrigin=" + page.formOrigin);
                if (templateContents.includes('id=0')) {
                    templateContents = templateContents.replace(/(id=)\d/, "id=" + page.encounterFormId);
                }
            }
            // init editor. if a frame, will use iframe src href.
            $('#templatecontent').html(templateContents);
            page.version = $("#portal_version").val() ? $("#portal_version").val() : 'Legacy';
            if (page.version === 'Legacy') {
                restoreDocumentEdits();
            }
            $('.signature').each(function () {
                // set/reset cursor default for all
                $(this).css('cursor', 'pointer');
                if (isModule) {
                    // Make sure current user is set so can witness patient signature
                    $(this).attr('data-user', cuser);
                }
            });
            if (page.onsiteDocument.get('denialReason') === 'Locked') {
                $("#sendTemplate").hide();
                asyncAlertMsg("History Document. Edits unavailable", 2000, 'warning');
            }
            initSignerApi();
        } else { // this makes it a new template
            const libUrl = webRoot + '/portal/lib/download_template.php';
            $.ajax({
                type: "POST",
                url: libUrl,
                data: {template_id: template_id, docid: templateName, pid: pid, isModule: isModule},
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error: Get Document");
                },
                success: function (templateHtml) {
                    $("#docid").val(templateName);
                    page.onsiteDocument.set('fileName', templateName);
                    $('#templatecontent').html(templateHtml);
                    if (templateHtml.includes('Error') && (autoRender + auditRender) > 0) {
                        autoRender = auditRender = 0;
                        asyncAlertMsg("Onetime document is no longer available!" + "<br />" + templateHtml, 5000, 'warning')
                        .then(r => {
                            $("#Help").click();
                        });
                        return false;
                    } else if (templateHtml.includes('Error')) {
                        asyncAlertMsg(jsText("Sorry!") + " " + jsText(templateHtml) + "<br />" + jsText("Try to uncheck Activity table Show All."), 5000, 'danger')
                        .then(r => {
                            $("#Help").click();
                        });
                        return false;
                    }
                    page.version = $("#portal_version").val() ? $("#portal_version").val() : 'Legacy';
                    if (page.isNewDoc) {
                        page.isNewDoc = false;
                        $("#printTemplate").hide();
                        $("#submitTemplate").hide();
                        page.onsiteDocument.set('fullDocument', templateHtml);
                        if (isPortal) {
                            $('#adminSignature').css('cursor', 'default').off();
                        } else if (!isModule) {
                            $('#patientSignature').css('cursor', 'default').off();
                            $('#witnessSignature').css('cursor', 'default').off();
                        }
                        if (typeof bindFetch == 'function') {
                            bindFetch();
                        }
                        // new encounter form
                        // lbf has own signer instance. no binding here.
                        // page.encounterFormName & page.isFrameForm is set from template directive
                        $(function () {
                            // an iframe in <form><iframe src=???></iframe> this page.
                            if (page.isFrameForm) {
                                // a layout form
                                if (page.encounterFormName) {
                                    let url = '';
                                    if (page.encounterFormName.startsWith('LBF') || page.encounterFormName.startsWith('HIS')) {
                                        // iframe from template directive {EncounterDocument:LBFxxxxx} for a LBF form
                                        url = webRoot + "/interface/forms/LBF/new.php" + "" +
                                            "?isPortal=" + encodeURIComponent(isPortal ? 1 : 0) +
                                            "&formOrigin=" + encodeURIComponent(page.formOrigin) +
                                            "&formname=" + encodeURIComponent(page.encounterFormName) + "&id=0";
                                    } else {
                                        // iframe from template directive {EncounterDocument:xxxxx} for a native form
                                        // first, ensure form name is valid
                                        if (!page.verifyValidEncounterForm(page.encounterFormName)) {
                                            asyncAlertMsg("There is an issue loading form. Form does not exist.");
                                            return false;
                                        }
                                        url = webRoot + "/interface/forms/" + encodeURIComponent(page.encounterFormName) + "/new.php" +
                                            "?isPortal=" + encodeURIComponent(isPortal ? 1 : 0) +
                                            "&formOrigin=" + encodeURIComponent(page.formOrigin) +
                                            "&formname=" + encodeURIComponent(page.encounterFormName) + "&id=0";
                                        if (page.isQuestionnaire) {
                                            url = webRoot + "/interface/forms/questionnaire_assessments/questionnaire_assessments.php" +
                                                "?isPortal=" + encodeURIComponent(isPortal ? 1 : 0) +
                                                "&formOrigin=" + encodeURIComponent(page.formOrigin) +
                                                "&formname=" + encodeURIComponent(page.encounterFormName) + "&id=0";
                                        }
                                    }
                                    document.getElementById('encounterForm').src = url;
                                }
                            }
                            if ((autoRender + auditRender) > 0) {
                                $(".helpHide").removeClass("d-none");
                                $("#saveTemplate").show();
                                $("#sendTemplate").show();
                                $("#submitTemplate").hide();
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
        $(document).one('change', 'body *', function () {
            page.isSaved = false;
            $(document).off('change', 'body *');
        });
        if (page.currentName !== 'Help') {
            $('#docPanelHeader').append('<span class="bg-light text-dark px-1">' + jsText(currentNameStyled) + '</span>' +
                jsText(' ' + page.version + ' Version:' + ' Dated:' + cdate + ' Status:' + status));
        }
    },
    /**
     * show the doc for editing
     * @param m doc id
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
                        if (page.isDashboard || isModule) {
                            page.renderModelView(true); // allow admin to delete
                        } else {
                            page.renderModelView(false);
                        }
                    } else {
                        page.renderModelView(true);
                    }
                    page.version = $("#portal_version").val() ? $("#portal_version").val() : 'Legacy';
                },
                error: function (m, r) {
                    app.appendAlert(app.getErrorMessage(r), 'alert-error', 0, 'modelAlert');
                }
            });
        }
    },
    /**
     * Render the model template in the container
     * @param showDeleteButton
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
    updateModel: function (reload = false, saveType = '') {
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
        let s = page.onsiteDocument.get('denialReason');
        if (!isNew && s === 'New' && s !== 'In Review') {
            page.onsiteDocument.set('denialReason', 'Open');
            app.showProgress('modelLoader');
        }
        let isLink = $('#patientSignature').attr('src') ? $('#patientSignature').attr('src').indexOf('signhere') : -1;
        let isWitnessLink = $('#witnessSignature').attr('src') ? $('#witnessSignature').attr('src').indexOf('signhere') : -1;
        if (isLink !== -1) {
            $('#patientSignature').attr('src', signhere);
        }
        if (isWitnessLink !== -1) {
            $('#witnessSignature').attr('src', signhere);
        }
        let ptsignature = $('#patientSignature').attr('src');
        if (ptsignature == signhere) {
            if (page.signaturesRequired && page.presentPatientSignature) {
                asyncAlertMsg(signMsg, 6000, 'danger');
                return false;
            }
            ptsignature = "";
        }
        let wtsignature = $('#witnessSignature').attr('src');
        if (wtsignature == signhere) {
            wtsignature = "";
        }

        page.formOrigin = isPortal ? 0 : 1;
        page.formOrigin = isModule ? 2 : page.formOrigin;
        let templateContent = document.getElementById('templatecontent').innerHTML;
        if (page.encounterFormName && page.encounterFormId) {
            // lbf templates are saved as iframe tag with src url for fetch content on doc load.
            // no frame content is maintained in onsite document activity but template directives are.
            templateContent = templateContent.replace("id=0", "id=" + page.encounterFormId);
        }

        page.version = $('#portal_version').val() !== 'undefined' ? $('#portal_version').val() : '';
        let data = page.fetchTempateElements(event);
        saveType = saveType !== '' ? saveType : page.isFlattened ? 'flattened' : '';
        // This uses the framework routing.
        page.onsiteDocument.save({
            'pid': cpid,
            'facility': page.formOrigin, /* 0 portal, 1 dashboard, 2 patient documents */
            'provider': page.onsiteDocument.get('provider'),
            'encounter': page.onsiteDocument.get('encounter'),
            'createDate': page.onsiteDocument.get('createDate') ? page.onsiteDocument.get('createDate') : new Date(),
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
            // flattened document save if flattened after admin review
            // controller will run it through purifier because flatten converts elements.
            'fullDocument': page.isFlattened ? templateContent : '',
            'fileName': page.onsiteDocument.get('fileName'),
            'filePath': page.onsiteDocument.get('filePath'),
            'templateData': data,
            'version': page.version,
            'type': page.isFlattened ? 'flattened' : saveType // just being sure!
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
                if (isNew || autoRender > 0) {
                    $('#confirmDeleteOnsiteDocumentContainer').hide('fast');
                    $('#deleteOnsiteDocumentButtonContainer').show();
                    if (isPortal) {
                        $("#submitTemplate").show();
                        $("#sendTemplate").show();
                    } else {
                        $("#submitTemplate").hide();
                        $("#sendTemplate").hide();
                    }
                    page.isNewDoc = false;
                    page.onsiteDocuments.add(page.onsiteDocument)
                }
                if (model.reloadCollectionOnModelUpdate) {
                    page.fetchOnsiteDocuments(page.fetchParams, true);
                    page.showDetailDialog(page.onsiteDocument);
                }
                asyncAlertMsg(msgSuccess, 2000, 'success');
                if (page.isCharted && isModule) {
                    $("#a_docReturn").click();
                    return;
                }
                page.isSaved = true;
                if (reload) {
                    setTimeout("location.reload(true);", 3000);
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
                asyncAlertMsg(msgDelete, 2000, 'success');
                app.hideProgress('modelLoader');
                pageAudit.onsitePortalActivity.set('status', 'deleted');
                pageAudit.onsitePortalActivity.set('pendingAction', 'none');
                pageAudit.onsitePortalActivity.set('activity', 'document');

                pageAudit.onsitePortalActivity.set('pid', cpid);
                pageAudit.onsitePortalActivity.set('tableAction', 'delete');
                pageAudit.onsitePortalActivity.set('tableArgs', page.onsiteDocument.get('id'));
                pageAudit.onsitePortalActivity.set('narrative', 'Patient deleted un-charted template');
                pageAudit.updateModel()

                if (model.reloadCollectionOnModelUpdate) {
                    // re-fetch and render the collection after the model has been updated
                    page.fetchOnsiteDocuments(page.fetchParams, true);
                    page.isSaved = true;
                    setTimeout("location.reload(true);", 3000);
                }
            },
            error: function (model, response, scope) {
                app.appendAlert(app.getErrorMessage(response), 'alert-error', 0, 'modelAlert');
                app.hideProgress('modelLoader');
            }
        });
    },
    /**
     *  Fetch form data to send back to controller.
     */
    fetchTempateElements: function (event) {
        const form = document.getElementById('template');
        let formData = new FormData(form);
        let objectArray = [];
        // iterate form pairs and return those with populated values only.
        for (const [key, value] of formData) {
            objectArray.push({
                'name': key,
                'value': value
            });
        }
        // Save signatures.
        let imgElements = document.querySelectorAll('.signature');
        imgElements.forEach(function (signature) {
            if (signature.src !== signhere && signature.src) {
                if (!signature.name) {
                    return;
                }
                objectArray.push({
                    'name': signature.name,
                    'value': signature.src
                });
            }
        });
        let frameSrc = $('#encounterForm').attr('src');
        if (frameSrc) {
            objectArray.push({
                'name': 'encounterForm',
                'value': frameSrc || ''
            });
        }
        objectArray.push({
            'name': 'encounterFormId',
            'value': page.encounterFormId || 0
        });
        // will send to controller.
        return JSON.stringify(objectArray);
    },
    initFileDrop: function (event) {
        return new Dropzone("#patientFileDrop", {
            paramName: 'file',
            clickable: true,
            acceptedFiles: 'application/pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.csv,.tsv,.ppt,.pptx,.odt,.rtf',
            dictDefaultMessage: "Drop file or Click here.",
            maxFiles: 2,
            enqueueForUpload: true,
            maxFilesize: 100,
            uploadMultiple: true,
            addRemoveLinks: true,
            createImageThumbnails: true,
            autoProcessQueue: false,
            init: function (e) {
                const thisDropzone = this;
                let fileCnt = 0;
                $("#idSubmit").click(function (e) {
                    e.preventDefault();
                    thisDropzone.processQueue();
                });
                this.on('sending', function (file, xhr, formData) {
                    let data = $('#frmTarget').serializeArray();
                    $.each(data, function (key, el) {
                        formData.append(el.name, el.value);
                    });
                });
                this.on("success", function (file, response) {
                    let data = JSON.parse(response);
                });
                this.on("complete", function (file) {
                    this.removeFile(file);
                    if (dropzoneCount() < 1) {
                        $("#idShow").click();
                    }
                });
                this.on("queuecomplete", function () {
                    $('.meter').delay(999).slideUp(999);
                });
                this.on("removedfile", function (file) {
                    if (dropzoneCount() < 1) {
                        $("#idSubmit").addClass('d-none');
                    }
                });
                this.on("addedfile", function (file) {
                    $("#idSubmit").removeClass('d-none');
                    file.previewElement.classList.add('type-' + fileType(file.name));
                    fileCnt = dropzoneCount();
                });

                function fileType(fileName) {
                    let fileType = /[.]/.exec(fileName) ? /[^.]+$/.exec(fileName) : undefined;
                    return fileType[0];
                }

                function dropzoneCount() {
                    return $('#patientFileDrop > .dz-preview').length;
                }

                if (fileCnt > 0) {
                    $("#idSubmit").removeClass('d-none');
                }
                $(".dz-button").addClass("bg-dark text-light");
            }
        });
    }
};
