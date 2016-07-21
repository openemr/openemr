<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

include_once 'phing/listener/DefaultLogger.php';
include_once 'phing/BuildEvent.php';

/**
 * This is a special logger that is designed to profile builds.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.listener
 */
class ProfileLogger extends DefaultLogger
{
    private $profileData = array();

    protected static $dateFormat = DATE_RFC2822;

    /**
     * Logs a message to say that the target has started.
     *
     * @param BuildEvent $event
     *            An event with any relevant extra information. Must not be
     *            <code>null</code>.
     */
    public function targetStarted(BuildEvent $event)
    {
        if (@date_default_timezone_get() === 'UTC') {
            date_default_timezone_set('Europe/Berlin');
        }
        $now = Phing::currentTimeMillis();
        $name = "Target " . $event->getTarget()->getName();
        $this->logStart($event, $now, $name);
        $this->profileData[] = $now;
    }

    /**
     * Logs a message to say that the target has finished.
     *
     * @param BuildEvent $event
     *            An event with any relevant extra information. Must not be
     *            <code>null</code>.
     */
    public function targetFinished(BuildEvent $event)
    {
        $start = array_pop($this->profileData);

        $name = "Target " . $event->getTarget()->getName();
        $this->logFinish($event, $start, $name);
    }

    /**
     * Logs a message to say that the task has started.
     *
     * @param BuildEvent $event
     *            An event with any relevant extra information. Must not be
     *            <code>null</code>.
     */
    public function taskStarted(BuildEvent $event)
    {
        $name = $event->getTask()->getTaskName();
        $now = Phing::currentTimeMillis();
        $this->logStart($event, $now, $name);
        $this->profileData[] = $now;
    }

    /**
     * Logs a message to say that the task has finished.
     *
     * @param BuildEvent $event
     *            An event with any relevant extra information. Must not be
     *            <code>null</code>.
     */
    public function taskFinished(BuildEvent $event)
    {
        $start = array_pop($this->profileData);

        $name = $event->getTask()->getTaskName();
        $this->logFinish($event, $start, $name);
    }

    private function logFinish(BuildEvent $event, $start, $name)
    {
        $msg = null;
        if ($start != null) {
            $diff = self::formatTime(Phing::currentTimeMillis() - $start);
            $msg = Phing::getProperty("line.separator") . $name . ": finished "
                . date(self::$dateFormat, time()) . " ("
                . $diff
                . ")";
        } else {
            $msg = Phing::getProperty("line.separator") . $name . ": finished " . date(self::$dateFormat, time())
                . " (unknown duration, start not detected)";
        }
        $this->printMessage($msg, $this->out, $event->getPriority());
    }

    private function logStart(BuildEvent $event, $start, $name)
    {
        $msg = Phing::getProperty("line.separator") . $name . ": started " . date(self::$dateFormat, $start);
        $this->printMessage($msg, $this->out, $event->getPriority());
    }
}
