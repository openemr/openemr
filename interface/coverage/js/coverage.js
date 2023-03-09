class Coveragelib {

	constructor(pid = '') {
		this.pid = pid;
	}

	/*Set Button Status*/
	setLoadingValue(ele, status) {
		if(status == true) {
			ele.disabled = true;
			ele.innerHTML = 'Eligibility Verifying...';

			// Set pageloader
			if(caselibObj) caselibObj.setPageLoader(true);
		} else if(status == false) {
			ele.disabled = false;
			ele.innerHTML = 'Eligibility Verification';

			// Set pageloader
			if(caselibObj) caselibObj.setPageLoader(false);
		}
	}

	/*Handle eligibility verification */
	async handleEligibilityVerification(ele, cnt, pid, case_id, ins_id, provider_id) {
		
		if(ele.disabled == false) {
			var provider_value = $("#provider_id").val();

			this.setLoadingValue(ele, true);

			/*Fetch eligibility verification*/
			var responce = await this.fetchEligibilityVerification(cnt, pid, case_id, ins_id, provider_value);

			/*Log Info*/
			//console.info(getJson(responce));
			
			if(isJson(responce)) {
				var responceData = getJson(responce);

				if(responceData.action == 1) {
					this.setFieldsValue(cnt, responceData);
				} else {
					await this.handleEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
				}
				
				/*Process responce Data*/
				if(responceData.success == 0) {
					//alert(responceData.error);
					await confirmBoxModal({
	                    type: 'alert',
	                    title: "Alert",
	                    html: responceData.error
	                });
				} else {
					if(responceData.userMessage) {
						//alert(responceData.userMessage);
						await confirmBoxModal({
		                    type: 'alert',
		                    title: "Alert",
		                    html: responceData.userMessage
		                });
					}
				}
			} else {
				//alert('Something wrong');
				await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: 'Something wrong'
                });
			}

			this.setLoadingValue(ele, false);
		}
	}

	setFieldsValue(cnt, responceData, update_val = "1") {
		$('#ins_raw_data'+cnt).html(responceData ? JSON.stringify(responceData) : '');

		if(responceData.eligibility_status == "eligible") {
			$('#statusText'+cnt).html('<i class="fa fa-check-circle" aria-hidden="true" style="color:#059862;"></i>');
		} else {
			$('#statusText'+cnt).html('<i class="fa fa-times-circle" aria-hidden="true" style="color:#F44336;"></i>');
		}

		var statusMsg = responceData.statusMsg ? responceData.statusMsg : false;
		if(statusMsg != false) {
			alert(statusMsg);
		}
	}

	/* Fetch eligibility verification data by calling service*/
	async fetchEligibilityVerification(cnt, pid, case_id, ins_id, provider_id) {
		const result = await $.ajax({
			type: "POST",
			url: top.webroot_url + '/interface/coverage/coverage_verification.php',
			datatype: "json",
			data: {
				case_id: case_id,
				cnt: cnt,
				ins_id: ins_id,
				provider_id: provider_id,
				pid: pid
			}
		});
		return result;
	}

	handleHistory(pid, case_id, cnt) {
		var url = top.webroot_url + '/interface/coverage/coverage_history.php?pid='+pid+'&case_id='+case_id+'&cnt='+cnt;
		dlgopen(url,'_blank', 'modal-xl', '', '', '', {
			sizeHeight: 'full'
		});
	}

	/*Fetch eligibility content by calling service based on rule*/
	async fetchEligibilityContent(cnt, pid, case_id, ins_id, provider_id) {
		const result = await $.ajax({
			type: "POST",
			url: top.webroot_url + '/interface/coverage/coverage_view.php',
			datatype: "json",
			data: {
				case_id: case_id,
				cnt: cnt,
				ins_id: ins_id,
				provider_id: provider_id,
				pid: pid
			}
		});
		return result;
	}

	/*Handle content on change of incurence company value*/
	async handleEligibilityContent(cnt = '', pid ='', case_id = '', ins_id = '', provider_id) {
		let responce = '';
		if(pid != '' && ins_id != '') {
			responce = await this.fetchEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
		}
		$('#verification_contaner_'+cnt).html(responce);
	}

	// coverage init (form_head.inc.php)
	init(pid) {
	}
}

(function(window, coveragelibObj, bootstrap, jQuery) {

	window.coveragelibObj = new Coveragelib();

})(window, window.coveragelibObj || {}, bootstrap, $, window.dlgopen || function() {});