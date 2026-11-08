/**
 * View logic for OnsiteActivityViews
 *
 * application logic specific to the OnsiteActivityView listing page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
const actpage = {
    onsiteActivityViews: new model.OnsiteActivityViewCollection(),
    isInitialized: false,
    isInitializing: false,
    fetchParams: {filter: '', orderBy: 'patientId', orderDesc: 'DESC', page: 1, status: 'waiting', requireAudit: 1},
    fetchInProgress: false,
    dialogIsOpen: false,

    /**
     * Sets up the review list once: the return button, the payment, profile and document dialogs, and the list with its filter, sorting and paging.
     */
    init: function () {
        // ensure initialization only occurs once
        if (actpage.isInitialized || actpage.isInitializing) return;
        actpage.isInitializing = true;

        // make the return button clickable
        $("#returnHome").click(function (e) {
            e.preventDefault();
            window.location.href = './provider';
        });

        function showPaymentModal(cpid, recid) {
            let title = 'Patient Online Payment';
            const urlParams = new URLSearchParams({
                pid: cpid,
                recid: recid,
                user: cuser
            });
            let params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Done', style: 'danger btn-sm', close: true}],
                onClosed: 'reload',
                type: 'GET',
                url: './../portal_payment.php?' + urlParams
            };
            dlgopen('', '', 'modal-lg', 625, '', '', params);
        }

        function showProfileModal(cpid) {
            let title = 'Profile Edits' + ' ';
            const urlParams = new URLSearchParams({
                pid: cpid,
                user: cuser
            });
            let params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Cancel', close: true, style: 'default btn-sm'},
                    {text: 'Revert Edits', close: false, style: 'success btn-sm', id: 'replaceAllButton'},
                    {text: 'Commit to Chart', style: 'danger btn-sm', close: false, id: 'savePatientButton'}],
                onClosed: 'reload',
                sizeHeight: 'full',
                allowDrag: false,
                type: 'GET',
                url: top.webroot_url + '/portal/patient/patientdata?' + urlParams
            };
            dlgopen('', '', 'modal-xl', '', '', title, params);
        }

        function showDocumentModal(cpid, recid) {
            let title = 'Audit Document';
            const urlParams = new URLSearchParams({
                pid: cpid,
                recid: recid,
                user: cuser
            });
            let params = {
                buttons: [
                    {text: 'Help', close: false, style: 'info btn-sm', id: 'formHelp'},
                    {text: 'Done', style: 'danger btn-sm', close: true}],
                sizeHeight: 'full',
                onClosed: 'reload',
                url: './onsitedocuments?' + urlParams
            };
            dlgopen('', '', 'modal-lg', '', '', '', params);
        }

        // initialize the search filter
        $('#filter').change(function (obj) {
            actpage.fetchParams.filter = $('#filter').val();
            actpage.fetchParams.page = 1;
            actpage.fetchOnsiteActivityViews(actpage.fetchParams);
        });

        // make the rows clickable once the list is drawn
        actpage.bindCollection = function () {
            document.querySelectorAll('.delete-button').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const updateId = this.getAttribute('data-update-id');
                    const deleteId = this.getAttribute('data-delete-id');
                    const url = '../lib/doc_lib.php';

                    if (confirm(jsText('Are you sure you want to delete this audit?\nThis action cannot be undone.'))) {
                        // Create a FormData object to hold POST data
                        let formData = new FormData();
                        formData.append('dispose', 'audit_delete');
                        formData.append('update_id', updateId);
                        formData.append('delete_id', deleteId);
                        formData.append('csrf_token_form', csrfToken);

                        fetch(url, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('tr').remove();
                            } else {
                                alert('Error deleting document: ' + jsText(data.message));
                            }
                        })
                        .catch(error => {
                            alert('An error occurred: ' + jsText(error.message));
                        });

                        return false; // Stop the event propagation
                    }
                    return false;
                });
            });

            // attach click handler to the table rows for selection
            $('table.collection tbody tr').click(function (e) {
                e.preventDefault();
                // ignore event on the last cell (the delete button)
                if ($(e.target).closest('td').is($(this).find('td').last())) {
                    return;
                }

                let m = actpage.onsiteActivityViews.get(this.id);
                let cpid = m.get('patientId');
                let activity = m.get('activity');
                let eventData = {
                    activity: activity
                    , cpid: cpid
                    , modelAttributes: m.attributes
                };

                // this is a custom event that will bubble up to the window
                let node = document.createElement("tr");
                // note this is a synchronous operation! event consumers should not do anything that takes a long time
                // or they should fire off async work and return immediately
                let event = new CustomEvent('openemr:portal:provider:onsiteactivityview:click',
                    {bubbles: true, detail: eventData, cancelable: true});
                let continueExecution = e.target.dispatchEvent(event);
                if (!continueExecution) { // prevent default was called
                    return;
                }

                if (activity === 'document') {
                    let recid = m.get('tableArgs');
                    showDocumentModal(cpid, recid);
                } else if (activity === 'profile') {
                    showProfileModal(cpid);
                } else if (activity === 'payment') {
                    let recid = m.get('id');
                    showPaymentModal(cpid, recid);
                }
            });
            // make the headers clickable for sorting
            $('table.collection thead tr th').click(function (e) {
                e.preventDefault();
                let prop = this.id.replace('header_', '');
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
        };

        const initialParams = {
            filter: '',
            orderBy: 'Date',
            orderDesc: 'DESC',
            page: 1,
            status: 'waiting',
            requireAudit: 1
        };
        const requestedActivity = new URLSearchParams(window.location.search).get('activity');
        if (requestedActivity === 'payment') {
            initialParams.activity = requestedActivity;
        }
        this.fetchOnsiteActivityViews(initialParams);
    },

    /**
     * Draw the list from the last fetch: a row per activity, then the pager.
     */
    renderCollection: function () {
        const container = view.renderTemplate('onsiteActivityViewCollectionTemplate', 'onsiteActivityViewCollectionContainer');
        const tbody = container.querySelector('tbody');
        actpage.onsiteActivityViews.forEach(function (item) {
            const row = view.copyTemplate('onsiteActivityViewRowTemplate').firstElementChild;
            const cell = (field) => row.querySelector('[data-field="' + field + '"]');
            row.id = item.get('id') ?? '';
            cell('id').textContent = item.get('id') || '';
            cell('date').textContent = item.get('date')
                ? moment(app.parseDate(item.get('date'))).format('MMM D, YYYY h:mm A')
                : 'NULL';
            ['patientId', 'fname', 'mname', 'lname', 'narrative', 'activity', 'pendingAction', 'status'].forEach(function (field) {
                cell(field).textContent = item.get(field) || '';
            });
            cell('amount').textContent = actpage.amountText(item);
            const deleteButton = row.querySelector('.delete-button');
            deleteButton.setAttribute('data-update-id', item.get('id') || '');
            deleteButton.setAttribute('data-delete-id', item.get('tableArgs') || '');
            tbody.append(row);
        });
        container.append(view.pagination(actpage.onsiteActivityViews));
        actpage.bindCollection();
    },

    /**
     * The Amount column: the payment total for a payment, a label for a profile change, and
     * otherwise the activity's arguments as stored.
     */
    amountText: function (item) {
        if (item.get('activity') === 'profile') {
            return 'Demographic Profile Changes';
        }
        try {
            const args = JSON.parse(item.get('tableArgs') || '{}');
            if (args.form_paytotal) {
                return '$' + args.form_paytotal;
            }
        } catch {
            // not JSON: show it as stored
        }
        return item.get('tableArgs') || '';
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

        actpage.onsiteActivityViews.fetch(params).then(function () {
            app.hideProgress('loader');
            actpage.fetchInProgress = false;
            actpage.renderCollection();
        }, function (error) {
            app.appendAlert(app.getErrorMessage(error), 'alert-error', 0, 'collectionAlert');
            app.hideProgress('loader');
            actpage.fetchInProgress = false;
        });
    }
};
