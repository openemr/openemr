<?php
/**
 * Paytrace Payment Gateway
 * link    http://www.open-emr.org
 * author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 * license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Paytrace\Controllers;


class AppDispatch
{
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    protected $crypto;
    protected $_currentAction, $_defaultModel;
    static $_apiService;
    private $_credentials, $authUser;
    const ACTION_DEFAULT = 'index';

    public function __construct()
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session = &$_SESSION;
        $this->_credentials = array(
            'username' => '',
            'extension' => '',
            'password' => '',
            'appKey' => '',
            'appSecret' => '',
            'server' => '',
            'portal' => '',
            'smsNumber' => '',
            'production' => '',
            'redirect_url' => '',
            'smsHours' => "50",
            'smsMessage' => "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.",
        );
        //$this->crypto = new CryptoGen();
        $this->authUser = $this->getSession('authUser');
        $this->dispatchActions();
        $this->render();
    }

    private function indexAction()
    {
        return null;
    }


}
