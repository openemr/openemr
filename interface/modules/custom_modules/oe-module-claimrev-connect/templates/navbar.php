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

/** @var string $tab */

declare(strict_types=1);

?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><?php echo xlt("ClaimRev Connect"); ?> </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?php if ($tab == "home") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="index.php"><?php echo xlt("Home"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "claims") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="claims.php"><?php echo xlt("Claims"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "eras") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="era.php"><?php echo xlt("ERAs"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "payments") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="payment_advice.php"><?php echo xlt("Payment Advice"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "appointments") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="appointments.php"><?php echo xlt("Appointments"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "reconciliation") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="reconciliation.php"><?php echo xlt("Reconciliation"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "patient_balance") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="patient_balance.php"><?php echo xlt("Patient Balance"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "claim_status") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="claim_status.php"><?php echo xlt("Claim Status"); ?></a>
            </li>
            <li class="nav-item dropdown <?php if (in_array($tab, ['aging_report', 'denial_analytics', 'recoupment_report'])) {
                echo "active";
                                         } ?>">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"><?php echo xlt("Analytics"); ?></a>
                <div class="dropdown-menu">
                    <a class="dropdown-item <?php echo ($tab == 'aging_report') ? 'active' : ''; ?>" href="aging_report.php"><?php echo xlt("AR Aging Report"); ?></a>
                    <a class="dropdown-item <?php echo ($tab == 'denial_analytics') ? 'active' : ''; ?>" href="denial_analytics.php"><?php echo xlt("Denial Analytics"); ?></a>
                    <a class="dropdown-item <?php echo ($tab == 'recoupment_report') ? 'active' : ''; ?>" href="recoupment_report.php"><?php echo xlt("Recoupment Report"); ?></a>
                </div>
            </li>
            <li class="nav-item <?php if ($tab == "x12") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="x12Tracker.php"><?php echo xlt("X12 Tracker"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "setup") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="setup.php"><?php echo xlt("Setup"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "connectivity") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="debug-info.php"><?php echo xlt("Connectivity"); ?></a>
            </li>
        </ul>
    </div>
</nav>
