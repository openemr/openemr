{if isset($hide) && $hide }
<tr class="hide">
{else}
<tr>
{/if}
    <td class="graph" id="{$input|attr}">{$title|xlt}</td>
    <td>{xlt t=$unit}</td>

    <td class='currentvalues'>
        <textarea class="form-control form-control-sm" name='{$input|attr}' id='{$input|attr}_input'>{if $vitalsValue != 0}{$vitalsValue|text}{/if}</textarea>
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
