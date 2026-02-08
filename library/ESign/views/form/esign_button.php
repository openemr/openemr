<?php

/**
 * ESign button view script for form module
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<a target="<?php echo $this->target; ?>" href="#esign-mask-content" class="esign-button-form btn btn-text btn-sm" data-formdir="<?php echo attr($this->formDir); ?>" data-formid="<?php echo attr($this->formId); ?>" data-encounterid="<?php echo attr($this->encounterId); ?>"><i class="fa fa-signature"></i>&nbsp;<?php echo xlt('eSign'); ?></a>
