<?php

/**
 * ESign button view script for encounter module
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
<a target="<?php echo attr($this->target); ?>" href="#" class="esign-button-encounter btn btn-primary" data-encounterid="<?php echo attr($this->encounterId); ?>"><?php echo xlt('eSign'); ?></a>
