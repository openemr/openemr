<script type="text/javascript">
    // This invokes the find-patient popup.
    function openrecord() {
        dlgopen('<?php echo $GLOBALS['web_root']; ?>/record/record.php', '_blank', 1100, 600);
    }

    $(document).ready(function(){
        $("#recordWBtn").click(function(){
            openrecord();
        });
    });
    
</script>

<style type="text/css">
    #recordWBtn {
        position: fixed;
        bottom: 20px;
        z-index: 100;
        right: 30px;
    }
</style>