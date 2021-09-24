{foreach item=result from=$results}
    <td class='historicalvalues'>
        {if $useMetric }
            <!-- we use the original value since everything is converted from metric and we want to check for the value -->
            {if $result->$vitalsValue() != 0}
                {if isset($vitalsStringFormat) }
                    {$result->$vitalsValueMetric()|string_format:$vitalsStringFormat|text}
                {else}
                    {$result->$vitalsValueMetric()|text}
                {/if}
            {/if}
        {else}
            {if $result->$vitalsValue() != 0}
                {if isset($vitalsStringFormat) }
                    {$result->$vitalsValue()|string_format:$vitalsStringFormat|text}
                {else}
                    {$result->$vitalsValue()|text}
                {/if}
            {/if}
        {/if}

    </td>
{/foreach}