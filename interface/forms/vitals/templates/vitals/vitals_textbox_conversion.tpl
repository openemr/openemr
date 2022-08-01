{** We hide the hidden input value so we can properly format our data values **}
<tr class="hide">
    <td>
        <input type="hidden" class="vitals-conv-unit-save-value" id='{$input|attr}_input' name="{$input|attr}"
               value="{if is_numeric($vitals->$vitalsValue()) }{$vitals->$vitalsValue()|attr}{/if}" />
    </td>
    {foreach item=result from=$results}
        <td class="historicalvalues"></td>
    {/foreach}
</tr>

<!-- USA row comes first -->
    {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY}
    <tr class="hide">
    {else}
    <tr>
    {/if}
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_METRIC}
        <td class="unfocus graph" id="{$input|attr}">
    {else}
        <td class="graph" id="{$input|attr}">
    {/if}
        {xlt t=$title} {if !empty($codes)}<small>({$codes|text})</small>{/if}
        </td>
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_METRIC}
        <td class="unfocus">
    {else}
        <td>
    {/if}
            {xlt t=$unit}
        </td>
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_METRIC}
        <td class="valuesunfocus">
    {else}
        <td class='currentvalues p-2'>
    {/if}
            <input type="text" class="form-control vitals-conv-unit skip-template-editor" size='5' id='{$input|attr}_input_usa'
                   value="{if is_numeric($vitals->$vitalsValue()) }{$vitals->$vitalsValue()|string_format:$vitalsStringFormat|attr}{/if}"
                   data-system="usa" data-unit="{$unit|attr}" data-target-input="{$input|attr}_input"
                   data-target-input-conv="{$input|attr}_input_metric"
                   title='{$vitalsValueUSAHelpTitle|default:''|xlt}'/>
        </td>
    <td class="editonly">
        { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
    </td>
    <td class="editonly actions">
        { include file='vitals_actions.tpl' }
    </td>
    { include file='vitals_historical_values.tpl' useMetric=false vitalsValue=$vitalsValue vitalsValueMetric=$vitalsValueMetric
            results=$results }
    </tr>

<!-- Metric row comes second -->
{if $units_of_measurement == $MEASUREMENT_USA_ONLY}
    <tr class="hide">
{else}
    <tr>
{/if}
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_USA}
        <td class="unfocus graph" id="{$input|attr}_metric">
    {else}
        <td class="graph" id="{$input|attr}_metric">
    {/if}
        {xlt t=$title} {if !empty($codes)}<small>({$codes|text})</small>{/if}
        </td>
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_USA}
        <td class="unfocus">
    {else}
        <td>
    {/if}
            {xlt t=$unitMetric}
    </td>
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_USA}
        <td class="valuesunfocus">
    {else}
        <td class='currentvalues p-2'>
    {/if}
            <!-- Note we intentionally use vitalsValue not vitalValuesMetric because of how data is stored internally -->
            <input type="text" class="form-control vitals-conv-unit skip-template-editor" size='5' id='{$input|attr}_input_metric'
                   value="{if is_numeric($vitals->$vitalsValue()) }{$vitals->$vitalsValueMetric()|string_format:$vitalsStringFormat|attr}{/if}"
                   data-system="metric" data-unit="{$unit|attr}" data-target-input="{$input|attr}_input"
                   data-target-input-conv="{$input|attr}_input_usa" />
        </td>
        <td class="editonly">
            {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY }
                <!-- we only show the selector if this the only row being displayed -->
                { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
            {/if}
        </td>
        <td class="editonly actions">
            {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY }
                { include file='vitals_actions.tpl' }
            {/if}
        </td>
        { include file='vitals_historical_values.tpl' useMetric=true vitalsValue=$vitalsValue vitalsValueMetric=$vitalsValueMetric
        results=$results }
    </tr>

{ include file='vitals_reason_row.tpl' input=$input title=$title vitalDetails=$vitals->get_details_for_column($input) }