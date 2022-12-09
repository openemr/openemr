class Caselib {

	constructor(pid = '') {
		this.pid = pid;
		this.curr_lpc_ele = null;
		this.insuranceChangeEvent = null;
	}

	async isCaseInsDataValid(ids = [], pid, employer = "") {
		var status = true;

		if(ids) {
			var bodyObj = { ids :  ids, pid : pid, employer : employer };
			const result = await $.ajax({
				type: "POST",
				url: top.webroot_url + '/library/OemrAD/interface/forms/cases/ajax/get_case_form_status.php',
				datatype: "json",
				data: bodyObj
			});

			if(result != '') {
				var resultObj = JSON.parse(result);
				if(resultObj && resultObj['case_form_status'] === false) {
					if(!confirm("Warning - all insurances must have subscribers listed, including PI.  Press \"Cancel\" to go back and set the subscriber or Press \"Ok\" to save and continue.")) {
						status = false;
					}
				}

				if(resultObj && resultObj['case_employer_status'] === false) {
					if(!confirm("Warning - all workers compensation insurances require an employer to be listed.  Press \"Cancel\" to go back and set the employer or Press \"Ok\" to save and continue.")) {
						status = false;
					}
				}
			}
		}

		return status;
	}

	checkAuthValidation() {
		var isAuthChecked = document.querySelector('.auth_req').checked;
		var numEle = document.querySelector('.auth_num_visit').value;
		var authStartDate = document.querySelector('.auth_start_date').value;
		var authEndDate = document.querySelector('.auth_end_date').value;

		var validationStatus = true;
		var errorMsg = [];

		if(isAuthChecked == true) {
			if((authStartDate == "" && authEndDate != "") || (authStartDate != "" && authEndDate == "") ) {
				validationStatus = false;
				errorMsg.push('Start Date and End Date are required if Authorization Dates are specified.');
			}

			if(authStartDate != "" && authEndDate != "") {
				var authStartD = new Date(authStartDate);
				var authEndD = new Date(authEndDate);

				if(authStartD > authEndD) {
					errorMsg.push('Authorization Details - the End Date must be equal or greater than the Start Date.');
					validationStatus = false;
				}
			}

			if(numEle != "" && isAuthChecked == true) {
				if(!isNaN(numEle) && numEle >= 0 && numEle < 100) {
					//validationStatus = true;
				} else {
					errorMsg.push('Authorized Number of Visits must be a number less than 100.');
					validationStatus = false;
				}
			}
		}

		if(errorMsg.length > 0) {
			alert(errorMsg.join('\n\n'));
		}

		return validationStatus;
	}

	validateNoteEmails() {
		let inValidEmailList = [];
		let case_header_notes = document.getElementById('case_header_notes').value;
		//let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;

		if(case_header_notes != "") {	
			let em_list = case_header_notes.split(",").map(element => element.trim());
			em_list.forEach((eItem, ei) => {
				if(eItem != "" && !eItem.match(mailformat)) {
					inValidEmailList.push(eItem);
				}
			});
		}

		if(inValidEmailList.length > 0) {
			return "Invalid Email ('" + inValidEmailList.join("', '") + "')";
		}

		return true;	
	}

	validateCaseForm() {
		var tmp_casemanager_hidden_sec = document.getElementsByClassName('tmp_casemanager_hidden_sec')[0].value;
		
		if(tmp_casemanager_hidden_sec != 1) {
			return true;
		}

		var case_manager_val = document.getElementById('case_header_case_manager').value;
		var errorList = [];
		
		if(case_manager_val == "") {
			errorList.push("Case manager field is required.");
		}

		let noteEmailStatus = this.validateNoteEmails();
		if(noteEmailStatus !== true) {
			//errorList.push(noteEmailStatus);
			alert(noteEmailStatus);
		}

		var csFieldContainer = document.querySelectorAll(".csmanager_container .csmanager_inputcontainer .csmanager_field_container");
		for(var i = 0; i < csFieldContainer.length; i++){
			var r1 = csFieldContainer[i].getElementsByClassName("rehab_field_1")[0];
			var r2 = csFieldContainer[i].getElementsByClassName("rehab_field_2")[0];
		   	
		   	if(r1 && r2) {
		   		if(r1.value.trim() == "" && r2.value.trim() != "") {
		   			errorList.push("Rehab plan-"+(i+1)+" field 1 must not be empty.");
		   		}

		   		if(r1.value.trim() != "" && r2.value.trim() == "") {
		   			errorList.push("Rehab plan-"+(i+1)+" field 2 must not be empty.");
		   		}
		   	}
		}

		if(errorList.length > 0) {
			alert(errorList.join("\n"));
			return false;
		}

		return true;
	}

	// saveCase (common.php)
	async saveCase(pid) {
		var caseData = $("#cases").serializeArray();
		var caseEmployer = $('#case_header_employer').val();

		var subScriberStatus = true;
		var finalStatus = true;

		var insIds = [];
		$('select[name^="case_header_ins_data_id"]').each(function(i, obj) {
			var eleVal = $(this).val();
			if(eleVal != "") {
				insIds.push(eleVal);
			}
		});

		var caseStatus = await this.isCaseInsDataValid(insIds, pid, caseEmployer);
		var authReqStatus = this.checkAuthValidation();
		var caseFormValidate = this.validateCaseForm();

		if(caseStatus === true && authReqStatus === true && caseFormValidate === true) {
			const formValidate = validateForm();
			return formValidate;
		}
	}

	// resetPISectionVal() {
	// 	var isEnable = $('#pi_case_row').hasClass("trHide");
	// 	if(isEnable) {
	// 		this.resetPiSectionValue(true);
	// 	} else {
	// 		this.resetPiSectionValue(false);
	// 	}
	// }

	// resetPiSectionValue(disable = true) {
	// 	if(disable === true) {
	// 		$('#case_header_case_manager').attr("disabled", "disabled");
	// 		$('.csmanager_field_container select').attr("disabled", "disabled");
	// 	} else {
	// 		$('#case_header_case_manager').removeAttr("disabled");
	// 		$('.csmanager_field_container select').removeAttr("disabled");
	// 	}
	// }

	// resetBillingSectionValue() {
	// 	$('#bc_date').val('');
	// 	$('#bc_notes').val('');
	// 	$('#bc_notes_dsc').val('');
	// 	$('#tmp_old_bc_value').val('');
	// }

	// toggleSubSection(type = 'show') {
	// 	var ele = $('.sec_row');

	// 	if(type == 'show' && ele) {
	// 		ele.find('.tmp_casemanager_hidden_sec').val('1');
	// 		ele.removeClass('trHide').trigger('sectionClassChange');
	// 	} else {
	// 		ele.find('.tmp_casemanager_hidden_sec').val('0');
	// 		ele.addClass('trHide').trigger('sectionClassChange');
	// 	}
	// }

	piCaseManagerSet(wrapper = '', needToEnable = "") {
		let ele = document.querySelector(wrapper);
		let visibiltyStatus = false;

		if(!ele) return false;


		let checkElement = ele.querySelector('.hidden_sec_input');
		if(needToEnable !== "") {
			if (checkElement) {
				if(needToEnable === true) checkElement.value = '1';
				if(needToEnable === false) checkElement.value = '0';
			}
		}

		if(checkElement) {
			if(checkElement.value === '1') {
				visibiltyStatus = true;
			} else if(checkElement.value === '0') {
				visibiltyStatus = false;
			}
		}

		if(visibiltyStatus === true) {
			ele.style.display = 'block';
		} else if(visibiltyStatus === false) {
			ele.style.display = 'none';
		}

		//Make Disable field
		let allField = ele.querySelectorAll('.makedisable');
		for (var i = 0, len = allField.length; i<len; i++) {
			if(visibiltyStatus == true) {
		    	allField[i].disabled = false;
			} else if(visibiltyStatus == false) {
				allField[i].disabled = true;
			}
		}
	}

	resetValue() {
		$('#lb_notes').val('');
		$('#lb_date').val('');
		$('#tmp_lb_notes').val('');
	}

	// resetPICaseManagerValues(ele) {
	// 	if(ele) {
	// 		ele.find('.case_manager').val('');
	// 		ele.find('.csmanager_container .csmanager_remove_btn').trigger( "click" );
	// 	}
	// }

	// This is for callback by the find-user popup.
	setuser(uid, uname, username, status) {
		if(window.curr_lpc_ele && window.curr_lpc_ele != null) {
			$(window.curr_lpc_ele).find('.uinner_container .ufield').eq(0).val(uid).trigger('change');
		}
	}

	// initCsRP($ids) {
	// 	$.each($ids, (index, item) => {
	// 		//var sEle = $('.cs_rp_container .cs_referring').eq(index);
	// 		var sEle = $('.cs_rp_container .ufield').eq(index);
	// 		if(sEle.length > 0) {
	// 			sEle.val(item);
	// 		} else {
	// 			//addCsRP(item);
	// 			this.adduEle(item, $('.cs_rp_container'));
	// 		}
	// 	});
	// }

	//Add Init Lawyer/Paralegal Contacts
	// initLPC(items) {
	// 	var itemId = JSON.parse(items);
	// 	$.each(itemId, (index, item) => {
	// 		//var sEle = $('.cs_rp_container .cs_referring').eq(index);
	// 		var sEle = $('.lpc_main_container .ufield').eq(index);
			
	// 		if(sEle.length > 0) {
	// 			sEle.val(item).trigger('change');
	// 		} else {
	// 			//addCsRP(item);
	// 			this.adduEle1(item, $('.lpc_main_container'));
	// 		}
	// 	});
	// }

	// adduEle(eVal = '', e) {
	// 	let e_clone =  $(e).find('.rawelements .uinner_container').clone();

	// 	if(eVal && eVal != '') {
	// 		e_clone.find('.ufield').eq(0).val(eVal);
	// 	}

	// 	let e_html = $("<div class='u_inputcontainer'><div>").html(e_clone).append("<div class='actionsBtn'><button type='button' class='u_remove_btn uRemoveBtn'>"+this.svgRemove+"</button></div>");
	// 	$(e).append(e_html);
	// }

	// adduEle1(eVal = '', e) {
	// 	let e_clone =  $(e).find('.rawelements .uinner_container').clone();
	// 	let e_html = $("<div class='u_inputcontainer'><div>").html(e_clone).append("<div class='actionsBtn'><button type='button' class='u_remove_btn uRemoveBtn'>"+this.svgRemove+"</button></div><div class='ipc_info_container'></div>");
	// 	$(e).append(e_html);

	// 	if(eVal && eVal != '') {
	// 		e_html.find('.ufield').eq(0).val(eVal).trigger('change');
	// 	}
	// }

	// addCsManagerRehabPlanField(eVal = '', eVal1 = '') {
	// 	let csmanager_clone = $('.csmanager_container .rawelements .csmanager_field_container').clone();

	// 	if(eVal && eVal != '') {
	// 		csmanager_clone.find('.rehab_field_1').eq(0).val(eVal);
	// 	} else {
	// 		csmanager_clone.find('.rehab_field_1').eq(0).val('');
	// 	}

	// 	if(eVal1 && eVal1 != '') {
	// 		csmanager_clone.find('.rehab_field_2').eq(0).val(eVal1);
	// 	} else {
	// 		csmanager_clone.find('.rehab_field_2').eq(0).val('');
	// 	}

	// 	let csmanager_html = $("<div class='csmanager_inputcontainer'><div>").html(csmanager_clone).append("<div><button type='button' class='csmanager_remove_btn'>"+this.svgRemove+"</button></div>");
	// 	$('.csmanager_inner_container').append(csmanager_html);
	// }

	// removeFirstCsManagerRehabPlanField(ele) {
	// 	$csmanagerEle = ele.parent().parent();
	// 	if($csmanagerEle) {
	// 		$csmanagerEle.find('.csmanager_field_container .rehab_field_1').eq(0).val('');
	// 		$csmanagerEle.find('.csmanager_field_container .rehab_field_2').eq(0).val('');
	// 	}
	// }

	// This invokes the find-addressbook popup.
	open_notes_log(pid, id) {
		var url = top.webroot_url + '/library/OemrAD/interface/forms/cases/case_view_logs.php?pid='+ pid +'&id='+id;
	  	let title = 'Logs';
	  	dlgopen(url, 'notesLogs', 600, 400, '', title);
	}

	open_field_log(pid, id, field_id = '', form_name = '') {
		var url = top.webroot_url + '/library/OemrAD/interface/forms/cases/view_logs.php?pid='+pid+'&form_id='+id+'&field_id='+field_id+'&form_name='+form_name;
	  	let title = 'Logs';
	  	dlgopen(url, 'viewfieldlog', 700, 400, '', title);
	}

	updatenotes() {
		var actionUrl = document.forms[0].action.split('?')[0];
		const params = this.getParams(document.forms[0].action);
		params['mode'] = 'updatenotes';

		var newParams = Object.keys(params).map(function(k) {
		    return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])
		}).join('&');

		document.forms[0].action = actionUrl +'?'+newParams;
		document.forms[0].submit();
	}

	getParams(url) {
		var params = {};
		var parser = document.createElement('a');
		parser.href = url;
		var query = parser.search.substring(1);
		var vars = query.split('&');
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			params[pair[0]] = decodeURIComponent(pair[1]);
		}
		return params;
	}

	// Hide show page loader
	setPageLoader(status = false) {
		if(status === true) {
			$('#pageLoader').show();
		} else {
			$('#pageLoader').hide();
		}
	}

	// Eligbility coverage
	handleEligibilityContent(self, pid) {
		let cnt = self.dataset.id;
		let ins_id = self.value;
		let provider_id = document.getElementById("provider_id")?.value;
		let case_id = document.getElementById("case_id").value;

		coveragelibObj.handleEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
	}
	
	//Handle Insurance liability 
	async handleInsuranceLiability(insEle, pid) {
		var eles = $('.ins-dropdown');
		var ids = [];

		$.each( eles, function( index, ele ){
		    var eleVal = $(ele).val();
		    if(eleVal && eleVal != "") {
		    	ids.push(eleVal);
			}
		});

		var bodyObj = { ids: ids, pid: pid };
		const result = await $.ajax({
			type: "POST",
			url: top.webroot_url + '/library/OemrAD/interface/forms/cases/ajax/check_lb.php',
			datatype: "json",
			data: bodyObj
		});

		if(result != '') {
			var resultObj = JSON.parse(result);
			if(resultObj && resultObj['status'] == true) {
				//$('.lb_row').removeClass('trHide').trigger('sectionClassChange');
				this.piCaseManagerSet('#pi_case_row', true);
			} else {
				//$('.lb_row').addClass('trHide').trigger('sectionClassChange');
				this.resetValue();
				this.piCaseManagerSet('#pi_case_row', false);
			}
		}

		// Trigger the Insurance Change Event
		insEle.dispatchEvent(new CustomEvent('insurance_change', { bubbles: true }));
	} 

	// Set Lawyer/Paralegal Contacts Info
	 setLawyerParalegalContacts(self) {
		let infoElement = $(self).closest('.m-element-wrapper').eq(0).find('.ipc_info_container');
		let dropExtInfo = $(self).find(':selected').attr('data-extinfo');
		dropExtInfo = atob(dropExtInfo);

		if(dropExtInfo != '') {
			infoElement.html(dropExtInfo);
		}
	}

	// case init (form_head.inc.php)
	caseInit(pid) {
		const self = this;

		// // Init multi elements
		// $('#reahab_wrapper').multielement();
		// $('#lpc_ele_container').multielement();
		// $('#aprovider_wrapper').multielement();

		// // To trigger the event Listener
		// document.addEventListener("insurance_change", (e) => {
		//     let targetElement = event.target || event.srcElement;
		//     self.handleEligibilityContent(targetElement, pid);
		// });

		// //Init check
		// self.piCaseManagerSet('#pi_case_row');

		// // Insurance Change check other details.
		// $('.ins-dropdown').change(async function() {
		// 	await self.handleInsuranceLiability(this, pid);
		// });

		// Check coverage check on change.
		// $( ".ins-dropdown" ).change(function() {
		// 	self.handleEligibilityContent($(this), pid);
		// });

		// Event sectionClassChange
		// $('#pi_case_row').on('sectionClassChange', function() {
		// 	self.resetPISectionVal();
		// });

		// /* Lawyer/Paralegal */
		// // Lawyer/Paralegal Contacts Set Info
		// $('#lpc_ele_container').on('change', 'select[data-field-id="lp_contact"]', function() {
		// 	setLawyerParalegalContacts(this);
		// });

		// $('#lpc_ele_container select[data-field-id="lp_contact"]').each(function(i, ele) {
		//     setLawyerParalegalContacts(ele);
		// });

		// /* End */

		/*
		
		//Reset
		self.resetPISectionVal();

		// Lawyer/Paralegal Contacts set info
		$('.u_container').on('change', '.lpcDropChange', function(){
			var dropExtInfo = $(this).find(':selected').attr('data-extinfo');
			dropExtInfo = atob(dropExtInfo);

			if(dropExtInfo != '') {
				$(this).parent().parent().find('.ipc_info_container').html(dropExtInfo);
			}
		});

		$('.u_container').on('click', '.uAddmoreBtn1', function(){
			self.adduEle1('', $(this).parent().parent().parent());
		});

		$('.u_container').on('click', '.uAddmoreBtn', function(){
			self.adduEle('', $(this).parent().parent().parent());
		});

		$('.u_container').on('click', '.uRemoveBtn', function(){
			//$(this).parent().parent().remove();
			var csEle = $(this).parent().parent();
			if(!csEle.hasClass('rawelements')) {
				$(this).parent().parent().remove();
			} else {
				$(csEle).find('.ufield').val('');
			}
		});

		//Case Manager
		$('.csmanager_container').on('click', '.csmanager_addmore_btn', function(){
			self.addCsManagerRehabPlanField();
		});

		$('.csmanager_container').on('click', '.csmanager_remove_btn', function(){
			//$csEle = $('.csmanager_container .csmanager_inputcontainer').length;
			var csEle = $(this).parent().parent();
			if(!csEle.hasClass('rawelements')) {
				$(this).parent().parent().remove();
			} else {
				self.removeFirstCsManagerRehabPlanField($(this));
			}
		});

		$('.csmanager_container').on('click', '.csmanager_fremove_btn', function(){
			self.removeFirstCsManagerRehabPlanField($(this));
		});

		// search lawyer
		$(".u_container").on('click', '.medium_modal', function(e) {
			window.curr_lpc_ele = $(this).parent().parent();

	        e.preventDefault();
	        e.stopPropagation();
	        dlgopen('', '', 700, 400, '', '', {
	            buttons: [
	                { text: 'Close', close: true, style: 'default btn-sm'}
	            ],
	            //onClosed: 'refreshme',
	            allowResize: false,
	            allowDrag: true,
	            dialogId: '',
	            type: 'iframe',
	            url: $(this).attr('href')
	        });
	    });
	    */
	}
}

(function(window, caselibObj, bootstrap, jQuery) {

	window.caselibObj = new Caselib();

})(window, window.caselibObj || {}, bootstrap, $, window.dlgopen || function() {});

// Multi Element JS
$.fn.multielement = function (opts = {}) {
	let mainChilds = $(this).children()[0];
	let elementsWrapper = $(this).find('.m-elements-wrapper');
	let elementWrapper = $(this).find('.m-elements-wrapper .m-element-wrapper').eq(0);
	let rawClone = elementWrapper ? $(elementWrapper) : null;
	let addEle = $(this).find('.m-btn-add').eq(0);
	let removeEle = $(this).find('.m-btn-remove').eq(0);
	let self = this;
	let eCount = elementsWrapper ? elementsWrapper.children().length : 0;
	let fValues = opts.values ? opts.values : [];

	// Add elements
	this.addElement = function(fieldVals =  {}) {
		if(rawClone) {
			// Element Clone
			let eleClone = rawClone.eq(0).clone();

			if(Object.keys(fieldVals).length > 0) {
				for (let fieldName in fieldVals) {
					let fVal = fieldVals[fieldName] ? fieldVals[fieldName] : '';
					let ce = eleClone.find('[data-field-id="'+fieldName+'"]').eq(0);

					if(fVal != "") {
						ce.val(fVal);
					} else {
						ce.val('');
					}
				}
			} else {
				// Set Value
				$(eleClone).find('input:text').val('');
				$(eleClone).find('select').val('');

				$(eleClone).find('.c-text-info').html('');
			}

			// Append Value
			$(elementsWrapper.eq(0)).append(eleClone);

			eCount++;
		}
	}

	// Remove elements
	this.removeElement = function(event) {
		let targetElement = event.target || event.srcElement;
		let cElement = $(targetElement).closest('.m-element-wrapper').eq(0);

		if(eCount > 1) {
			$(cElement).remove();
		} else {
			$(cElement).find('input:text').val('');
			$(cElement).find('select').val('');

			$(cElement).find('.c-text-info').html('');
		}

		eCount--;
	}

	$(this).on('click', '.m-btn-add', function() {
		self.addElement();
	});

	$(this).on('click', '.m-btn-remove', function(event) {
		self.removeElement(event);
	});

	// Set inti values
	fValues.forEach((s,i) => {
	   self.addElement(s);
	});
}