/**
 * Records and lists for the portal/patient JSON API (the global `model`).
 *
 * A record is one row of an API resource (a document, a profile); a list is the result of a list
 * query, with the pager figures the server sends. Both talk to the server with fetch and keep the
 * JSON requests the Backbone models sent: a save sends the record's attributes merged with the
 * changed ones, as JSON, by POST for a new record and PUT for an existing one.
 *
 * Paths are relative, so they resolve against the page's base URL as before.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var model = (function () {
    'use strict';

    /**
     * A refused or failed API call. `responseText` is the raw body, as the pages show it.
     */
    class PortalApiError extends Error {
        constructor(status, responseText) {
            super('Portal API request failed with status ' + status);
            this.name = 'PortalApiError';
            this.status = status;
            this.responseText = responseText;
        }
    }

    /**
     * The query string for a request, as jQuery built it: a missing value is sent empty.
     *
     * @param {Object<string, *>|undefined} params
     * @returns {string} '' or '?a=1&b=2'
     */
    function queryString(params) {
        if (!params) {
            return '';
        }
        const query = new URLSearchParams();
        Object.keys(params).forEach(function (key) {
            const value = params[key];
            query.append(key, value === null || value === undefined ? '' : String(value));
        });
        const text = query.toString();
        return text === '' ? '' : '?' + text;
    }

    /**
     * Call the API and return the decoded JSON body. A status other than 2xx, or a body that is
     * not JSON, rejects with a PortalApiError. Writes carry the session's CSRF token, which the
     * page sets in window.portalApiCsrfToken; portal/patient/index.php refuses them without it.
     *
     * @param {string} method GET, POST, PUT or DELETE
     * @param {string} path the API path, such as 'api/onsitedocument/12'
     * @param {{params?: Object, body?: Object}} [options] query values and the JSON body
     * @returns {Promise<*>}
     */
    async function request(method, path, options) {
        const opts = options || {};
        const headers = {
            'Accept': 'application/json, text/javascript, */*; q=0.01',
            'X-Requested-With': 'XMLHttpRequest'
        };
        const init = {method: method, headers: headers, credentials: 'same-origin'};
        if (method !== 'GET') {
            headers['X-CSRF-Token'] = window.portalApiCsrfToken || '';
        }
        if (opts.body !== undefined) {
            headers['Content-Type'] = 'application/json';
            init.body = JSON.stringify(opts.body);
        }

        const response = await fetch(path + queryString(opts.params), init);
        const text = await response.text();
        if (!response.ok) {
            throw new PortalApiError(response.status, text);
        }
        try {
            return JSON.parse(text);
        } catch {
            throw new PortalApiError(response.status, text);
        }
    }

    /**
     * One row of an API resource. `attributes` holds its fields; a record with no id is new.
     */
    class Record {
        /**
         * @param {string} urlRoot the resource path, such as 'api/onsitedocument'
         * @param {Object} defaults the fields a new record starts with
         * @param {Object} [attributes] fields to set over the defaults
         */
        constructor(urlRoot, defaults, attributes) {
            this.urlRoot = urlRoot;
            this.attributes = Object.assign({}, defaults, attributes || {});
        }

        /**
         * The record id, or null for a record not saved yet.
         */
        get id() {
            const id = this.attributes.id;
            return id === undefined ? null : id;
        }

        get(key) {
            return this.attributes[key];
        }

        /**
         * Set one field, or several from an object.
         */
        set(key, value) {
            if (key !== null && typeof key === 'object') {
                Object.assign(this.attributes, key);
            } else {
                this.attributes[key] = value;
            }
            return this;
        }

        isNew() {
            return this.id === null;
        }

        /**
         * The record URL: the collection root for a new record, otherwise root/id.
         */
        url() {
            return this.isNew() ? this.urlRoot : this.urlRoot + '/' + encodeURIComponent(this.id);
        }

        /**
         * Load the record from the server, keeping any field the response leaves out.
         *
         * @param {Object} [params] query values
         * @returns {Promise<Record>}
         */
        async fetch(params) {
            this.set(await request('GET', this.url(), {params: params}));
            return this;
        }

        /**
         * Send the record with `changes` applied. The changes are kept only once the server has
         * accepted them, and then the server's copy of the record is applied over them.
         *
         * @param {Object} changes
         * @returns {Promise<Record>}
         */
        async save(changes) {
            const body = Object.assign({}, this.attributes, changes);
            const saved = await request(this.isNew() ? 'POST' : 'PUT', this.url(), {body: body});
            this.set(Object.assign({}, changes, saved));
            return this;
        }

        /**
         * Delete the record on the server. A new record has nothing to delete.
         *
         * @returns {Promise<void>}
         */
        async destroy() {
            if (!this.isNew()) {
                await request('DELETE', this.url());
            }
        }
    }

    /**
     * The result of a list query: its records, in the server's order, and the pager figures. A
     * response that is a bare array is one page holding every row.
     */
    class List {
        /**
         * @param {string} url the list path, such as 'api/onsitedocuments'
         * @param {function(Object): Record} makeRecord builds a record from a row
         */
        constructor(url, makeRecord) {
            this.url = url;
            this.makeRecord = makeRecord;
            this.models = [];
            this.totalResults = 0;
            this.totalPages = 0;
            this.currentPage = 0;
            this.pageSize = 0;
            this.orderBy = '';
            this.orderDesc = false;
            this.lastResponseText = null;
            this.lastRequestParams = null;
            this.collectionHasChanged = true;
        }

        get length() {
            return this.models.length;
        }

        /**
         * Run the query. A record already in the list is updated in place, so a page that holds
         * it sees the new values.
         *
         * @param {Object} [params] query values
         * @returns {Promise<List>}
         */
        async fetch(params) {
            const response = await request('GET', this.url, {params: params});
            const responseText = JSON.stringify(response);
            this.collectionHasChanged = this.lastResponseText !== responseText;
            this.lastResponseText = responseText;
            this.lastRequestParams = params;

            let rows;
            if (response.currentPage) {
                rows = response.rows;
                this.totalResults = response.totalResults;
                this.totalPages = response.totalPages;
                this.currentPage = response.currentPage;
                this.pageSize = response.pageSize;
            } else {
                rows = response;
                this.totalResults = rows.length;
                this.totalPages = 1;
                this.currentPage = 1;
                this.pageSize = this.totalResults;
            }
            this.orderBy = response.orderBy;
            this.orderDesc = response.orderDesc;

            const known = new Map(this.models.map(function (record) {
                return [String(record.id), record];
            }));
            this.models = rows.map((row) => {
                const record = known.get(String(row.id));
                return record ? record.set(row) : this.makeRecord(row);
            });
            return this;
        }

        /**
         * Run the last query again.
         */
        refetch() {
            return this.fetch(this.lastRequestParams);
        }

        get(id) {
            return this.models.find(function (record) {
                return String(record.id) === String(id);
            });
        }

        first() {
            return this.models[0];
        }

        /**
         * Add a record the list does not hold yet.
         *
         * @returns {boolean} whether it was added
         */
        add(record) {
            if (this.models.includes(record) || (!record.isNew() && this.get(record.id))) {
                return false;
            }
            this.models.push(record);
            return true;
        }

        /**
         * Take a record out of the list.
         *
         * @returns {boolean} whether the list held it
         */
        remove(record) {
            const index = this.models.indexOf(record);
            if (index === -1) {
                return false;
            }
            this.models.splice(index, 1);
            return true;
        }

        forEach(callback) {
            this.models.forEach(callback);
        }
    }

    const ONSITE_DOCUMENT_DEFAULTS = {
        'id': null,
        'pid': 0,
        'facility': 0,
        'provider': 0,
        'encounter': 0,
        'createDate': null,
        'docType': '',
        'patientSignedStatus': '0',
        'patientSignedTime': '0000-00-00',
        'authorizeSignedTime': '0000-00-00',
        'acceptSignedStatus': '0',
        'authorizingSignator': '',
        'reviewDate': '0000-00-00',
        'denialReason': 'New',
        'authorizedSignature': '',
        'patientSignature': '',
        'fullDocument': '',
        'fileName': '',
        'filePath': '',
        'templateData': ''
    };

    const PORTAL_ACTIVITY_DEFAULTS = {
        'id': null,
        'date': '0000-00-0000',
        'patientId': '0',
        'activity': '',
        'requireAudit': '1',
        'pendingAction': 'review',
        'actionTaken': '',
        'status': 'waiting',
        'narrative': '',
        'tableAction': '',
        'tableArgs': '',
        'actionUser': '0',
        'actionTakenTime': '0000-00-0000',
        'checksum': '0'
    };

    const PROFILE_FIELDS = [
        'title', 'language', 'fname', 'lname', 'mname', 'dob', 'street', 'postalCode', 'city',
        'state', 'countryCode', 'driversLicense', 'ss', 'occupation', 'phoneHome', 'phoneBiz',
        'phoneContact', 'phoneCell', 'pharmacyId', 'status', 'contactRelationship', 'date', 'sex',
        'referrer', 'referrerid', 'providerid', 'refProviderid', 'email', 'emailDirect',
        'ethnoracial', 'race', 'ethnicity', 'religion', 'familySize', 'pubpid', 'pid', 'hipaaMail',
        'hipaaVoice', 'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms', 'hipaaAllowemail', 'regdate',
        'mothersname', 'guardiansname', 'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx',
        'allowPatientPortal', 'careTeam', 'county', 'note'
    ];

    /**
     * A new profile: every field empty, with today as its date and registration date.
     */
    function profileDefaults() {
        const today = new Date().toISOString().slice(0, 10);
        const defaults = {'id': null};
        PROFILE_FIELDS.forEach(function (field) {
            defaults[field] = '';
        });
        defaults.date = today;
        defaults.regdate = today;
        return defaults;
    }

    class OnsiteDocumentModel extends Record {
        constructor(attributes) {
            // A new document is dated when it is made.
            super('api/onsitedocument', Object.assign({}, ONSITE_DOCUMENT_DEFAULTS, {createDate: new Date()}), attributes);
        }
    }

    class OnsitePortalActivityModel extends Record {
        constructor(attributes) {
            super('api/onsiteportalactivity', PORTAL_ACTIVITY_DEFAULTS, attributes);
        }
    }

    class OnsiteActivityViewModel extends Record {
        constructor(attributes) {
            super('api/onsiteactivityview', {'id': null}, attributes);
        }
    }

    /** The patient's chart profile; staff save it to the chart. */
    class PatientModel extends Record {
        constructor(attributes) {
            super('api/patient', profileDefaults(), attributes);
        }
    }

    /** The patient's profile changes awaiting review. */
    class PortalPatientModel extends Record {
        constructor(attributes) {
            super('api/portalpatient', profileDefaults(), attributes);
        }
    }

    class OnsiteDocumentCollection extends List {
        constructor() {
            super('api/onsitedocuments', (row) => new OnsiteDocumentModel(row));
        }
    }

    class OnsitePortalActivityCollection extends List {
        constructor() {
            super('api/onsiteportalactivities', (row) => new OnsitePortalActivityModel(row));
        }
    }

    class OnsiteActivityViewCollection extends List {
        constructor() {
            super('api/onsiteactivityviews', (row) => new OnsiteActivityViewModel(row));
        }
    }

    class PatientCollection extends List {
        constructor() {
            super('api/patientdata', (row) => new PatientModel(row));
        }
    }

    return {
        /** Re-run the document list query after a document is saved. Pages turn it off. */
        reloadCollectionOnModelUpdate: true,
        PortalApiError: PortalApiError,
        request: request,
        Record: Record,
        List: List,
        OnsiteDocumentModel: OnsiteDocumentModel,
        OnsiteDocumentCollection: OnsiteDocumentCollection,
        OnsitePortalActivityModel: OnsitePortalActivityModel,
        OnsitePortalActivityCollection: OnsitePortalActivityCollection,
        OnsiteActivityViewModel: OnsiteActivityViewModel,
        OnsiteActivityViewCollection: OnsiteActivityViewCollection,
        PatientModel: PatientModel,
        PatientCollection: PatientCollection,
        PortalPatientModel: PortalPatientModel
    };
}());
