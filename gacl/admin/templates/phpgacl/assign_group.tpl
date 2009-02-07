{include file="phpgacl/header.tpl"}
<script LANGUAGE="JavaScript">
{$js_array}
</script>
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
  <body onload="populate(document.assign_group.{$group_type}_section,document.assign_group.elements['objects[]'], '{$js_array_name}')">
    {include file="phpgacl/navigation.tpl"}    
    <form method="post" name="assign_group" action="assign_group.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th width="32%">Sections</th>
            <th width="32%">{$object_type}s</th>
            <th width="4%">&nbsp;</th>
            <th width="32%">Selected</th>
          </tr>
          <tr valign="top" align="center">
            <td>
              [ <a href="edit_object_sections.php?object_type={$group_type}&return_page={$return_page}">Edit</a> ]
              <br />
              <select name="{$group_type}_section" tabindex="0" size="10" width="200" onclick="populate(document.assign_group.{$group_type}_section,document.assign_group.elements['objects[]'],'{$js_array_name}')">
                {html_options options=$options_sections selected=$section_value}
              </select>
            </td>
            <td>
              [ <a href="javascript: location.href = 'edit_objects.php?object_type={$group_type}&section_value=' + document.assign_group.{$group_type}_section.options[document.assign_group.{$group_type}_section.selectedIndex].value + '&return_page={$return_page}';">Edit</a> ]
              [ <a href="#" onClick="window.open('object_search.php?src_form=assign_group&object_type={$group_type}&section_value=' + document.assign_group.{$group_type}_section.options[document.assign_group.{$group_type}_section.selectedIndex].value,'','status=yes,width=400,height=400','','status=yes,width=400,height=400');">Search</a> ]
              <br />
              <select name="objects[]" tabindex="0" size="10" width="200" multiple>
              </select>
            </td>
            <td valign="middle">
              <br /><input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.assign_group.{$group_type}_section, document.assign_group.elements['objects[]'], document.assign_group.elements['selected_{$group_type}[]'])">
              <br /><input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item(document.assign_group.elements['selected_{$group_type}[]'])">
            </td>
            <td>
              <br />
              <select name="selected_{$group_type}[]" tabindex="0" size="10" width="200" multiple>
				{html_options options=$options_selected_objects selected=$selected_object}
              </select>
            </td>
          </tr>
          <tr class="controls" align="center">
            <td colspan="4">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
            </td>
          </tr>
        </tbody>
      </table>
      <br />
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tr align="center">
	      <td colspan="5"><b>{$total_objects}</b> {$group_type|upper}s in Group: <b>{$group_name}</b></td>
        </tr>
        <tr class="pager">
          <td colspan="5">
        {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?group_type=$group_type&group_id=$group_id&"}
          </td>
        </tr>
        <tr>
	<th>Section</th>
	<th>{$object_type}</th>
	<th>{$group_type|upper} Value</th>
	<th width="4%">Functions</th>
	<th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
        </tr>
{foreach from=$rows item=row}
  <tr valign="top" align="center">
    <td>
      {$row.section}
    </td>
    <td>
      {$row.name}
    </td>
    <td>
      {$row.value}
    </td>
    <td>
      [ <a href="acl_list.php?action=Filter&filter_{$group_type}_section={$row.section_value}&filter_{$group_type}={$row.name}&return_page={$return_page}">ACLs</a> ]
    </td>
    <td>
      <input type="checkbox" class="checkbox" name="delete_assigned_object[]" value="{$row.section_value}^{$row.value}">
    </td>
  </tr>
{/foreach}
  <tr class="pager">
    <td colspan="5">
      {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?"}
    </td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="3">&nbsp;</td>
    <td colspan="2">
      <input type="submit" class="button" name="action" value="Remove">
    </td>
  </tr>
</table>
<input type="hidden" name="group_id" value="{$group_id}">
<input type="hidden" name="group_type" value="{$group_type}">
<input type="hidden" name="return_page" value="{$return_page}">
</form>
{include file="phpgacl/footer.tpl"}