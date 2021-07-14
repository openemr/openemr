<select name="{$interpretation}" class="form-control" id='{$interpretation}'>
    <option value=""> </option>
    {foreach item=option from=$interpretation_options}
        <option value="{$option.id}">{xlt t=$option.title}</option>
    {/foreach}
</select>