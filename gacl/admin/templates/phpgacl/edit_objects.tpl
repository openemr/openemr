{include file="phpgacl/header.tpl"}
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
  <body>
    {include file="phpgacl/navigation.tpl"}
    <form method="post" name="edit_objects" action="edit_objects.php">
      <input type="hidden" name="csrf_token_form" value="{$CSRF_TOKEN_FORM|attr}">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr class="pager">
            <td colspan="7">
                {if isset($paging_data)}
                    {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?section_value=$section_value_escaped&object_type=$object_type_escaped&"}
                {else}
                    {include file="phpgacl/pager.tpl" link="?section_value=$section_value_escaped&object_type=$object_type_escaped&"}
                {/if}
            </td>
          </tr>
          <tr>
            <th width="2%">ID</th>
            <th>Section</th>
            <th>Value</th>
            <th>Order</th>
            <th>Name</th>
            <th width="4%">Functions</th>
            <th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
          </tr>
{if isset($objects)}
    {section name=x loop=$objects}
          <tr valign="top" align="center">
            <td>
              {$objects[x].id|text}
              <input type="hidden" name="objects[{$objects[x].id|attr}][]" value="{$objects[x].id|attr}">
            </td>
            <td>{$section_name|text}</td>
            <td><input type="text" size="10" name="objects[{$objects[x].id|attr}][]" value="{$objects[x].value|attr}"></td>
            <td><input type="text" size="10" name="objects[{$objects[x].id|attr}][]" value="{$objects[x].order|attr}"></td>
            <td><input type="text" size="40" name="objects[{$objects[x].id|attr}][]" value="{$objects[x].name|attr}"></td>
            <td>&nbsp;</td>
            <td><input type="checkbox" class="checkbox" name="delete_object[]" value="{$objects[x].id|attr}"></td>
          </tr>
    {/section}
{/if}
          <tr class="pager">
            <td colspan="7">
                {if isset($paging_data)}
                    {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?section_value=$section_value_escaped&object_type=$object_type_escaped&"}
                {else}
                    {include file="phpgacl/pager.tpl" link="?section_value=$section_value_escaped&object_type=$object_type_escaped&"}
                {/if}
            </td>
          </tr>
          <tr class="spacer">
            <td colspan="7"></td>
          </tr>
          <tr align="center">
            <td colspan="7"><b>Add {$object_type|upper|text}s</b></td>
          </tr>
          <tr>
            <th>ID</th>
            <th>Section</th>
            <th>Value</th>
            <th>Order</th>
            <th>Name</th>
            <th>Functions</th>
            <th>&nbsp;</th>
          </tr>
{if isset($new_objects)}
    {section name=y loop=$new_objects}
          <tr valign="top" align="center">
            <td>N/A</td>
            <td>{$section_name|text}</td>
            <td><input type="text" size="10" name="new_objects[{$new_objects[y].id|attr}][]" value=""></td>
            <td><input type="text" size="10" name="new_objects[{$new_objects[y].id|attr}][]" value=""></td>
            <td><input type="text" size="40" name="new_objects[{$new_objects[y].id|attr}][]" value=""></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
    {/section}
{/if}
          <tr class="controls" align="center">
            <td colspan="5">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset"><br />
            </td>
            <td colspan="2">
              <input type="submit" class="button" name="action" value="Delete">
            </td>
          </tr>
        </tbody>
      </table>
    <input type="hidden" name="section_value" value="{$section_value|attr}">
    <input type="hidden" name="object_type" value="{$object_type|attr}">
    <input type="hidden" name="return_page" value="{$return_page|attr}">
  </form>
{include file="phpgacl/footer.tpl"}
