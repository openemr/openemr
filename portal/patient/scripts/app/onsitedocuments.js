/**
 * View logic for OnsiteDocuments
 *
 * application logic specific to the OnsiteDocument listing page
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

	onsiteDocuments: new model.OnsiteDocumentCollection(),
	collectionView: null,
	onsiteDocument: null,
	modelView: null,
	isInitialized: false,
	isInitializing: false,
	isSaved: true,
	fetchParams: { filter: '', orderBy: '', orderDesc: '', page: 1,patientId: cpid,recid: recid },
	fetchInProgress: false,
	dialogIsOpen: false,

	init: function() {
		// ensure initialization only occurs once
		if (page.isInitialized || page.isInitializing) return;
		page.isInitializing = true;

		if (!$.isReady && console) console.warn('page was initialized before dom is ready.  views may not render properly.');

		// make the new button clickable
		$("#newOnsiteDocumentButton").click(function(e) {
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
		$('#filter').change(function(obj) {
			page.fetchParams.filter = $('#filter').val();
			page.fetchParams.page = 1;
			page.fetchOnsiteDocuments(page.fetchParams);
		});

		// make the rows clickable ('rendered' is a custom event, not a standard backbone event)
		this.collectionView.on('rendered',function(){
			if( cuser != '-patient-') $("#topnav").hide();
			// attach click handler to the table rows for editing
			$('table.collection tbody tr').click(function(e) {
				e.preventDefault();
				var m = page.onsiteDocuments.get(this.id);
				page.showDetailDialog(m);
			});
			// make the headers clickable for sorting
 			$('table.collection thead tr th').click(function(e) {
 				e.preventDefault();
				var prop = this.id.replace('header_','');
				// toggle the ascending/descending before we change the sort prop
				page.fetchParams.orderDesc = (prop == page.fetchParams.orderBy && !page.fetchParams.orderDesc) ? '1' : '';
				page.fetchParams.orderBy = prop;
				page.fetchParams.page = 1;
 				page.fetchOnsiteDocuments(page.fetchParams);
 			});
			// attach click handlers to the pagination controls
			$('.pageButton').click(function(e) {
				e.preventDefault();
				page.fetchParams.page = this.id.substr(5);
				page.fetchOnsiteDocuments(page.fetchParams);
			});
			// No dups - turn off buttons if doc exist
			this.collection.each(function(model, index, list){
				var tplname = model.get('docType')
				if(model.get('denialReason') != 'Locked')
					$('#'+tplname.slice(0, -4)).hide();
			});

			page.isInitialized = true;
			page.isInitializing = false;
		});
// ---------  Get Collection ------------------------//
		this.fetchOnsiteDocuments(page.fetchParams);

		// initialize the model view
		this.modelView = new view.ModelView({
			el: $("#onsiteDocumentModelContainer")
		});

		// tell the model view where it's template is located
		this.modelView.templateEl = $("#onsiteDocumentModelTemplate");
// template rendered ready -------------------------------------------------------//
		this.modelView.on('rendered',function(){
			docid = page.onsiteDocument.get('docType');
			if( cuser != '-patient-'){
				function todocument(docid) {
					  h = webRoot+'/controller.php?document&view&patient_id=30&doc_id=' + docid;
					  location.href = h;
					}
				$("#signTemplate").hide();
				$("#printTemplate").hide();
				$("#submitTemplate").hide();
				$("#sendTemplate").hide();
				$("#saveTemplate").hide();
				$("#homeTemplate").hide();
				$("#downloadTemplate").show();
				$("#chartTemplate").show();

				$("#chartTemplate").on('click', function(e) {
					 e.preventDefault();
					 flattenDocument();
					 var documentContents = document.getElementById('templatecontent').innerHTML;
					 $("#content").val(documentContents);
					 $("#docid").val(docid);
					 $("#handler").val('chart');
					 $("#status").val('charted');
					 page.onsiteDocument.set('denialReason','Locked')
					 page.onsiteDocument.set('authorizingSignator',cuser)
					 //page.onsiteDocument.set('','')
					 pageAudit.onsitePortalActivity.set('status','closed')
					 pageAudit.onsitePortalActivity.set('pendingAction','completed')
					 pageAudit.onsitePortalActivity.set('actionUser',cuser)
				    // $("#template").submit();
					 var posting = $.post(
							 "./../lib/doc_lib.php",
						{
								 docid: docid,
								 content: documentContents,
								 handler: "chart"
                     });
					 posting.done(function( rtn ){
						 page.updateModel();
						 eModal.alert(alertMsg1,"Success");
					 });

				  });
				 $("#downloadTemplate").on('click', function(e) {
					 e.preventDefault();
					 flattenDocument();
					 var documentContents = document.getElementById('templatecontent').innerHTML;
					 $("#content").val(documentContents);
					 $("#docid").val(docid);
					 $("#handler").val('download');
					 $("#status").val('downloaded');
					 page.onsiteDocument.set('denialReason','Locked')
					 page.onsiteDocument.set('authorizingSignator',cuser)
					 //page.onsiteDocument.set('','')
					 pageAudit.onsitePortalActivity.set('status','closed')
					 pageAudit.onsitePortalActivity.set('pendingAction','completed')
					 pageAudit.onsitePortalActivity.set('actionUser',cuser)

				     $("#template").submit();
					 page.updateModel();
				    // location.reload();
				  });

			}
			else{
				$("#downloadTemplate").hide();
				$("#chartTemplate").hide();
			}

			$("#saveTemplate").on('click', function(e) {
				 e.preventDefault();
				 var documentContents = document.getElementById('templatecontent').innerHTML;
				 $("#content").val(documentContents);
				 if( page.onsiteDocument.get('denialReason') == 'In Review')
						pageAudit.onsitePortalActivity.set('status','waiting')
					else
						pageAudit.onsitePortalActivity.set('status','editing')
			     page.updateModel();
				// setTimeout("location.reload();",1500);
			    // flattenDocument();
			  });
			$('#sidebar').affix({
			      offset: {
			        top: $('navbar').height()
			      }
			});
			$("#sendTemplate").on('click', function(e) {
				 e.preventDefault();
				 var documentContents = document.getElementById('templatecontent').innerHTML;
				 //$("#docid").val('hipaa.pdf');
				 $("#content").val(documentContents);
				 pageAudit.onsitePortalActivity.set('status','waiting')
				 page.onsiteDocument.set('denialReason','In Review')
			     page.updateModel();
				 setTimeout("location.reload();",1500);
			    // flattenDocument();
			  });

			 $("#submitTemplate").on('click', function() {
				 if( page.onsiteDocument.get('denialReason') == 'In Review')
						pageAudit.onsitePortalActivity.set('status','waiting')
					else
						pageAudit.onsitePortalActivity.set('status','editing')

				 flattenDocument();

				// document.getElementById("loader").style.display = "block";
				 var documentContents = document.getElementById('templatecontent').innerHTML;
				 $("#docid").val(docid);
				 $("#content").val(documentContents);
			     $("#template").submit();
			     page.updateModel();
			    // location.reload();
			  });
			 	page.getDocument(page.onsiteDocument.get('docType'),cpid)
			 	if( cuser != '-patient-')
			 		flattenDocument();
			 	pageAudit.fetchParams.doc = page.onsiteDocument.get('id')
				pageAudit.fetchOnsitePortalActivities(pageAudit.fetchParams);
		});
	},

	/**
	 * Fetch the collection data from the server
	 * @param object params passed through to collection.fetch
	 * @param bool true to hide the loading animation
	 */
	fetchOnsiteDocuments: function(params, hideLoader) {
		// persist the params so that paging/sorting/filtering will play together nicely
		page.fetchParams = params;

		if (page.fetchInProgress) {
			if (console) console.log('supressing fetch because it is already in progress');
		}

		page.fetchInProgress = true;

		if (!hideLoader) app.showProgress('loader');

		page.onsiteDocuments.fetch({
			data: params,
			success: function() {
				if (page.onsiteDocuments.collectionHasChanged) {

				}
				app.hideProgress('loader');
				page.fetchInProgress = false;
			},
			error: function(m, r) {
				app.appendAlert(app.getErrorMessage(r), 'alert-error',0,'collectionAlert');
				app.hideProgress('loader');
				page.fetchInProgress = false;
			}

		});
	},
	// ----------------------------------------------------//
	newDocument: function(pid,user,docname){
		docid = docname;
		cuser = user;
		cpid = pid;
		isNewDoc = true;
		m = new model.OnsiteDocumentModel();
		m.set('docType',docid)
		m.set('denialReason','New')
		$('#docid').val('docid')
		$('#status').val('New')

		page.showDetailDialog(m); // saved in rendered event

	},
	getDocument: function(docname,pid){
		var dn = page.onsiteDocument.get('docType');
		if( dn == docname && dn > '' && !isNewDoc){
			$("#docid").val(dn)
			$('#templatecontent').empty().append( page.onsiteDocument.get('fullDocument') );
			restoreDocumentEdits();
		}
		else{
			var liburl = webRoot+'/portal/lib/download_template.php';
			$.ajax({
				type: "POST",
				url: liburl,
				data: {docid: docname, pid: pid},
				beforeSend: function(xhr){
					console.log("Please wait...");
				},
				error: function(qXHR, textStatus, errorThrow){
					console.log("There was an error");
				},
				success: function(templateHtml, textStatus, jqXHR){
					$("#docid").val(docname);
					$('#templatecontent').empty().append(templateHtml);
					if( isNewDoc ){
						isNewDoc = false;
						page.isSaved = false;
						$("#printTemplate").hide();
						$("#submitTemplate").hide();
						$("#sendTemplate").hide();
						page.onsiteDocument.set('fullDocument',templateHtml);
					}
				}
			});
		}
		var cdate = page.onsiteDocument.get('createDate')
		var s = page.onsiteDocument.get('denialReason')
		$('#docPanelHeader').append(' : '+dn+' Dated: '+cdate+' Status: ' + s)
	},
	/**
	 * show the doc for editing
	 * @param model
	 */
	showDetailDialog: function(m) {
		page.onsiteDocument = m ? m : new model.OnsiteDocumentModel();
		page.modelView.model = page.onsiteDocument;

		if (page.onsiteDocument.id == null || page.onsiteDocument.id == '') {
			page.renderModelView(false);
		} else {
			app.showProgress('modelLoader');

			page.onsiteDocument.fetch({
				success: function() {
					if(cuser != '-patient-')
						page.renderModelView(false);
					else
						page.renderModelView(true);
				},
				error: function(m, r) {
					app.appendAlert(app.getErrorMessage(r), 'alert-error',0,'modelAlert');
					app.hideProgress('modelLoader');
				}
			});
		}
	},

	/**
	 * Render the model template in the popup
	 * @param bool show the delete button
	 */
	renderModelView: function(showDeleteButton)	{
		page.modelView.render();
		app.hideProgress('modelLoader');

		// initialize any special controls
		if (showDeleteButton) {
			// attach click handlers to the delete buttons
			$('#confirmDeleteOnsiteDocumentContainer').hide('fast');
			$('#deleteOnsiteDocumentButtonContainer').show();
			$('#deleteOnsiteDocumentButton').click(function(e) {
				e.preventDefault();
				$('#confirmDeleteOnsiteDocumentContainer').show('fast');
			});

			$('#cancelDeleteOnsiteDocumentButton').click(function(e) {
				e.preventDefault();
				$('#confirmDeleteOnsiteDocumentContainer').hide('fast');
			});

			$('#confirmDeleteOnsiteDocumentButton').click(function(e) {
				e.preventDefault();
				page.deleteModel();

			});

		} else {
			// no point in initializing the click handlers if we don't show the button
			$('#patientSignature').prop( "onclick", null );
			$('#deleteOnsiteDocumentButtonContainer').hide();
		}
	},

	/**
	 * update the model that is currently displayed in the dialog
	 */
	updateModel: function() {
		// reset any previous errors
		$('#modelAlert').html('');
		$('.control-group').removeClass('error');
		$('.help-inline').html('');

		// if this is new then on success we need to add it to the collection
		var isNew = page.onsiteDocument.isNew();
		var s = page.onsiteDocument.get('denialReason')
		if( !isNew && s == 'New' && s != 'In Review')
			page.onsiteDocument.set('denialReason','Open')

		app.showProgress('modelLoader');
		var isLink = $('#patientSignature').attr( 'src') ? $('#patientSignature').attr( 'src').indexOf('signhere') : -1
		if(isLink != -1 )
			$('#patientSignature').attr( 'src',signhere);
		var ptsignature = $('#patientSignature').attr( 'src');
		if( ptsignature == signhere) {
			ptsignature = "";
		}
		page.onsiteDocument.save({
			'pid': cpid,
			//'facility': $('input#facility').val(),
			'provider': page.onsiteDocument.get('provider'),
			//'encounter': $('input#encounter').val(),
			'createDate': page.onsiteDocument.get('createDate'),
			'docType': page.onsiteDocument.get('docType'),
			'patientSignedStatus': ptsignature?'1':'0',
			'patientSignedTime': ptsignature? new Date() : '0000-00-00', //page.onsiteDocument.get('patientSignedTime'),
			'authorizeSignedTime': page.onsiteDocument.get('authorizeSignedTime'),
			'acceptSignedStatus': page.onsiteDocument.get('acceptSignedStatus'),
			'authorizingSignator': page.onsiteDocument.get('authorizingSignator'),
			'reviewDate': (cuser != '-patient-')?new Date() : '0000-00-00',  //page.onsiteDocument.get('reviewDate'),
			'denialReason': page.onsiteDocument.get('denialReason'),
			'authorizedSignature': page.onsiteDocument.get('authorizedSignature'),
			'patientSignature': ptsignature,
			'fullDocument': $( '#templatecontent').html(),
			'fileName': page.onsiteDocument.get('fileName'),
			'filePath': page.onsiteDocument.get('filePath')
		}, {
			wait: true,
			success: function(){
				setTimeout("app.appendAlert('" + msgSuccess + "','alert-success',2000,'collectionAlert')",500);
				app.hideProgress('modelLoader');
				pageAudit.onsitePortalActivity.set('date',page.onsiteDocument.get('createDate'))
				pageAudit.onsitePortalActivity.set('activity','document')

				pageAudit.onsitePortalActivity.set('patientId',cpid)
				pageAudit.onsitePortalActivity.set('tableAction','update')
				pageAudit.onsitePortalActivity.set('tableArgs',page.onsiteDocument.get('id'))
				pageAudit.onsitePortalActivity.set('narrative',page.onsiteDocument.get('docType'))
				pageAudit.updateModel()
				if (isNew) {
					$('#confirmDeleteOnsiteDocumentContainer').hide('fast');
					$('#deleteOnsiteDocumentButtonContainer').show();
					$("#printTemplate").show();
					$("#submitTemplate").show();
					$("#sendTemplate").show();
					isNewDoc = false;
					page.onsiteDocuments.add(page.onsiteDocument)
				}

				if (model.reloadCollectionOnModelUpdate) {
					page.fetchOnsiteDocuments(page.fetchParams,true);
					page.showDetailDialog(page.onsiteDocument);

				}
		},
			error: function(model,response,scope){
				app.hideProgress('modelLoader');
				app.appendAlert(app.getErrorMessage(response), 'alert-error',0,'modelAlert');
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

		page.onsiteDocument.destroy({
			wait: true,
			success: function(){
				setTimeout("app.appendAlert('"+msgDelete+"','alert-success',2000,'collectionAlert')",500);
				app.hideProgress('modelLoader');
				pageAudit.onsitePortalActivity.set('status','deleted')
			    pageAudit.onsitePortalActivity.set('pendingAction','none')
			    pageAudit.onsitePortalActivity.set('activity','document')

				pageAudit.onsitePortalActivity.set('pid',cpid)
				pageAudit.onsitePortalActivity.set('tableAction','delete')
				pageAudit.onsitePortalActivity.set('tableArgs',page.onsiteDocument.get('id'))
				pageAudit.onsitePortalActivity.set('narrative','Patient deleted un-charted template')
				pageAudit.updateModel()
				//pageAudit.onsitePortalActivity.destroy();

				if (model.reloadCollectionOnModelUpdate) {
					// re-fetch and render the collection after the model has been updated
					page.fetchOnsiteDocuments(page.fetchParams,true);
					setTimeout("location.reload();",2000);

				}
			},
			error: function(model,response,scope) {
				app.appendAlert(app.getErrorMessage(response), 'alert-error',0,'modelAlert');
				app.hideProgress('modelLoader');
			}
		});
	}
};

