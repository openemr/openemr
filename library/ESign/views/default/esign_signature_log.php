<?php
/**
 * default signature log view script
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/
?>
<div id='esign-signature-log-<?php echo attr($this->logId); ?>' class='esign-signature-log-container'>
    <div class="esign-signature-log-table">
    
        <div class="body_title esign-log-row header"><?php echo xlt('eSign Log'); ?></div>
        
        <?php if (!$this->verified) { ?>
        <div class="esign-log-row">
            <div style='text-align:center;color:red;'><?php echo xlt('The data integrity test failed for this form'); ?></div>
        </div>
        <?php } ?>
        
        <?php foreach ($this->signatures as $count => $signature) { ?>
        <div class="esign-log-row esign-log-row-container <?php echo text($signature->getClass()); ?>">
            
            <?php if ($signature->getAmendment()) { ?>
            <div class="esign-log-row">
                <span class="esign-amendment"><?php echo text($signature->getAmendment()); ?></span>
            </div>
            <?php } ?>
            
            <div class="esign-log-row">
                <div class="esign-log-element span3"><span><?php echo text($signature->getFirstName()); ?></span></div> 
                <div class="esign-log-element span3"><span><?php echo text($signature->getLastName()); ?></span></div> 
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
