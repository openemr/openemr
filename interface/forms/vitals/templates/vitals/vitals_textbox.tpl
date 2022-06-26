{if isset($hide) && $hide }
<tr class="hide">
{else}
<tr>
{/if}
    <td class="graph" id="{$input|attr}">{xlt t=$title} {if !empty($codes)}<small>({$codes|text})</small>{/if}</td>
    <td>{xlt t=$unit|xlt}</td>

    <td class='currentvalues p-2'>
        {if isset($vitalsStringFormat) }
        <input type="text" class="form-control skip-template-editor" size='5' name='{$input|attr}' id='{$input|attr}_input'
               value="{if is_numeric($vitals->$vitalsValue()) }{$vitals->$vitalsValue()|string_format:$vitalsStringFormat|attr}{/if}"/>
        {else}
        <input type="text" class="form-control skip-template-editor" size='5' name='{$input|attr}' id='{$input|attr}_input'
               value="{if is_numeric($vitals->$vitalsValue())}{$vitals->$vitalsValue()|attr}{/if}"/>
        {/if}

    </td>
    <td class="editonly">
        { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
    </td>
    <td class="editonly actions">
        { include file='vitals_actions.tpl' }
    </td>

    { include file='vitals_historical_values.tpl' useMetric=false vitalsValue=$vitalsValue results=$results
        vitalsStringFormat=$vitalsStringFormat }
</tr>
{ include file='vitals_reason_row.tpl' input=$input title=$title vitalDetails=$vitals->get_details_for_column($input)  }