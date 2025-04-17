{include file="phpgacl/header.tpl"}
  </head>
  <body>
	{include file="phpgacl/navigation.tpl"}
    <div style="text-align: center;">
      <table cellpadding="2" cellspacing="2" border="2" align="center">
        <tbody>
          <tr>
			<th>
				Report
			</th>
          </tr>
          <tr>
			<td align="center">
				<textarea name="system_information" rows="10" cols="60" wrap="VIRTUAL">{$system_info|text}</textarea>
			</td>
          </tr>
		  <tr>
			<th>
				Credits
			</th>
          </tr>
          <tr>
			<td>
<pre>
{$credits|text}
</pre>
			</td>
          </tr>
        </tbody>
      </table>
    </div>
{include file="phpgacl/footer.tpl"}
