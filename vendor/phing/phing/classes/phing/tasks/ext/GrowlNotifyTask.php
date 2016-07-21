<?php
/**
 * Copyright (c) 2012-2013, Laurent Laville <pear@laurent-laville.org>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the authors nor the names of its contributors
 *       may be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @version    $Id: 50d0d2d926e6a51ab78d6801407e90f020dcbad9 $
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       https://github.com/llaville/phing-GrowlNotifyTask
 */

require_once 'phing/Task.php';

/**
 * Growl notification task for Phing, the PHP build tool.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @version    $Id: 50d0d2d926e6a51ab78d6801407e90f020dcbad9 $
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       https://github.com/llaville/phing-GrowlNotifyTask
 */
class GrowlNotifyTask extends Task
{
    protected $growl;

    protected $name;
    protected $sticky;
    protected $message;
    protected $title;
    protected $notification;
    protected $appicon;
    protected $host;
    protected $password;
    protected $priority;
    protected $protocol;
    protected $icon;

    /**
     * Initializes task with default options
     *
     * @param Net_Growl $growl (optional) mock instance
     */
    public function __construct(Net_Growl $growl = null)
    {
        $this->growl = $growl;
    }

    /**
     * The init method check if Net_Growl is available
     * (exists and can be loaded)
     *
     * @return void
     * @throws BuildException
     */
    public function init()
    {
        $autoloader = 'Net/Growl/Autoload.php';

        if (!$handle = @fopen($autoloader, 'r', true)) {
            throw new BuildException(
                'The Growl Notify task requires the Net_Growl PEAR package.'
            );
        } else {
            fclose($handle);
            include_once $autoloader;
        }

        $this->setTaskName('GrowlNotify');
        $this->setName();
        $this->setSticky(false);
        $this->setMessage();
        $this->setTitle();
        $this->setNotification();
        $this->setAppicon();
        $this->setHost();
        $this->setPassword();
        $this->setPriority();
        $this->setProtocol();
        $this->setIcon();
    }

    /**
     * Defines the name of the application sending the notification
     *
     * @param string $name (optional) Name of the application
     *                     that appears in your Growl preferences
     *                     Default: "Growl for Phing"
     *
     * @return void
     * @throws BuildException
     */
    public function setName($name = '')
    {
        if ('' == $name) {
            $name = 'Growl for Phing';
        }

        if (!is_string($name)) {
            throw new BuildException(
                '"name" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($name)
            );
        }

        $this->name = $name;
    }

    /**
     * Indicates if the notification should be sticky
     *
     * @param bool $sticky (optional) Notification should be sticky
     *
     * @return void
     */
    public function setSticky($sticky = true)
    {
        $this->sticky = (bool) $sticky;
    }

    /**
     * The notification's text is required.
     * Use \n to specify a line break.
     *
     * @param string $message Notification's text
     *
     * @return void
     * @throws BuildException
     */
    public function setMessage($message = '')
    {
        if (!is_string($message)) {
            throw new BuildException(
                '"message" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($message)
            );
        }

        $this->message = $message;
    }

    /**
     * The notification's title.
     * Use \n to specify a line break.
     *
     * @param string $title (optional) Notification's title
     *                      Default: GrowlNotify
     *
     * @return void
     * @throws BuildException
     */
    public function setTitle($title = '')
    {
        if ('' == $title) {
            $title = 'GrowlNotify';
        }

        if (!is_string($title)) {
            throw new BuildException(
                '"title" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($title)
            );
        }

        $this->title = $title;
    }

    /**
     * The notification name/type
     *
     * @param string $notification Name/type
     *                             Default: "General Notification"
     *
     * @return void
     * @throws BuildException
     */
    public function setNotification($notification = '')
    {
        if ('' == $notification) {
            $notification = 'General Notification';
        }

        if (!is_string($notification)) {
            throw new BuildException(
                '"notification" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($notification)
            );
        }

        $this->notification = $notification;
    }

    /**
     * The icon of the application being registered.
     *
     * Must be a valid file type (png, jpg, gif, ico).
     * Can be any of the following:
     *  - absolute url (http://domain/image.png)
     *  - absolute file path (c:\temp\image.png)
     *  - relative file path (.\folder\image.png) (relative file paths must start
     *    with a dot and are relative to GrowlNotify's phing task location
     *
     * @param string $icon Icon of the application
     *
     * @return void
     * @throws BuildException
     */
    public function setAppicon($icon = '')
    {
        if (!is_string($icon)) {
            throw new BuildException(
                '"appicon" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($icon)
            );
        }

        // relative location
        if (strpos($icon, '..') === 0) {
            $icon = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $icon);

        } elseif (strpos($icon, '.') === 0) {
            $icon = dirname(__FILE__) . substr($icon, 1);
        }

        $this->appicon = $icon;
    }

    /**
     * The host address to send the notification to.
     *
     * If any value other than 'localhost' or '127.0.0.1' is provided, the host
     * is considered a remote host and the "pass" attribute must also be provided.
     * Default: 127.0.0.1
     *
     * @param string $host Remote host name/ip
     *                     Default: 127.0.0.1
     *
     * @return void
     * @throws BuildException
     */
    public function setHost($host = '127.0.0.1')
    {
        if (!is_string($host)) {
            throw new BuildException(
                '"host" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($host)
            );
        }

        $this->host = $host;
    }

    /**
     * The password required to send notifications.
     *
     * A password is required to send a request to a remote host. If host attribute
     * is specified and is any value other than 'localhost' or '127.0.0.1',
     * then "pass" attribute is also required.
     * Default: no password
     *
     * @param string $password Password to send request to a remote host
     *
     * @return void
     * @throws BuildException
     */
    public function setPassword($password = '')
    {
        if (!is_string($password)) {
            throw new BuildException(
                '"password" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($password)
            );
        }

        $this->password = $password;
    }

    /**
     * The notification priority.
     *
     * Valid values are : low, moderate, normal, high, emergency
     * Default: normal
     *
     * @param string $priority Notification priority
     *                         Default: normal
     *
     * @return void
     * @throws BuildException
     */
    public function setPriority($priority = '')
    {
        if ('' == $priority) {
            $priority = 'normal';
        }

        switch ($priority) {
            case 'low' :
                $priority = Net_Growl::PRIORITY_LOW;
                break;
            case 'moderate' :
                $priority = Net_Growl::PRIORITY_MODERATE;
                break;
            case 'normal' :
                $priority = Net_Growl::PRIORITY_NORMAL;
                break;
            case 'high' :
                $priority = Net_Growl::PRIORITY_HIGH;
                break;
            case 'emergency' :
                $priority = Net_Growl::PRIORITY_EMERGENCY;
                break;
            default :
                throw new BuildException(
                    '"priority" attribute is invalid.'
                );
        }

        $this->priority = $priority;
    }

    /**
     * The protocol (and port) to send the notification to.
     *
     * With TCP (GNTP) protocol, port is always 23053
     * With UDP protocol, port is always 9887
     * Default: 23053
     *
     * @param string $protocol Protocol to use to send request to remote host
     *                         Default: gntp
     *
     * @return void
     * @throws BuildException
     */
    public function setProtocol($protocol = '')
    {
        if ('' == $protocol) {
            $protocol = 'gntp';
        }

        switch ($protocol) {
            case 'udp' :
            case 'gntp' :
                break;
            default :
                throw new BuildException(
                    '"protocol" attribute is invalid.' .
                    ' Expect to be either udp or gntp.'
                );
        }

        $this->protocol = $protocol;
    }

    /**
     * The icon to show for the notification.
     *
     * Must be a valid file type (png, jpg, gif, ico).
     * Can be any of the following:
     *  - absolute url (http://domain/image.png)
     *  - absolute file path (c:\temp\image.png)
     *  - relative file path (.\folder\image.png) (relative file paths must start
     *    with a dot and are relative to GrowlNotify's phing task location
     *
     * @param string $icon Icon of the message
     *
     * @return void
     * @throws BuildException
     */
    public function setIcon($icon = '')
    {
        if (!is_string($icon)) {
            throw new BuildException(
                '"icon" attribute is invalid.' .
                ' Expect to be a string, actual is ' . gettype($icon)
            );
        }

        // relative location
        if (strpos($icon, '..') === 0) {
            $icon = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $icon);

        } elseif (strpos($icon, '.') === 0) {
            $icon = dirname(__FILE__) . substr($icon, 1);
        }

        $this->icon = $icon;
    }

    /**
     * The main entry point method
     *
     * @return void
     * @throws BuildException
     */
    public function main()
    {
        if (empty($this->message)) {
            throw new BuildException(
                '"message" attribute cannot be empty'
            );
        }

        $notifications = array(
            $this->notification
        );
        $options = array(
            'host' => $this->host,
            'protocol' => $this->protocol,
        );
        if (!empty($this->appicon)) {
            $options['AppIcon'] = $this->appicon;
        }

        try {
            if ($this->growl instanceof Net_Growl) {
                $growl = $this->growl;
            } else {
                $growl = Net_Growl::singleton(
                    $this->name,
                    $notifications,
                    $this->password,
                    $options
                );
            }
            $response = $growl->register();

            if ($this->protocol == 'gntp') {
                if ($response->getStatus() != 'OK') {
                    throw new BuildException(
                        'Growl Error ' . $response->getErrorCode() .
                        ' - ' . $response->getErrorDescription()
                    );
                }
            }
            $this->log(
                'Application ' . $this->name . ' registered',
                Project::MSG_VERBOSE
            );

            $logRequest = array(
                'Application-Name' => $this->name,
                'Application-Icon' => $this->appicon,
                'Notification-Name' => $this->notification,
                'Notification-Title' => $this->title,
                'Notification-Text' => $this->message,
                'Notification-Priority' => $this->priority,
                'Notification-Icon' => $this->icon,
                'Notification-Sticky' => $this->sticky,
            );
            foreach ($logRequest as $key => $value) {
                $this->log($key . ': ' . $value, Project::MSG_DEBUG);

            }

            $options = array(
                'sticky' => $this->sticky,
                'priority' => $this->priority,
                'icon' => $this->icon,
            );
            $response = $growl->publish(
                $this->notification,
                $this->title,
                $this->message,
                $options
            );

            if ($this->protocol == 'gntp') {
                if ($response->getStatus() != 'OK') {
                    throw new BuildException(
                        'Growl Error ' . $response->getErrorCode() .
                        ' - ' . $response->getErrorDescription()
                    );
                }
            }
            $this->log('Notification was sent to remote host ' . $this->host);

        } catch (Net_Growl_Exception $e) {
            throw new BuildException(
                'Growl Exception : ' . $e->getMessage()
            );
        }
    }

}
