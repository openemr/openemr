window.formScriptValidations = [];

class Caselib {

	constructor(pid = '') {
		this.pid = pid;
		this.curr_lpc_ele = null;
		this.insuranceChangeEvent = null;
	}

	async validate_InsData(pid) {
		var employer = $('#case_header_employer').val();
		var ids = [];
		$('select[name^="case_header_ins_data_id"]').each(function(i, obj) {
			var eleVal = $(this).val();
			if(eleVal != "") {
				ids.push(eleVal);
			}
		});

		var status = true;

		if(ids) {
			var bodyObj = { ids :  ids, pid : pid, employer : employer };
			const result = await $.ajax({
				type: "POST",
				url: top.webroot_url + '/interface/forms/cases/ajax/get_case_form_status.php',
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

	validate_Auth() {
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

	validate_CaseForm() {
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

	// This is for callback by the find-user popup.
	setuser(uid, uname, username, status) {
		if(window.curr_lpc_ele && window.curr_lpc_ele != null) {
			$(window.curr_lpc_ele).find('.uinner_container .ufield').eq(0).val(uid).trigger('change');
		}
	}

	// This invokes the find-addressbook popup.
	open_notes_log(pid, id) {
		var url = top.webroot_url + '/interface/forms/cases/case_view_logs.php?pid='+ pid +'&id='+id;
	  	dlgopen(url, '_blank', 'modal-mlg', '', '', '', {
	  		sizeHeight : 'full'
	  	});
	}

	open_field_log(pid, id, field_id = '', form_name = '') {
		let url = top.webroot_url + '/interface/forms/cases/view_logs.php?pid='+pid+'&form_id='+id+'&field_id='+field_id+'&form_name='+form_name;
	  	dlgopen(url, '_blank', 'modal-mlg', '', '', '', {
	  		sizeHeight : 'full'
	  	});
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
	async setPageLoader(status = false) {
		return await new Promise(resolve => {
			if(status === true) {
				$('#pageLoader').show();
			} else {
				$('#pageLoader').hide();
			}

			setTimeout(function () {
      			return resolve(true);
    		}, 100);
		});
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
			url: top.webroot_url + '/interface/forms/cases/ajax/check_lb.php',
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
		dropExtInfo = dropExtInfo && dropExtInfo != '' ? atob(dropExtInfo) : '';

		if(dropExtInfo != '') {
			infoElement.html(dropExtInfo);
		}
	}

	// saveCase (common.php)
	async saveCase(pid) {

		// Set page loader.
		await this.setPageLoader(true);

		var caseData = $("#cases").serializeArray();
		var validationStatus = true;

		for (const validationFun of window.formScriptValidations) {
			if(typeof validationFun == 'function') {
  				var funValidationStatus = await validationFun();
  				if(funValidationStatus === false) {
  					validationStatus = funValidationStatus;
  				}
  			}
		}

		// Unset page loader.
		await this.setPageLoader(false);

		if(validationStatus === true) {
			const formValidate = validateForm();
			return formValidate;
		}
	}
}

(function(window, caselibObj, bootstrap, jQuery) {

	// Case lib object.
	window.caselibObj = new Caselib(); 

})(window, window.caselibObj || {}, bootstrap, $, window.dlgopen || function() {});
