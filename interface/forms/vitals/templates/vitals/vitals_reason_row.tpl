<!-- Note if you change this id you need to change the vitals_actions.tpl and vitals.js to match -->
<tr id="{$input|attr}_reason_code" class="reasonCodeContainer {if !($vitals->has_reason_for_column($input))}d-none{/if}">
    <td colspan="5" class="border-top-0">
        <div class="card mt-2 mb-4">
            <div class="card-header">
                {xlt t=$title} {xlt t="Reason Information"}
            </div>
            <div class="card-body">
                <div class="row">
                    <p class="col">
                        {xlt t="When recording a reason for the value (or absence of a value) of an observation both the reason code and status of the reason are required"}
                    </p>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{xlt t="Reason Code"}</label>
                        {if isset($vitalDetails) && !empty($vitalDetails->get_reason_code())}
                        <input class="code-selector-popup form-control" placeholder="{xlt t="Select a reason code"}"
                               name="reasonCode[{$input|attr}]" type="text" value="{$vitalDetails->get_reason_code()|attr}" />
                        {else}
                        <input class="code-selector-popup form-control" placeholder="{xlt t="Select a reason code"}"
                               name="reasonCode[{$input|attr}]" type="text" value="" />
                        {/if}

                        {if isset($vitalDetails) && !empty($vitalDetails->get_reason_description())}
                            <p class="code-selector-text-display">{$vitalDetails->get_reason_description()|text}</p>
                            <input type="hidden" name="reasonCodeText[{$input|attr}]" class="code-selector-text" value="{$vitalDetails->get_reason_description()|attr}" />
                        {else}
                            <p class="code-selector-text-display d-none"></p>
                            <input type="hidden" name="reasonCodeText[{$input|attr}]" class="code-selector-text" value="" />
                        {/if}
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{xlt t="Reason Status"}</label>
                        <select name="reasonCodeStatus[{$input|attr}]" class="form-control">
                            {foreach item=codeDesc from=$reasonCodeStatii}
                                <option value="{$codeDesc.code|attr}"
                                    {if isset($vitalDetails) && $vitalDetails->get_reason_status() == $codeDesc.code}
                                        selected
                                    {/if}
                                >{$codeDesc.description|text}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </td>

    {foreach item=result from=$results}
        <td class="historicalvalues"></td>
    {/foreach}
</tr>