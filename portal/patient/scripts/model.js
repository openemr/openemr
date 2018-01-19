/**
 * backbone model definitions for Patient Portal
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/**
 * Use emulated HTTP if the server doesn't support PUT/DELETE or application/json requests
 */
Backbone.emulateHTTP = false;
Backbone.emulateJSON = false;

var model = {};

/**
 * long polling duration in miliseconds.  (5000 = recommended, 0 = disabled)
 * warning: setting this to a low number will increase server load
 */
model.longPollDuration = 0;

/**
 * whether to refresh the collection immediately after a model is updated
 */
model.reloadCollectionOnModelUpdate = true;


/**
 * a default sort method for sorting collection items.  this will sort the collection
 * based on the orderBy and orderDesc property that was used on the last fetch call
 * to the server.
 */
model.AbstractCollection = Backbone.Collection.extend({
	totalResults: 0,
	totalPages: 0,
	currentPage: 0,
	pageSize: 0,
	orderBy: '',
	orderDesc: false,
	lastResponseText: null,
	lastRequestParams: null,
	collectionHasChanged: true,

	/**
	 * fetch the collection from the server using the same options and
	 * parameters as the previous fetch
	 */
	refetch: function() {
		this.fetch({ data: this.lastRequestParams })
	},

	/* uncomment to debug fetch event triggers
	fetch: function(options) {
            this.constructor.__super__.fetch.apply(this, arguments);
	},
	// */

	/**
	 * client-side sorting baesd on the orderBy and orderDesc parameters that
	 * were used to fetch the data from the server.  Backbone ignores the
	 * order of records coming from the server so we have to sort them ourselves
	 */
	comparator: function(a,b) {

		var result = 0;
		var options = this.lastRequestParams;

		if (options && options.orderBy) {

			// lcase the first letter of the property name
			var propName = options.orderBy.charAt(0).toLowerCase() + options.orderBy.slice(1);
			var aVal = a.get(propName);
			var bVal = b.get(propName);

			if (isNaN(aVal) || isNaN(bVal)) {
				// treat comparison as case-insensitive strings
				aVal = aVal ? aVal.toLowerCase() : '';
				bVal = bVal ? bVal.toLowerCase() : '';
			} else {
				// treat comparision as a number
				aVal = Number(aVal);
				bVal = Number(bVal);
			}

			if (aVal < bVal) {
				result = options.orderDesc ? 1 : -1;
			} else if (aVal > bVal) {
				result = options.orderDesc ? -1 : 1;
			}
		}

		return result;

	},
	/**
	 * override parse to track changes and handle pagination
	 * if the server call has returned page data
	 */
	parse: function(response, options) {

		// the response is already decoded into object form, but it's easier to
		// compary the stringified version.  some earlier versions of backbone did
		// not include the raw response so there is some legacy support here
		var responseText = options && options.xhr ? options.xhr.responseText : JSON.stringify(response);
		this.collectionHasChanged = (this.lastResponseText != responseText);
		this.lastRequestParams = options ? options.data : undefined;

		// if the collection has changed then we need to force a re-sort because backbone will
		// only resort the data if a property in the model has changed
		if (this.lastResponseText && this.collectionHasChanged) this.sort({ silent:true });

		this.lastResponseText = responseText;

		var rows;

		if (response.currentPage) {
			rows = response.rows;
			this.totalResults = response.totalResults;
			this.totalPages = response.totalPages;
			this.currentPage = response.currentPage;
			this.pageSize = response.pageSize;
			this.orderBy = response.orderBy;
			this.orderDesc = response.orderDesc;
		} else {
			rows = response;
			this.totalResults = rows.length;
			this.totalPages = 1;
			this.currentPage = 1;
			this.pageSize = this.totalResults;
			this.orderBy = response.orderBy;
			this.orderDesc = response.orderDesc;
		}

		return rows;
	}
});
/**
 * OnsiteDocument Backbone Model
 */
model.OnsiteDocumentModel = Backbone.Model.extend({
	urlRoot: 'api/onsitedocument',
	idAttribute: 'id',
	id: '',
	pid: '',
	facility: '',
	provider: '',
	encounter: '',
	createDate: '',
	docType: '',
	patientSignedStatus: '',
	patientSignedTime: '',
	authorizeSignedTime: '',
	acceptSignedStatus: '',
	authorizingSignator: '',
	reviewDate: '',
	denialReason: '',
	authorizedSignature: '',
	patientSignature: '',
	fullDocument: '',
	fileName: '',
	filePath: '',
	defaults: {
		'id': null,
		'pid': 0,
		'facility': 0,
		'provider': 0,
		'encounter': 0,
		'createDate':new Date(),
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
		'filePath': ''
	}
});

/**
 * OnsiteDocument Backbone Collection
 */
model.OnsiteDocumentCollection = model.AbstractCollection.extend({
	url: 'api/onsitedocuments',
	model: model.OnsiteDocumentModel
});

/**
 * OnsitePortalActivity Backbone Model
 */
model.OnsitePortalActivityModel = Backbone.Model.extend({
	urlRoot: 'api/onsiteportalactivity',
	idAttribute: 'id',
	id: '',
	date: '',
	patientId: '',
	activity: '',
	requireAudit: '',
	pendingAction: '',
	actionTaken: '',
	status: '',
	narrative: '',
	tableAction: '',
	tableArgs: '',
	actionUser: '',
	actionTakenTime: '',
	checksum: '',
	defaults: {
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
	}
});

/**
 * OnsitePortalActivity Backbone Collection
 */
model.OnsitePortalActivityCollection = model.AbstractCollection.extend({
	url: 'api/onsiteportalactivities',
	model: model.OnsitePortalActivityModel
});
/**
 * OnsiteActivityView Backbone Model
 */
model.OnsiteActivityViewModel = Backbone.Model.extend({
	urlRoot: 'api/onsiteactivityview',
	idAttribute: 'id',
	id: '',
	date: '',
	patientId: '',
	activity: '',
	requireAudit: '',
	pendingAction: '',
	actionTaken: '',
	status: '',
	narrative: '',
	tableAction: '',
	tableArgs: '',
	actionUser: '',
	actionTakenTime: '',
	checksum: '',
	title: '',
	fname: '',
	lname: '',
	mname: '',
	dob: '',
	ss: '',
	street: '',
	postalCode: '',
	city: '',
	state: '',
	referrerid: '',
	providerid: '',
	refProviderid: '',
	pubpid: '',
	careTeam: '',
	username: '',
	authorized: '',
	ufname: '',
	umname: '',
	ulname: '',
	facility: '',
	active: '',
	utitle: '',
	physicianType: '',
	defaults: {
		'id': null,
		'date': new Date(),
		'patientId': '',
		'activity': '',
		'requireAudit': '',
		'pendingAction': '',
		'actionTaken': '',
		'status': '',
		'narrative': '',
		'tableAction': '',
		'tableArgs': '',
		'actionUser': '',
		'actionTakenTime': new Date(),
		'checksum': '',
		'title': '',
		'fname': '',
		'lname': '',
		'mname': '',
		'dob': new Date(),
		'ss': '',
		'street': '',
		'postalCode': '',
		'city': '',
		'state': '',
		'referrerid': '',
		'providerid': '',
		'refProviderid': '',
		'pubpid': '',
		'careTeam': '',
		'username': '',
		'authorized': '',
		'ufname': '',
		'umname': '',
		'ulname': '',
		'facility': '',
		'active': '',
		'utitle': '',
		'physicianType': ''
	}
});

/**
 * OnsiteActivityView Backbone Collection
 */
model.OnsiteActivityViewCollection = model.AbstractCollection.extend({
	url: 'api/onsiteactivityviews',
	model: model.OnsiteActivityViewModel
});

/**
 * Patient Backbone Model
 */
model.PatientModel = Backbone.Model.extend({
	urlRoot: 'api/patient',
	idAttribute: 'id',
	id: '',
	title: '',
	language: '',
	financial: '',
	fname: '',
	lname: '',
	mname: '',
	dob: '',
	street: '',
	postalCode: '',
	city: '',
	state: '',
	countryCode: '',
	driversLicense: '',
	ss: '',
	occupation: '',
	phoneHome: '',
	phoneBiz: '',
	phoneContact: '',
	phoneCell: '',
	pharmacyId: '',
	status: '',
	contactRelationship: '',
	date: '',
	sex: '',
	referrer: '',
	referrerid: '',
	providerid: '',
	refProviderid: '',
	email: '',
	emailDirect: '',
	ethnoracial: '',
	race: '',
	ethnicity: '',
	religion: '',
	interpretter: '',
	migrantseasonal: '',
	familySize: '',
	monthlyIncome: '',
	billingNote: '',
	homeless: '',
	financialReview: '',
	pubpid: '',
	pid: '',
	hipaaMail: '',
	hipaaVoice: '',
	hipaaNotice: '',
	hipaaMessage: '',
	hipaaAllowsms: '',
	hipaaAllowemail: '',
	squad: '',
	fitness: '',
	referralSource: '',
	pricelevel: '',
	regdate: '',
	contrastart: '',
	completedAd: '',
	adReviewed: '',
	vfc: '',
	mothersname: '',
	guardiansname: '',
	allowImmRegUse: '',
	allowImmInfoShare: '',
	allowHealthInfoEx: '',
	allowPatientPortal: '',
	deceasedDate: '',
	deceasedReason: '',
	soapImportStatus: '',
	cmsportalLogin: '',
	careTeam: '',
	county: '',
	industry: '',
	note: '',
	defaults: {
		'id': null,
		'title': '',
		'language': '',
		'financial': '',
		'fname': '',
		'lname': '',
		'mname': '',
		'dob': '',
		'street': '',
		'postalCode': '',
		'city': '',
		'state': '',
		'countryCode': '',
		'driversLicense': '',
		'ss': '',
		'occupation': '',
		'phoneHome': '',
		'phoneBiz': '',
		'phoneContact': '',
		'phoneCell': '',
		'pharmacyId': '',
		'status': '',
		'contactRelationship': '',
		'date': new Date().toISOString().slice(0,10),
		'sex': '',
		'referrer': '',
		'referrerid': '',
		'providerid': '',
		'refProviderid': '',
		'email': '',
		'emailDirect': '',
		'ethnoracial': '',
		'race': '',
		'ethnicity': '',
		'religion': '',
		'interpretter': '',
		'migrantseasonal': '',
		'familySize': '',
		'monthlyIncome': '',
		'billingNote': '',
		'homeless': '',
		'financialReview': '',
		'pubpid': '',
		'pid': '',
		'hipaaMail': '',
		'hipaaVoice': '',
		'hipaaNotice': '',
		'hipaaMessage': '',
		'hipaaAllowsms': '',
		'hipaaAllowemail': '',
		'squad': '',
		'fitness': '',
		'referralSource': '',
		'pricelevel': '',
		'regdate': new Date().toISOString().slice(0,10),
		'contrastart': '',
		'completedAd': '',
		'adReviewed': '',
		'vfc': '',
		'mothersname': '',
		'guardiansname': '',
		'allowImmRegUse': '',
		'allowImmInfoShare': '',
		'allowHealthInfoEx': '',
		'allowPatientPortal': '',
		'deceasedDate': '',
		'deceasedReason': '',
		'soapImportStatus': '',
		'cmsportalLogin': '',
		'careTeam': '',
		'county': '',
		'industry': '',
		'note': ''
	}
});

/**
 * Patient Backbone Collection
 */
model.PatientCollection = model.AbstractCollection.extend({
	url: 'api/patientdata',
	model: model.PatientModel
});
/**
 * Portal Patient Edit Backbone Model
 */
model.PortalPatientModel = Backbone.Model.extend({
	urlRoot: 'api/portalpatient',
	idAttribute: 'id',
	id: '',
	title: '',
	language: '',
	financial: '',
	fname: '',
	lname: '',
	mname: '',
	dob: '',
	street: '',
	postalCode: '',
	city: '',
	state: '',
	countryCode: '',
	driversLicense: '',
	ss: '',
	occupation: '',
	phoneHome: '',
	phoneBiz: '',
	phoneContact: '',
	phoneCell: '',
	pharmacyId: '',
	status: '',
	contactRelationship: '',
	date: '',
	sex: '',
	referrer: '',
	referrerid: '',
	providerid: '',
	refProviderid: '',
	email: '',
	emailDirect: '',
	ethnoracial: '',
	race: '',
	ethnicity: '',
	religion: '',
	interpretter: '',
	migrantseasonal: '',
	familySize: '',
	monthlyIncome: '',
	billingNote: '',
	homeless: '',
	financialReview: '',
	pubpid: '',
	pid: '',
	hipaaMail: '',
	hipaaVoice: '',
	hipaaNotice: '',
	hipaaMessage: '',
	hipaaAllowsms: '',
	hipaaAllowemail: '',
	squad: '',
	fitness: '',
	referralSource: '',
	pricelevel: '',
	regdate: '',
	contrastart: '',
	completedAd: '',
	adReviewed: '',
	vfc: '',
	mothersname: '',
	guardiansname: '',
	allowImmRegUse: '',
	allowImmInfoShare: '',
	allowHealthInfoEx: '',
	allowPatientPortal: '',
	deceasedDate: '',
	deceasedReason: '',
	soapImportStatus: '',
	cmsportalLogin: '',
	careTeam: '',
	county: '',
	industry: '',
	note: '',
	defaults: {
		'id': null,
		'title': '',
		'language': '',
		'financial': '',
		'fname': '',
		'lname': '',
		'mname': '',
		'dob': '',
		'street': '',
		'postalCode': '',
		'city': '',
		'state': '',
		'countryCode': '',
		'driversLicense': '',
		'ss': '',
		'occupation': '',
		'phoneHome': '',
		'phoneBiz': '',
		'phoneContact': '',
		'phoneCell': '',
		'pharmacyId': '',
		'status': '',
		'contactRelationship': '',
		'date': new Date(),
		'sex': '',
		'referrer': '',
		'referrerid': '',
		'providerid': '',
		'refProviderid': '',
		'email': '',
		'emailDirect': '',
		'ethnoracial': '',
		'race': '',
		'ethnicity': '',
		'religion': '',
		'interpretter': '',
		'migrantseasonal': '',
		'familySize': '',
		'monthlyIncome': '',
		'billingNote': '',
		'homeless': '',
		'financialReview': '',
		'pubpid': '',
		'pid': '',
		'hipaaMail': '',
		'hipaaVoice': '',
		'hipaaNotice': '',
		'hipaaMessage': '',
		'hipaaAllowsms': '',
		'hipaaAllowemail': '',
		'squad': '',
		'fitness': '',
		'referralSource': '',
		'pricelevel': '',
		'regdate': new Date(),
		'contrastart': '',
		'completedAd': '',
		'adReviewed': '',
		'vfc': '',
		'mothersname': '',
		'guardiansname': '',
		'allowImmRegUse': '',
		'allowImmInfoShare': '',
		'allowHealthInfoEx': '',
		'allowPatientPortal': '',
		'deceasedDate': '',
		'deceasedReason': '',
		'soapImportStatus': '',
		'cmsportalLogin': '',
		'careTeam': '',
		'county': '',
		'industry': '',
		'note': ''
	}
});

/**
 * Portal Patient Backbone Collection
 */
model.PortalPatientCollection = model.AbstractCollection.extend({
	url: 'api/portalpatientdata',
	model: model.PortalPatientModel
});/**/

/**
 * User Backbone Model
 */
model.UserModel = Backbone.Model.extend({
	urlRoot: 'api/user',
	idAttribute: 'id',
	id: '',
	username: '',
	password: '',
	authorized: '',
	info: '',
	source: '',
	fname: '',
	mname: '',
	lname: '',
	federaltaxid: '',
	federaldrugid: '',
	upin: '',
	facility: '',
	facilityId: '',
	seeAuth: '',
	active: '',
	npi: '',
	title: '',
	specialty: '',
	billname: '',
	email: '',
	emailDirect: '',
	eserUrl: '',
	assistant: '',
	organization: '',
	valedictory: '',
	street: '',
	streetb: '',
	city: '',
	state: '',
	zip: '',
	street2: '',
	streetb2: '',
	city2: '',
	state2: '',
	zip2: '',
	phone: '',
	fax: '',
	phonew1: '',
	phonew2: '',
	phonecell: '',
	notes: '',
	calUi: '',
	taxonomy: '',
	ssiRelayhealth: '',
	calendar: '',
	abookType: '',
	pwdExpirationDate: '',
	pwdHistory1: '',
	pwdHistory2: '',
	defaultWarehouse: '',
	irnpool: '',
	stateLicenseNumber: '',
	newcropUserRole: '',
	cpoe: '',
	physicianType: '',
	defaults: {
		'id': null,
		'username': '',
		'password': '',
		'authorized': '',
		'info': '',
		'source': '',
		'fname': '',
		'mname': '',
		'lname': '',
		'federaltaxid': '',
		'federaldrugid': '',
		'upin': '',
		'facility': '',
		'facilityId': '',
		'seeAuth': '',
		'active': '',
		'npi': '',
		'title': '',
		'specialty': '',
		'billname': '',
		'email': '',
		'emailDirect': '',
		'eserUrl': '',
		'assistant': '',
		'organization': '',
		'valedictory': '',
		'street': '',
		'streetb': '',
		'city': '',
		'state': '',
		'zip': '',
		'street2': '',
		'streetb2': '',
		'city2': '',
		'state2': '',
		'zip2': '',
		'phone': '',
		'fax': '',
		'phonew1': '',
		'phonew2': '',
		'phonecell': '',
		'notes': '',
		'calUi': '',
		'taxonomy': '',
		'ssiRelayhealth': '',
		'calendar': '',
		'abookType': '',
		'pwdExpirationDate': '',
		'pwdHistory1': '',
		'pwdHistory2': '',
		'defaultWarehouse': '',
		'irnpool': '',
		'stateLicenseNumber': '',
		'newcropUserRole': '',
		'cpoe': '',
		'physicianType': ''
	}
});

/**
 * User Backbone Collection
 */
model.UserCollection = model.AbstractCollection.extend({
	url: 'api/users',
	model: model.UserModel
});

/**
 * InsuranceCompany Backbone Model
 */
model.InsuranceCompanyModel = Backbone.Model.extend({
	urlRoot: 'api/insurancecompany',
	idAttribute: 'id',
	id: '',
	name: '',
	attn: '',
	cmsId: '',
	freebType: '',
	x12ReceiverId: '',
	x12DefaultPartnerId: '',
	altCmsId: '',
	defaults: {
		'id': null,
		'name': '',
		'attn': '',
		'cmsId': '',
		'freebType': '',
		'x12ReceiverId': '',
		'x12DefaultPartnerId': '',
		'altCmsId': ''
	}
});

/**
 * InsuranceCompany Backbone Collection
 */
model.InsuranceCompanyCollection = model.AbstractCollection.extend({
	url: 'api/insurancecompanies',
	model: model.InsuranceCompanyModel
});

/**
 * InsuranceData Backbone Model
 */
model.InsuranceDataModel = Backbone.Model.extend({
	urlRoot: 'api/insurancedata',
	idAttribute: 'id',
	id: '',
	type: '',
	provider: '',
	planName: '',
	policyNumber: '',
	groupNumber: '',
	subscriberLname: '',
	subscriberMname: '',
	subscriberFname: '',
	subscriberRelationship: '',
	subscriberSs: '',
	subscriberDob: '',
	subscriberStreet: '',
	subscriberPostalCode: '',
	subscriberCity: '',
	subscriberState: '',
	subscriberCountry: '',
	subscriberPhone: '',
	subscriberEmployer: '',
	subscriberEmployerStreet: '',
	subscriberEmployerPostalCode: '',
	subscriberEmployerState: '',
	subscriberEmployerCountry: '',
	subscriberEmployerCity: '',
	copay: '',
	date: '',
	pid: '',
	subscriberSex: '',
	acceptAssignment: '',
	policyType: '',
	defaults: {
		'id': null,
		'type': '',
		'provider': '',
		'planName': '',
		'policyNumber': '',
		'groupNumber': '',
		'subscriberLname': '',
		'subscriberMname': '',
		'subscriberFname': '',
		'subscriberRelationship': '',
		'subscriberSs': '',
		'subscriberDob': new Date(),
		'subscriberStreet': '',
		'subscriberPostalCode': '',
		'subscriberCity': '',
		'subscriberState': '',
		'subscriberCountry': '',
		'subscriberPhone': '',
		'subscriberEmployer': '',
		'subscriberEmployerStreet': '',
		'subscriberEmployerPostalCode': '',
		'subscriberEmployerState': '',
		'subscriberEmployerCountry': '',
		'subscriberEmployerCity': '',
		'copay': '',
		'date': new Date(),
		'pid': '',
		'subscriberSex': '',
		'acceptAssignment': '',
		'policyType': ''
	}
});

/**
 * InsuranceData Backbone Collection
 */
model.InsuranceDataCollection = model.AbstractCollection.extend({
	url: 'api/insurancedatas',
	model: model.InsuranceDataModel
});
