<?php

/**
 * knockoutjs template for rendering the interface for justifying procedures
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<script type="text/html" id="justify-display">
    <div data-bind="visible: $data.show">
        <div class="duplicate_warning" data-bind="visible: $data.duplicates().length>0">
            <div data-bind="event:{click: toggle_warning_details}" class="problem_warning" title="<?php echo xla('Click for more details') ?>"><?php echo xlt("Warning, patient has ambiguous codes in the problems list!") ?></div>
            <div class="warning_details" data-bind="visible: show_warning_details">
                <?php echo xlt("The following problems have the same diagnosis codes. Encounter issues will not be updated. Please use the encounter interface instead.") ?>
                <span data-bind="foreach: $data.duplicates">
                    <div data-bind="text: description() +':'+ code_type() + '|' + code()"></div>
                </span>
            </div>
        </div>
        <span>
            <span data-bind="visible: diag_code_types.length>0">
                <input value= type="text" class="form-control" data-bind="value: search_query, valueUpdate: 'afterkeydown', event:{focus:search_focus, blur: search_blur, keydown:search_key}, hasfocus: search_has_focus" />
                <span class="search_results" data-bind="visible: (search_results().length>0) && search_show">
                    <table>
                        <tbody data-bind="foreach: $data.search_results">
                            <tr>
                                <td class="search_result_code" data-bind="text:description, event:{click: function(data,event){return choose_search_diag(data,event,$parent)}}"></td>
                                <td data-bind="text:code, attr:{title:code_type}"></td>
                            </tr>
                        </tbody>
                    </table>
                </span>
                <select class="form-control" data-bind="value: searchType, options: diag_code_types, optionsText: 'key'"></select>
            </span>
        </span>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th class='sort' data-bind="event: {click: sort_justify}" title="<?php echo xla('Click to sort') ?>">#</th>
                    <th title="<?php echo xla('Justification Entries') ?>"><?php echo xlt("J{{Justify Header}}");?></th>
                    <th title="<?php echo xla('Problem')?>"><?php echo xlt("P{{Justify Header}}");?></th>
                </tr>
            </thead>
            <tbody data-bind="foreach: $data.diagnosis_options">
                <tr data-bind="attr:{class: source, encounter_issue: encounter_issue}">
                    <td class="priority" data-bind="text: priority()!=99999 ? priority() : ''"/></td>
                    <td class=""><input type="checkbox" data-bind="checked: selected, event:{click: function(data,event){return check_justify(data,event,$parent);}}" /></td>
                    <td class="problem_info"><input type="checkbox" data-bind="visible: $data.prob_id()==null && $data.allowed_to_create_problem_from_diagnosis()=='TRUE', checked: create_problem" title="<?php echo xla('Check to create problem from this diagnosis');?>"/></td>
                    <td class="info" data-bind="text: code, attr:{title:code_type}"></td>
                    <td class="info">
                        <span data-bind="text: description"></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="btn-group">
            <input type="button" class="btn btn-primary" data-bind="click: update_justify" value="<?php echo xlt("Update");?>"/>
            <input class="cancel_dialog btn btn-primary" type="button" data-bind="click: cancel_justify" value="<?php echo xla("Cancel");?>"/>
        </div>
    </div>
</script>
