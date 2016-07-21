<?php

/*
 *  $Id: 2ce2fe6389844147fcaa7f85308157891a38e7af $
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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Replaces tokens in the original input with the contents of a file.
 * The file to be used is controlled by the name of the token which
 * corresponds to the basename of the file to be used together with
 * the optional pre and postfix strings that is possible to set.
 *
 * By default all HTML entities in the file is replaced by the
 * corresponding HTML entities. This behaviour can be controlled by
 * the "translatehtml" parameter.
 *
 * Supported parameters are:
 *  <pre>
 *  prefix         string Text to be prefixed to token before using as filename
 *  postfix        string Text to be prefixed to token before using as filename
 *  dir            string The directory where the files should be read from
 *  translatehtml  bool   If we should translate all HTML entities in the file.
 * </pre>
 * Example:
 *
 * <pre><filterreader classname="phing.filters.ReplaceTokensWithFile">
 *   <param name="dir" value="examples/" />
 *   <param name="postfix" value=".php" />
 * </filterreader></pre>
 *
 * @author    johan persson, johanp@aditus.nu
 * @version   $Id: 2ce2fe6389844147fcaa7f85308157891a38e7af $
 * @see       ReplaceTokensWithFile
 * @package   phing.filters
 */
class ReplaceTokensWithFile extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Default "begin token" character.
     * @var string
     */
    const DEFAULT_BEGIN_TOKEN = "#@#";

    /**
     * Default "end token" character.
     * @var string
     */
    const DEFAULT_END_TOKEN = "#@#";

    /**
     * Array to hold the token sources that make tokens from
     * different sources available
     * @var array
     */
    private $_tokensources = array();

    /**
     * Character marking the beginning of a token.
     * @var string
     */
    private $_beginToken = ReplaceTokensWithFile::DEFAULT_BEGIN_TOKEN;

    /**
     * Character marking the end of a token.
     * @var string
     */
    private $_endToken = ReplaceTokensWithFile::DEFAULT_END_TOKEN;

    /**
     * File prefix to be inserted in front of the token to create the
     * file name to be used.
     * @var string
     */
    private $_prefix = '';

    /**
     * File postfix to be inserted in front of the token to create the
     * file name to be used.
     * @var string
     */
    private $_postfix = '';

    /**
     * Directory where to look for the files. The default is to look in the
     * current file.
     *
     * @var string
     */
    private $_dir = './';

    /**
     * Translate all HTML entities in the file to the corresponding HTML
     * entities before it is used as replacements. For example all '<'
     * will be translated to &lt; before the content is inserted.
     *
     * @var boolean
     */
    private $_translatehtml = true;

    /**
     * Sets the drectory where to look for the files to use for token replacement
     *
     * @param $translate
     * @internal param string $dir
     */
    public function setTranslateHTML($translate)
    {
        $this->_translatehtml = (bool) $translate;
    }

    /**
     * Returns the drectory where to look for the files to use for token replacement
     */
    public function getTranslateHTML()
    {
        return $this->_translatehtml;
    }

    /**
     * Sets the drectory where to look for the files to use for token replacement
     *
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->_dir = (string) $dir;
    }

    /**
     * Returns the drectory where to look for the files to use for token replacement
     */
    public function getDir()
    {
        return $this->_dir;
    }

    /**
     * Sets the prefix that is prepended to the token in order to create the file
     * name. For example if the token is 01 and the prefix is "example" then
     * the filename to look for will be "example01"
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = (string) $prefix;
    }

    /*
     * Returns the prefix that is prepended to the token in order to create the file
     * name. For example if the token is 01 and the prefix is "example" then
     * the filename to look for will be "example01"
     */
    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Sets the postfix that is added to the token in order to create the file
     * name. For example if the token is 01 and the postfix is ".php" then
     * the filename to look for will be "01.php"
     *
     * @param string $postfix
     */
    public function setPostfix($postfix)
    {
        $this->_postfix = (string) $postfix;
    }

    /**
     * Returns the postfix that is added to the token in order to create the file
     * name. For example if the token is 01 and the postfix is ".php" then
     * the filename to look for will be "01.php"
     */
    public function getPostfix()
    {
        return $this->_postfix;
    }

    /**
     * Sets the "begin token" character.
     *
     * @param string $beginToken the character used to denote the beginning of a token.
     */
    public function setBeginToken($beginToken)
    {
        $this->_beginToken = (string) $beginToken;
    }

    /**
     * Returns the "begin token" character.
     *
     * @return string The character used to denote the beginning of a token.
     */
    public function getBeginToken()
    {
        return $this->_beginToken;
    }

    /**
     * Sets the "end token" character.
     *
     * @param string $endToken the character used to denote the end of a token
     */
    public function setEndToken($endToken)
    {
        $this->_endToken = (string) $endToken;
    }

    /**
     * Returns the "end token" character.
     *
     * @return the character used to denote the beginning of a token
     */
    public function getEndToken()
    {
        return $this->_endToken;
    }

    /**
     * Replace the token found with the appropriate file contents
     * @param  array  $matches Array of 1 el containing key to search for.
     * @return string Text with which to replace key or value of key if none is found.
     */
    private function replaceTokenCallback($matches)
    {

        $filetoken = $matches[1];

        // We look in all specified directories for the named file and use
        // the first directory which has the file.
        $dirs = explode(';', $this->_dir);

        $ndirs = count($dirs);
        $n = 0;
        $file = $dirs[$n] . $this->_prefix . $filetoken . $this->_postfix;

        while ($n < $ndirs && !is_readable($file)) {
            ++$n;
        }

        if (!is_readable($file) || $n >= $ndirs) {
            $this->log(
                "Can not read or find file \"$file\". Searched in directories: {$this->_dir}",
                Project::MSG_WARN
            );
            //return $this->_beginToken  . $filetoken . $this->_endToken;
            return "[Phing::Filters::ReplaceTokensWithFile: Can not find file " . '"' . $filetoken . $this->_postfix . '"' . "]";
        }

        $buffer = file_get_contents($file);
        if ($this->_translatehtml) {
            $buffer = htmlentities($buffer);
        }

        if ($buffer === null) {
            $buffer = $this->_beginToken . $filetoken . $this->_endToken;
            $this->log("No corresponding file found for key \"$buffer\"", Project::MSG_WARN);
        } else {
            $this->log(
                "Replaced \"" . $this->_beginToken . $filetoken . $this->_endToken . "\" with content from file \"$file\""
            );
        }

        return $buffer;
    }

    /**
     * Returns stream with tokens having been replaced with appropriate values.
     * If a replacement value is not found for a token, the token is left in the stream.
     *
     * @param null $len
     * @return mixed filtered stream, -1 on EOF.
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // read from next filter up the chain
        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        // filter buffer
        $buffer = preg_replace_callback(
            "$" . preg_quote($this->_beginToken) . "([\w\.\-:\/]+?)" . preg_quote($this->_endToken) . "$",
            array($this, 'replaceTokenCallback'),
            $buffer
        );

        return $buffer;
    }

    /**
     * Creates a new ReplaceTokensWithFile using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering
     *                the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new ReplaceTokensWithFile($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setTranslateHTML($this->getTranslateHTML());
        $newFilter->setDir($this->getDir());
        $newFilter->setPrefix($this->getPrefix());
        $newFilter->setPostfix($this->getPostfix());
        $newFilter->setBeginToken($this->getBeginToken());
        $newFilter->setEndToken($this->getEndToken());
        $newFilter->setInitialized(true);

        return $newFilter;
    }

    /**
     * Initializes parameters
     * This method is only called when this filter is used through
     * a <filterreader> tag in build file.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        $n = count($params);

        if ($params !== null) {
            for ($i = 0; $i < $n; $i++) {
                if ($params[$i] !== null) {
                    $name = $params[$i]->getName();
                    switch ($name) {
                        case 'begintoken' :
                            $this->_beginToken = $params[$i]->getValue();
                            break;
                        case 'endtoken' :
                            $this->_endToken = $params[$i]->getValue();
                            break;
                        case 'dir':
                            $this->_dir = $params[$i]->getValue();
                            break;
                        case 'prefix':
                            $this->_prefix = $params[$i]->getValue();
                            break;
                        case 'postfix':
                            $this->_postfix = $params[$i]->getValue();
                            break;
                        case 'translatehtml':
                            $this->_translatehtml = $params[$i]->getValue();
                            break;
                    }
                }
            }
        }
    }
}
