<?php

/**
 * Module to handle the 2015 ONC certification (b)(10) requirement for
 * Electronic Health Information Export.
 *
 * This module fulfills the following requirements:
 * (b)(10)(i)(A) - Enable a user to timely create an export file(s) with all
 *  of a single patient’s electronic health information stored at the time
 *  of certification by the product, of which the Health IT Module is a part.
 * (b)(10)(i)(B) -  A user must be able to execute this capability at any time
 *          the user chooses and without subsequent developer assistance
 *          to operate.
 * (b)(10)(i)(C) - Limit the ability of users who can create export file(s) in at
 *         least one of these two ways: (1) To a specific set of
 *         identified users (2) As a system administrative function
 *         [Module implements option 2].
 * (b)(10)(i)(D) - The export files(s) created must be electronic and in a
 *         computable format.
 * (b)(10)(i)(E) - The publicly accessible hyperlink of the export’s format must
 *         be included with the exported file(s).
 * (b)(10)(ii)   - Create an export of all the electronic health information
 *         that can be stored at the time of certification by product of
 *         which the Health IT Module is a part.
 *
 * For purposes of Electronic Health Information the information exported by
 * this module follows the ONC definition given which is:
 *  EHI means “electronic protected health information” (ePHI) as defined
 *  in 45 CFR 160.103 to the extent that it would be included in a
 *  designated record set as defined in 45 CFR 164.501, regardless of whether
 *  the group of records are used or maintained by or for a covered entity.
 *  But EHI does not include psychotherapy notes as defined in 45 CFR
 *  164.501 or information compiled in reasonable anticipation of, or for
 *  use in, a civil, criminal, or administrative action or proceeding.
 *
 * The regulation text for 45 CFR 160.103 defines "health information" as the
 * following:
 *  Health information means any information, including genetic information,
 *  whether oral or recorded in any form or medium, that: (1) Is created
 *  or received by a health care provider, health plan, public health
 *  authority, employer, life insurer, school or university, or health care
 *  clearinghouse; and (2) Relates to the past, present, or future physical
 *  or mental health or condition of an individual; the provision of health
 *  care to an individual; or the past, present, or future payment for the
 *  provision of health care to an individual.
 *
 * The regulation text for 45 CFR 160.103 defines "protected health information"
 * as the following:
 *  Protected health information means individually identifiable health
 *  information: (1) Except as provided in paragraph (2) of this definition,
 *  that is:
 *      (i) Transmitted by electronic media;
 *      (ii) Maintained in electronic media; or
 *      (iii) Transmitted or maintained in any other form
 *      or medium.
 *  (2) Protected health information excludes individually
 *  identifiable health information:
 *      (i) In education records covered by the Family Educational
 *      Rights and Privacy Act, as amended, 20 U.S.C. 1232g;
 *      (ii) In records described at 20 U.S.C. 1232g(a)(4)(B)(iv);
 *      (iii) In employment records held by a covered entity in its
 *      role as employer; and
 *      (iv) Regarding a person who has been deceased for more than 50
 *      years.
 *
 * OpenEMR does not have a way currently of distinguishing notes that are
 * specific to psychotherapy notes and which are to be excluded from the export.
 * Users wanting to keep their psychotherapy notes confidential that are stored
 * inside of OpenEMR will need to use a different mechanism than this export.
 *
 * OpenEMR does not have a way of marking records as education records.
 *
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Core\ModulesClassLoader;

/**
 * @global ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\EhiExporter\\", __DIR__ . DIRECTORY_SEPARATOR . "src");

/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */
$bootstrap = Bootstrap::instantiate($eventDispatcher, $GLOBALS['kernel']);
