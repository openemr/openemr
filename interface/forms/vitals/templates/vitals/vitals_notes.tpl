{if isset($hide) && $hide }
<tr class="hide">
{else}
<tr>
{/if}
    <td class="graph" id="{$input}">{xlt t=$title}</td>
    <td>{xlt t=$unit}</td>

    <td class='currentvalues p-2'>
        <textarea class="form-control" name='{$input}' id='{$input}_input'>{if $vitalsValue != 0}{$vitalsValue|text}{/if}</textarea>
    </td>
    <td class="editonly">
    </td>
    {foreach item=result from=$results}
        <td  class='historicalvalues'>
            {if $result->get_note() != 0}
                {$result->get_note()|text}
            {/if}
        </td>
    {/foreach}
</tr>