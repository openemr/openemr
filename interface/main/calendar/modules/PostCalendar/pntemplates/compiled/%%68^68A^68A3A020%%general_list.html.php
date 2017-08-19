<?php /* Smarty version 2.6.30, created on 2017-08-19 21:03:30
         compiled from /Users/alfiecarlisle/Documents/openemr/templates/prescription/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_list.html', 92, false),array('modifier', 'escape', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_list.html', 185, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];  ?>" type="text/css">
<script type="text/javascript" src="<?php  echo $GLOBALS['assets_static_relative'];  ?>/jquery-min-1-2-2/index.js"></script>

<?php echo '
<style type="text/css" title="mystyles" media="all">
.inactive {
  color:#777777;
}
</style>

<script language="javascript">

function changeLinkHref(id,addValue,value) {
    var myRegExp = new RegExp(":" + value + ":");
    if (addValue){ //add value to href
        document.getElementById(id).href += \':\' + value + \':\';
    }
    else { //remove value from href
       document.getElementById(id).href = document.getElementById(id).href.replace(myRegExp,\'\');
    }
}

function changeLinkHref_All(id,addValue,value) {
    var myRegExp = new RegExp(":" + value + ":");
    if (addValue){ //add value to href
        document.getElementById(id).href += \':\' + value + \':\';
    }
    else { //remove value from href
        document.getElementById(id).href = document.getElementById(id).href.replace(myRegExp,\'\');
		// TajEmo Work By CB 2012/06/14 02:17:16 PM remove the target change 
    //document.getElementById(id).target = \'\';
    }
}

function Check(chk) {
    var len=chk.length;
    if (len==undefined) {chk.checked=true;}
    else {
        for (pr = 0; pr < chk.length; pr++){
            if($(chk[pr]).parents("tr.inactive").length==0)
                {
                    chk[pr].checked=true;
                    changeLinkHref_All(\'multiprint\',true,chk[pr].value);
                    changeLinkHref_All(\'multiprintcss\',true, chk[pr].value);
                    changeLinkHref_All(\'multiprintToFax\',true, chk[pr].value);
                }
        }
    }
}

function Uncheck(chk) {
    var len=chk.length;
    if (len==undefined) {chk.checked=false;}
    else {
        for (pr = 0; pr < chk.length; pr++){
            chk[pr].checked=false;
            changeLinkHref_All(\'multiprint\',false,chk[pr].value);
            changeLinkHref_All(\'multiprintcss\',false, chk[pr].value);
            changeLinkHref_All(\'multiprintToFax\',false, chk[pr].value);
        }
    }
}

var CheckForChecks = function(chk) {
    // Checks for any checked boxes, if none are found than an alert is raised and the link is killed
    if (Checking(chk) == false) { return false; }
    return top.restoreSession();
};

function Checking(chk) {
    var len=chk.length;
	var foundone=false;
	 
    if (len==undefined) {
			if (chk.checked == true){
				foundone=true;
			}
	} 
	else {
		for (pr = 0; pr < chk.length; pr++){
			if (chk[pr].checked == true) {
				foundone=true;
			}
		}
	}	
	if (foundone) {
		return true;
	} else {
		alert("'; ?>
<?php echo smarty_function_xl(array('t' => 'Please select at least one prescription!'), $this);?>
<?php echo '");
		return false;
	}
}

$(document).ready(function(){
  $(":checkbox:checked").each(function () { 
      changeLinkHref(\'multiprint\',this.checked, this.value);
      changeLinkHref(\'multiprintcss\',this.checked, this.value);
      changeLinkHref(\'multiprintToFax\',this.checked, this.value);
  });
})

</script>

'; ?>

</head>
<body class="body_top">

<?php if ($this->_tpl_vars['prescriptions']): ?>
<span class="title"><b><?php echo smarty_function_xl(array('t' => 'List'), $this);?>
</b></span>

<div id="prescription_list">

<form name="presc">

<div id="print_links">
    <table width="100%">
        <tr>
            <td align="left">
                <table>
                    <tr>
                        <td>
                            <a id="multiprint" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprint&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Download'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'PDF'), $this);?>
)</span></a>
                        </td>
                        <td>
                          <!-- TajEmo work by CB 2012/06/14 02:16:32 PM target="_script" opens better -->
                            <a target="_script" id="multiprintcss" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprintcss&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'View Printable Version'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'HTML'), $this);?>
)</span></a>
                        </td>
                        <td style="border-style:none;">
                            <a id="multiprintToFax" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprintfax&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Download'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'Fax'), $this);?>
)</span></a>
                        </td>
                        <?php if ($this->_tpl_vars['CAMOS_FORM'] == true): ?>
                        <td>
                            <a id="four_panel_rx" href="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/interface/forms/CAMOS/rx_print.php?sigline=plain" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'View Four Panel'), $this);?>
</span></a>
                        </td>
                        <?php endif; ?>
                    </tr>
                </table>
            </td>
            <td align="right">
                <table>
                <tr>
                    <td>
                        <a href="#" class="small" onClick="Check(document.presc.check_list);"><span><?php echo smarty_function_xl(array('t' => 'Check All'), $this);?>
</span></a> |
                        <a href="#" class="small" onClick="Uncheck(document.presc.check_list);"><span><?php echo smarty_function_xl(array('t' => 'Clear All'), $this);?>
</span></a>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
</div>


<table width="100%" class="showborder_head" cellspacing="0px" cellpadding="2px">
    <tr> 
       <!-- TajEmo Changes 2012/06/14 02:01:43 PM by CB added Heading for checkbox column -->   
        <th width="8px">&nbsp;</th>
		    <th width="8px">&nbsp;</th>
        <th width="180px"><?php echo smarty_function_xl(array('t' => 'Drug'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Code'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Created'), $this);?>
<br /><?php echo smarty_function_xl(array('t' => 'Changed'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Dosage'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Qty'), $this);?>
.</th>
        <th><?php echo smarty_function_xl(array('t' => 'Unit'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Provider'), $this);?>
</th>
    </tr>

	<?php $_from = $this->_tpl_vars['prescriptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['prescription']):
?> 
  <!-- TajEmo Changes 2012/06/14 02:03:17 PM by CB added cursor:pointer for easier user understanding -->  
  <tr style="cursor:pointer" id="<?php echo $this->_tpl_vars['prescription']->id; ?>
" class="showborder onescript <?php if ($this->_tpl_vars['prescription']->active <= 0): ?> inactive<?php endif; ?>" title="<?php echo smarty_function_xl(array('t' => 'Click to view/edit'), $this);?>
">
	 <td align="center"> 
      <input class="check_list" id="check_list" type="checkbox" value="<?php echo $this->_tpl_vars['prescription']->id; ?>
" <?php if ($this->_tpl_vars['prescription']->encounter == $this->_tpl_vars['prescription']->get_encounter() && $this->_tpl_vars['prescription']->active > 0): ?>checked="checked" <?php endif; ?>onclick="changeLinkHref('multiprint',this.checked, this.value);changeLinkHref('multiprintcss',this.checked, this.value);changeLinkHref('multiprintToFax',this.checked, this.value)" title="<?php echo smarty_function_xl(array('t' => 'Select for printing'), $this);?>
">
    </td>
	<?php if ($this->_tpl_vars['prescription']->erx_source == 0): ?>
    <td class="editscript"  id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
        <a class='editscript css_button_small' id='<?php echo $this->_tpl_vars['prescription']->id; ?>
' href="controller.php?prescription&edit&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" style="margin-top:-2px"><span><?php echo smarty_function_xl(array('t' => 'Edit'), $this);?>
</span></a>
      <!-- TajEmo Changes 2012/06/14 02:02:22 PM by CB commented out, to avoid duplicate display of drug name
        <?php if ($this->_tpl_vars['prescription']->active > 0): ?><b><?php endif; ?><?php echo $this->_tpl_vars['prescription']->drug; ?>
<?php if ($this->_tpl_vars['prescription']->active > 0): ?></b><?php endif; ?>&nbsp;
      --> 
    </td>
	<td class="editscript"  id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
	<?php if ($this->_tpl_vars['prescription']->active > 0): ?><b><?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->drug)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<?php if ($this->_tpl_vars['prescription']->active > 0): ?></b><?php endif; ?>&nbsp;
  <br /><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->note)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

    </td>
	<?php else: ?>
  <td>&nbsp;</td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
	<?php if ($this->_tpl_vars['prescription']->active > 0): ?><b><?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->drug)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<?php if ($this->_tpl_vars['prescription']->active > 0): ?></b><?php endif; ?>&nbsp;
  <br /><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->note)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

    </td>
	<?php endif; ?>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->rxnorm_drugcode)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->date_added; ?>
<br />
      <?php echo $this->_tpl_vars['prescription']->date_modified; ?>
&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->get_dosage_display())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 &nbsp;
    </td>
	<?php if ($this->_tpl_vars['prescription']->erx_source == 0): ?>
    <td class="editscript" id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->quantity)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 &nbsp;
    </td>
	<?php else: ?>
	<td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->quantity)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 &nbsp;
    </td>
	<?php endif; ?>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
       <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->get_size())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo $this->_tpl_vars['prescription']->get_unit_display(); ?>
&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->provider->get_name_display(); ?>
&nbsp;
    </td>
  </tr>
	<?php endforeach; endif; unset($_from); ?>
</table>

</form>
</div>

<?php else: ?>
<div class="text" style="margin-top:10px"><?php echo smarty_function_xl(array('t' => 'There are currently no prescriptions'), $this);?>
.</div>
<?php endif; ?>

</body>
<?php echo '
<script language=\'JavaScript\'>

$(document).ready(function(){
$("#multiprint").click(function() { return CheckForChecks(document.presc.check_list); });
$("#multiprintcss").click(function() { return CheckForChecks(document.presc.check_list); });
$("#multiprintToFax").click(function() { return CheckForChecks(document.presc.check_list); });
$(".editscript").click(function() { ShowScript(this); });
$(".onescript").mouseover(function() { $(this).children().toggleClass("highlight"); });
$(".onescript").mouseout(function() { $(this).children().toggleClass("highlight"); });
});

var ShowScript = function(eObj) {
    top.restoreSession();
    objID = eObj.id;
    document.location.href="'; ?>
<?php echo $this->_tpl_vars['WEB_ROOT']; ?>
<?php echo '/controller.php?prescription&edit&id="+objID;
    return true;
};

</script>
'; ?>

</html>