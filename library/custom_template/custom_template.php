<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
$contextName = $_REQUEST['contextName'];
$type = $_REQUEST['type'];
$rowContext = sqlQuery("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_list_item_long=?",array($contextName));

?>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
    <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
    <script src="ckeditor/_samples/sample.js" type="text/javascript"></script>
    <link href="ckeditor/_samples/sample.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.7.1.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.easydrag.handler.beta2.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajax_functions_writer.js"></script>
    <script language="JavaScript" type="text/javascript">
    $(document).ready(function(){

    // fancy box
    enable_modals();

    tabbify();

    // special size for
	$(".iframe_small").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 120,
		'frameWidth' : 330
	});
	$(".iframe_medium").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 430,
		'frameWidth' : 680
	});
        $(".iframe_abvmedium").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 500,
		'frameWidth' : 700
	});
	$(function(){
		// add drag and drop functionality to fancybox
		$("#fancy_outer").easydrag();
	});

        $("#menu5 > li > a.expanded + ul").slideToggle("medium");
	$("#menu5 > li > a").click(function() {
		$("#menu5 > li > a.expanded").not(this).toggleClass("expanded").toggleClass("collapsed").parent().find('> ul').slideToggle("medium");
		$(this).toggleClass("expanded").toggleClass("collapsed").parent().find('> ul').slideToggle("medium");
	});
    });
    </script>
     <script type="text/javascript">
$(document).ready(function(){ 
						   
	$(function() {
		$("#menu5 div").sortable({ opacity: 0.3, cursor: 'move', update: function() {
			var order = $(this).sortable("serialize") + '&action=updateRecordsListings'; 
			$.post("updateDB.php", order);									 
		}								  
		});
	});

});
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script> 
</head>
<body class="body_top">
   <input type="hidden" name="list_id" id="list_id" value="<?php echo $rowContext['cl_list_id'];?>">
    <table width=100% align=left cellpadding=0 cellspacing=0 margin-left=0px>
        <?php
        if($rowContext['cl_list_item_long']){
        ?>
        <tr class="text"><th colspan="2" align="center"><?php echo strtoupper(htmlspecialchars(xl($rowContext['cl_list_item_long']),ENT_QUOTES));?></th></tr>
        <tr>
            <td>
                <div id="tab1" class="tabset_content tabset_content_active">
                    <form>
                    <table width=100%>
			<tr clss="text">
			    <td>
				<a href="#" onclick="return SelectToSave('<?php echo $type;?>')" class="css_button" ><span><?php echo htmlspecialchars(xl('SAVE'),ENT_QUOTES);?></span></a>
			    </td>
			</tr>
                        <tr class="text">
                            <td id="templateDD">
                                <select name="template" id="template" onchange="TemplateSentence(this.value)" style="width:180px">
                                    <option value=""><?php echo htmlspecialchars(xl('Select category'),ENT_QUOTES);?></option>
                                    <?php
                                    $resTemplates = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE tu.tu_user_id=? AND c.cl_list_type=3 AND cl_list_id=? AND cl_deleted=0 ORDER BY c.cl_list_item_long",array($_SESSION['authId'],$rowContext['cl_list_id']));
                                    while($rowTemplates = sqlFetchArray($resTemplates)){
                                    echo "<option value='".htmlspecialchars($rowTemplates['cl_list_slno'],ENT_QUOTES)."'>".htmlspecialchars(xl($rowTemplates['cl_list_item_long']),ENT_QUOTES)."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <div id="share" style="display:none"></div>
				<a href="#" id="enter" onclick="top.restoreSession();ascii_write('13','textarea1');" title="<?php echo htmlspecialchars(xl('Enter Key'),ENT_QUOTES);?>"><img border=0 src="../../images/enter.gif"></a>&nbsp;
                                <a href="#" id="quest" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('? ');" title="<?php echo htmlspecialchars(xl('Question Mark'),ENT_QUOTES);?>"><img border=0 src="../../images/question.png"></a>&nbsp;
                                <a href="#" id="para" onclick="top.restoreSession();ascii_write('para','textarea1');"  title="<?php echo htmlspecialchars(xl('New Paragraph'),ENT_QUOTES);?>"><img border=0 src="../../images/paragraph.png"></a>&nbsp;
                                <a href="#" id="space" onclick="top.restoreSession();ascii_write('32','textarea1');" class="css_button" title="<?php echo htmlspecialchars(xl('Space'),ENT_QUOTES);?>"><span><?php echo htmlspecialchars(xl('SPACE'),ENT_QUOTES);?></span></a>
                                <?php
                                $res=sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS cl ON cl.cl_list_slno=tu.tu_template_id
                                                    WHERE tu.tu_user_id=? AND cl.cl_list_type=6 AND cl.cl_deleted=0 ORDER BY cl.cl_order",array($_SESSION['authId']));
                                while($row=sqlFetchArray($res)){
                                ?>
                                    <a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['cl_list_item_short'];?>');" class="css_button" title="<?php echo htmlspecialchars(xl($row['cl_list_item_long']),ENT_QUOTES);?>"><span><?php echo ucfirst(htmlspecialchars(xl($row['cl_list_item_long']),ENT_QUOTES));?></span></a>
                                <?php                   
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td valign=top style="width:180px;">
                                <div style="background-color:#DFEBFE">
                                <div style="overflow-y:scroll;overflow-x:hidden;height:400px">
                                <ul id="menu5" class="example_menu" style="width:100%;">
                                    <li><a class="expanded"><?php echo htmlspecialchars(xl('Components'),ENT_QUOTES);?></a>
                                        <ul>
                                        <div id="template_sentence">
                                        </div>
                                        </ul>
                                    </li>
                                    <?php
                                    if($pid!=''){
                                        $row = sqlQuery("SELECT * FROM patient_data WHERE pid=?",array($pid));
                                    ?>
                                    <li><a class="collapsed"><?php echo htmlspecialchars(xl('Patient Details'),ENT_QUOTES);?></a>
                                        <ul>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['fname'];?>');"><?php echo htmlspecialchars(xl('First name',ENT_QUOTES));?></a></span></li>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['lname'];?>');"><?php echo htmlspecialchars(xl('Last name',ENT_QUOTES));?></a></span></li>
                                            <?php
                                            if($row['phone_home']){
                                            ?>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['phone_home'];?>');"><?php echo htmlspecialchars(xl('Phone',ENT_QUOTES));?></a></span></li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if($row['ss']){
                                            ?>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['ss'];?>');"><?php echo htmlspecialchars(xl('SSN',ENT_QUOTES));?></a></span></li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if($row['DOB']){
                                            ?>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['DOB'];?>');"><?php echo htmlspecialchars(xl('Date Of Birth',ENT_QUOTES));?></a></span></li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if($row['providerID']){
                                                $val=sqlQuery("SELECT CONCAT(lname,',',fname) AS name FROM users WHERE id='".$row['providerID']."'");
                                            ?>
                                            <li><span><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $val['name'];?>');"><?php echo htmlspecialchars(xl('PCP',ENT_QUOTES));?></a></span></li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                                </div>
                                </div>
                                <a href="personalize.php?list_id=<?php echo $rowContext['cl_list_id'];?>" id="personalize_link" class="iframe_medium css_button"><span><?php echo htmlspecialchars(xl('Personalize'),ENT_QUOTES);?></span></a>
                                <a href="add_custombutton.php" id="custombutton" class="iframe_medium css_button" title="<?php echo htmlspecialchars(xl('Add Buttons for Special Chars,Texts to be Displayed on Top of the Editor for inclusion to the text on a Click'),ENT_QUOTES);?>"><span><?php echo htmlspecialchars(xl('Add Buttons'),ENT_QUOTES);?></span></a>
                            </td>
                            <td valign=top style="width:700px;">
                                <textarea class="ckeditor" cols="100" id="textarea1" name="textarea1" rows="80"></textarea>
                            </td>                            
                        </tr>
                    </table>
                    </form>
                </div>     
                
            </td>
        </tr>
    <?php
        }
    else{
        echo htmlspecialchars(xl('NO SUCH CONTEXT NAME').$contextName,ENT_QUOTES);
    }
    ?>
    </table>
    <table>
    <script type="text/javascript">
    edit('<?php echo $type;?>');
    </script>
    </table>
</body>
</html>
