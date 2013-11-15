<?php

namespace ESign;

/**
 * Abstract implementation of the ESign controller. Implement the
 * rest of me to create your own controller.
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
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

require_once $GLOBALS['srcdir'].'/ESign/Viewer.php';
require_once $GLOBALS['srcdir'].'/ESign/ViewableIF.php';

abstract class Abstract_Controller implements ViewableIF
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    
    protected $_method = null;
    protected $_params = null;
    protected $_viewDir = null;
    protected $_viewScript = null;
    protected $_viewer = null;
    protected $_request = null;
    
    public function __construct( Request $request )
    {
        $this->_request = $request;
        $this->_method = $this->_request->getParam( 'method' );     
        $this->_viewDir = $GLOBALS['srcdir']."/ESign/views";
        $this->_viewScript = 'esign_error.php';
        $this->_view = new Viewer();
    }
    
    /**
     * Triggered when the module's ESign/ButtonIF is clicked.
     * The controller method gets all the parameters that match
     * data-* within the button's attributes.
     */
    public abstract function esign_form_view();
    
    /**
     * Triggered when the module's form is saved (refresh
     * the log.)
     */
    public abstract function esign_log_view();
    
    /**
     * Triggered when the ESign Sigature form is submitted
     */
    public abstract function esign_form_submit();
    
    protected function getRequest()
    {
        return $this->_request;
    }
    
    protected function setViewScript( $viewScript )
    {
        $this->_viewScript = $viewScript;
    }
    
    public function getViewScript()
    {
        return $this->_viewDir.DIRECTORY_SEPARATOR.$this->_viewScript;
    }
    
    public function run()
    {
        if ( method_exists( $this, $this->_method) ) {
            $this->{$this->_method}();
        } else {
            throw new \Exception( "The method ".$this->_method." does not exist and cannot be executed" );
        }
        
    }
    
    public function getHtml()
    {
        return $this->_view->getHtml( $this );
    }
    
    public function render()
    {
        return $this->_view->render( $this );
    }

}

class Request
{
    public function __construct()
    {
        $this->parseParams();
    }
    
    public function getParam( $key, $default = '' )
    {
        if ( isset( $this->_params[$key] ) ) {
            return $this->_params[$key];
        }
    
        return $default;
    }
    
    protected function parseParams()
    {
        foreach ( $_REQUEST as $key => $value ) {
            $this->_params[$key] = $value;
        }
    }
}

class Response
{
    public $status = null;
    public $message = null;
    
    public function __construct( $status, $message )
    {
        $this->status = $status;
        $this->message = $message;
    }
}
