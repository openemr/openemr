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

require_once 'phing/input/InputHandler.php';
include_once 'phing/system/io/ConsoleReader.php';

/**
 * Prompts using print(); reads input from Console.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 *
 * @package phing.input
 */
class DefaultInputHandler implements InputHandler
{
    /**
     * Prompts and requests input.
     * May loop until a valid input has been entered.
     *
     * @param InputRequest $request
     *
     * @return void
     *
     * @throws BuildException
     */
    public function handleInput(InputRequest $request)
    {
        $prompt = $this->getPrompt($request);
        $in = new ConsoleReader();
        do {
            print $prompt;
            try {
                $input = $in->readLine();
                if ($input === "" && ($request->getDefaultValue() !== null)) {
                    $input = $request->getDefaultValue();
                }
                $request->setInput($input);
            } catch (Exception $e) {
                throw new BuildException("Failed to read input from Console.", $e);
            }
        } while (!$request->isInputValid());
    }

    /**
     * Constructs user prompt from a request.
     *
     * <p>This implementation adds (choice1,choice2,choice3,...) to the
     * prompt for <code>MultipleChoiceInputRequest</code>s.</p>
     *
     * @param InputRequest $request the request to construct the prompt for.
     *                              Must not be <code>null</code>.
     *
     * @return string
     */
    protected function getPrompt(InputRequest $request)
    {
        $prompt = $request->getPrompt();
        $defaultValue = $request->getDefaultValue();

        if ($request instanceof YesNoInputRequest) {
            $choices = $request->getChoices();
            $defaultValue = $choices[(int) !$request->getDefaultValue()];
            $prompt .= '(' . implode('/', $request->getChoices()) . ')';
        } elseif ($request instanceof MultipleChoiceInputRequest) { // (a,b,c,d)
            $prompt .= '(' . implode(',', $request->getChoices()) . ')';
        }

        if ($request->getDefaultValue() !== null) {
            $prompt .= ' [' . $defaultValue . ']';
        }
        $pchar = $request->getPromptChar();

        return $prompt . ($pchar ? $pchar . ' ' : ' ');
    }
}
