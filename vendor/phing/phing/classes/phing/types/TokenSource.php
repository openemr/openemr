<?php
/*
 *  $Id: 8364cd2c776b9cc8e544da4eb56cf96ef5b4e7df $
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

require_once 'phing/types/DataType.php';
include_once 'phing/util/StringHelper.php';

/**
 * A parameter is composed of a name, type and value.
 *
 * Example of usage:
 *
 * <replacetokens>
 *   <tokensource classname="phing.filters.util.IniFileTokenReader">
 *     <!-- all params for the TokenReader here -->
 *     <param name="file" value="tokens.ini" />
 *   </tokensource>
 * </replacetokens>
 *
 * or:
 *
 * <filterreader classname="phing.filters.ReplaceTokens">
 *   <param type="tokensource>
 *     <param name="classname" value="phing.filters.util.IniFileTokenReader" />
 *     <param name="file" value="tokens.ini" />
 *   </param>
 * </filterreader>
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @package   phing.types
 */
class TokenSource extends DataType
{

    /**
     * String to hold the path to the TokenReader
     * @var     string
     */
    protected $classname = null;

    /**
     * Array holding parameters for the wrapped TokenReader.
     * @var array
     */
    protected $parameters = array();

    /**
     * Reference to the TokenReader used by this TokenSource
     * @var TokenReader
     */
    protected $reader;

    /**
     * Array with key/value pairs of tokens
     */
    protected $tokens = array();

    /**
     * This method is called to load the sources from the reader
     * into the buffer of the source.
     */
    public function load()
    {
        // Create new Reader
        if ($this->classname === null) {
            throw new BuildException("No Classname given to TokenSource.");
        }

        $classname = Phing::import($this->classname);
        $this->reader = new $classname($this->project);

        // Configure Reader
        $this->configureTokenReader($this->reader);

        // Load Tokens
        try {
            while ($token = $this->reader->readToken()) {
                $this->tokens[] = $token;
            }
        } catch (BuildException $e) {
            $this->log("Error reading TokenSource: " . $e->getMessage(), Project::MSG_WARN);
        } catch (IOException $e) {
            $this->log("Error reading TokenSource: " . $e->getMessage(), Project::MSG_WARN);
        }
    }

    /**
     * This function uses the wrapper to read the tokens and then
     * returns them.
     *
     */
    public function getTokens()
    {
        if (count($this->tokens) == 0) {
            $this->Load();
        }

        return $this->tokens;
    }

    /**
     * Configures a TokenReader with the parameters passed to the
     * TokenSource.
     * @param TokenReader $reader
     */
    private function configureTokenReader(TokenReader $reader)
    {
        $count = count($this->parameters);
        for ($i = 0; $i < $count; $i++) {
            $method_name = "Set" . $this->parameters[$i]->getName();
            $value = $this->parameters[$i]->getValue();
            $reader->$method_name($value);
        }
    }

    /**
     * Set the classname (dot-path) to use for handling token replacement.
     * @param string $c
     */
    public function setClassname($c)
    {
        $this->classname = $c;
    }

    /**
     * Returns the qualified classname (dot-path) to use for handling token replacement.
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * Create nested <param> tag.
     * Uses standard name/value Parameter class.
     * @return Parameter
     */
    public function createParam()
    {
        $num = array_push($this->parameters, new Parameter());

        return $this->parameters[$num - 1];
    }
}
