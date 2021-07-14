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
                   value="{if $vitalsValue != 0}{$vitalsValue|attr}{/if}"
                   onChange="convUnit('usa', '{$unit}','{$input}_input')" title='{xla t=$vitalsValueUSAHelpTitle|default:''}'/>
        </td>
    <td>
        {if isset($interpretation)}
            <!-- we only show the selector if this the only row being displayed -->
            { include file='vitals_interpretation_selector.tpl' }
        {/if}
    </td>
    {foreach item=result from=$results}
        <td class='historicalvalues'>
            {if $result[$input].usa != 0}
                {if isset($vitalsStringFormat) }
                    {$result[$input].usa|string_format:$vitalsStringFormat|text}
                {else}
                    {$result[$input].usa|text}
                {/if}
            {/if}
        </td>
    {/foreach}
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
                   value="{if $vitalsValue != 0}{$vitalsValueMetric|attr}{/if}"
                   onChange="convUnit('metric', '{$unit}','{$input}_input')"/>
        </td>
        <td>
            {if $units_of_measurement == $MEASUREMENT_METRIC_ONLY && isset($interpretation)}
                <!-- we only show the selector if this the only row being displayed -->
                { include file='vitals_interpretation_selector.tpl' }
            {/if}
        </td>
    {foreach item=result from=$results}
        <td class='historicalvalues'>
            {if $result[$input].metric != 0}
                {if isset($vitalsStringFormat) }
                    {$result[$input].metric|string_format:$vitalsStringFormat|text}
                {else}
                    {$result[$input].metric|text}
                {/if}
            {/if}
        </td>
    {/foreach}
    </tr>