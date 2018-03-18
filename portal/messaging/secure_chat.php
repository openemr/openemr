<?php
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

namespace SMA_Common;

 session_start();
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['pid']);
} else {
    session_destroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    if (! isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: '.$landingpage);
        exit;
    }

    define('IS_DASHBOARD', $_SESSION['authUserID']);
    define('IS_PORTAL', false);
    $_SERVER[REMOTE_ADDR] = 'admin::' . $_SERVER[REMOTE_ADDR];
}

define('C_USER', IS_PORTAL ?  IS_PORTAL : IS_DASHBOARD);

if (isset($_REQUEST['fullscreen'])) {
    $_SESSION['whereto'] = 'messagespanel';
    define('IS_FULLSCREEN', true);
} else {
    define('IS_FULLSCREEN', false);
}

define('CHAT_HISTORY', '150');
define('CHAT_ONLINE_RANGE', '1');
define('ADMIN_USERNAME_PREFIX', 'adm_');

abstract class Model
{
    public $db;

    public function __construct()
    {
        //$this->db = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    }
}

abstract class Controller
{
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    protected $_currentAction, $_defaultModel;

    const ACTION_POSTFIX = 'Action';
    const ACTION_DEFAULT = 'indexAction';

    public function __construct()
    {
        $this->_request  = &$_REQUEST;
        $this->_query    = &$_GET;
        $this->_post     = &$_POST;
        $this->_server   = &$_SERVER;
        $this->_cookies  = &$_COOKIE;
        $this->_session  = &$_SESSION;
        $this->init();
    }

    public function init()
    {
        $this->dispatchActions();
        $this->render();
    }

    public function dispatchActions()
    {
        $action = $this->getQuery('action');
        if ($action && $action .= self::ACTION_POSTFIX) {
            if (method_exists($this, $action)) {
                $this->setResponse(
                    call_user_func(array($this, $action), array())
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
            }
        } else {
            $this->setResponse(
                call_user_func(array($this, self::ACTION_DEFAULT), array())
            );
        }

        return $this->_response;
    }

    public function render()
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new \Exception('Response content must be scalar');
            }

            exit;
        }
    }

    public function indexAction()
    {
        return null;
    }

    public function setResponse($content)
    {
        $this->_response = $content;
    }

    public function setHeader($params)
    {
        if (! headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach ($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }

        return $this;
    }

    public function setModel($namespace)
    {
        $this->_defaultModel = $namespace;
        return $this;
    }

    public function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function setCookie($key, $value, $seconds = 3600)
    {
        $this->_cookies[$key] = $value;
        if (! headers_sent()) {
            setcookie($key, $value, time() + $seconds);
            return $this;
        }
    }

    public function getRequest($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_request[$param]) ?
                $this->_request[$param] : $default;
        }

        return $this->_request;
    }

    public function getQuery($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_query[$param]) ?
                $this->_query[$param] : $default;
        }

        return $this->_query;
    }

    public function getPost($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_post[$param]) ?
                $this->_post[$param] : $default;
        }

        return $this->_post;
    }

    public function getServer($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_server[$param]) ?
                $this->_server[$param] : $default;
        }

        return $this->_server;
    }

    public function getSession($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_session[$param]) ?
                $this->_session[$param] : $default;
        }

        return $this->_session;
    }

    public function getCookie($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_cookies[$param]) ?
                $this->_cookies[$param] : $default;
        }

        return $this->_cookies;
    }

    public function getUser()
    {
        return $this->_session['ptName'] ? $this->_session['ptName'] : $this->_session['authUser'];
    }
    public function getIsPortal()
    {
        return IS_PORTAL;
    }
    public function getIsFullScreen()
    {
        return IS_FULLSCREEN;
    }
    public function getModel()
    {
        if ($this->_defaultModel && class_exists($this->_defaultModel)) {
            return new $this->_defaultModel;
        }
    }

    public function sanitize($string, $quotes = ENT_QUOTES, $charset = 'utf-8')
    {
        return htmlentities($string, $quotes, $charset);
    }
}

abstract class Helper
{

}

namespace SMA_Msg;

// @codingStandardsIgnoreStart
use SMA_Common;
// @codingStandardsIgnoreEnd
class Model extends SMA_Common\Model
{
    public function getAuthUsers()
    {
        $response = sqlStatementNoLog("SELECT patient_data.pid as recip_id, Concat_Ws(' ', patient_data.fname, patient_data.lname) as username FROM patient_data WHERE allow_patient_portal = 'YES'");
        $resultpd = array ();
        while ($row = sqlFetchArray($response)) {
            $resultpd[] = $row;
        }

        $response = sqlStatementNoLog("SELECT users.id as recip_id, users.authorized as dash, CONCAT(users.fname,' ',users.lname) as username  FROM users WHERE authorized = 1");
        $result = array ();
        while ($row = sqlFetchArray($response)) {
            $result[] = $row;
        }

        return json_encode(array_merge($result, $resultpd));
    }
    public function getMessages($limit = CHAT_HISTORY, $reverse = true)
    {
        $response = sqlStatementNoLog("(SELECT * FROM onsite_messages
            ORDER BY `date` DESC LIMIT {$limit}) ORDER BY `date` ASC");

        $result = array();
        while ($row = sqlFetchArray($response)) {
            if (IS_PORTAL) {
                $u = json_decode($row['recip_id'], true);
                if (!is_array($u)) {
                    continue;
                }

                if ((in_array(C_USER, $u)) || $row['sender_id'] == C_USER) {
                     $result[] = $row; // only current patient messages
                }
            } else {
                $result[] = $row; // admin gets all
            }
        }

        return $result;
    }

    public function addMessage($username, $message, $ip, $senderid = 0, $recipid = '')
    {
        return sqlQueryNoLog("INSERT INTO onsite_messages VALUES (NULL, ?, ?, ?, NOW(), ?, ?)", array($username,$message,$ip,$senderid,$recipid));
    }

    public function removeMessages()
    {
        return sqlQueryNoLog("TRUNCATE TABLE onsite_messages");
    }

    public function removeOldMessages($limit = CHAT_HISTORY)
    {
    /* @todo Patched out to replace with soft delete. Besides this query won't work with current ado(or any) */
        /* return sqlStatementNoLog("DELETE FROM onsite_messages
            WHERE id NOT IN (SELECT id FROM onsite_messages
                ORDER BY date DESC LIMIT {$limit})"); */
    }

    public function getOnline($count = true, $timeRange = CHAT_ONLINE_RANGE)
    {
        if ($count) {
            $response = sqlStatementNoLog("SELECT count(*) as total FROM onsite_online");
            return sqlFetchArray($response);
        }

        $response = sqlStatementNoLog("SELECT * FROM onsite_online");
        $result = array();
        while ($row = sqlFetchArray($response)) {
            $result[] = $row;
        }

        return $result;
    }

    public function updateOnline($hash, $ip, $username = '', $userid = 0)
    {
        return sqlStatementNoLog("REPLACE INTO onsite_online
            VALUES ( ?, ?, NOW(), ?, ? )", array($hash, $ip, $username, $userid)) or die(mysql_error());
    }

    public function clearOffline($timeRange = CHAT_ONLINE_RANGE)
    {
        return sqlStatementNoLog("DELETE FROM onsite_online
            WHERE last_update <= (NOW() - INTERVAL {$timeRange} MINUTE)");
    }

    public function __destruct()
    {
    }
}

class Controller extends SMA_Common\Controller
{
    protected $_model;

    public function __construct()
    {
        $this->setModel('SMA_Msg\Model');
        parent::__construct();
    }

    public function indexAction()
    {
    }
    public function authusersAction()
    {
        return $this->getModel()->getAuthUsers(true);
    }
    public function listAction()
    {
        $this->setHeader(array('Content-Type' => 'application/json'));
        $messages = $this->getModel()->getMessages();
        foreach ($messages as &$message) {
            $message['me'] = C_USER === $message['sender_id']; // $this->getServer('REMOTE_ADDR') === $message['ip'];
        }

        return json_encode($messages);
    }

    public function saveAction()
    {
        $username = $this->getPost('username');
        $message = $this->getPost('message');
        $ip = $this->getServer('REMOTE_ADDR');
        $this->setCookie('username', $username, 9999 * 9999);
        $recipid = $this->getPost('recip_id');

        if (IS_PORTAL) {
            $senderid = IS_PORTAL;
        } else {
            $senderid = IS_DASHBOARD;
        }

        $result = array('success' => false);
        if ($username && $message) {
            $cleanUsername = preg_replace('/^'.ADMIN_USERNAME_PREFIX.'/', '', $username);
            $result = array(
                'success' => $this->getModel()->addMessage($cleanUsername, $message, $ip, $senderid, $recipid)
            );
        }

        if ($this->_isAdmin($username)) {
            $this->_parseAdminCommand($message);
        }

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($result);
    }

    private function _isAdmin($username)
    {
        return IS_DASHBOARD?true:false;
        //return preg_match('/^'.ADMIN_USERNAME_PREFIX.'/', $username);
    }

    private function _parseAdminCommand($message)
    {
        if (strpos($message, '/clear') !== false) {
            $this->getModel()->removeMessages();
            return true;
        }

        if (strpos($message, '/online') !== false) {
            $online = $this->getModel()->getOnline(false);
            $ipArr = array();
            foreach ($online as $item) {
                $ipArr[] = $item->ip;
            }

            $message = 'Online: ' . implode(", ", $ipArr);
            $this->getModel()->addMessage('Admin Command', $message, '0.0.0.0');
            return true;
        }
    }

    private function _getMyUniqueHash()
    {
        $unique  = $this->getServer('REMOTE_ADDR');
        $unique .= $this->getServer('HTTP_USER_AGENT');
        $unique .= $this->getServer('HTTP_ACCEPT_LANGUAGE');
        $unique .= C_USER;
        return md5($unique);
    }

    public function pingAction()
    {
        $ip = $this->getServer('REMOTE_ADDR');
        $hash = $this->_getMyUniqueHash();
        $user = $this->getRequest('username', 'No Username');
        if ($user == 'currentol') {
            $onlines = $this->getModel()->getOnline(false);
            $this->setHeader(array('Content-Type' => 'application/json'));
            return json_encode($onlines);
        }

        if (IS_PORTAL) {
            $userid = IS_PORTAL;
        } else {
            $userid = IS_DASHBOARD;
        }

        $this->getModel()->updateOnline($hash, $ip, $user, $userid);
        $this->getModel()->clearOffline();
       // $this->getModel()->removeOldMessages(); // @todo For soft delete when I decide. DO NOT REMOVE

        $onlines = $this->getModel()->getOnline();

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($onlines);
    }
}

$msgApp = new Controller();
?>
<!doctype html>
<html ng-app="MsgApp">
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">

    <title><?php echo xlt('Secure Patient Chat'); ?></title>
    <meta name="author" content="Jerry Padgett sjpadgett{{at}} gmail {{dot}} com">

    <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>

    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <?php if ($_SESSION['language_direction'] == 'rtl') { ?>
        <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />
    <?php } ?>

    <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/summernote-0-8-2/dist/summernote.css" />
    <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/summernote-0-8-2/dist/summernote.js"></script>

    <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-1-5-8/angular.min.js"></script>
    <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-summernote-0-8-1/dist/angular-summernote.js"></script>
     <script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-sanitize-1-5-8/angular-sanitize.min.js"></script>
    <script src='<?php echo $GLOBALS['assets_static_relative']; ?>/checklist-model-0-10-0/checklist-model.js'></script>

</head>
<script type="text/javascript">
(function() {
    var MsgApp = angular.module('MsgApp',['ngSanitize','summernote',"checklist-model"]);
    MsgApp.config(function( $compileProvider ) {
            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|file|ftp|blob):|data:image\//);
          }
        );
    MsgApp.directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function (){
                        scope.$eval(attrs.ngEnter);
                    });
                    event.preventDefault();
                }
            });
        };
    });
    MsgApp.directive('tooltip', function(e){
        return {
            restrict: 'A',
            link: function(scope, element, attrs){
                $(element).hover(function(){
                    $(element).tooltip('show');
                }, function(){
                    $(element).tooltip('hide');
                });
            }
        };
    });
    MsgApp.filter('unique', function() {
        return function(collection, keyname) {
           var output = [],
               keys = [];
               angular.forEach(collection, function(item) {
               var key = item[keyname];
              if(keys.indexOf(key) === -1) {
                   keys.push(key);
                   output.push(item);
               }
           });
           return output;
        };
     });

    MsgApp.controller('MsgAppCtrl', ['$scope', '$http', '$filter', function($scope, $http, $filter) {
        $scope.urlListMessages = '?action=list'; // actions for restful
        $scope.urlSaveMessage = '?action=save';
        $scope.urlListOnlines = '?action=ping';
        $scope.urlGetAuthUsers = '?action=authusers';

        $scope.pidMessages = null;
        $scope.pidPingServer = null;

        $scope.beep = new Audio('beep.ogg'); // you've got mail!!!! really just a beep
        $scope.messages = [];
        $scope.online = null;
        $scope.lastMessageId = null;
        $scope.historyFromId = null;
        $scope.onlines = []; // all online users id and ip's
        $scope.user = "<?php echo $_SESSION['ptName'] ? $_SESSION['ptName'] : $_SESSION['authUser'];?>" // current user - dashboard user is from session authUserID
        $scope.userid = "<?php echo IS_PORTAL ? $_SESSION['pid'] : $_SESSION['authUserID'];?>"
        $scope.isPortal = "<?php echo IS_PORTAL;?>" ;
        $scope.isFullScreen = "<?php echo IS_FULLSCREEN; ?>";
        $scope.pusers = []; // selected recipients for chat
        $scope.chatusers = []; // authorize chat recipients for dashboard user
        $scope.me = {
            username: $scope.user,
            message: null,
            sender_id: $scope.userid,
            recip_id: 0
        };
        $scope.options =  {
            height: 200,
            focus: true,
            placeholder: 'Start typing your message...',
            //direction: 'rtl',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link','picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]
        };
       $scope.checkAll = function() {
           $scope.pusers = [];
           $scope.pusers = $scope.chatusers.map(function(item) { return item.recip_id; });
           $scope.getAuthUsers();
         };
        $scope.uncheckAll = function() {
              $scope.pusers = [];
              $scope.getAuthUsers();
         };
        $scope.makeCurrent = function(sel) {
             if( !sel.me ){
                $scope.pusers.splice(0, $scope.pusers.length);
                $scope.pusers.push(sel.sender_id);
             }
        };
        $scope.pageTitleNotificator = {
            vars: {
                originalTitle: window.document.title,
                interval: null,
                status: 0
            },
            on: function(title, intervalSpeed) {
                var self = this;
                if (! self.vars.status) {
                    self.vars.interval = window.setInterval(function() {
                        window.document.title = (self.vars.originalTitle == window.document.title) ?
                        title : self.vars.originalTitle;
                    },  intervalSpeed || 500);
                    self.vars.status = 1;
                }
            },
            off: function() {
                window.clearInterval(this.vars.interval);
                window.document.title = this.vars.originalTitle;
                this.vars.status = 0;
            }
        };

        $scope.editmsg = function() {
            $('.summernote').summernote();
        };

        $scope.saveedit = function() {
             var makrup = $('.summernote').summernote('code');
            $scope.me.message = makrup;
            $scope.saveMessage();
            $('.summernote').summernote('code', ''); //add this options to reset editor or not-default is persistant content
            //$('.summernote').summernote('destroy');
        };

        $scope.saveMessage = function(form, callback) {
            $scope.me.recip_id =  JSON.stringify(angular.copy($scope.pusers));
            var data = $.param($scope.me);
            if (! ($scope.me.username && $scope.me.username.trim())) {
                return $scope.openModal();
            }
            if (! ($scope.me.message && $scope.me.message.trim() &&
                   $scope.me.username && $scope.me.username.trim())) {
                return;
            }
            $scope.me.message = '';
            return $http({
                method: 'POST',
                url: $scope.urlSaveMessage,
                data: data,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function(data) {
                $scope.listMessages(true);
            });
        };

        $scope.replaceShortcodes = function(message) {
            var msg = '';
            msg = message.toString().replace(/(\[img])(.*)(\[\/img])/, "<img class='img-responsive' src='$2' />");
            msg = msg.toString().replace(/(\[url])(.*)(\[\/url])/, "<a href='$2'>$2</a>");
            msg = message.toString().replace("<img ", "<img class='img-responsive' ");
            return msg;
        };

        $scope.notifyLastMessage = function() {
            if (typeof window.Notification === 'undefined') {
                return;
            }
            window.Notification.requestPermission(function (permission) {
                var lastMessage = $scope.getLastMessage();
                if (permission == 'granted' && lastMessage && lastMessage.username) {
                    var notify = new window.Notification('Message notification from ' + lastMessage.username + ' : ', {
                        body: 'New message' //lastMessage.message
                    });
                    notify.onclick = function() {
                        window.focus();
                    };
                    notify.onclose = function() {
                        $scope.pageTitleNotificator.off();
                    };
                    var timmer = setInterval(function() {
                        notify && notify.close();
                        typeof timmer !== 'undefined' && window.clearInterval(timmer);
                    }, 10000);
                }
            });
        };

        $scope.getLastMessage = function() {
            return $scope.messages[$scope.messages.length - 1];
        };

        $scope.listMessages = function(wasListingForMySubmission) {
            return $http.post($scope.urlListMessages, {}).success(function(data) {
                $scope.messages = [];
                angular.forEach(data, function(message) {
                    message.message = $scope.replaceShortcodes(message.message);
                    $scope.messages.push(message);
                });

                var lastMessage = $scope.getLastMessage();
                var lastMessageId = lastMessage && lastMessage.id;

                if ($scope.lastMessageId !== lastMessageId) {
                    $scope.onNewMessage(wasListingForMySubmission);
                }
                $scope.lastMessageId = lastMessageId;
                if($scope.pusers == ''){ // overkill but may need later
                       angular.forEach($filter('unique')($scope.messages,'sender_id'), function(m,k){
                       var flg = false;
                       angular.forEach($scope.pusers, function(id) {
                               if(id == m.sender_id){ flg = true; }
                           });
                          if(!flg) $scope.pusers.push(m.sender_id);
                       });
               }
                $scope.getOnlines();
            });
        };

        $scope.onNewMessage = function(wasListingForMySubmission) {
            if ($scope.lastMessageId && !wasListingForMySubmission) {
                $scope.playAudio();
                $scope.pageTitleNotificator.on('New message');
                $scope.notifyLastMessage();
            }
            $scope.scrollDown();
            window.addEventListener('focus', function() {
                $scope.pageTitleNotificator.off();
            });
        };
        $scope.getAuthUsers = function() {
            $scope.chatusers = [];
            return $http.post($scope.urlGetAuthUsers, {}).success(function(data) {
                $scope.chatusers = data;
            });
        };
        $scope.pingServer = function(msgItem) {
            return $http.post($scope.urlListOnlines+'&username='+$scope.user, {}).success(function(data) {
                $scope.online = data;
            });

        };

        $scope.getOnlines = function() {
            return $http.post($scope.urlListOnlines+'&username=currentol', {}).success(function(data) {
                $scope.onlines = data;
            });
        };

        $scope.init = function() {
            $scope.listMessages();
            $scope.pidMessages = window.setInterval($scope.listMessages, 6000);
            $scope.pidPingServer = window.setInterval($scope.pingServer, 10000);
            $scope.getAuthUsers();
            $("#popeditor").on("show.bs.modal", function() {
              var height = $(window).height() - 200;
              $(this).find(".modal-body").css("max-height", height);
            });
        };

        $scope.scrollDown = function() {
            var pidScroll;
            pidScroll = window.setInterval(function() {
                $('.direct-chat-messages').scrollTop(window.Number.MAX_SAFE_INTEGER * 0.001);
                window.clearInterval(pidScroll);
            }, 100);
        };

        $scope.clearHistory = function() {
            var lastMessage = $scope.getLastMessage();
            var lastMessageId = lastMessage && lastMessage.id;
            lastMessageId = (lastMessageId-1 >= 2) ? lastMessageId -1 : lastMessageId;
            lastMessageId && ($scope.historyFromId = lastMessageId);
        };

        $scope.openModal = function(e) {
            var mi = $('#popeditor').modal({backdrop: "static"});
           //$scope.editmsg();
        };

        $scope.playAudio = function() {
            $scope.beep && $scope.beep.play();
        };

        $scope.renderMessageBody = function(html)
        {
            return html;
        };
        $scope.init();
    }]);
})();
</script>
<style>
.direct-chat-text {
    border-radius:5px;
    position:relative;
    padding:5px 10px;
    background:#FBFBFB;
    border:1px solid #C2C6CE;
    margin:5px 0 0 50px;
    color:#444;
}
.direct-chat-msg,.direct-chat-text {
    display:block;
    word-wrap: break-word;
}
.direct-chat-img {
    border-radius:50%;
    float:left;
    width:40px;
    height:40px;
}
.direct-chat-info {
    display:block;
    margin-bottom:2px;
    font-size:12px;
}
.direct-chat-msg {
    margin-bottom:5px;
}
.direct-chat-messages,.direct-chat-contacts {
    -webkit-transition:-webkit-transform .5s ease-in-out;
    -moz-transition:-moz-transform .5s ease-in-out;
    -o-transition:-o-transform .5s ease-in-out;
    transition:transform .5s ease-in-out;
}
.direct-chat-messages {
    -webkit-transform:translate(0,0);
    -ms-transform:translate(0,0);
    -o-transform:translate(0,0);
    transform:translate(0,0);
    padding: 5px;
    height: calc(100vh - 175px);
    /* height: 400px; */
    /*height:100%; */
    overflow:auto;
    word-wrap: break-word;
}
.direct-chat-text:before {
    border-width:6px;
    margin-top:-6px;
}
.direct-chat-text:after {
    border-width:5px;
    margin-top:-5px;
}
.direct-chat-text:after,.direct-chat-text:before {
    position:absolute;
    right:100%;
    top:15px;
    border:solid rgba(0,0,0,0);
    border-right-color:#D2D6DE;
    content:' ';
    height:0;
    width:0;
    pointer-events:none;
}
.direct-chat-warning .right>.direct-chat-text {
    background:#fef7ec;
    border-color:#F39C12;
    color:#000;
}
.right .direct-chat-text {
    margin-right:50px;
    margin-left:0;
}
.direct-chat-warning .right>.direct-chat-text:after,
.direct-chat-warning .right>.direct-chat-text:before {
    border-left-color:#F39C12;
}
.right .direct-chat-text:after,.right .direct-chat-text:before {
    right:auto;
    left:100%;
    border-right-color:rgba(0,0,0,0);
    border-left-color:#D2D6DE;
}
.right .direct-chat-img {
    float:right;
}
.box-footer {
    border-top-left-radius:0;
    border-top-right-radius:0;
    border-bottom-right-radius:3px;
    border-bottom-left-radius:3px;
    border-top:1px solid #F4F4F4;
    padding:10px 0;
    background-color:#FFF;
}
.direct-chat-name {
    font-weight:600;
}
.box-footer form {
    margin-bottom:10px;
}
input,button,.alert,.modal-content {
    border-radius: 0!important;
}
.ml10 {
    margin-left:10px;
}
.ml5 {
    margin-left:5px;
}
.sidebar{
    background-color: lightgrey;
    height:100%;
    margin-top:5px;
    margin-right:0;
    padding-right:5px;
    max-height: 530px;
    overflow: auto;
}
.rtsidebar{
    background-color: lightgrey;
    height:100%;
    margin-top:5px;
    margin-right:0;
}
.fixed-panel{
    height: 100%;
   padding: 5px 5px 0 5px;
}
label {display: block;}
legend{
font-size:14px;
margin-bottom:2px;
background:#fff;
}
.modal.modal-wide .modal-dialog {
  width: 75%;
}
.modal-wide .modal-body {
  overflow-y: auto;
}
</style>

<body ng-controller="MsgAppCtrl">
    <div class="container">
        <!-- <h2 class="hidden-xs">Secure Chat</h2> -->
        <div class="row">
            <div class="col-md-2 sidebar">
                <h4><span class="label label-danger"><?php echo xlt('In Current Chat'); ?></span></h4>
                <label ng-repeat="user in chatusers | unique : 'username'" ng-if="pusers.indexOf(user.recip_id) !== -1 && user.recip_id != me.sender_id">
                    <input type="checkbox" data-checklist-model="pusers" data-checklist-value="user.recip_id"> {{user.username}}
                </label>
                <h4><span class="label label-warning"><?php echo xlt('Authorized Users'); ?></span></h4>
                <span>
                    <button id="chkall" class="btn btn-xs btn-success" ng-show="!isPortal" ng-click="checkAll()" type="button"><?php echo xlt('All'); ?></button>
                    <button id="chknone" class="btn btn-xs btn-success" ng-show="!isPortal" ng-click="uncheckAll()" type="button"><?php echo xlt('None'); ?></button>
                </span>
                <label ng-repeat="user in chatusers | unique : 'username'" ng-show="!isPortal || (isPortal && user.dash)">
                    <input type="checkbox" data-checklist-model="pusers" data-checklist-value="user.recip_id"> {{user.username}}
                </label>
            </div>
            <div class="col-md-8 fixed-panel">
                <div class="panel panel-warning direct-chat direct-chat-warning">
                    <div class="panel-heading">
                        <div class="clearfix">
                            <a class="btn btn-sm btn-primary pull-right ml10" href=""
                                data-toggle="modal" data-target="#clear-history"><?php echo xlt('Clear history'); ?></a>
                            <a class="btn btn-sm btn-success pull-left ml10" href="./../patient/provider" ng-show="!isPortal"><?php echo xlt('Home'); ?></a>
                            <a class="btn btn-sm btn-success pull-left ml10" href="./../home.php" ng-show="isFullScreen"><?php echo xlt('Home'); ?></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="direct-chat-messages">
                            <div class="direct-chat-msg" ng-repeat="message in messages" ng-if="historyFromId < message.id" ng-class="{'right':!message.me}">
                                <div class="direct-chat-info clearfix">
                                    <span class="direct-chat-name"
                                        ng-class="{'pull-left':message.me, 'pull-right':!message.me}">{{message.username }}</span>
                                        <span class="direct-chat-timestamp "
                                        ng-class="{'pull-left':!message.me, 'pull-right':message.me}">{{message.date }}</span>
                                </div>
                                <img class="direct-chat-img" ng-show="!message.me"
                                    src="./../images/Unknown-person.gif"
                                    alt="">
                                <img class="direct-chat-img" ng-show="message.me")
                                    src="<?php echo $GLOBALS['images_static_relative']; ?>/favicon-32x32.png"
                                    alt="">
                                <div class="direct-chat-text right">
                                    <div style="padding-left: 0px; padding-right: 0px;" title="<?php echo xlt('Click to make chat this current recipient only...'); ?>" ng-click="makeCurrent(message)" ng-bind-html=renderMessageBody(message.message)></div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer box-footer-hide">
                            <form id='msgfrm' ng-submit="saveMessage()">
                                <div class="input-group">
                                    <input type="text" placeholder="Type message..." id="msgedit" autofocus="autofocus" class="form-control" ng-model="me.message" ng-enter="saveMessage()">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-danger btn-flat"><?php echo xlt('Send'); ?></button>
                                        <button type="button" class="btn btn-success btn-flat" ng-click="openModal(event)"><?php echo xlt('Edit'); ?></button>
                                    </span>
                                </div>
                            </form>
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 rtsidebar">
                <h4><span class="label label-info"><?php echo xlt('Online'); ?> : {{ online.total || '0' }}</span></h4>
                <label ng-repeat="ol in onlines | unique : 'username'">
                    <input type="checkbox" data-checklist-model="onlines" data-checklist-value="ol"> {{ol.username}}
                </label>
            </div>
    </div>

    <div class="modal modal-wide fade" id="popeditor">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only"><?php echo xlt('Close'); ?></span>
                        </button>
                        <h4 class="modal-title"><?php echo xlt('Style your messsage and/or add Image/Video'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <summernote config="options"></summernote>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal"><?php echo xlt('Dismiss'); ?></button>
                        <button type="button" class="btn btn-success" data-dismiss="modal" ng-click="saveedit()"><?php echo xlt('Send It'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal" id="clear-history">
        <div class="modal-dialog">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only"><?php echo xlt('Close'); ?></span>
                        </button>
                        <h4 class="modal-title"><?php echo xlt('Chat history'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <label class="radio"><?php echo xlt('Are you sure to clear chat history?'); ?></label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                        <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal" ng-click="clearHistory()"><?php echo xlt('Accept'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
