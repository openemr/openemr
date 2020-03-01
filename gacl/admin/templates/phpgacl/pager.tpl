{* Ready for smarty 3 No changes  
Possible Changes:
Removed literal tag, no longer required if {/space content /space}
*}
<table width="100%" cellspacing="0" cellpadding="2" border="0">
  <tr valign="middle">
    <td align="left">
{if $paging_data.atfirstpage}
      |&lt; &lt;&lt;
{else}
      <a href="{$link}page=1">|&lt;</a> <a href="{$link}page={$paging_data.prevpage|escape:'html'}">&lt;&lt;</a>
{/if}
    </td>
    <td align="right">
{if $paging_data.atlastpage}
      &gt;&gt; &gt;|
{else}
      <a href="{$link}page={$paging_data.nextpage|escape:'html'}">&gt;&gt;</a> <a href="{$link}page={$paging_data.lastpageno|escape:'html'}">&gt;|</a>
{/if}
    </td>
  </tr>
</table>