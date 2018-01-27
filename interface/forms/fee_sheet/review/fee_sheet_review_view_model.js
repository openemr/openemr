/**
 * knockout.js view model for review of previous fee sheets
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
function code_entry(json_source)
{
    this.code=ko.observable(json_source.code);
    this.code_type=ko.observable(json_source.code_type);
    this.description=ko.observable(json_source.description);
    this.selected=ko.observable(json_source.selected);
    this.priority=ko.observable(99999);
    this.jsonify=function()
    {
        var retval={};
        retval.code=this.code();
        retval.code_type=this.code_type();
        retval.description=this.description();
        return retval;
    }
    this.key=function()
    {
        return this.code_type()+"|"+ this.code();
    }
    return this;
}

function procedure(json_source)
{
    var retval=new code_entry(json_source);
    retval.fee=ko.observable(json_source.fee);
    retval.modifiers=ko.observable(json_source.modifiers);
    retval.units=ko.observable(json_source.units);
    retval.mod_size=ko.observable(json_source.mod_size);
    retval.justify=ko.observableArray();
    if (json_source.justify !== null) {
        var justify_codes = json_source.justify.split(":");
        for (var idx = 0; idx < justify_codes.length; idx++) {
            var justify_parse = justify_codes[idx].split("|");
            if (justify_parse.length == 2) {
                var new_code = {};
                new_code.code_type = justify_parse[0];
                new_code.code = justify_parse[1];
                new_code.descriptions = "";
                new_code.selected = true;
                var ko_code = new code_entry(new_code)
                ko_code.priority = idx + 1;
                retval.justify.push(ko_code);
            }
        }
    }
    retval.genJustify=function()
    {
        var justify_string="";
        for(var idx=0;idx<this.justify().length;idx++)
        {
            var cur_justify=this.justify()[idx];
            if(cur_justify.selected())
            {
                justify_string+=cur_justify.code_type() +"|"+cur_justify.code()+":";
            }
        }
        return justify_string;
    }

    retval.procedure_choices=ko.observableArray();
    retval.procedure_choices.push(new fee_sheet_option(retval.code(), retval.code_type(),retval.description(),retval.fee()));
    for(idx=0;idx<fee_sheet_options.length;idx++)
    {
        retval.procedure_choices.push(fee_sheet_options[idx]);
    }
    retval.procedure_choice=ko.observable(retval.procedure_choices[0]);
    retval.change_procedure=function(data,event)
    {
        data.description(data.procedure_choice().description);
        data.code(data.procedure_choice().code);
        data.code_type(data.procedure_choice().code_type);
        data.fee(data.procedure_choice().fee);
        
    }
    retval.jsonify=function()
    {
        var json_return={};
        json_return.code=this.code();
        json_return.code_type=this.code_type();
        json_return.description=this.description();
        json_return.fee=this.fee();
        json_return.modifiers=this.modifiers();
        json_return.units=this.units();
        json_return.justify=this.genJustify();
        return json_return;
    }
    return retval;
}
// This function takes json objects for procedures and maps them to the knockoutjs model with observables
function map_procedures(json_objects)
{
    var retval=[];
    for(var idx=0;idx<json_objects.length;idx++)
    {
        retval.push(procedure(json_objects[idx]));
    }
    return retval;
}



// This function takes json objects and maps them to the knockoutjs model with observables
function map_code_entries(json_objects)
{
    var retval=[];
    for(idx=0;idx<json_objects.length;idx++)
    {
        retval.push(new code_entry(json_objects[idx]));
    }
    return retval;
}

function request_encounter_data(model_data,mode,prev_encounter)
{
    var request={
            pid: pid,
            encounter: enc,
            mode: mode,
            task: "retrieve"
            };
    if(prev_encounter!=null)
    {
        request.prev_encounter=prev_encounter;
    }
    $.post(review_ajax,request,function(result){

                model_data.prev_encounter(null)
                if(typeof result.encounters!='undefined')
                    {
                        model_data.encounters(result.encounters);                
                        for(idx=0;idx<model_data.encounters().length;idx++)
                        {
                            if(model_data.encounters()[idx].id==result.prev_encounter)
                                {
                                    model_data.selectedEncounter(model_data.encounters()[idx]);                
                                }
                        }
                    }
                    else
                        {
                            model_data.encounters([]);
                        }
                model_data.prev_encounter(result.prev_encounter)
                if(typeof result.procedures!='undefined')
                    {
                        model_data.procedures(map_procedures(result.procedures));                
                    }
                    else
                        {
                            model_data.procedures([]);
                        }
                
                model_data.issues(map_code_entries(result.issues));
                model_data.show(true);
            },"json");
}

function review_event(data,event)
{
    event.preventDefault();
    $(".cancel_dialog").click();
    request_encounter_data(data.review,data.review.mode,null);
}

function cancel_review(data,event)
{
    event.preventDefault();
    data.show(false);
    
}

function choose_encounter(data,event)
{
    if(data.prev_encounter()!=null)
        {
            if(data.selectedEncounter().id!=data.prev_encounter())
            {
                request_encounter_data(data,"encounters",data.selectedEncounter().id)
            }
        }
}
function fee_sheet_review_view_model()
{
    this.review= {name: 'Hello' 
                  ,mode: "encounters"
                  ,show: ko.observable(false)
                  ,prev_encounter: ko.observable()
                  ,encounters: ko.observableArray()
                  ,procedures: ko.observableArray()
                  ,issues: ko.observableArray()
                  ,selectedEncounter: ko.observable()
                 };
    this.justify= {};
    this.procedure_options={
        current_procedure: ko.observable()
        ,fee_sheet_options: ko.observableArray()
    }
    
    this.cancel_review= cancel_review;
    this.review_event= review_event;
    this.choose_encounter = choose_encounter;
}
function add_review(data,event)
{
    var diag_list=[];
    for(var idx=0;idx<data.issues().length;idx++)
    {
        var cur_diag=data.issues()[idx];
        if(cur_diag.selected())
        {
            diag_list.push(cur_diag.jsonify());
        }
    }
    
    var proc_list=[];
    for(idx=0;idx<data.procedures().length;idx++)
    {
        var cur_proc=data.procedures()[idx];
        if(cur_proc.selected())
        {
            proc_list.push(cur_proc.jsonify());
        }
    }
    top.restoreSession();
    $.post(review_ajax,{
        pid: pid,
        encounter: enc,
        task: 'add_diags',
        diags: JSON.stringify(diag_list),
        procs: JSON.stringify(proc_list)
    },
    function(data)
        {
            refresh_codes();
        }

    );

    data.show(false);
}