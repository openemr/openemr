{literal}
<script>
/*
 * vitals_functions.js
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function convUnit(system, unit, name)
{
    if (unit == 'kg' || unit == 'lbs')
    {
        if (system == 'metric')
        {
            return convKgtoLb(name);
        }
        else
        {
            return convLbtoKg(name);
        }
    }

    if (unit == 'in' || unit == 'cm')
    {
        if (system == 'metric')
        {
            return convCmtoIn(name);
        }
        else
        {
            return convIntoCm(name);
        }
    }

    if (unit == 'C' || unit=='F')
    {
        if (system == 'metric')
        {
            return convCtoF(name);
        }
        else
        {
            return convFtoC(name);
        }
    }
}

function convLbtoKg(name) {
    var lb = $("#"+name).val();
    var hash_loc=lb.indexOf("#");
    if(hash_loc>=0)
    {
        var pounds=lb.substr(0,hash_loc);
        var ounces=lb.substr(hash_loc+1);
        var num=parseInt(pounds)+parseInt(ounces)/16;
        lb=num;
        $("#"+name).val(lb);
    }
    if (lb == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (lb == parseFloat(lb)) {
        kg = lb*0.45359237;
        kg = kg.toFixed(2);
        $("#"+name+"_metric").val(kg);
    }
    else {
        $("#"+name+"_metric").val("");
    }

    if (name == "weight_input") {
        calculateBMI();
    }
}

function convKgtoLb(name) {
    var kg = $("#"+name+"_metric").val();

    if (kg == "0") {
        $("#"+name).val("0");
    }
    else if (kg == parseFloat(kg)) {
        lb = kg/0.45359237;
        lb = lb.toFixed(2);
        $("#"+name).val(lb);
    }
    else {
        $("#"+name).val("");
    }

    if (name == "weight_input") {
        calculateBMI();
    }
}

function convIntoCm(name) {
    var inch = $("#"+name).val();

    if (inch == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (inch == parseFloat(inch)) {
        cm = inch*2.54;
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
        inch = cm/2.54;
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
        Cdeg = (Fdeg-32)*0.5556;
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
        Fdeg = (Cdeg/0.5556)+32;
        Fdeg = Fdeg.toFixed(2);
        $("#"+name).val(Fdeg);
    }
    else {
        $("#"+name).val("");
    }
}

function calculateBMI() {
    var bmi = 0;
    var height = $("#height_input").val();
    var weight = $("#weight_input").val();
    if(height == 0 || weight == 0) {
        $("#BMI").val("");
    }
    else if((height == parseFloat(height)) && (weight == parseFloat(weight))) {
        bmi = weight/height/height*703;
        bmi = bmi.toFixed(1);
        $("#BMI_input").val(bmi);
    }
    else {
        $("#BMI_input").val("");
    }
}
</script>
{/literal}