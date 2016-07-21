<?php

/*
 * $Id: 11f5ebfbb271f1fc34e3f6e2795510f22306099e $
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

require_once 'phing/types/selectors/NoneSelector.php';

/**
 * This selector has one other selectors whose meaning it inverts. It
 * actually relies on NoneSelector for its implementation of the
 * isSelected() method, but it adds a check to ensure there is only one
 * other selector contained within.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class NotSelector extends NoneSelector
{

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "";
        if ($this->hasSelectors()) {
            $buf .= "{notselect: ";
            $buf .= parent::toString();
            $buf .= "}";
        }

        return $buf;
    }

    /**
     * Makes sure that there is only one entry, sets an error message if
     * not.
     */
    public function verifySettings()
    {
        if ($this->selectorCount() != 1) {
            $this->setError(
                "One and only one selector is allowed within the " .
                "<not> tag"
            );
        }
    }

}
