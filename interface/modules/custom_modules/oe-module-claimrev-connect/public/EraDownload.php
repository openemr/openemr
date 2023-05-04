<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
    require_once "../../../../globals.php";

    use OpenEMR\Modules\ClaimRevConnector\EraPage;

    $eraId = $_GET['eraId'];
    $fileViewModel = EraPage::downloadEra($eraId);

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Length: " . strlen($fileViewModel->fileText));
    header("Content-Disposition: attachment; filename=" . $fileViewModel->fileName  . ";");
    header("Content-Description: File Transfer");
    echo text($fileViewModel->fileText);
    exit(0);
