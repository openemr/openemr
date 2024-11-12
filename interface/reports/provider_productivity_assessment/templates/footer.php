<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

global $footerData;

?>

        <div class="row">
            <div class="col-md-12 mt-5">
                <div>
                    <footer>
                        <p>&copy; <?php echo $footerData['company'] . " " . $footerData['year'];?></p>
                    </footer>
                </div>
            </div>
        </div>
    </div>

<script>
    const helpFile = 'productivity_report_help.php';
</script>
    <?php
    require "../../../help_modal.php";
    ?>
</body>
</html>
