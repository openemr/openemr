<?php

/**
 *
 * Derived from phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

    $this->assign('title', xlt('Patient Portal'));
?>

<div class="container">

    <h1><?php echo xlt('Oh Snap!'); ?></h1>

    <!-- this is used by app.js for scraping -->
    <!-- ERROR <?php $this->eprint($this->message); ?> /ERROR -->

    <h3 onclick="$('#stacktrace').show('slow');" class="jumbotron jumbotron-fluid" style="cursor: pointer;"><?php $this->eprint($this->message); ?></h3>

    <p><?php echo xlt('You may want to try returning to the the previous page and verifying that all fields have been filled out correctly.'); ?></p>

    <p><?php echo xlt('If you continue to experience this error please contact support.'); ?></p>

    <div id="stacktrace" class="jumbotron jumbotron-fluid hide">
        <p class="font-weight-bold"><?php echo xlt('Stack Trace'); ?>:</p>
        <?php if ($this->stacktrace) { ?>
            <p style="white-space: nowrap; overflow: auto; padding-bottom: 15px; font-family: 'Courier New', 'Courier', sans-serif; font-size: 11px;"><pre><?php $this->eprint($this->stacktrace); ?></pre></p>
        <?php } ?>
    </div>

</div> <!-- /container -->

<?php
    $this->display('_Footer.tpl.php');
?>
