<!-- USA row comes first -->
    {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY}
    <tr class="hide">
    {else}
    <tr>
    {/if}
    {if $units_of_measurement == $MEASUREMENT_PERSIST_IN_METRIC}
        <td class="unfocus graph" id="{$input}">
    {else}
        <td class="graph" id="{$input}">
    {/if}
            {xlt t=$title}
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
            <input type="text" class="form-control" size='5' name='{$input}' id='{$input}_input'
                   value="{if $vitals->$vitalsValue() != 0}{$vitals->$vitalsValue()|attr}{/if}"
                   onChange="convUnit('usa', '{$unit}','{$input}_input')" title='{xla t=$vitalsValueUSAHelpTitle|default:''}'/>
        </td>
    <td>
        { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
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
        <td class="unfocus graph" id="{$input}_metric">
    {else}
        <td class="graph" id="{$input}_metric">
    {/if}
            {xlt t=$title}
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
            <input type="text" class="form-control" size='5' id='{$input}_input_metric'
                   value="{if $vitals->$vitalsValueMetric() != 0}{$vitals->$vitalsValueMetric()|attr}{/if}"
                   onChange="convUnit('metric', '{$unit}','{$input}_input')"/>
        </td>
        <td>
            {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY }
                <!-- we only show the selector if this the only row being displayed -->
                { include file='vitals_interpretation_selector.tpl' vitalDetails=$vitals->get_details_for_column($input) }
            {/if}
        </td>
        { include file='vitals_historical_values.tpl' useMetric=true vitalsValue=$vitalsValue vitalsValueMetric=$vitalsValueMetric
        results=$results }
    </tr>