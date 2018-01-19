/**
 * knockout.js view model for fee sheet justification
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
function start_edit(data,event)
{
    data.edit_mode(true);
    var elem=$(event.target).siblings("input").get(0);
}
function end_edit(data,event)
{
    data.edit_mode(false);
}
function edit_key(data,event)
{
    if(event.keyCode==13)
        {
            data.edit_mode(false);
            return false;
        }
   return true;
}
function search_key(data,event)
{
    if(event.keyCode==13)
        {
            $(event.target).siblings(".search_results").find(".search_result_code:first").click();
            return false;
        }
    return true;
}

function justify_entry(json_object)
{
    var retval=new code_entry(json_object);
    retval.encounter_issue=ko.observable(false);
    retval.edit_mode=ko.observable(false);
    retval.prob_id=ko.observable();
    retval.allowed_to_create_problem_from_diagnosis=ko.observable();
    retval.create_problem=ko.observable(false);
    retval.jsonify=function()
    {
        var json={};
        json.code=this.code();
        json.code_type=this.code_type();
        json.description=this.description();
        json.allowed_to_create_problem_from_diagnosis=this.allowed_to_create_problem_from_diagnosis();
        json.prob_id=this.prob_id();
        json.create_problem=this.create_problem();
        return json;
    };
    return retval;
}

function choose_search_diag(data,event,parent)
{
    var code_key=data.code_type+"|"+data.code;
    var existing=parent.added_keys[code_key];
    if(typeof existing!='undefined')
    {
        parent.search_has_focus(false);
        if(!existing.selected())
        {
             existing.selected(true);
             update_diagnosis_options(parent.diagnosis_options,existing);
        }
    }
    else
    {
        var new_justify=justify_entry({code: data.code, code_type:data.code_type, description:data.description, selected:true});
        new_justify.source='search';
        new_justify.source_idx=99999;
        new_justify.create_problem(data.allowed_to_create_problem_from_diagnosis);
        new_justify.allowed_to_create_problem_from_diagnosis(data.allowed_to_create_problem_from_diagnosis);
        parent.diagnosis_options.push(new_justify);
        parent.added_keys[code_key]=new_justify;
        update_diagnosis_options(parent.diagnosis_options,new_justify);
        parent.search_has_focus(false);
        parent.diagnosis_options.sort(priority_order);
    }
    parent.search_query("");
    return true;
}

function search_change(data,model)
{
    var search_query=data;
    model.search_show(true);
    var search_type= model.searchType().key;
    var search_type_id=model.searchType().id;
    if(search_query.length>0)
        {
            $.post(ajax_fee_sheet_search,
                    {
                        search_query:search_query,
                        search_type: search_type,
                        search_type_id: search_type_id
                    },
                    function(result)
                    {
                        model.search_results.removeAll();
                        if(result.codes!=null)
                            {
                                for(var idx=0;idx<result.codes.length;idx++)
                                    {
                                        var cur_code=result.codes[idx];
                                        model.search_results.push(cur_code);
                                    }                                
                            }
                    },
                    "json");                
        }
        else
        {
            model.search_results.removeAll();
        }
   return true;
}
function search_focus(data,event)
{
    data.search_show(true);
}
function search_blur(data,event)
{
    data.search_show(false);
}
function update_justify(data,event)
{
    event.preventDefault();
    data.diagnosis_options.sort(priority_order);
    var justify=[];
    for(var idx=0;idx<data.diagnosis_options().length;idx++)
    {
        var cur=data.diagnosis_options()[idx];
        if(cur.selected())
        {
            justify.push(cur.jsonify());
        }
    }
    var skip_issues=data.duplicates().length>0;
    top.restoreSession();
    $.post(justify_ajax,{
        skip_issues: skip_issues,
        pid: data.patient_id,
        encounter: data.encounter_id,
        task: 'update',
        billing_id: data.billing_id,
        diags: JSON.stringify(justify)
    },
    function(data)
        {
            refresh_codes();
        }

    ); 
    data.show(false);
}

function cancel_justify(data,event)
{
    event.preventDefault();
    data.show(false);
}
function sort_justify(data,event)
{
    data.diagnosis_options.sort(priority_order);
}

var source_order={current:1,patient:2,common:3,search:4}
function priority_order(left,right)
{
    if(left.priority()>right.priority())
    {
        return 1;
    }
    if(left.priority()<right.priority())
    {
        return -1;
    }   
    else
    {
        if(left.source==right.source)
        {
            if(left.source=='patient')
            {
                if(left.encounter_issue()!=right.encounter_issue())
                {
                    return left.encounter_issue() ? -1 : 1;
                }
            }
            if(left.source_idx>right.source_idx)
            {
                return 1;
            }
            else
            {
                return -1;
            }
        }
        else
        {
            if(source_order[left.source]<source_order[right.source])
            {
                return -1;
            }
            return 1;
        }
    }
}
function update_diagnosis_options(diagnosis_options,data)
{
    var chosen=0;
    for(var idx=0;idx<diagnosis_options().length;idx++)
    {
        var cur=diagnosis_options()[idx];
        if(cur.selected())
        {
            chosen++;
        }
    }
    var old_priority=99999;
    if(data.selected())
    {
        data.priority(chosen);
    }
    else
    {
        old_priority=data.priority();
        data.priority(99999);
    }
//    diagnosis_options.sort(priority_order);
    if(!data.selected())
    {
        for(idx=0;idx<diagnosis_options().length;idx++)
            {
                cur=diagnosis_options()[idx];
                if((cur.priority()>old_priority) && cur.priority()!=99999)
                {
                    cur.priority(cur.priority()-1);
                }
            }
    }
}
function check_justify(data,event,model)
{
    
    update_diagnosis_options(model.diagnosis_options,data);
    return true;
}
function lookup_justify(current_justifications,entry)
{
    for(var idx=0;idx<current_justifications.length;idx++)
        {
            var cur=current_justifications[idx];
            if((cur.code()==entry.code())&&(cur.code_type()==entry.code_type()))
                {
                    entry.priority(cur.priority());
                    entry.selected(true);
                }
        }
}
function setup_justify(model,current,patient,common)
{
    model.added_keys={};
    for(var idx=0;idx<current.length;idx++)
    {
        var cur_entry=current[idx];
        var new_justify=new justify_entry(cur_entry);
        if(typeof model.added_keys[new_justify.key()]=='undefined')
        {
            model.added_keys[new_justify.key()]=new_justify;
            new_justify.selected(false);
            lookup_justify(model.current_justify(),new_justify);
            //new_justify.priority(idx+1);
            new_justify.source='current';
            new_justify.source_idx=idx;
            new_justify.allowed_to_create_problem_from_diagnosis(cur_entry.allowed_to_create_problem_from_diagnosis);
            model.diagnosis_options.push(new_justify);
            
        }
    }
    for(idx=0;idx<patient.length;idx++)
    {
        cur_entry=patient[idx];
        if((cur_entry.code!=null) || cur_entry.code_type!="")
        {         
            new_justify=new justify_entry(cur_entry);
            if(typeof model.added_keys[new_justify.key()]=='undefined')
            {
                model.added_keys[new_justify.key()]=new_justify;
                if(new_justify.selected())
                {
                    new_justify.encounter_issue(true);
                }
                new_justify.selected(false);
                lookup_justify(model.current_justify(),new_justify);
                new_justify.source='patient';
                new_justify.source_idx=idx;
                new_justify.prob_id(cur_entry.db_id);
                new_justify.allowed_to_create_problem_from_diagnosis(cur_entry.allowed_to_create_problem_from_diagnosis);
                model.diagnosis_options.push(new_justify);        
            }
            else
            {
                var entry=model.added_keys[new_justify.key()];
                if((entry.prob_id()!=null) &&(entry.prob_id()!=cur_entry.db_id))
                {
                    new_justify.prob_id(cur_entry.db_id);
                    new_justify.allowed_to_create_problem_from_diagnosis(cur_entry.allowed_to_create_problem_from_diagnosis);
                    if(model.duplicates().length==0)
                        {
                            model.duplicates.push(entry);
                        }
                    model.duplicates.push(new_justify);
                }
                else
                {
                    entry.prob_id(cur_entry.db_id);
                    entry.allowed_to_create_problem_from_diagnosis(cur_entry.allowed_to_create_problem_from_diagnosis);
                    entry.description(cur_entry.description);
                    if(cur_entry.selected)
                    {
                        entry.encounter_issue(true);
                    }
                }
            }
        }
    }
    for(idx=0;idx<common.length;idx++)
    {
        cur_entry=common[idx];
        new_justify=new justify_entry(cur_entry);
        if(typeof model.added_keys[new_justify.key()]=='undefined')
        {
            model.added_keys[new_justify.key()]=new_justify;
            new_justify.selected(false);
            lookup_justify(model.current_justify(),new_justify);
            new_justify.source='common';
            new_justify.source_idx=idx;
            new_justify.allowed_to_create_problem_from_diagnosis(cur_entry.allowed_to_create_problem_from_diagnosis);
            model.diagnosis_options.push(new_justify);        
        }
    }
    model.diagnosis_options.sort(priority_order);
    
}
function toggle_warning_details(data,event)
{
    data.show_warning_details(!data.show_warning_details());
}
function fee_sheet_justify_view_model(billing_id,enc_id,pat_id,current_justify)
{
    this.justify={
                    billing_id:billing_id
                    ,encounter_id: enc_id
                    ,patient_id: pat_id
                    ,diagnosis_options: ko.observableArray()
                    ,current: ko.observableArray()
                    ,patient: ko.observableArray()
                    ,common: ko.observableArray()
                    ,show: ko.observable(false)
                    ,current_justify: ko.observable(current_justify)
                    ,search_results: ko.observableArray()
                    ,search_show: ko.observable(false).extend({throttle:300})
                    ,search_has_focus: ko.observable(false)
                    ,added_keys: {}
                    ,search_query: ko.observable()
                    ,diag_code_types: diag_code_types
                    ,searchType: ko.observable()
                    ,duplicates:ko.observableArray()
                    ,show_warning_details:ko.observable(false)
                  };
    var vm=this.justify;
    vm.search_query_throttled=ko.computed(vm.search_query).extend({throttle:300}).subscribe(function(data){search_change(data,vm)});
    var mode='common';   
    $.post(justify_ajax,{
            pid: pat_id,
            encounter: enc_id,
            mode: mode,
                task: "retrieve"
            },function(data){
                setup_justify(vm,data.current,data.patient,data.common);
                
                vm.show(true);
                vm.search_has_focus(true);
            },
            "json");
    return this;
}
