{if isset($hide) && $hide }
<tr class="hide">
{else}
<tr>
{/if}
    <td class="graph" id="{$input}">{xlt t=$title}</td>
    <td>{xlt t=$unit}</td>

    <td class='currentvalues p-2'>
        {if isset($vitalsStringFormat) }
        <input type="text" class="form-control" size='5' name='{$input}' id='{$input}_input'
               value="{if $vitalsValue != 0}{$vitalsValue|string_format:$vitalsStringFormat|attr}{/if}"/>
        {else}
        <input type="text" class="form-control" size='5' name='{$input}' id='{$input}_input'
               value="{if $vitalsValue != 0}{$vitalsValue|attr}{/if}"/>
        {/if}

    </td>
    <td>
        {if isset($interpretation) }
            { include file='vitals_interpretation_selector.tpl' }
        {/if}
    </td>

    {foreach item=result from=$results}
        <td  class='historicalvalues'>
            {if $result[$input] != 0}
                {if isset($vitalsStringFormat)}
                    {$result[$input]|string_format:$vitalsStringFormat|text}
                {else}
                    {$result[$input]|text}
                {/if}
                {if isset($interpretation) && isset($result[$interpretation])}
                    <!-- we add on our interpretation piece here -->
                    - {$result[$interpretation]}
                {/if}
            {/if}
        </td>
    {/foreach}
</tr>