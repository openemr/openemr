<?php
/**
 * $Id: 33771f28a2886e13a8d3c38864d6758311c3e18d $
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

require_once 'PhpDocumentor/phpDocumentor/Errors.inc';

/**
 * Phing subclass of the ErrorTracker class provided with PhpDocumentor to work around limitations in PhpDocumentor API.
 *
 * This class is necessary because PhpDocumentor does directly output errors and
 * warnings occurred during testing for undocumented elements to stdout.
 * This class is injected globally to force PhpDocumentor to use phing's logging
 * mechanism.
 *
 * Obviously this is far from ideal, but there's also no solution given the inflexibility of the
 * PhpDocumentor design.
 *
 * @author Timo A. Hummel <privat@timohummel.com> @author felicitus
 * @version $Id: 33771f28a2886e13a8d3c38864d6758311c3e18d $
 * @package phing.tasks.ext.phpdoc
 */
class PhingPhpDocumentorErrorTracker extends ErrorTracker
{

    /**
     * @var object	Reference to the task we're called with
     */
    private $task;

    /**
     * Outputs a warning. This is an almost 1:1 copy from PhpDocumentor,
     * we're just processing the warning text and send it to phing's logger.
     *
     * @param $num integer    Number of parameters
     * @return nothing
     */
    public function addWarning($num)
    {
        $a = array('', '', '', '');
        if (func_num_args() > 1) {
            for ($i = 1; $i < func_num_args(); $i++) {
                $a[$i - 1] = func_get_arg($i);
            }
        }

        $message = sprintf($GLOBALS['phpDocumentor_warning_descrip'][$num], $a[0], $a[1], $a[2], $a[3]);
        $this->task->log($message, Project::MSG_WARN);

    }

    /**
     * Outputs an error. This is an almost 1:1 copy from PhpDocumentor,
     * we're just processing the error text and send it to phing's logger.
     *
     * @param $num integer    Number of parameters
     * @return nothing
     */

    public function addError($num)
    {
        $a = array('', '', '', '');
        if (func_num_args() > 1) {
            for ($i = 1; $i < func_num_args(); $i++) {
                $a[$i - 1] = func_get_arg($i);
            }
        }

        $message = sprintf($GLOBALS['phpDocumentor_error_descrip'][$num], $a[0], $a[1], $a[2], $a[3]);
        $this->task->log($message, Project::MSG_ERR);

    }

    /**
     * Sets the task we're working with. This is necessary since we need to be
     * able to call the method "log".
     *
     * @param  object  $task The task we're working with
     * @return nothing
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

}
