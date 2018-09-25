<?php
  /**
 * expand contract jquery script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
    ?>
$( document ).ready(function() {
    $('.expand_contract').click(function() {
        var elementTitle = $(this).prop('title');
        var contractTitle = '<?php echo xla('Click to Contract'); ?>';
        var expandTitle = '<?php echo xla('Click to Expand'); ?>';
        if (elementTitle == contractTitle) {
            elementTitle = expandTitle;
            $(this).toggleClass('fa-expand fa-compress');
            $('.expandable').toggleClass('container container-fluid');
            $('#form_current_state').attr('value', 0);
        } else if (elementTitle == expandTitle) {
            elementTitle = contractTitle;
            $(this).toggleClass('fa-compress fa-expand');
            $('.expandable').toggleClass('container-fluid container');
            $('#form_current_state').attr('value', 1);
        }
        $(this).prop('title', elementTitle);
        
        // to assign current status to anchor tag as GET string
        var getCurrStatus = $('#form_current_state').val();
        if ($('<?php echo attr($target_div); ?>').length) {
            $('<?php echo attr($target_div); ?> a[href]').each(function () {
                var $this = $(this);
                var href = $this.attr('href');
                if (href.indexOf('get_current_status') >=0) {
                    // to replace the GET string if it exists along with its value
                    if (href.indexOf('&get_current_status') >=0){
                        n = href.indexOf('&get_current_status');
                    } else {
                        n = href.indexOf('get_current_status');
                    }    
                    hrefPre = href.substring(0, n);
                    hrefPost = href.substring(n + 20); 
                    href = hrefPre + hrefPost;
                    if (href.substring (href.length - 1) == '&') {
                        newHref = href + "get_current_status=" + getCurrStatus;
                    } 
                    else if (href.substring (href.length - 1) == '?') {
                        newHref = href + "get_current_status=" + getCurrStatus;
                   } 
                    else {
                        newHref = href + "&get_current_status=" + getCurrStatus;
                    }
                    $this.attr('href', newHref);
                } else {
                    $this.attr('href', href + "get_current_status=" + getCurrStatus);
                }
            })
        }
    });
});
