<?php

namespace ESign;

/**
 * Encounter controller implementation
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
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
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

require_once $GLOBALS['srcdir'].'/ESign/Abstract/Controller.php';
require_once $GLOBALS['srcdir'].'/ESign/Encounter/Configuration.php';
require_once $GLOBALS['srcdir'].'/ESign/Encounter/Signable.php';
require_once $GLOBALS['srcdir'].'/ESign/Encounter/Log.php';

class Encounter_Controller extends Abstract_Controller
{   
    public function esign_is_encounter_locked()
    {
        $encounterId = $this->getRequest()->getParam( 'encounterId', '' );
        $signable = new Encounter_Signable( $encounterId );
        echo json_encode( $signable->isLocked() );
        exit;
    } 
    
    public function esign_form_view()
    {
        $form = new \stdClass();
        $form->table = 'form_encounter';
        $form->encounterId = $this->getRequest()->getParam( 'encounterid', 0 );
        $form->userId = $GLOBALS['authUserID'];
        $form->action = '#';
        $signable = new Encounter_Signable( $form->encounterId );
        $form->showLock = false;
        if ( $signable->isLocked() === false &&
            $GLOBALS['lock_esign_all'] &&
            $GLOBALS['esign_lock_toggle'] ) {
            $form->showLock = true;
        }
        
        $this->_view->form = $form;
        $this->setViewScript( 'encounter/esign_form.php' );
        $this->render();
    }
    
    public function esign_log_view()
    {
        $encounterId = $this->getRequest()->getParam( 'encounterId', '' );        
        $signable = new Encounter_Signable( $encounterId ); // Contains features that make object signable
        $log = new Encounter_Log( $encounterId ); // Make the log behavior
        $html = $log->getHtml( $signable );
        echo $html;
        exit;
    }
    
    /**
     * 
     * @return multitype:string
     */
    public function esign_form_submit()
    {
        $message = '';
        $status = self::STATUS_FAILURE;
        $password = $this->getRequest()->getParam( 'password', '' );
        $encounterId = $this->getRequest()->getParam( 'encounterId', '' );
        // Lock if 'Lock e-signed encounters and their forms' option is set, 
        // unless esign_lock_toggle option is enable in globals, then check the request param
        $lock = false;
        if ( $GLOBALS['lock_esign_all'] ) {
            $lock = true;
            if ( $GLOBALS['esign_lock_toggle'] ) {
                $lock = ( $this->getRequest()->getParam( 'lock', '' ) == 'on' ) ? true : false;
            }
        }
            
        $amendment = $this->getRequest()->getParam( 'amendment', '' );
        if ( confirm_user_password( $_SESSION['authUser'], $password ) ) {
            $signable = new Encounter_Signable( $encounterId );
            if ( $signable->sign( $_SESSION['authUserID'], $lock, $amendment ) ) {
                $message = xlt( "Form signed successfully" );
                $status = self::STATUS_SUCCESS;
            } else {
                $message = xlt( "An error occured signing the form" );
            }
        } else {
            $message = xlt( "The password you entered is invalid" );
        }
        $response = new Response( $status, $message );
        $response->encounterId = $encounterId;
        $response->locked = $lock;
        if ( $lock ) {
            $response->editButtonHtml = "<a href=# class='css_button_small form-edit-button-locked'><span>".xlt('Locked')."</span></a>";
        }
        echo json_encode( $response );
        exit;
    }
}
