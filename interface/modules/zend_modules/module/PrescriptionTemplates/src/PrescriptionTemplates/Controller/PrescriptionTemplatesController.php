<?php

/**
 * Copyright (C) 2018 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

namespace PrescriptionTemplates\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PrescriptionTemplatesController extends AbstractActionController
{

    /**
     * Create html page for 'default' template (match also for pdf)
     */
    protected function getDefaultTemplate($id)
    {
        $ids = preg_split('/::/', substr($id, 1, strlen($id) - 2), -1, PREG_SPLIT_NO_EMPTY);
        $prescriptions = array();
        foreach ($ids as $id) {
            $p = new \Prescription($id);

            if (!isset($prescriptions[$p->provider->id])) {
                $prescriptions[$p->provider->id] = array();
            }

            $prescriptions[$p->provider->id][] = $p;
        }
        $patient = $p->patient;

        $defaultHtml = new ViewModel(array('patient' => $patient, 'prescriptions' => $prescriptions, 'langDir' => $_SESSION['language_direction']));
        $defaultHtml->setTemplate("prescription-templates/default.phtml");

        return $defaultHtml;
    }
}
