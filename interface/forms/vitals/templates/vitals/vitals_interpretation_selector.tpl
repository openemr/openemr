<select name="interpretation[{$input}]" class="form-control" id='{$interpretation}'>
    <option value=""> </option>
    {foreach item=option from=$interpretation_options}
        {if isset($vitalDetails) && $option.id == $vitalDetails->get_interpretation_option_id()}
            <option selected="selected" value="{$option.id}">{xlt t=$option.title}</option>
        {else}
        <option {if $option.is_default}selected="selected"{/if}value="{$option.id}">{xlt t=$option.title}</option>
        {/if}
    {/foreach}
</select>