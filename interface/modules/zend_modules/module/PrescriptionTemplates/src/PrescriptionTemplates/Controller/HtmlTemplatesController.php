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

use Interop\Container\ContainerInterface;
use Laminas\View\Model\ViewModel;

/**
 * Class HtmlTemplatesController
 * Here you can add custom html template for print prescription.
 * How to -
 * 1. create new action function (syntax <VIEW_NAME>Action) in ths controller that load custom view
 * 2. in the 'globals settings' screen go to 'Rx' tab and save your action in the 'Name of zend template for html print' label
 * @package PrescriptionTemplates\Controller
 */
class HtmlTemplatesController extends PrescriptionTemplatesController
{
    public function defaultAction()
    {
        $id = $this->params()->fromQuery('id');
        return $this->getDefaultTemplate($id);
    }
}
