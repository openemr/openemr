{include file="phpgacl/header.tpl"}  
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
  <body>
    {include file="phpgacl/navigation.tpl"}  
    <form method="post" name="edit_group" action="edit_group.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th width="2%">ID</th>
            <th width="40%">Name</th>
            <th width="20%">Value</th>
            <th width="6%">Objects</th>
            <th width="30%">Functions</th>
            <th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
          </tr>
{foreach from=$groups item=group}
          <tr valign="middle" align="center">
            <td>{$group.id}</td>
            <td align="left">{$group.name}</td>
            <td align="left">{$group.value}</td>
            <td>{$group.object_count}</td>
            <td>
              [&nbsp;<a href="assign_group.php?group_type={$group_type}&group_id={$group.id}&return_page={$return_page}">Assign&nbsp;{$group_type|upper}</a>&nbsp;]
              [&nbsp;<a href="edit_group.php?group_type={$group_type}&parent_id={$group.id}&return_page={$return_page}">Add&nbsp;Child</a>&nbsp;]
              [&nbsp;<a href="edit_group.php?group_type={$group_type}&group_id={$group.id}&return_page={$return_page}">Edit</a>&nbsp;]
              [&nbsp;<a href="acl_list.php?action=Filter&filter_{$group_type}_group={$group.raw_name|urlencode}&return_page={$return_page}">ACLs</a>&nbsp;]
            </td>
            <td><input type="checkbox" class="checkbox" name="delete_group[]" value="{$group.id}"></td>
          </tr>
{/foreach}
          <tr class="controls" align="center">
            <td colspan="4">&nbsp;</td>
            <td colspan="2" nowrap><input type="submit" class="button" name="action" value="Add" /> <input type="submit" class="button" name="action" value="Delete" /></td>
          </tr>
        </tbody>
      </table>
    <input type="hidden" name="group_type" value="{$group_type}">
    <input type="hidden" name="return_page" value="{$return_page}">
  </form>
{include file="phpgacl/footer.tpl"}
