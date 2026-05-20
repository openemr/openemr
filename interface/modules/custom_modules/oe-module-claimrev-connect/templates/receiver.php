<?php

 /**
  *
  * @package OpenEMR
  * @link    https://www.open-emr.org
  *
  * @author    Brad Sharp <brad.sharp@claimrev.com>
  * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

/** @var \stdClass|null $receiver */

declare(strict_types=1);

if ($receiver === null) {
    return;
}

$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};

$companyProviderCaption = "Company Name";
$companyProviderName = $str($receiver, 'lastOrganizationName');
if ($str($receiver, 'entityIdentifierCodeQualifier') === '1') {
    $companyProviderCaption = "Provider Name";
    $companyProviderName = $str($receiver, 'firstName') . " " . $str($receiver, 'middleName') . " " . $str($receiver, 'lastOrganizationName') . " " . $str($receiver, 'suffix');
}
?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo xlt("Receiver Information"); ?></h5>
            <div class="row">
                <div class="col">
                    <?php echo xlt($companyProviderCaption); ?>
                </div>
                <div class="col">
                    <?php echo text($companyProviderName); ?>
                </div>
                <div class="col">
                    <?php echo xlt("ID"); ?>
                </div>
                <div class="col">
                    <?php echo text($str($receiver, 'identifier')); ?>
                </div>
            </div>
        </div>
    </div>
