<?php /* Smarty version 2.6.14, created on 2009-02-05 19:08:03
         compiled from phpgacl/pager.tpl */ ?>
<table width="100%" cellspacing="0" cellpadding="2" border="0">
  <tr valign="middle">
    <td align="left">
<?php if ($this->_tpl_vars['paging_data']['atfirstpage']): ?>
      |&lt; &lt;&lt;
<?php else: ?>
      <a href="<?php echo $this->_tpl_vars['link']; ?>
page=1">|&lt;</a> <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo $this->_tpl_vars['paging_data']['prevpage']; ?>
">&lt;&lt;</a>
<?php endif; ?>
    </td>
    <td align="right">
<?php if ($this->_tpl_vars['paging_data']['atlastpage']): ?>
      &gt;&gt; &gt;|
<?php else: ?>
      <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo $this->_tpl_vars['paging_data']['nextpage']; ?>
">&gt;&gt;</a> <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo $this->_tpl_vars['paging_data']['lastpageno']; ?>
">&gt;|</a>
<?php endif; ?>
    </td>
  </tr>
</table>