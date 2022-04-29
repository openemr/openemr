{if isset($hide) && $hide }
<tr class="hide">
{else}
<tr>
{/if}
    <td class="graph" id="{$input|attr}">{$title|xlt}</td>
    <td>{xlt t=$unit|xlt}</td>

    <td class='currentvalues p-2'>
        {if isset($vitalsStringFormat) }
        <input type="text" class="form-control" size='5' name='{$input|attr}' id='{$input|attr}_input'
               value="{if is_numeric($vitals->$vitalsValue()) }{$vitals->$vitalsValue()|string_format:$vitalsStringFormat|attr}{/if}"/>
        {else}
        <input type="text" class="form-control" size='5' name='{$input|attr}' id='{$input|attr}_input'
               value="{if is_numeric($vitals->$vitalsValue())}{$vitals->$vitalsValue()|attr}{/if}"/>
        {/if}

    </td>
    <td class="editonly">
        { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
    </td>

    { include file='vitals_historical_values.tpl' useMetric=false vitalsValue=$vitalsValue results=$results
        vitalsStringFormat=$vitalsStringFormat }
</tr>