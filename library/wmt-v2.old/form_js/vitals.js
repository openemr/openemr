function setUrineDipNegative() {
	document.getElementById('vital_specific_gravity').value = '1.005';
	document.getElementById('vital_ph').value = '5';
	document.getElementById('vital_leukocytes').value = 'neg';
	document.getElementById('vital_nitrite').value = 'neg';
	document.getElementById('vital_protein').value = 'neg';
	document.getElementById('vital_glucose').value = 'normal';
	document.getElementById('vital_ketones').value = 'neg';
	document.getElementById('vital_urobilinogen').value = 'normal';
	document.getElementById('vital_bilirubin').value = 'neg';
	document.getElementById('vital_blood').value = 'neg';
	document.getElementById('vital_hemoglobin').value = 'neg';
}

function convLbtoKg(name) {
    var lb = $("#"+name).val();
    var hash_loc = lb.indexOf("#");
    if(hash_loc >= 0)
    {
        var pounds = lb.substr(0,hash_loc);
        var ounces = lb.substr(hash_loc+1);
        var num = parseInt(pounds)+parseInt(ounces)/16;
        lb = num;
        $("#"+name).val(lb);
    }
    if (lb == "0") {
        $("#"+name+"_metric").val("0");
    }		
    else if (lb == parseFloat(lb)) {
        var kg = lb * 0.45359237;
        kg = kg.toFixed(2);
        $("#"+name+"_metric").val(kg);
    }
    else {
        $("#"+name+"_metric").val("");
    }
} 

function convKgtoLb(name) {
    var kg = $("#"+name+"_metric").val();

    if (kg == "0") {
        $("#"+name).val("0");
    }    
    else if (kg == parseFloat(kg)) {
        var lb = kg / 0.45359237;
        lb = lb.toFixed(2);
        $("#"+name).val(lb);
    }
    else {
        $("#"+name).val("");
    }
}

function convIntoCm(name) {
    var inch = $("#"+name).val();

    if (inch == "0") {
        $("#"+name+"_metric").val("0");
    }    
    else if (inch == parseFloat(inch)) {
        var cm = inch * 2.54;
        cm = cm.toFixed(2);
        $("#"+name+"_metric").val(cm);
    }
    else {
        $("#"+name+"_metric").val("");
    }

    if (name == "height_input") {
        calculateBMI();
    }
}

function convCmtoIn(name) {
    var cm = $("#"+name+"_metric").val();

    if (cm == "0") {
        $("#"+name).val("0");
    }    
    else if (cm == parseFloat(cm)) {
        var inch = cm / 2.54;
        inch = inch.toFixed(2);
        $("#"+name).val(inch);
    }
    else {
        $("#"+name).val("");
    }    

    if (name == "height_input") {
        calculateBMI();
    }
}

function convFtoC(name) {
    var Fdeg = $("#"+name).val();
    if (Fdeg == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (Fdeg == parseFloat(Fdeg)) {
        var Cdeg = (Fdeg - 32) * 0.5556;
        Cdeg = Cdeg.toFixed(2);
        $("#"+name+"_metric").val(Cdeg);
    }
    else {
        $("#"+name+"_metric").val("");
    }
}

function convCtoF(name) {
    var Cdeg = $("#"+name+"_metric").val();
    if (Cdeg == "0") {
        $("#"+name).val("0");
    }
    else if (Cdeg == parseFloat(Cdeg)) {
        var Fdeg = (Cdeg / 0.5556) + 32;
        Fdeg = Fdeg.toFixed(2);
        $("#"+name).val(Fdeg);
    }
    else {
        $("#"+name).val("");
    }
}

