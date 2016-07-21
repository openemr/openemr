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
 * BuildException + exit status.
 *
 * @author   Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package  phing
 */
class ExitStatusException extends BuildException
{
    /** Status code */
    protected $code;

    /**
     * Constructs an <code>ExitStatusException</code>.
     * @param null|int|string $arg1
     * @param int $arg2
     * @param Location $arg3
     */
    public function __construct($arg1 = null, $arg2 = 0, Location $arg3 = null)
    {
        $methodArgsNum = func_num_args();
        if ($methodArgsNum === 1) {
            parent::__construct();
            $this->code = (int) $arg1;
        } elseif ($methodArgsNum === 2 && is_string($arg1) && is_int($arg2)) {
            parent::__construct($arg1);
            $this->code = $arg2;
        } elseif ($methodArgsNum === 3 && is_string($arg1) && is_int($arg2)) {
            parent::__construct($arg1, $arg3);
            $this->code = $arg2;
        }
    }
}
