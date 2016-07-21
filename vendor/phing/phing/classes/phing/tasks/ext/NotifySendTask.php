<?php
/**
 * Utilise notify-send from within Phing.
 *
 * PHP Version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     https://github.com/kenguest/Phing-NotifySendTask
 */

require_once 'phing/Task.php';

/**
 * NotifySendTask
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     NotifySendTask.php
 */
class NotifySendTask extends Task
{
    protected $msg = null;
    protected $title = null;
    protected $icon = 'info';
    protected $silent = false;

    /**
     * Set icon attribute
     *
     * @param string $icon name/location of icon
     *
     * @return void
     */
    public function setIcon($icon)
    {
        switch ($icon)
        {
        case 'info':
        case 'error':
        case 'warning':
            $this->icon = $icon;
            break;
        default:
            if (file_exists($icon) && is_file($icon)) {
                $this->icon = $icon;
            } else {
                if (isset($this->log)) {
                    $this->log(
                        sprintf(
                            "%s is not a file. Using default icon instead.",
                            $icon
                        ),
                        Project::MSG_WARN
                    );
                }
            }
        }
    }

    /**
     * Get icon to be used (filename or generic name)
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set to a true value to not execute notifysend command.
     *
     * @param bool $silent Don't execute notifysend? True not to.
     *
     * @return void
     */
    public function setSilent($silent)
    {
        $this->silent = (bool) $silent;
    }

    /**
     * Set title attribute
     *
     * @param string $title Title to display
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set msg attribute
     *
     * @param string $msg Message
     *
     * @return void
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $msg = '';
        $title = 'Phing';

        if ($this->title != '') {
            $title = "'" . $this->title . "'";
        }

        if ($this->msg != '') {
            $msg = "'" . $this->msg . "'";
        }

        $cmd = 'notify-send -i ' . $this->icon . ' ' . $title . ' ' . $msg;

        $this->log(sprintf("cmd: %s", $cmd), Project::MSG_DEBUG);
        if (!$this->silent) {
            exec(escapeshellcmd($cmd), $output, $return);
            if ($return !== 0) {
                throw new BuildException("Notify task failed.");
            }
        }
        $this->log(sprintf("Title: '%s'", $title), Project::MSG_DEBUG);
        $this->log(sprintf("Message: '%s'", $msg), Project::MSG_DEBUG);
        $this->log($msg, Project::MSG_INFO);
    }
}

// vim:set et ts=4 sw=4:
