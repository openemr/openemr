<?php
    /**
     * /library/MedEx/MedEx_cron.php
     *
     * This file is not active.
     * To utilize this as a cron job, you should move it to a directory outside of the apache2 filepath.
     * You will need to correct the directory links to the "require_once" files, depending on where you place them.
     * Ensure the file is executable.
     *
     *  LARGE PRACTICES SHOULD DISABLE MedEx in background_services table and instead use this file w/ cron
     *  every 5 minutes as a suggested frequency to ensure medex_outgoing table is up-to-date.
     *
     * @package MedEx
     * @link    http://www.MedExBank.com
     * @author  MedEx <support@MedExBank.com>
     * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    
    $ignoreAuth=true;
    $_SERVER['HTTP_HOST']   = 'default'; //change for multi-site
    
    require_once(dirname(__FILE__)."/../../interface/globals.php");
    require_once(dirname(__FILE__)."/../patient.inc");
    require_once(dirname(__FILE__)."/../log.inc");
    require_once(dirname(__FILE__)."/API.php");
    
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $response = $MedEx->login('1');