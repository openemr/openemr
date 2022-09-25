{include file="phpgacl/header.tpl"}
    <style type="text/css">

      select {
        margin-top: 0px;
      }
      input.group-name, input.group-value {
        width: 99%;
      }

    </style>
  </head>
  <body>
{include file="phpgacl/navigation.tpl"}
    <form method="post" name="edit_group" action="edit_group.php">
      <input type="hidden" name="csrf_token_form" value="{$CSRF_TOKEN_FORM|attr}">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th width="4%">ID</th>
            <th width="32%">Parent</th>
            <th width="32%">Name</th>
            <th width="32%">Value</th>
          </tr>
          <tr valign="top">
            <td align="center">{$id|default:"N/A"}</td>
            <td>
                <select name="parent_id" tabindex="0" multiple>
                    {if isset($options_groups)}
                        {html_options options=$options_groups selected=$parent_id}
                    {/if}
                </select>
            </td>
            <td>
                <input type="text" class="group-name" size="50" name="name" value="{if isset($name)}{$name|attr}{/if}">
            </td>
            <td>
                <input type="text" class="group-value" size="50" name="value" value="{if isset($value)}{$value|attr}{/if}">
            </td>
          </tr>
          <tr class="controls" align="center">
            <td colspan="4">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
            </td>
          </tr>
        </tbody>
      </table>
    <input type="hidden" name="group_id" value="{if isset($id)}{$id|attr}{/if}">
    <input type="hidden" name="group_type" value="{$group_type|attr}">
    <input type="hidden" name="return_page" value="{$return_page|attr}">
  </form>
{include file="phpgacl/footer.tpl"}
