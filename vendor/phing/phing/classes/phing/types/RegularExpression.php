<?php
/*
 *  $Id: 4b0da5e4e84cc48bb4e9c669c305448d3d59b11a $
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

include_once 'phing/types/DataType.php';
include_once 'phing/Project.php';
include_once 'phing/util/regexp/Regexp.php';

/**
 * A regular expression datatype.  Keeps an instance of the
 * compiled expression for speed purposes.  This compiled
 * expression is lazily evaluated (it is compiled the first
 * time it is needed).  The syntax is the dependent on which
 * regular expression type you are using.
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @version   $Id: 4b0da5e4e84cc48bb4e9c669c305448d3d59b11a $
 * @see       phing.util.regex.RegexMatcher
 * @package   phing.types
 */
class RegularExpression extends DataType
{

    private $regexp = null;
    /**
     * @todo Probably both $ignoreCase and $multiline should be removed
     * from attribute list of RegularExpression class:
     * actual values are preserved on regexp *engine* level, not expression
     * object itself.
     */
    private $ignoreCase = false;
    private $multiline = false;

    /**
     *
     */
    public function __construct()
    {
        $this->regexp = new Regexp();
    }

    /**
     * @param $pattern
     */
    public function setPattern($pattern)
    {
        $this->regexp->setPattern($pattern);
    }

    /**
     * @param $replace
     */
    public function setReplace($replace)
    {
        $this->regexp->setReplace($replace);
    }

    /**
     * @param $p
     * @return string
     * @throws BuildException
     */
    public function getPattern($p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getPattern($p);
        }

        return $this->regexp->getPattern();
    }

    /**
     * @param Project $p
     * @return string
     * @throws BuildException
     */
    public function getReplace($p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getReplace($p);
        }

        return $this->regexp->getReplace();
    }

    /**
     * @param $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->regexp->setModifiers($modifiers);
    }

    /**
     * @return string
     */
    public function getModifiers()
    {
        return $this->regexp->getModifiers();
    }

    /**
     * @param $bit
     */
    public function setIgnoreCase($bit)
    {
        $this->regexp->setIgnoreCase($bit);
    }

    /**
     * @return bool
     */
    public function getIgnoreCase()
    {
        return $this->regexp->getIgnoreCase();
    }

    /**
     * @param $multiline
     */
    public function setMultiline($multiline)
    {
        $this->regexp->setMultiline($multiline);
    }

    /**
     * @return bool
     */
    public function getMultiline()
    {
        return $this->regexp->getMultiline();
    }

    /**
     * @param Project $p
     * @return null|Regexp
     * @throws BuildException
     */
    public function getRegexp(Project $p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getRegexp($p);
        }

        return $this->regexp;
    }

    /**
     * @param Project $p
     * @return mixed
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        if (!$this->checked) {
            $stk = array();
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }

        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof RegularExpression)) {
            throw new BuildException($this->ref->getRefId() . " doesn't denote a RegularExpression");
        } else {
            return $o;
        }
    }
}
