<?php
/**
 * $Id: 5038479697b9cd62aa39006c53598ef0c41caac4 $
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

include_once 'phing/tasks/ext/property/AbstractPropertySetterTask.php';
include_once 'phing/util/regexp/Regexp.php';

/**
 * Regular Expression Task for properties.
 *
 * <pre>
 *   <propertyregex property="pack.name"
 *                  subject="package.ABC.name"
 *                  pattern="package\.([^.]*)\.name"
 *                  match="$1"
 *                  casesensitive="false"
 *                  defaultvalue="test1"/>
 *
 *   <echo message="${pack.name}"/>
 *
 *   <propertyregex property="pack.name"
 *                  override="true"
 *                  subject="package.ABC.name"
 *                  pattern="(package)\.[^.]*\.(name)"
 *                  replace="$1.DEF.$2"
 *                  casesensitive="false"
 *                  defaultvalue="test2"/>
 *
 *   <echo message="${pack.name}"/>
 *
 * </pre>
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.regex
 */
class RegexTask extends AbstractPropertySetterTask
{
    /** @var string $subject */
    private $subject;

    /** @var string $pattern */
    private $pattern;

    /** @var string $match */
    private $match;

    /** @var string $replace */
    private $replace;

    /** @var string $defaultValue */
    private $defaultValue;

    /** @var bool $caseSensitive */
    private $caseSensitive = true;

    /** @var array $modifiers */
    private $modifiers = '';

    /** @var Regexp $reg */
    private $reg;

    /** @var int $limit */
    private $limit = -1;
    
    public function init()
    {
        $this->reg = new Regexp();
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->log('Set default value to ' . $defaultValue, Project::MSG_DEBUG);

        $this->defaultValue = $defaultValue;
    }

    /**
     * @param  string $pattern
     * @throws BuildException
     */
    public function setPattern($pattern)
    {
        if ($this->pattern !== null) {
            throw new BuildException(
                'Cannot specify more than one regular expression'
            );
        }

        $this->log('Set pattern to ' . $pattern, Project::MSG_DEBUG);

        $this->pattern = $pattern;
    }

    /**
     * @param $replace
     * @throws BuildException
     */
    public function setReplace($replace)
    {
        if ($this->replace !== null) {
            throw new BuildException(
                'Cannot specify more than one replace expression'
            );
        }
        if ($this->match !== null) {
            throw new BuildException(
                'You cannot specify both a select and replace expression'
            );
        }

        $this->log('Set replace to ' . $replace, Project::MSG_DEBUG);

        $this->replace = $replace;
    }

    /**
     * @param $match
     * @throws BuildException
     */
    public function setMatch($match)
    {
        if ($this->match !== null) {
            throw new BuildException(
                'Cannot specify more than one match expression'
            );
        }

        $this->log('Set match to ' . $match, Project::MSG_DEBUG);

        $this->match = $match;
    }

    /**
     * @param $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {

        $this->log("Set case-sensitive to $caseSensitive", Project::MSG_DEBUG);

        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @return mixed|string
     * @throws BuildException
     */
    protected function doReplace()
    {
        if ($this->replace === null) {
            throw new BuildException('No replace expression specified.');
        }
        $this->reg->setPattern($this->pattern);
        $this->reg->setReplace($this->replace);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);
        $this->reg->setLimit($this->limit);

        try {
            $output = $this->reg->replace($this->subject);
        } catch (Exception $e) {
            $output = $this->defaultValue;
        }

        return $output;
    }

    /**
     * @return string
     *
     * @throws BuildException
     */
    protected function doSelect()
    {
        $this->reg->setPattern($this->pattern);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);

        $output = $this->defaultValue;

        try {
            if ($this->reg->matches($this->subject)) {
                $output = $this->reg->getGroup((int) ltrim($this->match, '$'));
            }
        } catch (Exception $e) {
            throw new BuildException($e);
        }

        return $output;
    }

    /**
     * @throws BuildException
     */
    protected function validate()
    {
        if ($this->pattern === null) {
            throw new BuildException('No match expression specified.');
        }
        if ($this->replace === null && $this->match === null) {
            throw new BuildException(
                'You must specify either a preg_replace or preg_match pattern'
            );
        }
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->validate();

        $output = $this->match;

        if ($this->replace !== null) {
            $output = $this->doReplace();
        } else {
            $output = $this->doSelect();
        }

        if ($output !== null) {
            $this->setPropertyValue($output);
        }
    }
}
