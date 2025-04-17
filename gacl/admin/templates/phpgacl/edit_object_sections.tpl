{include file="phpgacl/header.tpl"}
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
  <body>
    {include file="phpgacl/navigation.tpl"}
    <form method="post" name="edit_object_sections" action="edit_object_sections.php">
      <input type="hidden" name="csrf_token_form" value="{$CSRF_TOKEN_FORM|attr}">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr class="pager">
            <td colspan="11">
                {if isset($paging_data)}
                    {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?object_type=$object_type_escaped&"}
                {else}
                    {include file="phpgacl/pager.tpl" link="?object_type=$object_type_escaped&"}
                {/if}
            </td>
          </tr>
          <tr>
            <th width="2%">ID</th>
            <th>Value</th>
            <th>Order</th>
            <th>Name</th>
            <th width="4%">Functions</th>
            <th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
          </tr>
{if isset($sections)}
    {section name=x loop=$sections}
          <tr valign="top" align="center">
            <td>
              {$sections[x].id|text}
              <input type="hidden" name="sections[{$sections[x].id|attr}][]" value="{$sections[x].id|attr}">
            </td>
            <td><input type="text" size="10" name="sections[{$sections[x].id|attr}][]" value="{$sections[x].value|attr}"></td>
            <td><input type="text" size="10" name="sections[{$sections[x].id|attr}][]" value="{$sections[x].order|attr}"></td>
            <td><input type="text" size="40" name="sections[{$sections[x].id|attr}][]" value="{$sections[x].name|attr}"></td>
            <td>&nbsp;</td>
            <td><input type="checkbox" class="checkbox" name="delete_sections[]" value="{$sections[x].id|attr}"></td>
          </tr>
    {/section}
{/if}
          <tr class="pager">
            <td colspan="6">
                {if isset($paging_data)}
                    {include file="phpgacl/pager.tpl" pager_data=$paging_data link="?object_type=$object_type_escaped&"}
                {else}
                    {include file="phpgacl/pager.tpl" link="?object_type=$object_type_escaped&"}
                {/if}
            </td>
          </tr>
          <tr class="spacer">
            <td colspan="6"></td>
          </tr>
          <tr align="center">
            <td colspan="6"><b>Add {$object_type|upper|text} Sections</b></td>
          </tr>
          <tr>
            <th>ID</th>
            <th>Value</th>
            <th>Order</th>
            <th>Name</th>
            <th>Functions</th>
            <th>&nbsp;</td>
          </tr>
{if isset($new_sections)}
    {section name=y loop=$new_sections}
          <tr valign="top" align="center">
            <td>N/A</td>
            <td><input type="text" size="10" name="new_sections[{$new_sections[y].id|attr}][]" value=""></td>
            <td><input type="text" size="10" name="new_sections[{$new_sections[y].id|attr}][]" value=""></td>
            <td><input type="text" size="40" name="new_sections[{$new_sections[y].id|attr}][]" value=""></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
    {/section}
{/if}
          <tr class="controls" align="center">
            <td colspan="4">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
            </td>
            <td colspan="2">
              <input type="submit" class="button" name="action" value="Delete">
            </td>
          </tr>
        </tbody>
      </table>
    <input type="hidden" name="object_type" value="{$object_type|attr}">
    <input type="hidden" name="return_page" value="{$return_page|attr}">
    </form>
{include file="phpgacl/footer.tpl"}
