<?php
/** @package Openemr::Controller */

/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

/** import supporting libraries */
require_once("AppBaseController.php");

/**
 * DefaultController is the entry point to the application
 *
 * @package Openemr::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class ProviderController extends AppBaseController
{
    /**
     * Override here for any controller-specific functionality
     */
    protected function Init()
    {
        parent::Init();

        // $this->RequirePermission(SecureApp::$PERMISSION_USER,'SecureApp.LoginForm');
    }

    /**
     * Display the home page for the application
     */
    public function Home()
    {
        $cpid=$cuser=0;
        if (isset($_SESSION['authUserID'])) {
            $cuser = $_SESSION['authUserID'];
        } else {
            header("refresh:5;url= ./provider");
            echo 'Shared session not allowed with Portal!!!  <br>Onsite portal is using this session<br>Waiting until Onsite Portal is logged out........';
            exit;
        }

        $this->Assign('cpid', $GLOBALS['pid']);
        $this->Assign('cuser', $cuser);

        $this->Render();
    }

    /**
     * Displayed when an invalid route is specified
     */
    public function Error404()
    {
        $this->Render();
    }

    /**
     * Display a fatal error message
     */
    public function ErrorFatal()
    {
        $this->Render();
    }

    public function ErrorApi404()
    {
        $this->RenderErrorJSON('An unknown API endpoint was requested.');
    }
}
