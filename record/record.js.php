<script type="text/javascript">
    var activenote  = null;

    // This invokes the find-patient popup.
    function openrecord() {
        dlgopen('<?php echo $GLOBALS['web_root']; ?>/record/record.php', '_blank', 1100, 600);
    }

    function setnotevalue(note) {
        if(activenote && note != "") {
            activenote.value = note;
        }
    }

    $(document).ready(function(){
        $("#recordWBtn").click(function(){
            openrecord();
        });
    });

    var targetNode = document.querySelector('.frameDisplay', document.getElementById("frameDisplay"));
    const waitForElement = async (selector, rootElement = document.documentElement) => {
        return new Promise((resolve) => {
            const observer = new MutationObserver(() => {
                const element = document.querySelectorAll(selector);

                if (element) {
                    for (var i = 0; i < element.length; i++) {
                        if(element[i].style.display != 'none'){
                            var iframeEle = element[i].querySelector('iframe');
                            if(iframeEle) {
                                var jiframe = $(iframeEle).contents();
                                jiframe.find(".form-control").click(function(){
                                    activenote = $(this)[0];
                                });
                            }
                        }
                    }
                }
            });
          
            observer.observe(rootElement, {
                attributes: true, childList: true, subtree: true
            });
        });
    };

    $(document).ready(function() {
        waitForElement(".frameDisplay", document.getElementById("framesDisplay"));
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