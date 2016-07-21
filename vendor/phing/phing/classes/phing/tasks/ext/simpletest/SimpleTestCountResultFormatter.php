<?php
/**
 * $Id: f3db5ef14b0d98fcfbcabf12e421edb3dabc9508 $
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
 * Dummy result formatter used to count SimpleTest results
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: f3db5ef14b0d98fcfbcabf12e421edb3dabc9508 $
 * @package phing.tasks.ext.simpletest
 * @since 2.2.0
 */
class SimpleTestCountResultFormatter extends SimpleTestResultFormatter
{
    const SUCCESS = 0;
    const FAILURES = 1;
    const ERRORS = 2;

    /**
     * @return int
     */
    public function getRetCode()
    {
        if ($this->getExceptionCount() != 0) {
            return self::ERRORS;
        } else {
            if ($this->getFailCount() != 0) {
                return self::FAILURES;
            }
        }

        return self::SUCCESS;
    }
}
