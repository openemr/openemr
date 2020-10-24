<table width="100%" cellspacing="0" cellpadding="2" border="0">
  <tr valign="middle">
    <td align="left">
{if isset($paging_data.atfirstpage) && $paging_data.atfirstpage}
      |&lt; &lt;&lt;
{else}
      <a href="{$link}page=1">|&lt;</a> <a href="{$link}page={if isset($paging_data.prevpage)}{$paging_data.prevpage|escape:'html'}{/if}">&lt;&lt;</a>
{/if}
    </td>
    <td align="right">
{if isset($paging_data.atlastpage) && $paging_data.atlastpage}
      &gt;&gt; &gt;|
{else}
      <a href="{$link}page={if isset($paging_data.nextpage)}{$paging_data.nextpage|escape:'html'}{/if}">&gt;&gt;</a> <a href="{$link}page={if isset($paging_data.lastpageno)}{$paging_data.lastpageno|escape:'html'}{/if}">&gt;|</a>
{/if}
    </td>
  </tr>
</table>
