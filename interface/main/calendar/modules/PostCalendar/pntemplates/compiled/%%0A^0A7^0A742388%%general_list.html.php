<?php /* Smarty version 2.6.30, created on 2017-08-14 01:19:01
         compiled from /Users/alfiecarlisle/Documents/openemr/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/Users/alfiecarlisle/Documents/openemr/templates/documents/general_list.html', 63, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>
<?php $this->assign('GLOBALS', $GLOBALS); ?>
<?php 
$is_new = isset($_GET['patient_name']) ? 1 : false;
$place_hld = isset($_GET['patient_name']) ? filter_input(INPUT_GET, 'patient_name') : xl("Patient search or select.");
$cur_pid = isset($_GET['patient_id']) ? filter_input(INPUT_GET, 'patient_id') : '';
$used_msg = xl('Current patient unavailable here. Use Patient Documents');
if ($cur_pid == '00' ) {
    $cur_pid = '0';
    $is_new = 1;
}
$this->assign('is_new', $is_new);
$this->assign('place_hld', $place_hld);
$this->assign('cur_pid', $cur_pid);
$this->assign('used_msg', $used_msg);
$this->assign('demo_pid', $_SESSION['pid']);
 ?>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['css_header']; ?>
" type="text/css">
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone-4-3-0/dist/dropzone.css">
<link href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-ui-1-12-1/themes/ui-lightness/jquery-ui.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<?php echo '
<style type="text/css">
.ui-autocomplete {
    position: absolute;
    top: 0;
    left: 0;
    min-width:200px;
    cursor: default;
}
.ui-menu-item{
     min-width:200px;
}
.fixed-height{
min-width:200px;
padding: 1px;
max-height: 35%;
overflow: auto;
}
</style>
'; ?>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-ui-1-12-1/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/DocumentTreeMenu.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone-4-3-0/dist/dropzone.js"></script>
<script type="text/javascript" src="library/dialog.js?v=<?php echo $this->_tpl_vars['GLOBALS']['v_js_includes']; ?>
"></script>
<script type="text/javascript" src="library/textformat.js?v=<?php echo $this->_tpl_vars['GLOBALS']['v_js_includes']; ?>
"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
    // dropzone javascript asset translation(s)
    Dropzone.prototype.defaultOptions.dictDefaultMessage = "<?php echo smarty_function_xl(array('t' => 'Drop files here to upload'), $this);?>
";
</script>

</head>
<!--<body bgcolor="<?php echo $this->_tpl_vars['STYLE']['BGCOLOR2']; ?>
">-->
<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php  if ($GLOBALS['expand_document_tree']) {   ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php  } else {  ?>
  <body class="body_top">
<?php  }  ?>

<div id="documents_list">
    <div class="ui-widget"style="float:right;">
        <button id='pid' class="pBtn" type="button" style="float:right;">0</button>
         <input id="selectPatient" type="text" placeholder="<?php echo $this->_tpl_vars['place_hld']; ?>
">
    </div>
<a id="list_collapse" href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;">&nbsp;(<?php echo smarty_function_xl(array('t' => 'Collapse all'), $this);?>
)</a>
<div class="title"><?php echo smarty_function_xl(array('t' => 'Documents'), $this);?>
</div>
<?php echo $this->_tpl_vars['tree_html']; ?>

</div>
<div id="documents_actions">
		<?php if ($this->_tpl_vars['message']): ?>
			<div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['message']; ?>
</i></div><br>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['messages']): ?>
            <div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['messages']; ?>
</i></div><br>
		<?php endif; ?>
		<?php echo $this->_tpl_vars['activity']; ?>

</div>
<script type="text/javascript">
var curpid = "<?php echo $this->_tpl_vars['cur_pid']; ?>
";
var newVersion="<?php echo $this->_tpl_vars['is_new']; ?>
";
var demoPid = "<?php echo $this->_tpl_vars['demo_pid']; ?>
";
var inUseMsg = "<?php echo $this->_tpl_vars['used_msg']; ?>
";
<?php echo '
if(curpid == demoPid && !newVersion){
    $(".ui-widget").hide();
}
else{
    $("#pid").text(curpid);
}
$(function() {
    $( "#selectPatient" ).autocomplete({
    	source: "'; ?>
<?php echo $this->_tpl_vars['WEBROOT']; ?>
<?php echo '/library/ajax/document_helpers.php",
    	focus: function(event, sel) {
            event.preventDefault();
        },
        select: function(event, sel) {
            event.preventDefault();
            if (sel.item.value == \'00\' && ! sel.item.label.match(\''; ?>
<?php echo smarty_function_xl(array('t' => 'Reset'), $this);?>
<?php echo '\')){
            	alert(inUseMsg);
            	return false;
            }
            $(this).val(sel.item.label);
            location.href = "'; ?>
<?php echo $this->_tpl_vars['WEBROOT']; ?>
<?php echo '/controller.php?document&list&patient_id="+sel.item.value+"&patient_name=" + sel.item.label;
            $("#pid").text(sel.item.value);
        },
        minLength: 0
    }).autocomplete("widget").addClass("fixed-height");
 });
$(".pBtn").click(function(event) {
    var $input = $("#selectPatient");
        $input.val(\'*\');
        $input.autocomplete(\'search\'," ");
        $input.val(\'\');
});
$("#list_collapse").detach().appendTo("#objTreeMenu_1_node_1 nobr");

$(document).ready(function(){
    $(\'.datepicker\').datetimepicker({
        '; ?>
<?php  $datetimepicker_timepicker = false;  ?>
        <?php  $datetimepicker_showseconds = false;  ?>
        <?php  $datetimepicker_formatInput = false;  ?>
        <?php  require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');  ?>
        <?php  // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma  ?><?php echo '
    });
});'; ?>


</script>
</body>
</html>