<select name="interpretation[{$input|attr}]" class="form-control" id='interpretation_{$input|attr}'>
    <option value=""> </option>
    {foreach item=option from=$interpretation_options}
        {if isset($vitalDetails) && $option.id == $vitalDetails->get_interpretation_option_id()}
            <option selected="selected" value="{$option.id|attr}">{$option.title|xlt}</option>
        {else}
        <option {if $option.is_default}selected="selected"{/if}value="{$option.id|attr}">{$option.title|xlt}</option>
        {/if}
    {/foreach}
</select>