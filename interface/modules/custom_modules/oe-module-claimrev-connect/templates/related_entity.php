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

/** @var \stdClass $benefit */

declare(strict_types=1);

$relatedEntities = property_exists($benefit, 'relatedEntities') && is_iterable($benefit->relatedEntities) ? $benefit->relatedEntities : null;
if ($relatedEntities === null) {
    return;
}

$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};
?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6> <?php echo xlt("Related Entity"); ?></h6>
    <?php
    foreach ($relatedEntities as $relatedEntity) {
        if (!is_object($relatedEntity)) {
            continue;
        }
        $entityQualifier = $str($relatedEntity, 'entityIdentifierCodeQualifier');
        $taxonomyCode = $str($relatedEntity, 'taxonomyCode');
        $address = property_exists($relatedEntity, 'address') && is_object($relatedEntity->address) ? $relatedEntity->address : null;
        $contacts = property_exists($relatedEntity, 'contacts') && is_iterable($relatedEntity->contacts) ? $relatedEntity->contacts : null;
        if ($entityQualifier === '2') {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Organization Name"); ?>
                                </dt>
                                <dd class="col">
                    <?php echo text($str($relatedEntity, 'lastOrganizationName')); ?>
                                </dd>
                            <dl>
            <?php
        }
        if ($entityQualifier === '1') {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Name"); ?>
                                </dt>
                                <dd class="col">
                    <?php echo text($str($relatedEntity, 'firstName')); ?> <?php echo text($str($relatedEntity, 'middleName')); ?> <?php echo text($str($relatedEntity, 'lastOrganizationName')); ?> <?php echo text($str($relatedEntity, 'suffix')); ?>
                                </dd>
                            <dl>
                            <dl class="row">
                                <dt class="col">

                                </dt>
                                <dd class="col">
                    <?php echo text($str($relatedEntity, 'identifier')); ?>
                                </dd>
                            <dl>
            <?php
        }
        ?>
                    <dl class="row">
                        <dt class="col">
            <?php echo xlt("Address"); ?>
                        </dt>
                        <dd class="col">
                            <div class="row">
                                <div class="col">
                    <?php echo $address !== null ? text($str($address, 'address1')) : ''; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                    <?php echo $address !== null ? text($str($address, 'address2')) : ''; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                    <?php echo $address !== null ? text($str($address, 'city')) : ''; ?>
                                </div>
                                <div class="col">
                    <?php echo $address !== null ? text($str($address, 'state')) : ''; ?>
                                </div>
                                <div class="col">
                    <?php echo $address !== null ? text($str($address, 'zip')) : ''; ?>
                                </div>
                            </div>
                        </dd>
                    </dl>
        <?php
        if ($taxonomyCode !== '') {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Taxonomy Code"); ?>

                                </dt>
                                <dd class="col">
                                    (<?php echo text($str($relatedEntity, 'taxonomyProviderCode')); ?>) <?php echo text($taxonomyCode); ?>
                                </dd>
                            <dl>
            <?php
        }
        if ($contacts !== null) {
            foreach ($contacts as $c) {
                if (!is_object($c)) {
                    continue;
                }
                $contactMethods = property_exists($c, 'contactMethods') && is_iterable($c->contactMethods) ? $c->contactMethods : [];
                ?>
                                <dl class="row">
                                    <dt class="col">
                        <?php echo xlt("Contact Name"); ?>
                                    </dt>
                                    <dt class="col">
                        <?php echo text($str($c, 'contactName')); ?>
                <?php
                foreach ($contactMethods as $m) {
                    if (!is_object($m)) {
                        continue;
                    }
                    ?>
                                                <dl class="row">
                                                    <dt class="col">
                                <?php echo text($str($m, 'contactType')); ?>
                                                    </dt>
                                                    <dt class="col">
                                <?php echo text($str($m, 'contactValue')); ?>
                                                    </dt>
                                                </dl>
                    <?php
                }
                ?>


                                    </dt>
                                </dl>
                <?php
            }
        }
    }//end foreach


    ?>
                </div>
            </div>
        </div>
    </div>
