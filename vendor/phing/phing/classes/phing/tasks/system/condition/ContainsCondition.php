<?php

/*
 *  $Id: d3373cdf3bbaa92ac40c577198e5a01c6c114796 $
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

require_once 'phing/tasks/system/condition/Condition.php';

/**
 * Is one string part of another string?
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @version $Id: d3373cdf3bbaa92ac40c577198e5a01c6c114796 $
 * @package phing.tasks.system.condition
 */
class ContainsCondition implements Condition
{

    private $string;
    private $subString;
    private $caseSensitive = true;

    /**
     * The string to search in.
     * @param string $a1
     */
    public function setString($a1)
    {
        $this->string = $a1;
    }

    /**
     * The string to search for.
     * @param string $a2
     */
    public function setSubstring($a2)
    {
        $this->subString = $a2;
    }

    /**
     * Whether to search ignoring case or not.
     * @param $b
     */
    public function setCaseSensitive($b)
    {
        $this->caseSensitive = (boolean) $b;
    }

    /**
     * Check whether string contains substring.
     * @throws BuildException
     */
    public function evaluate()
    {
        if ($this->string === null || $this->subString === null) {
            throw new BuildException("both string and substring are required "
                . "in contains");
        }

        return $this->caseSensitive
            ? strpos($this->string, $this->subString) !== false
            : strpos(strtolower($this->string), strtolower($this->subString)) !== false;
    }
}
