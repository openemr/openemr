/**
 * View logic for OnsitePortalActivities
 *
 * application logic specific to the OnsitePortalActivity listing pageAudit
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var pageAudit = {
	onsitePortalActivities: new model.OnsitePortalActivityCollection(),
	collectionView: null,
	onsitePortalActivity: null,
	modelView: null,
	isInitialized: false,
	isInitializing: false,

	fetchParams: { orderBy: '', orderDesc: '', page:'', patientId:cpid, activity:'document', doc: '0' },
	fetchInProgress: false,
	dialogIsOpen: false,

	/**
	 *
	 */
	init: function() {
		// ensure initialization only occurs once
		if (pageAudit.isInitialized || pageAudit.isInitializing) return;
		pageAudit.isInitializing = true;

		if (!$.isReady && console) console.warn('pageAudit was initialized before dom is ready.  views may not render properly.');
		// initialize the model view
		pageAudit.modelView = new view.ModelView({
			el: $("#onsitePortalActivityModelContainer")
		});

		// tell the model view where it's template is located
		pageAudit.modelView.templateEl = $("#onsitePortalActivityModelTemplate");

	},

	/**
	 * Fetch the collection data from the server
	 * @param object params passed through to collection.fetch
	 * @param bool true to hide the loading animation
	 */
	fetchOnsitePortalActivities: function(params, hideLoader) {
		// persist the params so that paging/sorting/filtering will play together nicely

		pageAudit.fetchParams = params;

		if (pageAudit.fetchInProgress) {
			if (console) console.log('pageAudit supressing fetch because it is already in progress');
		}

		pageAudit.fetchInProgress = true;

		pageAudit.onsitePortalActivities.fetch({
			data: params,
			success: function() {
				if (pageAudit.onsitePortalActivities.collectionHasChanged) {
				}
				//if(!pageAudit.isInitialized){
					var m = pageAudit.onsitePortalActivities.first();
					pageAudit.getAudit(m);
				//}
				pageAudit.fetchInProgress = false;
                console.log('Activity fetched!');
			},

			error: function(m, r) {
				pageAudit.getAudit("");
				pageAudit.fetchInProgress = false;
			}

		});
	},

	/**
	 * show the dialog for editing a model
	 * @param model
	 */
	getAudit: function(m) {

		pageAudit.onsitePortalActivity = m ? m : new model.OnsitePortalActivityModel();

		pageAudit.modelView.model = pageAudit.onsitePortalActivity;

		if (pageAudit.onsitePortalActivity.id == null || pageAudit.onsitePortalActivity.id == '') {
			// this is a new record, there is no need to contact the server
			//pageAudit.renderModelView(false);
		} else {
			app.showProgress('modelLoader');
			// fetch the model from the server so we are not updating stale data
			pageAudit.onsitePortalActivity.fetch({

				success: function() {
					// data returned from the server.  render the model view
					//pageAudit.renderModelView(true);
				},

				error: function(m, r) {

				}

			});
		}

	},

	/**
	 * Render the model template in the popup
	 * @param bool show the delete button
	 */
	renderModelView: function(showDeleteButton)	{
		pageAudit.modelView.render();

		if (showDeleteButton) {
			// attach click handlers to the delete buttons

			$('#deleteOnsitePortalActivityButton').click(function(e) {
				e.preventDefault();
				$('#confirmDeleteOnsitePortalActivityContainer').show('fast');
			});

			$('#cancelDeleteOnsitePortalActivityButton').click(function(e) {
				e.preventDefault();
				$('#confirmDeleteOnsitePortalActivityContainer').hide('fast');
			});

			$('#confirmDeleteOnsitePortalActivityButton').click(function(e) {
				e.preventDefault();
				pageAudit.deleteModel();
			});

		} else {
			// no point in initializing the click handlers if we don't show the button
			$('#deleteOnsitePortalActivityButtonContainer').hide();
		}
	},

	/**
	 * update the model that is currently displayed in the dialog
	 */
	updateModel: function() {

		// if this is new then on success we need to add it to the collection
		var isNew = pageAudit.onsitePortalActivity.isNew();
		if(isNew){
			pageAudit.onsitePortalActivity.set('patientId',cpid)
		}
		pageAudit.onsitePortalActivity.save({

			'date': pageAudit.onsitePortalActivity.get('date'),
			'patientId': pageAudit.onsitePortalActivity.get('patientId'),
			'activity': pageAudit.onsitePortalActivity.get('activity'),
			'requireAudit': pageAudit.onsitePortalActivity.get('requireAudit'),
			'pendingAction': pageAudit.onsitePortalActivity.get('pendingAction'),
			'actionTaken': pageAudit.onsitePortalActivity.get('actionTaken'),
			'status': pageAudit.onsitePortalActivity.get('status'),
			'narrative': pageAudit.onsitePortalActivity.get('narrative'),
			'tableAction': pageAudit.onsitePortalActivity.get('tableAction'),
			'tableArgs': pageAudit.onsitePortalActivity.get('tableArgs'),
			'actionUser': pageAudit.onsitePortalActivity.get('actionUser'),
			'actionTakenTime': pageAudit.onsitePortalActivity.get('actionTakenTime'),
			'checksum': pageAudit.onsitePortalActivity.get('checksum')
		}, {
			wait: true,
			success: function(){

				if (isNew) { pageAudit.onsitePortalActivities.add(pageAudit.onsitePortalActivity) }

				if (model.reloadCollectionOnModelUpdate) {
					// re-fetch and render the collection after the model has been updated
					pageAudit.fetchOnsitePortalActivities(pageAudit.fetchParams,true);
				}
		},
			error: function(model,response,scope){

				try {
					var json = $.parseJSON(response.responseText);

					if (json.errors) {
						$.each(json.errors, function(key, value) {
							/*$('#'+key+'InputContainer').addClass('error');
							$('#'+key+'InputContainer span.help-inline').html(value);
							$('#'+key+'InputContainer span.help-inline').show();*/
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

		pageAudit.onsitePortalActivity.destroy({
			wait: true,
			success: function(){
				$('#onsitePortalActivityDetailDialog').modal('hide');
				setTimeout("app.appendAlert('The OnsitePortalActivity record was deleted','alert-success',3000,'collectionAlert')",500);
				app.hideProgress('modelLoader');

				if (model.reloadCollectionOnModelUpdate) {
					// re-fetch and render the collection after the model has been updated
					pageAudit.fetchOnsitePortalActivities(pageAudit.fetchParams,true);
				}
			},
			error: function(model,response,scope) {
				//app.appendAlert(app.getErrorMessage(response), 'alert-error',0,'modelAlert');
				//app.hideProgress('modelLoader');
			}
		});
	}
};

