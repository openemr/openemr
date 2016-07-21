<?php
/**
 * $Id: 3b2f67ab5e1a8dec51d3e7f1128bcaeceee3e75c $
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

require_once 'phing/tasks/ext/simpletest/SimpleTestResultFormatter.php';

/**
 * Prints short summary output of the test to Phing's logging system.
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 3b2f67ab5e1a8dec51d3e7f1128bcaeceee3e75c $
 * @package phing.tasks.ext.simpletest
 * @since 2.2.0
 */
class SimpleTestSummaryResultFormatter extends SimpleTestResultFormatter
{
    /**
     * @param string $test_name
     */
    public function paintCaseEnd($test_name)
    {
        parent::paintCaseEnd($test_name);

        /* Only count suites where more than one test was run */
        if ($this->getRunCount()) {
            $sb .= "Tests run: " . $this->getRunCount();
            $sb .= ", Failures: " . $this->getFailureCount();
            $sb .= ", Errors: " . $this->getErrorCount();
            $sb .= ", Time elapsed: " . $this->getElapsedTime();
            $sb .= " sec\n";

            if ($this->out != null) {
                $this->out->write($sb);
            }
        }
    }
}
