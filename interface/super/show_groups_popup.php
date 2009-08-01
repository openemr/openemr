<?php
 /*
  * This popup is called when choosing a group into which to move fields
  */

include_once("../globals.php");

?>

<html>
<head>
<?php html_header_show();?>
<title><?php xl('List groups','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<style>
h1 {
    font-size: 120%;
    padding: 3px;
    margin: 3px;
}
ul {
    list-style: none;
    padding: 3px;
    margin: 3px;
}
li {
    cursor: pointer;
    border-bottom: 1px solid #ccc;
    background-color: white;
}
.highlight {
    background-color: #336699;
    color: white;
}
.translation {
    color: green;
    font-size:10pt;
}
</style>

</head>

<body class="body_top text">
<div id="groups">
<h1><?php xl('Layout groups'); ?></h1>
<ul>
<?php
$res = sqlStatement("SELECT distinct(group_name) FROM layout_options WHERE " .
                    "form_id = '".$_GET['layout_id']."' ORDER BY group_name");
while ($row = sqlFetchArray($res)) {
    $gname = preg_replace("/^\d+/", "", $row['group_name']);
    $xlgname = "";
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
      $xlgname = "<span class='translation'>>>&nbsp; " . xl($gname) . "</span>";
    }
    echo "<li id='".$row['group_name']."' class='oneresult'> $gname $xlgname </li>";
}
?>
</ul>
</div>
</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectItem(this); });

    var SelectItem = function(obj) {
        var groupname = $(obj).attr("id");
        if (opener.closed)
            alert('The destination form was closed; I cannot act on your selection.');
        else
            opener.MoveFields(groupname);
        window.close();
        return false;
    };

});

</script>

</html>
