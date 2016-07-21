<?php
/*
 *  $Id: 8f108ef006e6cc3be1e0449384ff96e53b8e2e39 $
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

require_once 'phing/input/MultipleChoiceInputRequest.php';

/**
 * Encapsulates an input request that returns a boolean (yes/no).
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @version $Id: 8f108ef006e6cc3be1e0449384ff96e53b8e2e39 $
 * @package phing.input
 */
class YesNoInputRequest extends MultipleChoiceInputRequest
{

    /**
     * @return true if the input is one of the allowed values.
     */
    public function isInputValid()
    {
        return StringHelper::isBoolean($this->input);
    }

    /**
     * Converts input to boolean.
     * @return boolean
     */
    public function getInput()
    {
        return StringHelper::booleanValue($this->input);
    }
}
