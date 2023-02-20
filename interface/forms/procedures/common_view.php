<?php
$chp_printed = false;
$chp_printed = PrintChapter($ftitle,$chp_printed);

if($chp_printed) CloseChapter();
?>
