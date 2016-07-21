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

/**
 * This class can be used to obtain the execution time of all of the scripts
 * that are executed in the process of building a page.
 *
 * Example:
 * To be done before any scripts execute:
 *
 * $Timer = new Timer;
 * $Timer->Start_Timer();
 *
 * To be done after all scripts have executed:
 *
 * $timer->Stop_Timer();
 * $timer->Get_Elapsed_Time(int number_of_places);
 *
 * @author    Charles Killian
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package    phing.system.util
 */
class Timer
{
    /**
     * start time
     *
     * @var float
     */
    protected $stime;

    /**
     * end time
     *
     * @var float
     */
    protected $etime;

    /**
     * This function sets the class variable $stime to the current time in
     * microseconds.
     *
     * @return void
     */
    public function start()
    {
        $this->stime = $this->getMicrotime();
    }

    /**
     * This function sets the class variable $etime to the current time in
     * microseconds.
     *
     * @return void
     */
    public function stop()
    {
        $this->etime = $this->getMicrotime();
    }

    /**
     * This function returns the elapsed time in seconds.
     *
     * Call start_time() at the beginning of script execution and end_time() at
     * the end of script execution.  Then, call elapsed_time() to obtain the
     * difference between start_time() and end_time().
     *
     * @param  int $places decimal place precision of elapsed time (default is 5)
     *
     * @return string Properly formatted time.
     */
    public function getElapsedTime($places = 5)
    {
        $etime = $this->etime - $this->stime;
        $format = "%0." . $places . "f";

        return (sprintf($format, $etime));
    }

    /**
     * This function returns the current time in microseconds.
     *
     * @author Everett Michaud, Zend.com
     *
     * @return float current time in microseconds
     */
    private function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }
}
