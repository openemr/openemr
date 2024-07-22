/**
 * View logic for OnsiteActivityViews
 *
 * application logic specific to the OnsiteActivityView listing page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
var actpage = {

    onsiteActivityViews: new model.OnsiteActivityViewCollection(),
    collectionView: null,
    onsiteActivityView: null,
    modelView: null,
    isInitialized: false,
    isInitializing: false,
    fetchParams: {filter: '', orderBy: 'patientId', orderDesc: 'DESC', page: 1, status: 'waiting'},
    fetchInProgress: false,
    dialogIsOpen: false,

    init: function () {
        // ensure initialization only occurs once
        if (actpage.isInitialized || actpage.isInitializing) return;
        actpage.isInitializing = true;

        if (!$.isReady && console) console.warn('page was initialized before dom is ready.  views may not render properly.');

        // make the return button clickable
        $("#returnHome").click(function (e) {
            e.preventDefault();
            window.location.href = './provider';
        });

        function showPaymentModal(cpid, recid) {
            var title = 'Patient Online Payment';
            var params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Cancel', close: true, style: 'secondary btn-sm'},
                    {text: 'Done', style: 'danger btn-sm', close: true}],
                onClosed: 'reload',
                type: 'GET',
                url: './../portal_payment.php?pid=' + encodeURIComponent(cpid) + '&user=' + encodeURIComponent(cuser) + '&recid=' + encodeURIComponent(recid)
            };
            dlgopen('', '', 'modal-lg', 625, '', '', params);
        }

        function showProfileModal(cpid) {
            var title = 'Profile Edits' + ' ';
            var params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Cancel', close: true, style: 'default btn-sm'},
                    {text: 'Revert Edits', close: false, style: 'success btn-sm', id: 'replaceAllButton'},
                    {text: 'Commit to Chart', style: 'danger btn-sm', close: false, id: 'savePatientButton'}],
                onClosed: 'reload',
                sizeHeight: 'full',
                allowDrag: false,
                type: 'GET',
                url: top.webroot_url + '/portal/patient/patientdata?pid=' + encodeURIComponent(cpid) + '&user=' + encodeURIComponent(cuser)
            };
            dlgopen('', '', 'modal-xl', '', '', title, params);
        }

        function showDocumentModal(cpid, recid) {
            var title = 'Audit Document';
            var params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Cancel', close: true, style: 'default btn-sm'},
                    {text: 'Done', style: 'danger btn-sm', close: true}],
                sizeHeight: 'full',
                onClosed: 'reload',
                url: './onsitedocuments?pid=' + cpid + '&user=' + encodeURIComponent(cuser) + '&recid=' + encodeURIComponent(recid)
            };
            dlgopen('', '', 'modal-lg', '', '', '', params);
        }

        // initialize the collection view
        this.collectionView = new view.CollectionView({
            el: $("#onsiteActivityViewCollectionContainer"),
            templateEl: $("#onsiteActivityViewCollectionTemplate"),
            collection: actpage.onsiteActivityViews
        });

        // initialize the search filter
        $('#filter').change(function (obj) {
            actpage.fetchParams.filter = $('#filter').val();
            actpage.fetchParams.page = 1;
            actpage.fetchOnsiteActivityViews(actpage.fetchParams);
        });

        // make the rows clickable ('rendered' is a custom event, not a standard backbone event)
        this.collectionView.on('rendered', function () {
            // attach click handler to the table rows for selection
            $('table.collection tbody tr').click(function (e) {
                e.preventDefault();
                var m = actpage.onsiteActivityViews.get(this.id);
                var cpid = m.get('patientId');
                var activity = m.get('activity');
                let eventData = {
                    activity: activity
                    , cpid: cpid
                    , modelAttributes: m.attributes
                };
                let node = document.createElement("tr");
                // note this is a synchronous operation! event consumers should not do anything that takes a long time
                // or they should fire off async work and return immediately
                let event = new CustomEvent('openemr:portal:provider:onsiteactivityview:click',
                    {bubbles: true, detail: eventData, cancelable: true});
                let continueExecution = e.target.dispatchEvent(event);
                if (!continueExecution) { // prevent default was called
                    return;
                }

                if (activity == 'document') {
                    let recid = m.get('tableArgs');
                    showDocumentModal(cpid, recid);
                } else if (activity == 'profile') {
                    showProfileModal(cpid);
                } else if (activity == 'payment') {
                    let recid = m.get('id');
                    showPaymentModal(cpid, recid);
                }
            });

            // make the headers clickable for sorting
            $('table.collection thead tr th').click(function (e) {
                e.preventDefault();
                var prop = this.id.replace('header_', '');

                // toggle the ascending/descending before we change the sort prop
                actpage.fetchParams.orderDesc = (prop == actpage.fetchParams.orderBy && !actpage.fetchParams.orderDesc) ? '1' : '';
                actpage.fetchParams.orderBy = prop;
                actpage.fetchParams.page = 1;
                actpage.fetchOnsiteActivityViews(actpage.fetchParams);
            });

            // attach click handlers to the pagination controls
            $('.pageButton').click(function (e) {
                e.preventDefault();
                actpage.fetchParams.page = this.id.substr(5);
                actpage.fetchOnsiteActivityViews(actpage.fetchParams);
            });

            actpage.isInitialized = true;
            actpage.isInitializing = false;
        });

        this.fetchOnsiteActivityViews({filter: '', orderBy: 'Date', orderDesc: 'DESC', page: 1, status: 'waiting'});

        // initialize the model view
        this.modelView = new view.ModelView({
            el: $("#onsiteActivityViewModelContainer")
        });

        this.modelView.templateEl = $("#onsiteActivityViewModelTemplate");

        if (model.longPollDuration > 0) {
            setInterval(function () {

                if (!actpage.dialogIsOpen) {
                    actpage.fetchOnsiteActivityViews(actpage.fetchParams, true);
                }

            }, model.longPollDuration);
        }
    },

    /**
     * Fetch the collection data from the server
     * @param object params passed through to collection.fetch
     * @param bool true to hide the loading animation
     */
    fetchOnsiteActivityViews: function (params, hideLoader) {
        // persist the params so that paging/sorting/filtering will play together nicely
        //params.status = 'waiting';
        actpage.fetchParams = params;

        if (actpage.fetchInProgress) {
            if (console) console.log('supressing fetch because it is already in progress');
        }

        actpage.fetchInProgress = true;

        if (!hideLoader) app.showProgress('loader');

        actpage.onsiteActivityViews.fetch({

            data: params,

            success: function () {
                if (actpage.onsiteActivityViews.collectionHasChanged) {
                    // TODO: add any logic necessary if the collection has changed
                    // the sync event will trigger the view to re-render
                }
                app.hideProgress('loader');
                actpage.fetchInProgress = false;
            },
            error: function (m, r) {
                app.appendAlert(app.getErrorMessage(r), 'alert-error', 0, 'collectionAlert');
                app.hideProgress('loader');
                actpage.fetchInProgress = false;
            }
        });
    },

    /**
     * show the dialog for editing a model
     * @param model
     */
    showDetailDialog: function (m) {

        // show the modal dialog
        $('#onsiteActivityViewDetailDialog').modal({show: true});

        // if a model was specified then that means a user is editing an existing record
        // if not, then the user is creating a new record
        actpage.onsiteActivityView = m ? m : new model.OnsiteActivityViewModel();

        actpage.modelView.model = actpage.onsiteActivityView;

        if (actpage.onsiteActivityView.id == null || actpage.onsiteActivityView.id == '') {
            // this is a new record, there is no need to contact the server
            actpage.renderModelView(false);
        } else {
            app.showProgress('modelLoader');

            // fetch the model from the server so we are not updating stale data
            actpage.onsiteActivityView.fetch({

                success: function () {
                    // data returned from the server.  render the model view
                    actpage.renderModelView(true);
                },

                error: function (m, r) {
                    app.appendAlert(app.getErrorMessage(r), 'alert-error', 0, 'modelAlert');
                    app.hideProgress('modelLoader');
                }

            });
        }
    },

    /**
     * Render the model template in the popup
     * @param bool show the delete button
     */
    renderModelView: function (showDeleteButton) {
        actpage.modelView.render();

        app.hideProgress('modelLoader');
        // initialize any special controls
        try {
            $('.date-picker').datepicker().on('changeDate', function (ev) {
                $('.date-picker').datepicker('hide');
            });
        } catch (error) {
            // this happens if the datepicker input.value isn't a valid date
            if (console) console.log('datepicker error: ' + error.message);
        }
        if (showDeleteButton) {
            // attach click handlers to the delete buttons
            $('#deleteOnsiteActivityViewButton').click(function (e) {
                e.preventDefault();
                $('#confirmDeleteOnsiteActivityViewContainer').show('fast');
            });

            $('#cancelDeleteOnsiteActivityViewButton').click(function (e) {
                e.preventDefault();
                $('#confirmDeleteOnsiteActivityViewContainer').hide('fast');
            });

            $('#confirmDeleteOnsiteActivityViewButton').click(function (e) {
                e.preventDefault();
                actpage.deleteModel();
            });

        } else {
            // no point in initializing the click handlers if we don't show the button
            $('#deleteOnsiteActivityViewButtonContainer').hide();
        }
    },

};

