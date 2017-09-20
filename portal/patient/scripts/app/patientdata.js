/**
 * View logic for Patient Data
 *
 * application logic specific to the Patient profile page
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
var page = {
	patientData: new model.PatientCollection(),
	collectionView: null,
	patient: null,
	portalpatient: null,
	modelView: null,
	isInitialized: false,
	isInitializing: false,
	fetchParams: { filter: '', orderBy: '', orderDesc: '', page: 1, patientId: cpid,lookupList: null },
	fetchInProgress: false,
	dialogIsOpen: false,
	isEdited: false,

	/**
	 *
	 */
	init: function() {
		// ensure initialization only occurs once
		if (page.isInitialized || page.isInitializing) return;
		page.isInitializing = true;
		if( cuser < 1 )
			$('#savePatientButton').hide();

		$("#donePatientButton").hide();
		$("#replaceAllButton").hide();

		if (!$.isReady && console) console.warn('page was initialized before dom is ready.  views may not render properly.');
		// portal or audit
		$("#formHelp").click(function(e){
			e.preventDefault();
			$('#profileHelp').toggle();
		});

		$("#savePatientButton").click(function(e){
			e.preventDefault();
			page.updateModel(1);
		});
		$("#donePatientButton").click(function(e){
			e.preventDefault();
			page.updateModel();
		});
		$("#replaceAllButton").click(function(e){
			e.preventDefault();
			page.revertAll();
			$("#donePatientButton").show();
		});

		// initialize the collection view
		this.collectionView = new view.CollectionView({
			el: $("#patientCollectionContainer"),
			templateEl: $("#patientCollectionTemplate"),
			collection: page.patientData
		});

		this.collectionView.on('rendered',function(){
			if(!page.isInitialized){
				var m = page.patientData.first();
				m = (m === undefined) ? "" : m;
				if(m){
					if(m.get('pid') < 1){
						m = "";
					}
				}
				page.showDetailForm(m);
			}
			page.isInitialized = true;
			page.isInitializing = false;
		});

		this.fetchPatientData({ page: 1,patientId: cpid });

		// initialize the model view
		this.modelView = new view.ModelView({
			el: $("#patientModelContainer")
		});

		// tell the model view where it's template is located
		this.modelView.templateEl = $("#patientModelTemplate");

		this.modelView.on('rendered',function(){ // model rendered
			$(function (){
				$('.jquery-date-picker').datetimepicker({
					i18n:{
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
				    timepicker:false
	            });
			});
			// initialize any special controls
			// populate the dropdown options for provider and referer
			var examinerIdValues = new model.UserCollection();
			examinerIdValues.fetch({
				success: function(c){
					var dd = $('#providerid');
					var ddd = $('#careTeam');
					var dddd = $('#refProviderid');
					dd.append('<option value="0">Unassigned</option>');
					ddd.append('<option value="0">Unassigned</option>');
					dddd.append('<option value="0">Unassigned</option>');
					c.forEach(function(item,index) {
						if( item.get( 'authorized') != '1' ) return;
						var uid=item.get( 'id')
						var tname = item.get('title')? item.get('title')+' ':'';
						var sname = item.get('specialty')?','+item.get('specialty'):'';
						var uname = tname+item.get('fname')+' '+item.get('lname')+sname;
						dd.append(app.getOptionHtml(
							uid,
							uname,
							page.patient.get('providerid') == item.get( 'id')
						));
						ddd.append(app.getOptionHtml(
								uid,
								uname,
								page.patient.get('careTeam') == item.get( 'id')
						));
						dddd.append(app.getOptionHtml(
								uid,
								uname,
								page.patient.get('refProviderid') == item.get( 'id')
						));/**/
					});
					
				if( page.portalpatient.get('pid') ){
					$("#replaceAllButton").show();
					page.isEdited = true;
					$.each(page.portalpatient.attributes, function(key, value) {
						if( value != page.patient.get(key) ){
							if(key=='providerid' || key=='refProviderid' || key=='careTeam' ){
								var os = 0+value-1;
								if( os > -1 ){
									var em = examinerIdValues.at(os)
									value = em.get('title')+' '+em.get('fname')+' '+em.get('lname')
								}
								else
									value = 'Unassigned'
							}

							if( ( $("input[name="+key+"]").attr('type') == 'radio' || $('#'+key).is('select') ) && value == "" )
								value = 'Unassigned';
							//$('#'+key+'InputContainer span.help-inline').addClass('editval');
							$('#'+key+'InputContainer span.help-inline').html(
									'<a class="editval" style="color:blue" onclick="page.toggleVal(this); return false;" data-tstate=new data-id="'+key+'">'+value+'</a>');
							$('#'+key+'InputContainer span.help-inline').show();
						}
					});
				}
				page.replaceAll();
				$('form :input').on("change", function(){
					$("#donePatientButton").show();
					$('#savePatientButton').show();
				});
				},
				error: function(collection,response,scope) {
					app.appendAlert(app.getErrorMessage(response), 'alert-danger',0,'modelAlert');
				}
			});

			$(".controls .inline-inputs").find(':input:checked').parent('.btn').addClass('active');

			$(function (){
				$('.jquery-date-time-picker').datetimepicker({
					i18n:{
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
				    timepicker:true
				});
			});
			 document.getElementById('title').value =page.patient.get('title')
			 document.getElementById('sex').value =page.patient.get('sex')
			 document.getElementById('status').value =page.patient.get('status')
			 document.getElementById('ethnicity').value =page.patient.get('ethnicity')
			 document.getElementById('race').value =page.patient.get('race')
			 document.getElementById('state').value =page.patient.get('state')
			 document.getElementById('religion').value =page.patient.get('religion')
			 document.getElementById('referralSource').value =page.patient.get('referralSource')
			$('#note').val(_.escape(page.portalpatient.get('note')))
			$("#dismissHelp").click(function(e){
				e.preventDefault();
				$('#profileHelp').toggle();
			});
			page.isInitialized = true;
			page.isInitializing = false;
		});
	},
	/**
	 * Replace field with edit
	 * @param element
	 */
	replaceVal:function( el ){
		var a = $(el).data('id');
		if( !document.getElementById(a) ){
			$('input[name='+a+'][value="' +  _.escape(page.portalpatient.get(a)) + '"'+']').prop('checked', true).closest('label').css({"color":"blue"});
		}
		else{
				$('#'+a).prop('value', page.portalpatient.get(a))
				$('#'+a).css({"color":"blue","font-weight":"normal"});
			}
		var v = _.escape(page.patient.get(a));
		if( ( $("input[name="+a+"]").attr('type') == 'radio' || $('#'+a).is('select') ) && v == "" )
			v = 'Unassigned';
		$('#'+a+'InputContainer span.help-inline').html('');
		$('#'+a+'InputContainer span.help-inline').html( '<a class="editval" style="color:red;font-size:16px" onclick="page.revertVal(this); return false;" data-tstate=chart data-id="'+a+'">'+v+'</a>');
		$('#'+a+'InputContainer span.help-inline').show();
	},
	revertVal:function( el ){
		var a = $(el).data('id');
		if( !document.getElementById(a) ){
			$('input[name='+a+'][value="' +  _.escape(page.patient.get(a)) + '"'+']').prop('checked', true).closest('label').css({"color":"red"});
		}
		else{
				$('#'+a).prop('value', page.patient.get(a))
				$('#'+a).css({"color":"red","font-weight":"normal"});
			}
		var v = _.escape(page.portalpatient.get(a));
		if( ( $("input[name="+a+"]").attr('type') == 'radio' || $('#'+a).is('select') ) && v == "" )
			v = 'Unassigned';
		$('#'+a+'InputContainer span.help-inline').html('');
		$('#'+a+'InputContainer span.help-inline').html( '<a class="editval" style="color:blue;font-size:16px" onclick="page.replaceVal(this); return false;" data-tstate=new data-id="'+a+'">'+v+'</a>');
		$('#'+a+'InputContainer span.help-inline').show();
		if( !$("#donePatientButton").is(":visible") ){
			$("#donePatientButton").show();
		}
		if( !$("#savePatientButton").is(":visible") ){
			$('#savePatientButton').show();
		}
	},

	/**
	 * Replace all fields with edited ie mass replace
	 * @param none
	 */
	replaceAll: function( ){
		$('.editval').each( function(){
			page.replaceVal(this);
		});
		//$("#replaceAllButton").hide();
	},
	revertAll: function( ){
		$('.editval').each( function(){
			page.revertVal(this);
		});
		$("#replaceAllButton").hide();
	},
	/**
	 * Fetch the collection data from the server
	 * @param object params passed through to collection.fetch
	 * @param bool true to hide the loading animation
	 */
	fetchPatientData: function(params, hideLoader) {
		// persist the params so that paging/sorting/filtering will play together nicely
		page.fetchParams = params;

		if (page.fetchInProgress) {
			if (console) console.log('supressing fetch because it is already in progress');
		}

		page.fetchInProgress = true;

		if (!hideLoader) app.showProgress('modelLoader');

		page.patientData.fetch({
			data: params,
			success: function() {
				if (page.patientData.collectionHasChanged) {
					// the sync event will trigger the view to re-render
				}
				app.hideProgress('modelLoader');
				page.fetchInProgress = false;
			},
			error: function(m, r) {
				app.appendAlert(app.getErrorMessage(r), 'alert-danger',0,'collectionAlert');
				app.hideProgress('modelLoader');
				page.fetchInProgress = false;
			}
		});
	},

	/**
	 * show the form for editing a model
	 * @param model
	 */
	showDetailForm: function(m) {
		page.patient = m ? m : new model.PatientModel();
		page.modelView.model = page.patient;
		if (page.patient.id == null || page.patient.id == '') {
			page.renderModelView(false);
			app.hideProgress('modelLoader');
		} else {
			app.showProgress('modelLoader');
			page.patient.fetch({
				data: { 'patientId': cpid },
				success: function() {
					var pm = page.portalpatient;
					page.getEditedPatient(pm)
				},
				error: function(m, r) {
					app.appendAlert(app.getErrorMessage(r), 'alert-danger',0,'modelAlert');
					app.hideProgress('modelLoader');
				}
			});
		}
	},
	/**
	 * get edited from audit table if any
	 * @param model
	 */
	getEditedPatient: function(m) {
		page.portalpatient = m ? m : new model.PortalPatientModel();
		page.portalpatient.set('id','1')
			page.portalpatient.fetch({
				data: { 'patientId': cpid },
				success: function() {
					// audit profil data returned render needs to be here due to chaining
					page.renderModelView(false);
					return true;
				},
				error: function(m, r) {
					// still have to render live data even if no changes pending
					page.renderModelView(false);
					return false;
				}
			});
	},
	/**
	 * Render the model template in the popup
	 * @param bool show the delete button
	 */
	renderModelView: function(showDeleteButton)	{
		page.modelView.render();
		app.hideProgress('modelLoader');
	},

	/**
	 * update the model that is currently displayed in the dialog
	 */
	updateModel: function(live) {
		// reset any previous errors
		$('#modelAlert').html('');
		$('.form-group').removeClass('error');
		$('.help-inline').html('');

		// if this is new then on success we need to add it to the collection
		var isNew = page.patient.isNew();

		app.showProgress('modelLoader');

		if( live != 1 )
			page.patient.urlRoot = 'api/portalpatient';

page.patient.save({
			'title': $('select#title').val(),
			'language': $('input#language').val(),
			'financial': $('input#financial').val(),
			'fname': $('input#fname').val(),
			'lname': $('input#lname').val(),
			'mname': $('input#mname').val(),
			'dob': $('input#dob').val(),
			'street': $('input#street').val(),
			'postalCode': $('input#postalCode').val(),
			'city': $('input#city').val(),
			'state': $('select#state').val(),
			'countryCode': $('input#countryCode').val(),
			'driversLicense': $('input#driversLicense').val(),
			'ss': $('input#ss').val(),
			'occupation': $('textarea#occupation').val(),
			'phoneHome': $('input#phoneHome').val(),
			'phoneBiz': $('input#phoneBiz').val(),
			'phoneContact': $('input#phoneContact').val(),
			'phoneCell': $('input#phoneCell').val(),
			'pharmacyId': $('input#pharmacyId').val() || 0,
			'status': $('select#status').val(),
			'contactRelationship': $('input#contactRelationship').val(),
			'date': $('input#date').val(),
			'sex': $('select#sex').val(),
			'referrer': $('input#referrer').val(),
			'referrerid': $('input#referrerid').val(),
			'providerid': $('select#providerid').val(),
			'refProviderid': $('select#refProviderid').val() || 0,
			'email': $('input#email').val(),
			'emailDirect': $('input#emailDirect').val(),
			'ethnoracial': $('input#ethnoracial').val(),
			'race': $('select#race').val(),
			'ethnicity': $('select#ethnicity').val(),
			'religion': $('select#religion').val(),
			'interpretter': $('input#interpretter').val(),
			'migrantseasonal': $('input#migrantseasonal').val(),
			'familySize': $('input#familySize').val(),
			'monthlyIncome': $('input#monthlyIncome').val(),
		//	'billingNote': $('textarea#billingNote').val(),
		//	'homeless': $('input#homeless').val(),
		//	'financialReview': $('input#financialReview').val()+' '+$('input#financialReview-time').val(),
			'pubpid': $('input#pubpid').val(),
			'pid': $('input#pid').val(),
			'hipaaMail': $('input[name=hipaaMail]:checked').val(),
			'hipaaVoice': $('input[name=hipaaVoice]:checked').val(),
			'hipaaNotice': $('input[name=hipaaNotice]:checked').val(),
			'hipaaMessage': $('input#hipaaMessage').val(),
			'hipaaAllowsms': $('input[name=hipaaAllowsms]:checked').val(),
			'hipaaAllowemail': $('input[name=hipaaAllowemail]:checked').val(),
			'referralSource': $('select#referralSource').val(),
			//'pricelevel': $('input#pricelevel').val(),
			'regdate': $('input#regdate').val(),
			'contrastart': $('input#contrastart').val(),
			'completedAd': $('input[name=completedAd]:checked').val(),
			//'adReviewed': $('input#adReviewed').val(),
			//'vfc': $('input#vfc').val(),
			'mothersname': $('input#mothersname').val(),
			'guardiansname': $('input#guardiansname').val(),
			'allowImmRegUse': $('input[name=allowImmRegUse]:checked').val(),
			'allowImmInfoShare': $('input[name=allowImmInfoShare]:checked').val(),
			'allowHealthInfoEx': $('input[name=allowHealthInfoEx]:checked').val(),
			'allowPatientPortal': $('input[name=allowPatientPortal]:checked').val(),
			//'deceasedDate': $('input#deceasedDate').val()+' '+$('input#deceasedDate-time').val(),
			//'deceasedReason': $('input#deceasedReason').val(),
			'soapImportStatus': $('input#soapImportStatus').val(),
			//'cmsportalLogin': $('input#cmsportalLogin').val(),
			'careTeam': $('select#careTeam').val() || 0,
			'county': $('input#county').val(),
			'industry': $('textarea#industry').val(),
			'note': $('textarea#note').val()
		}, {
			wait: true,
			success: function(){
				if( live != 1){
					setTimeout("app.appendAlert('Patient was sucessfully " + (isNew ? "inserted" : "updated") + "','alert-success',2000,'collectionAlert')",200);
					setTimeout("window.location.href ='"+webRoot+"/portal/home.php'",2500);
				}
				else if( live == 1 && register != '0'){ // for testing
					//alert('Save Success')
				}
				else {
					eModal.close(true)
				}
				app.hideProgress('modelLoader');
				if (isNew) {
					page.renderModelView(false);
				}
				if (model.reloadCollectionOnModelUpdate) {
					// re-fetch and render the collection after the model has been updated
					//page.fetchPatientData(page.fetchParams,true);
				}
			},
			error: function(model,response,scope){
				app.hideProgress('modelLoader');
				app.appendAlert(app.getErrorMessage(response), 'alert-danger',0,'modelAlert');
				try {
					var json = $.parseJSON(response.responseText);
					if (json.errors) {
						$.each(json.errors, function(key, value) {
							$('#'+key+'InputContainer').addClass('error');
							$('#'+key+'InputContainer span.help-inline').html(value);
							$('#'+key+'InputContainer span.help-inline').show();
						});
					}
				} catch (e2) {
					if (console) console.log('error parsing server response: '+e2.message);
				}
			}
		});

	},

	/**
	 * delete the model that is currently displayed in the dialog
	 */
	deleteModel: function()	{
		// reset any previous errors
		$('#modelAlert').html('');

		app.showProgress('modelLoader');

		page.patient.destroy({
			wait: true,
			success: function(){
				$('#patientDetailDialog').modal('hide');
				setTimeout("app.appendAlert('The Patient record was deleted','alert-success',3000,'collectionAlert')",500);
				app.hideProgress('modelLoader');

				if (model.reloadCollectionOnModelUpdate) {
					// re-fetch and render the collection after the model has been updated
					page.fetchPatientData(page.fetchParams,true);
				}
			},
			error: function(model,response,scope) {
				app.appendAlert(app.getErrorMessage(response), 'alert-danger',0,'modelAlert');
				app.hideProgress('modelLoader');
			}
		});
	}
};
