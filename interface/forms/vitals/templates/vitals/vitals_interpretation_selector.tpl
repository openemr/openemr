<select name="interpretation[{$input}]" class="form-control" id='{$interpretation}'>
    <option value=""> </option>
    {foreach item=option from=$interpretation_options}
        <option {if $option.is_default}selected="selected"{/if}value="{$option.id}">{xlt t=$option.title}</option>
    {/foreach}
</select>