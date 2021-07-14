<tr><td>{xlt t="Temp Location"}<td></td>
    <td class='currentvalues p-2'><select name="temp_method" class="form-control" id='temp_method'><option value=""> </option>
            <option value="Oral"              {if $vitals->get_temp_method() == "Oral"              || $vitals->get_temp_method() == 2 } selected{/if}>{xlt t="Oral"}
            <option value="Tympanic Membrane" {if $vitals->get_temp_method() == "Tympanic Membrane" || $vitals->get_temp_method() == 1 } selected{/if}>{xlt t="Tympanic Membrane"}
            <option value="Rectal"            {if $vitals->get_temp_method() == "Rectal"            || $vitals->get_temp_method() == 3 } selected{/if}>{xlt t="Rectal"}
            <option value="Axillary"          {if $vitals->get_temp_method() == "Axillary"          || $vitals->get_temp_method() == 4 } selected{/if}>{xlt t="Axillary"}
            <option value="Temporal Artery"   {if $vitals->get_temp_method() == "Temporal Artery" } selected{/if}>{xlt t="Temporal Artery"}
        </select></td>
    <td></td>
    {foreach item=result from=$results}
        <td class='historicalvalues'>{if $result.temp_method}{xlt t=$result.temp_method}{/if}</td>
    {/foreach}</tr>