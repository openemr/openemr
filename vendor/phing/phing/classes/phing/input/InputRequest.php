<?php

/*
 *  $Id: ff401fe7e6280742731cece9d15924c13dad8b1e $
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

/**
 * Encapsulates an input request.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @version $Id: ff401fe7e6280742731cece9d15924c13dad8b1e $
 * @package phing.input
 */
class InputRequest
{

    protected $prompt;
    protected $input;
    protected $defaultValue;
    protected $promptChar;

    /**
     * @param string $prompt The prompt to show to the user.  Must not be null.
     * @throws BuildException
     */
    public function __construct($prompt)
    {
        if ($prompt === null) {
            throw new BuildException("prompt must not be null");
        }
        $this->prompt = $prompt;
    }

    /**
     * Retrieves the prompt text.
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * Sets the user provided input.
     * @param $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Is the user input valid?
     */
    public function isInputValid()
    {
        return true;
    }

    /**
     * Retrieves the user input.
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the default value to use.
     * @param mixed $v
     */
    public function setDefaultValue($v)
    {
        $this->defaultValue = $v;
    }

    /**
     * Return the default value to use.
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value to use.
     * @param string $c
     */
    public function setPromptChar($c)
    {
        $this->promptChar = $c;
    }

    /**
     * Return the default value to use.
     * @return string
     */
    public function getPromptChar()
    {
        return $this->promptChar;
    }
}
