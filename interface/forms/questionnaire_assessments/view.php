<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Symfony\Component\HttpFoundation\Request;

$mode = 'update';
$request = Request::createFromGlobals();
$form_id = $request->query->getInt('id');
require("questionnaire_assessments.php");
