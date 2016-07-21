<?php
/**
 * $Id: 46a07222af7bd1fbfd0e39169d5f80a83c6525e1 $
 *
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

require_once 'phing/Task.php';

/**
 * Stopwatch.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @version $Id: 46a07222af7bd1fbfd0e39169d5f80a83c6525e1 $
 * @package phing.tasks.ext.stopwatch
 */
class StopwatchTask extends Task
{
    /**
     * Name of the timer.
     *
     * @var string $name
     */
    private $name = '';

    /**
     * Category of the timer.
     *
     * @var string $category optional
     */
    private $category = '';

    /**
     * Timer  action.
     *
     * @var string $action
     */
    private $action = 'start';

    /**
     * Holds an instance of Stopwatch.
     *
     * @var Stopwatch $timer
     */
    private static $timer = null;

    /**
     * Initialize Task.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Load stopwatch.
     *
     * @return void
     *
     * @throws BuildException
     */
    private function loadStopwatch()
    {
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            throw new BuildException("StopwatchTask requires at least PHP 5.3.3 installed.");
        }

        @include_once 'Symfony/Component/Stopwatch/autoload.php';
        @include_once 'vendor/autoload.php';

        if (!class_exists('\\Symfony\\Component\\Stopwatch\\Stopwatch')) {
            throw new BuildException("StopwatchTask requires Stopwatch to be installed");
        }
    }

    /**
     * Get the stopwatch instance.
     *
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    private function getStopwatchInstance()
    {
        if (self::$timer === null) {
            $stopwatch = '\\Symfony\\Component\\Stopwatch\\Stopwatch';
            self::$timer = new $stopwatch;
        }

        return self::$timer;
    }

    /**
     * Start timer.
     *
     * @return void
     */
    private function start()
    {
        $timer = $this->getStopwatchInstance();
        $timer->start($this->name, $this->category);
    }

    /**
     * Stop timer.
     *
     * @return void
     */
    private function stop()
    {
        $timer = $this->getStopwatchInstance();
        $event = $timer->stop($this->name);

        foreach ($event->getPeriods() as $period) {
            $this->log('Starttime: ' . $period->getStartTime() . ' - Endtime: ' . $period->getEndTime() . ' - Duration: ' . $period->getDuration() . ' - Memory: ' . $period->getMemory(), Project::MSG_INFO);
        }

        $this->log('Category:   ' . $event->getCategory(), Project::MSG_INFO);
        $this->log('Origin:     ' . $event->getOrigin(), Project::MSG_INFO);
        $this->log('Start time: ' . $event->getStartTime(), Project::MSG_INFO);
        $this->log('End time:   ' . $event->getEndTime(), Project::MSG_INFO);
        $this->log('Duration:   ' . $event->getDuration(), Project::MSG_INFO);
        $this->log('Memory:     ' . $event->getMemory(), Project::MSG_INFO);
    }

    /**
     * Measure lap time.
     *
     * @return void
     */
    private function lap()
    {
        $timer = $this->getStopwatchInstance();
        $timer->lap($this->name);
    }

    /**
     * Set the name of the stopwatch.
     *
     * @param string $name the name of the stopwatch timer
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set the category of the stopwatch.
     *
     * @param string $category
     *
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Set the action.
     * Action could be one of
     * - start
     * - lap
     * - stop
     *
     * @param string $action
     *
     * @return void
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * The main entry point
     *
     * @return void
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->loadStopwatch();

        switch ($this->action) {
            case "start":
                $this->start();
                break;
            case "stop":
                $this->stop();
                break;
            case "lap":
                $this->lap();
                break;
            default:
                throw new BuildException('action should be one of start, stop, lap.');
        }
    }
}
