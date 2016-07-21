<?php

/*
 *  $Id: 69ad2e753a6efce00ea1ac34adf119d41294d676 $
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
include_once 'phing/types/TokenSource.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Replaces tokens in the original input with user-supplied values.
 *
 * Example:
 *
 * <pre><replacetokens begintoken="#" endtoken="#">;
 *   <token key="DATE" value="${TODAY}"/>
 * </replacetokens></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.ReplaceTokens">
 *   <param type="tokenchar" name="begintoken" value="#"/>
 *   <param type="tokenchar" name="endtoken" value="#"/>
 *   <param type="token" name="DATE" value="${TODAY}"/>
 * </filterreader></pre>
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author    hans lellelid, hans@velum.net
 * @version   $Id: 69ad2e753a6efce00ea1ac34adf119d41294d676 $
 * @see       BaseParamFilterReader
 * @package   phing.filters
 */
class ReplaceTokens extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Default "begin token" character.
     * @var string
     */
    const DEFAULT_BEGIN_TOKEN = "@";

    /**
     * Default "end token" character.
     * @var string
     */
    const DEFAULT_END_TOKEN = "@";

    /**
     * [Deprecated] Data that must be read from, if not null.
     * @var string
     */
    private $_queuedData = null;

    /**
     * Array to hold the replacee-replacer pairs (String to String).
     * @var array
     */
    private $_tokens = array();

    /**
     * Array to hold the token sources that make tokens from
     * different sources available
     * @var array
     */
    private $_tokensources = array();

    /**
     * Array holding all tokens given directly to the Filter and
     * those passed via a TokenSource.
     * @var array
     */
    private $_alltokens = null;

    /**
     * Character marking the beginning of a token.
     * @var string
     */
    private $_beginToken = "@"; // self::DEFAULT_BEGIN_TOKEN;

    /**
     * Character marking the end of a token.
     * @var string
     */
    private $_endToken = "@"; //self::DEFAULT_END_TOKEN;

    /**
     * Performs lookup on key and returns appropriate replacement string.
     * @param  array  $matches Array of 1 el containing key to search for.
     * @return string Text with which to replace key or value of key if none is found.
     */
    private function replaceTokenCallback($matches)
    {

        $key = $matches[1];

        /* Get tokens from tokensource and merge them with the
         * tokens given directly via build file. This should be
         * done a bit more elegantly
         */
        if ($this->_alltokens === null) {
            $this->_alltokens = array();

            $count = count($this->_tokensources);
            for ($i = 0; $i < $count; $i++) {
                $source = $this->_tokensources[$i];
                $this->_alltokens = array_merge($this->_alltokens, $source->getTokens());
            }

            $this->_alltokens = array_merge($this->_tokens, $this->_alltokens);
        }

        $tokens = $this->_alltokens;

        $replaceWith = null;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->getKey() === $key) {
                $replaceWith = $tokens[$i]->getValue();
            }
        }

        if ($replaceWith === null) {
            $replaceWith = $this->_beginToken . $key . $this->_endToken;
            $this->log("No token defined for key \"" . $this->_beginToken . $key . $this->_endToken . "\"");
        } else {
            $this->log(
                "Replaced \"" . $this->_beginToken . $key . $this->_endToken . "\" with \"" . $replaceWith . "\"",
                Project::MSG_VERBOSE
            );
        }

        return $replaceWith;
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
            "/" . preg_quote($this->_beginToken, '/') . "([\w\.\-:]+?)" . preg_quote($this->_endToken, '/') . "/",
            array($this, 'replaceTokenCallback'),
            $buffer
        );

        return $buffer;
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
     * Adds a token element to the map of tokens to replace.
     *
     * @return object The token added to the map of replacements.
     *                Must not be <code>null</code>.
     */
    public function createToken()
    {
        $num = array_push($this->_tokens, new Token());

        return $this->_tokens[$num - 1];
    }

    /**
     * Adds a token source to the sources of this filter.
     *
     * @return object A Reference to the source just added.
     */
    public function createTokensource()
    {
        $num = array_push($this->_tokensources, new TokenSource());

        return $this->_tokensources[$num - 1];
    }

    /**
     * Sets the map of tokens to replace.
     * ; used by ReplaceTokens::chain()
     *
     * @param $tokens
     * @throws Exception
     * @internal param A $array map (String->String) of token keys to replacement
     *              values. Must not be <code>null</code>.
     */
    public function setTokens($tokens)
    {
        // type check, error must never occur, bad code of it does
        if (!is_array($tokens)) {
            throw new Exception("Excpected 'array', got something else");
        }

        $this->_tokens = $tokens;
    }

    /**
     * Returns the map of tokens which will be replaced.
     * ; used by ReplaceTokens::chain()
     *
     * @return array A map (String->String) of token keys to replacement values.
     */
    public function getTokens()
    {
        return $this->_tokens;
    }

    /**
     * Sets the tokensources to use; used by ReplaceTokens::chain()
     *
     * @param $sources
     * @throws Exception
     * @internal param An $array array of token sources.
     */
    public function setTokensources($sources)
    {
        // type check
        if (!is_array($sources)) {
            throw new Exception("Exspected 'array', got something else");
        }
        $this->_tokensources = $sources;
    }

    /**
     * Returns the token sources used by this filter; used by ReplaceTokens::chain()
     *
     * @return array
     */
    public function getTokensources()
    {
        return $this->_tokensources;
    }

    /**
     * Creates a new ReplaceTokens using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader
     * @throws Exception
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering
     *                the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new ReplaceTokens($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setBeginToken($this->getBeginToken());
        $newFilter->setEndToken($this->getEndToken());
        $newFilter->setTokens($this->getTokens());
        $newFilter->setTokensources($this->getTokensources());
        $newFilter->setInitialized(true);

        return $newFilter;
    }

    /**
     * Initializes tokens and loads the replacee-replacer hashtable.
     * This method is only called when this filter is used through
     * a <filterreader> tag in build file.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0; $i < count($params); $i++) {
                if ($params[$i] !== null) {
                    $type = $params[$i]->getType();
                    if ($type === "tokenchar") {
                        $name = $params[$i]->getName();
                        if ($name === "begintoken") {
                            $this->_beginToken = substr($params[$i]->getValue(), 0, strlen($params[$i]->getValue()));
                        } else {
                            if ($name === "endtoken") {
                                $this->_endToken = substr($params[$i]->getValue(), 0, strlen($params[$i]->getValue()));
                            }
                        }
                    } else {
                        if ($type === "token") {
                            $name = $params[$i]->getName();
                            $value = $params[$i]->getValue();

                            $tok = new Token();
                            $tok->setKey($name);
                            $tok->setValue($value);

                            array_push($this->_tokens, $tok);
                        } else {
                            if ($type === "tokensource") {
                                // Store data from nested tags in local array
                                $arr = array();

                                $subparams = $params[$i]->getParams();
                                foreach ($subparams as $subparam) {
                                    $arr[$subparam->getName()] = $subparam->getValue();
                                }

                                // Create TokenSource
                                $tokensource = new TokenSource();
                                if (isset($arr["classname"])) {
                                    $tokensource->setClassname($arr["classname"]);
                                }

                                // Copy other parameters 1:1 to freshly created TokenSource
                                foreach ($arr as $key => $value) {
                                    if (strtolower($key) === "classname") {
                                        continue;
                                    }
                                    $param = $tokensource->createParam();
                                    $param->setName($key);
                                    $param->setValue($value);
                                }

                                $this->_tokensources[] = $tokensource;
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Holds a token.
 *
 * @package   phing.filters
 */
class Token
{

    /**
     * Token key.
     * @var string
     */
    private $_key;

    /**
     * Token value.
     * @var string
     */
    private $_value;

    /**
     * Sets the token key.
     *
     * @param string $key The key for this token. Must not be <code>null</code>.
     */
    public function setKey($key)
    {
        $this->_key = (string) $key;
    }

    /**
     * Sets the token value.
     *
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    public function setValue($value)
    {
        // special case for boolean values
        if (is_bool($value)) {
            if ($value) {
                $this->_value = "true";
            } else {
                $this->_value = "false";
            }
        } else {
            $this->_value = (string) $value;
        }
    }

    /**
     * Returns the key for this token.
     *
     * @return string The key for this token.
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Returns the value for this token.
     *
     * @return string The value for this token.
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Sets the token value from text.
     *
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    public function addText($value)
    {
        $this->setValue($value);
    }
}
