function calc_weight_gain(old_weight, new_weight, target)
{
	if(document.getElementById(old_weight) == null) return false;
	if(document.getElementById(new_weight) == null) return false;
	if(document.getElementById(target) == null) return false;
	var w1 = document.getElementById(old_weight).value;
	var w2 = document.getElementById(new_weight).value;
	
	if(isNaN(w1) || isNaN(w2)) return false;
	if(!w1 || !w2) return false;
	var w3 = w2 - w1;
	document.getElementById(target).value = w3.toFixed(2);
}

function calc_all_weights(max)
{
	var cnt = parseInt(1);
	while (cnt <= max) {
		calc_weight_gain("pre_preg_wt", "pn_weight_"+cnt, "pn_gain_"+cnt);
		cnt++;
	}
	return true;
}

function visit_match_fill(form_dt, visit_dt, target, source)
{
	if(document.getElementById(form_dt) == null) return false;
	if(document.getElementById(visit_dt) == null) return false;
	var test = document.getElementById(visit_dt).value;
	if(document.getElementById(target) == null) return false;
	var dt = document.getElementById(form_dt).value;
	// IF THERE IS A '/' ASSUME MM/DD/YYYY FORMAT
	if(dt.indexOf('/') != -1) {
		var parts = dt.split('/');
		if(parts.length < 3) return false;
		dt = parts[2] + '-' + parts[0] + '-' + parts[1];
	}
	// alert('My Date ['+dt+'] and Test ('+test+')');
	if(dt != document.getElementById(visit_dt).value) return false;
	if(document.getElementById(target).value == '') document.getElementById(target).value = source;
}

function calc_weeks_gest(final_edd_field, visit_dt_field, target)
{
	if(document.getElementById(final_edd_field) == null) return false;
	if(document.getElementById(visit_dt_field) == null) return false;
	if(document.getElementById(target) == null) return false;
	var final_edd_dt = document.getElementById(final_edd_field).value;
	if(final_edd_dt == 0 || final_edd_dt == '') return false;	
	var visit_dt = document.getElementById(visit_dt_field).value;
	if(visit_dt == 0 || visit_dt == '') return false;	
	final_edd_dt = new Date(final_edd_dt);
	visit_dt = new Date(visit_dt);
	if(visit_dt == 'Invalid Date') return false;

	if(final_edd_dt == 'Invalid Date') {
		alert("Final EDD Is Not a Valid Date, Use 'YYYY-MM-DD' to Auto-Calc Weeks Gestation");
		return false;
	}
	var end_seconds = final_edd_dt.getTime();
	var start_seconds = visit_dt.getTime();
	var diff = (end_seconds - start_seconds);
	if(diff <= 0) return false;

	// THIS CHANGES OUR DIFFERENCE TO A NUMBER OF DAYS
	diff = Math.floor(diff / 86400000);
	// SUBTRACT THE DIFFERENCE FROM 282 WHICH WE WILL USE FOR TOTAL DAYS
	diff = 280 - diff;
	// TAKE THE LEFTOVER DAYS TO A FRACTION
	var days = diff % 7;
	// THEN TO WEEKS FOR THEIR DISPLAY
	diff = (diff / 7);
	var weeks = Math.floor(diff);
	if(days) weeks = weeks + " " + days + "/" + 7;
	document.getElementById(target).value = weeks;
}

function calc_all_weeks(max)
{
	var cnt = parseInt(1);
	while (cnt < max) {
		calc_weeks_gest("upd_edd", "pn_date_"+cnt, "pn_gest_"+cnt);
		cnt++;
	}
	return(true);
}
