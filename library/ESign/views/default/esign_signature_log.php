<?php

/**
 * default signature log view script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Logging\EventAuditLogger;

?>
<div id='esign-signature-log-<?php echo attr($this->logId); ?>' class='esign-signature-log-container'>
    <div class="esign-signature-log-table">

        <div class="esign-log-row header"><?php echo xlt('eSign Log'); ?></div>

        <?php if (!$this->verified) {
            EventAuditLogger::getInstance()->newEvent(
                "esign",
                $_SESSION['authUser'],
                $_SESSION['authProvider'],
                0,
                'Esign data integrity test failed for a form in encounter ' . $_SESSION['encounter'],
                $_SESSION['pid']
            );
        } ?>

        <?php foreach ($this->signatures as $signature) { ?>
        <div class="esign-log-row esign-log-row-container <?php echo text($signature->getClass()); ?>">

            <?php if ($signature->getAmendment()) { ?>
            <div class="esign-log-row">
                <span class="esign-amendment"><?php echo text($signature->getAmendment()); ?></span>
            </div>
            <?php } ?>

            <div class="esign-log-row">
                <div class="esign-log-element span3"><span><?php echo text($signature->getFirstName()); ?></span></div>
                <div class="esign-log-element span3"><span><?php echo text($signature->getLastName()); ?></span></div>
                <div class="esign-log-element span3"><span><?php echo text($signature->getSuffix()); ?></span></div>
                <div class="esign-log-element span3"><span><?php echo text($signature->getValedictory()); ?></span></div>
                <div class="esign-log-element span3"><span><?php echo text($signature->getDatetime()); ?></span></div>
            </div>

        </div>
        <?php } ?>

        <?php if (count($this->signatures) === 0) { ?>
        <div class="esign-log-row">
            <span><?php echo xlt('No signatures on file'); ?></span>
        </div>
        <?php } ?>

    </div>
</div>
