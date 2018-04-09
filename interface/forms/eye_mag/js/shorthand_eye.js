/**
 * forms/eye_mag/js/shorthand_eye.js
 *
 * JS Functions for eye_mag form(s) Shorthand Entry System
 *
 * Copyright (C) 2016 Raymond Magauran <magauran@MedFetch.com>
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
 * @author Ray Magauran <magauran@MedFetch.com>
 * @link http://www.open-emr.org
 */

function expand_vocab(text) {
    text = text.replace(/\binf\b/g,"inferior")
    .replace(/\bsup\b/g,"superior")
    .replace(/\bnas /g,"nasal")
    .replace(/\btemp /g,"temporal")
    .replace(/\bmed\b/g,"medial")
    .replace(/\blat\b/g,"lateral")
    .replace(/\bdermato\b/g,"dermatochalasis")
    .replace(/w\/ /g,"with ")
    .replace(/\blac(\s+)/g,"laceration")
    .replace(/\blacr\b/g,'lacrimal')
    .replace(/\bdcr\b/ig,"DCR")
    .replace(/\bbcc\b/ig,"BCC")
    .replace(/\bscc\b/ig,"SCC")
    .replace(/\bsebc\b/ig,"sebaceous cell carcinoma")
    .replace(/\bfh\b/ig,"forehead")
    .replace(/\bglab\b/ig,"glabellar")
    .replace(/\bcic\b/ig,"cicatricial")
    .replace(/\bentrop\b/i,"entropion")
    .replace(/\bectrop\b/i,"ectropion")
    .replace(/\bect\b/,"ectropion")
    .replace(/\bent\b/i,"entropion")
    .replace(/\btr\b/ig,"trace")
    .replace(/\bgut\b/g,"guttata")
    .replace(/\btr\b/ig,"trace")
    .replace(/\bpter\b/g,'pterygium')
    .replace(/\bpig\b/g,'pigmented')
    .replace(/\binj\b/ig,"injection")
    .replace(/\bfc\b/ig,"flare/cell")
    .replace(/\bks\b/ig,"kruckenberg spindle")
    .replace(/\bsebc\b/ig,"sebaceous cell carcinoma")
    .replace(/\bspk\b/ig,"SPK")
    .replace(/\bpek\b/ig,"PEK")
    .replace(/\bstr\b/ig,"stromal")
    .replace(/\bendo?\b/ig,"endothelial")
    .replace(/\brec\b/ig,"recession")
    .replace(/\b1 o\b/ig,"1 o'clock")
    .replace(/\b2 o\b/ig,"2 o'clock")
    .replace(/\b3 o\b/ig,"3 o'clock")
    .replace(/\b4 o\b/ig,"4 o'clock")
    .replace(/\b5 o\b/ig,"5 o'clock")
    .replace(/\b6 o\b/ig,"6 o'clock")
    .replace(/\b7 o\b/ig,"7 o'clock")
    .replace(/\b8 o\b/ig,"8 o'clock")
    .replace(/\b9 o\b/ig,"9 o'clock")
    .replace(/\b10 o\b/ig,"10 o'clock")
    .replace(/\b11 o\b/ig,"11 o'clock")
    .replace(/\b12 o\b/ig,"12 o'clock")
    .replace(/\blimb\b/i,"limbus")
    .replace(/\btl\b/i,"tear lake")
    .replace(/\bcsme\b/ig,"CSME")
    .replace(/\bbdr(\b)/ig,"BDR")
    .replace(/\bppdr\b/g,'PPDR')
    .replace(/\bht\b/ig,"horseshoe tear")
    .replace(/\bab\b/ig,"air bubble")
    .replace(/\bc3f8\b/ig,"C3F8")
    .replace(/\bma\b/ig,"macroaneurysm")
    .replace(/\bmias\b/ig,"microaneurysm")
    .replace(/\bped\b/ig,"PED")
    .replace(/\bmac\b/i,"macula")
    .replace(/\bfov\b/i,"fovea")
    .replace(/\bvh\b/i,"vitreous hemorrhage");
    return text;
}
function process_kb(field,text,appendix,prior_field,prior_text) {
    response = process_kb_1(field,text,appendix);
         // If the field is found, the text is added (or appended) to the field
    if (response['field'] =='error') {
            // Human error correcting:
            //  If the field is not found, append the "field + text" data to the prior_field's prior_text if it exists.
            //  In essence the ";" is believed to be an error so it is replaced by ", " for most fields.
            //  For POH/PMH/MEDS/ALLERGIES/PSURG it NOT appended but is instead added as a new entry to the list.
            //  In essence the ";" is believed to be an error so it is replaced by "." for these fields.
            //  Otherwise processing stops and a lot of ensuing entries could be lost.
            //  We need this type of error correcting if we are to move to dictating shorthand.
        field = field.toLocaleLowerCase();
        response = process_kb_1(prior_field,field + text,".a");
    }
    prior_field = response['field']; //a global variable
    prior_text = response['prior_text']; //a global variable
    return response;
}

function process_kb_1(field,text,appendix) {
    var field2='';
    var nofield = 'error';
    var reaction;
    var prior_text;
        // OK we need a rapid entry method for PMSFH.
        // imagine being able to enter meds as a list.  Just type them in the textarea
        // and poof they are documented. (or just dictate into this text area - sure!)
        // Structure = field:text.text.text;  field:text; all are appended, no replacement.
    
        // First list fields that exist but need special processing...
        // Second, if the field exists on the form just do it/
        // Third, if what is entered does not exist, see if it makes sense...
    
        // POH:text; 1 entry to POH.
        // PMH:text1.text2;PMH:text3;  3 entries to PMH.
        // POH:Phaco/IOL OD 4/4/1994.Phaco/IOL OS 4/24/1995.  Analyze for dates too?
        // Surg:text1.text2.text3;  3 entries to past surgery history
        // Meds:Lasix 80mg QAM.Timolol 0.5% GFS QHS OU.Brimonidine 0.1% BID OU;
        // ALL:Sulfa rash.PCN hives;  2 added with reaction to Allergies.
        //
        //0=PMH,1=Allergy,2=Meds,3=surgery,4=Dental,5=POH,6=FH,7=SH,8=ROS
        // First list fields that exist but need special processing...
        // this method should work for PMH,ALLERGY,MEDS,SURGERY,POH,POS (<--openEMR Eye terms).
            //Maybe Dental but we are not using that so leave it to others.
            //Not FH,SH or ROS though...
            //some aliases:
    
    if (field == "ALL") field = "Allergy";
    if (field == "ALLERGY") field = "Allergy";
    if (field == "MEDICATION") field = "Medication";
    if (field == "MEDICATIONS") field = "Medication";
    if (field == "MEDS") field = "Medication";
    if (field == "SURG") field = "Surgery";
    if (field == "SURGERY") field = "Surgery";
    if (field == "PSURG") field = "Surgery";
    if (field == "PSURGH") field = "Surgery";
        //For dictation, will search for the whole phrase, like "Past Surgical History" also
    if ((field =="PMH")||
        (field =="Allergy")||
        (field =="Medication")||
        (field =="Surgery")||
        (field =="POH")||
        (field =="POS")) {
        //if you create your own Issue, identify it now
        
            //Numerical text with a decimal need to be processed before splitting up entries.
        text = text.replace(/(\d)\.(\d)/g,"$1UGLYHACK$2");
        
        var url = "../../forms/eye_mag/save.php?PMSFH_save=1&mode=update&form_save=1";
        var text_seg = text.match(/[^\.]*/g);
        for (index=0; index < text_seg.length; ++index) {
            if (text_seg[index] =='') continue;
            text_seg[index] = text_seg[index].replace(/UGLYHACK/g,".");
            prior_text = text_seg[index];
            if ((field == "Allergy")&&(text_seg[index].match(/\s/))) {
                allergy = text_seg[index].match(/(.*)\s(.*)/);
                prior_text = allergy[1];
                reaction = allergy[2];
            }
                //here we can process surg dates also?
            if (reaction == null) reaction = '';
            var formData = {
                'form_save'         : "1",
                'mode'              : "update",
                'form_id'           : $('#form_id').val(),
                'uniqueID'          : $('#uniqueID').val(),
                'pid'               : $('#pid').val(),
                'form_type'         : field,
                'form_title'        : prior_text,
                'field'             : field,
                'form_begin'        : $('#pid').val(),
                'form_reaction'     : reaction,
            };
            top.restoreSession();
            $.ajax({
                   type 		: 'POST',
                   url          : url,
                   data 		: formData,
                   success:(function(result) {
                                obj = JSON.parse(result);
                                $("#QP_PMH").html(obj.PMH_panel);
                                if ($('#PMH_right').height() > $('#PMH_left').height()) {
                                    $('#PMH_left').height($('#PMH_right').height());
                                } else { $('#PMH_left').height($('#PMH_right').height()); }
                                $("#right_panel_refresh").html(obj.right_panel);
                            })
                   });
        }
        response['field'] = field;
        response['prior_text'] = prior_text;
        return response;
    }

            // Second, if the field name actually exists on the form
            // then "field" is not abbreviation so just update it
    else if ($("#"+field).length){
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +", "+text)) : $('#'+field).val(text);
        $('#'+field).css("background-color","#F0F8FF");
        response['field'] = field;
        response['prior_text'] = $('#'+field).val();
        el = document.getElementById(field);
        el.value = $('#'+field).val();
        ev = document.createEvent('Event');
        ev.initEvent('change', true, false);
        el.dispatchEvent(ev);

        return response;
    } else {
            //third, if the input fieldname is not on the form, is it a logical abbreviation?
            //if it points to one field specifically, create field2 variable and in the end, update field2
        if (field == 'CC') field2 = 'CC1';
        if (field == 'HPI') field2 = 'HPI1';
        if (field == 'RB' || field == 'RBROW')  field2 = "RBROW";
        if (field == 'LB' || field == 'LBROW')  field2 = "LBROW";
        if (field == 'RMC' || field == 'RMCT') field2 = "RMCT";
        if (field == 'LMC' || field == 'LMCT') field2 = "LMCT";
        if (field == 'RAD')     field2 = "RADNEXA";
        if (field == 'LAD')     field2 = "LADNEXA";
        if (field == 'RVF')     field2 = "RVFISSURE";
        if (field == 'LVF')     field2 = "LVFISSURE";
        if (field == 'RCAR')    field2 = "RCAROTID";
        if (field == 'LCAR')    field2 = "LCAROTID";
        if (field == 'RTA')     field2 = "RTEMPART";
        if (field == 'LTA')     field2 = "LTEMPART";
        if (field == 'RCN5')    field2 = "RCNV";
        if (field == 'LCN5')    field2 = "LCNVI";
        if (field == 'RCN7')    field2 = "RCMVII";
        if (field == 'LCN7')    field2 = "LCNVII";
        if (field == 'RH')      field2 = "ODHERTEL";
        if (field == 'LH')      field2 = "OLHERTEL";
        if (field == 'BHERT')   field2 = "HERTELBASE";
        if (field == 'EXTCOM')  field2 = 'EXT_COMMENTS';
        if (field == 'ECOM')    field2 = 'EXT_COMMENTS';
        if (field == 'RC')      field2 = "ODCONJ";
        if (field == 'LC')      field2 = "OSCONJ";
        if (field == 'RK')      field2 = "ODCORNEA";
        if (field == 'LK')      field2 = "OSCORNEA";
        if (field == 'RAC')     field2 = "ODAC";
        if (field == 'LAC')     field2 = "OSAC";
        if (field == 'RL')      field2 = "ODLENS";
        if (field == 'LL')      field2 = "OSLENS";
        if (field == 'RI')      field2 = "ODIRIS";
        if (field == 'LI')      field2 = "OSIRIS";
        if (field == 'RG')      field2 = "ODGONIO";
        if (field == 'LG')      field2 = "OSGONIO";
        if (field == 'RPACH')   field2 = "ODKTHICKNESS";
        if (field == 'LPACH')   field2 = "OSKTHICKNESS";
        if (field == 'RSCH1')   field2 = "ODSCHIRMER1";
        if (field == 'LSCH1')   field2 = "OSSCHIRMER1";
        if (field == 'RSCH2')   field2 = "ODSCHIRMER2";
        if (field == 'LSCH2')   field2 = "OSSCHIRMER2";
        if (field == 'RTBUT')   field2 = "ODTBUT";
        if (field == 'LTBUT')   field2 = "OSTBUT";
        if (field == 'ASCOM')   field2 = 'ANTSEG_COMMENTS';
        if (field == 'ACOM')   field2 = 'ANTSEG_COMMENTS';
        if (field == 'RD' || field =='RDISC')      field2 = "ODDISC";
        if (field == 'LD' || field =='LDISC')      field2 = "OSDISC";
        if (field == 'RCUP' || field =='RCUP')     field2 = "ODCUP";
        if (field == 'LCUP' || field =='LCUP')     field2 = "OSCUP";
        if (field == 'RMAC' || field == 'RMACULA')    field2 = "ODMACULA";
        if (field == 'LMAC' || field == 'LMACULA')    field2 = "OSMACULA";
        if (field == 'RV')      field2 = "ODVESSELS";
        if (field == 'LV')      field2 = "OSVESSELS";
        if (field == 'RVIT')      field2 = "ODVITREOUS";
        if (field == 'LVIT')      field2 = "OSVITREOUS";
        if (field == 'RP')      field2 = "ODPERIPH";
        if (field == 'LP')      field2 = "OSPERIPH";
        if (field == 'RCMT')    field2 = "ODCMT";
        if (field == 'LCMT')    field2 = "OSCMT";
        if (field == 'RCOM')    field2 = 'RETINA_COMMENTS';
        if ((field == 'RCOL')||(field =='RCOLOR')) field2 = 'ODCOLOR';
        if ((field == 'LCOL')||(field =='LCOLOR')) field2 = 'OSCOLOR';
        if ((field == 'RCOIN')||(field =='RCOINS')) field2 = 'ODCOINS';
        if ((field == 'LCOIN')||(field =='LCOINS')) field2 = 'OSCOINS';
        if (field == 'RRED')    field2 = 'ODREDDESAT';
        if (field == 'LRED')    field2 = 'OSREDDESAT';
        if (field == 'RNPC')    field2 = 'ODNPC';
        if (field == 'LNPC')    field2 = 'OSNPC';
        if (field == 'RNPA')    field2 = 'ODNPA';
        if (field == 'LNPA')    field2 = 'OSNPA';
        if (field == 'STEREO')  field2 = 'STEREOPSIS';
        if (field == 'VERTFUS') field2 = 'VERTFUSAMPS';
        if (field == 'CAD')     field2 = 'CACCDIST';
        if (field == 'CAN')     field2 = 'CACCNEAR';
        if (field == 'DAD')     field2 = 'DACCDIST';
        if (field == 'DAN')     field2 = 'DACCNEAR';
        if (field == 'NCOM')    field2 = 'NEURO_COMMENTS';
        if (field == 'IMPPLAN') field2 = 'IMP';
        
        if (field2 > '') {
            (appendix == ".a") ? ($('#'+field2).val($('#'+field2).val() +", "+text)) : $('#'+field2).val(text);
            $('#'+field2).css("background-color","#F0F8FF");
            response['field'] = field2;
            response['prior_text'] = $('#'+field2).val();
            return response;
        }
        
        if (field == 'HERT') {
            $('#ODHERTEL').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[1]).css("background-color","#F0F8FF");
            $('#OSHERTEL').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[3]).css("background-color","#F0F8FF");
            $('#HERTELBASE').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[2]).css("background-color","#F0F8FF");
            response['field'] = field;
            response['prior_text'] = text;
            return response;
        } else if ((field == 'BLF')||(field == 'LF')) {
            field = "RLF";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LLF";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RLF').css("background-color","#F0F8FF");
            $('#LLF').css("background-color","#F0F8FF");
            response['field'] = 'BLF';
            response['prior_text'] = $('#'+field).val();
            return response;
        } else if ((field == 'BMRD')||(field=="MRD")) {
            field = "RMRD";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LMRD";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RMRD').css("background-color","#F0F8FF");
            $('#LMRD').css("background-color","#F0F8FF");
            response['field'] = "MRD";
            response['prior_text'] = $('#LMRD').val();
            return response;
        } else if (field == 'BVF') {
            field = "RVFISSURE";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LVFISSURE";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RVFISSURE').css("background-color","#F0F8FF");
            $('#LVFISSURE').css("background-color","#F0F8FF");
            response['field'] = "BVF";
            response['prior_text'] = $('#LVFISSURE').val();
            return response;
        } else if ((field == 'BCAR')||(field == 'CAR')) {
            field = "RCAROTID";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LCAROTID";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RCAROTID').css("background-color","#F0F8FF");
            $('#LCAROTID').css("background-color","#F0F8FF");
            response['field'] = 'CAR';
            response['prior_text'] = $('#LCAROTID').val();
            return response;
        } else if ((field == 'BTA')||(field == 'TA')) {
            field = "RTEMPART";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LTEMPART";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RTEMPART').css("background-color","#F0F8FF");
            $('#LTEMPART').css("background-color","#F0F8FF");
            response['field'] = 'TA';
            response['prior_text'] = $('#LTEMPART').val();
            return response;
        } else if ((field == 'BCNV') || (field == 'BCN5')||(field == 'CNV')||(field=='CN5')) {
            field = "RCNV";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LCNV";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RCNV').css("background-color","#F0F8FF");
            $('#LCNV').css("background-color","#F0F8FF");
            response['field'] = 'BCNV';
            response['prior_text'] = $('#LCNV').val();
            return response;
        } else if ((field == 'BCNVII') || (field == 'BCNVII')||(field == 'CNVII')||(field == 'CN7')) {
            field = "RCNV";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LCNV";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RCNVII').css("background-color","#F0F8FF");
            $('#LCNVII').css("background-color","#F0F8FF");
            response['field'] = 'CN7';
            response['prior_text'] = $('#LCNVII').val();
            return response;
        } else if ((field == 'BLL')||(field=='LL')) {
            field = "RLL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LLL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RLL').css("background-color","#F0F8FF");
            $('#LLL').css("background-color","#F0F8FF");
            response['field'] = 'BLL';
            response['prior_text'] = text;
            return response;
        } else if ((field == '4XL')||(field == 'Lx4')) {
            field = "RLL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "RUL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LUL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LLL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RLL').css("background-color","#F0F8FF");
            $('#LLL').css("background-color","#F0F8FF");
            $('#RUL').css("background-color","#F0F8FF");
            $('#LUL').css("background-color","#F0F8FF");
            response['field'] = '4XL';
            response['prior_text'] = text;
            return response;
        } else if ((field == 'BUL')||(field=='UL')) {
            field = "RUL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LUL";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RUL').css("background-color","#F0F8FF");
            $('#LUL').css("background-color","#F0F8FF");
            response['field'] = 'BUL';
            response['prior_text'] = $('#LUL').val();
            return response;
        } else if (field == 'BAD') {
            field = "RAD";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LAD";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RAD').css("background-color","#F0F8FF");
            $('#LAD').css("background-color","#F0F8FF");
            response['field'] = 'BAD';
            response['prior_text'] = $('#BAD').val();
            return response;
        } else if ((field == 'FH')||(field == "BB")) {
            field = "RBROW";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "LBROW";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#RBROW').val(text).css("background-color","#F0F8FF");
            $('#LBROW').val(text).css("background-color","#F0F8FF");
            response['field'] = 'BB';
            response['prior_text'] = $('#RBROW').val();
            return response;
        } else if ((field == 'BC')||(field=='C')) {
            field = "ODCONJ";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSCONJ";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ORCONJ').css("background-color","#F0F8FF");
            $('#OSCONJ').css("background-color","#F0F8FF");
            response['field'] = 'C';
            response['prior_text'] = $('#OSCONJ').val();
            return response;
        } else if ((field == 'BK')||(field=='K')) {
            field = "ODCORNEA";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSCORNEA";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODCORNEA').css("background-color","#F0F8FF");
            $('#OSCORNEA').css("background-color","#F0F8FF");
            response['field'] = 'K';
            response['prior_text'] = $('#OSCORNEA').val();
            return response;
        } else if ((field == 'BAC')||(field=='AC')) {
            field = "ODAC";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSAC";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODAC').css("background-color","#F0F8FF");
            $('#OSAC').css("background-color","#F0F8FF");
            response['field'] = 'AC';
            response['prior_text'] = $('#OSAC').val();
            return response;
        } else if ((field == 'BL')||(field=='L')) {
            field = "ODLENS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSLENS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODLENS').css("background-color","#F0F8FF");
            $('#OSLENS').css("background-color","#F0F8FF");
            response['field'] = 'BL';
            response['prior_text'] = $('#OSLENS').val();
            return response;
        } else if ((field == 'BI')||(field=='I')) {
            field = "ODIRIS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSIRIS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODIRIS').css("background-color","#F0F8FF");
            $('#OSIRIS').css("background-color","#F0F8FF");
            response['field'] = 'BI';
            response['prior_text'] = $('#OSIRIS').val();
            return response;
        } else if ((field == 'BPACH')||(field=='PACH')) {
            field = "ODKTHICKNESS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSKTHICKNESS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODKTHICKNESS').css("background-color","#F0F8FF");
            $('#OSKTHICKNESS').css("background-color","#F0F8FF");
            response['field'] = 'PACH';
            response['prior_text'] = $('#OSKTHICKNESS').val();
            return response;
        } else if ((field == 'BG')||(field=='G')||(field=="GONIO")) {
            field = "ODGONIO";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSGONIO";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODGONIO').css("background-color","#F0F8FF");
            $('#OSGONIO').css("background-color","#F0F8FF");
            response['field'] = 'GONIO';
            response['prior_text'] = $('#OSGONIO').val();
            return response;
        } else if ((field == 'BTBUT')||(field=='TBUT')) {
            field = "ODTBUT";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSTBUT";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODTBUT').css("background-color","#F0F8FF");
            $('#OSTBUT').css("background-color","#F0F8FF");
            response['field'] = 'TBUT';
            response['prior_text'] = $('#OSTBUT').val();
            return response;
        } else if ((field == 'BD')||(field == 'BDISC')||(field == 'BDISCS')) {
            field = "ODDISC";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSDISC";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODDISC').css("background-color","#F0F8FF");
            $('#OSDISC').css("background-color","#F0F8FF");
            response['field'] = 'BD';
            response['prior_text'] = $('#OSDISC').val();
            return response;
        } else if ((field == 'BC')||(field == 'C')||(field == 'BCUP')||(field == 'BCUPS')) {
            field = "ODCUP";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSCUP";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODCUP').css("background-color","#F0F8FF");
            $('#OSCUP').css("background-color","#F0F8FF");
            response['field'] = 'C';
            response['prior_text'] = $('#OSCUP').val();
            return response;
        } else if ((field == 'BMAC')||(field == 'MAC')||(field=='BM')) {
            field = "ODMACULA";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSMACULA";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODMACULA').css("background-color","#F0F8FF");
            $('#OSMACULA').css("background-color","#F0F8FF");
            response['field'] = 'BM';
            response['prior_text'] = $('#OSMACULA').val();
            return response;
        } else if ((field == 'BV')||(field == 'V')) {
            field = "ODVESSELS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSVESSELS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODVESSELS').css("background-color","#F0F8FF");
            $('#OSVESSELS').css("background-color","#F0F8FF");
            response['field'] = 'V';
            response['prior_text'] = $('#OSVESSELS').val();
            return response;
        } else if ((field == 'BVIT')||(field == 'VIT')) {
            field = "ODVITREOUS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSVITREOUS";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODVITREOUS').css("background-color","#F0F8FF");
            $('#OSVITREOUS').css("background-color","#F0F8FF");
            response['field'] = 'V';
            response['prior_text'] = $('#OSVITREOUS').val();
            return response;
        } else if ((field == 'BP')||(field == 'P')) {
            field = "ODPERIPH";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSPERIPH";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODPERIPH').css("background-color","#F0F8FF");
            $('#OSPERIPH').css("background-color","#F0F8FF");
            response['field'] = 'P';
            response['prior_text'] = $('#OSPERIPH').val();
            return response;
        } else if ((field == 'BCMT')||(field == 'CMT')) {
            field = "ODCMT";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            field = "OSCMT";
            (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
            $('#ODCMT').css("background-color","#F0F8FF");
            $('#OSCMT').css("background-color","#F0F8FF");
            response['field'] = 'CMT';
            response['prior_text'] = $('#OSCMT').val();
            return response;
        } else if (field.match(/^(.CDIST|.CNEAR)/i)) {
            field = field.toUpperCase();
            if (field == 'SCDIST') $('#NEURO_ACT_zone').val('SCDIST').trigger('change');
            if (field == 'CCDIST') $('#NEURO_ACT_zone').val('CCDIST').trigger('change');
            if (field == 'SCNEAR') $('#NEURO_ACT_zone').val('SCNEAR').trigger('change');
            if (field == 'CCNEAR') $('#NEURO_ACT_zone').val('CCNEAR').trigger('change');
            response['field'] = field;
            response['prior_text'] = $('#'+field).val();
            return response;
        } else if (field.match(/^(\d{1,2})$/)) {
            var data = text.match(/(\d{0,2}||ortho)(.*)/i);
            var PD = data[1];
            $('#ACT').prop( "checked", false );
            zone = $("#NEURO_ACT_zone").val();
            if (PD >'') PD = PD + ' ';
            var strab = data[2].toUpperCase().replace (/I(.)/g,"$1(T)").replace(/\s*(\d)/,'\n$1');
            $('#ACT'+field+zone).val(PD+strab);
            $('#ACT'+field+zone).css("background-color","#F0F8FF");
            response['field'] = 'ACT'+field+zone;
            response['prior_text'] = PD+strab;
            return response;
        }
        //only way to get here is to NOT have matched a field!
        //set error variable and do it using the last field we found...
        response['field'] = 'error';
        response['prior_text'] = field;
        return response;
    }
}
