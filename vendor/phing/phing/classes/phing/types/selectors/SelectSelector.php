<?php

/*
 * $Id: 7c818dba6e4f6075f71fee4beb41767a280706ad $
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

require_once 'phing/types/selectors/AndSelector.php';

/**
 * This selector just holds one other selector and forwards all
 * requests to it. It exists so that there is a single selector
 * type that can exist outside of any targets, as an element of
 * project. It overrides all of the reference stuff so that it
 * works as expected. Note that this is the only selector you
 * can reference.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Bruce Atherton <bruce@callenish.com> (Ant)
 * @version   $Id: 7c818dba6e4f6075f71fee4beb41767a280706ad $
 * @package   phing.types.selectors
 */
class SelectSelector extends AndSelector
{

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "";
        if ($this->hasSelectors()) {
            $buf .= "{select: ";
            $buf .= parent::toString();
            $buf .= "}";
        }

        return $buf;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced Selector.
     */
    private function getRef()
    {
        $o = $this->getCheckedRef(get_class($this), "SelectSelector");

        return $o;
    }

    /**
     * Indicates whether there are any selectors here.
     */
    public function hasSelectors()
    {
        if ($this->isReference()) {
            return $this->getRef()->hasSelectors();
        }

        return parent::hasSelectors();
    }

    /**
     * Gives the count of the number of selectors in this container
     */
    public function selectorCount()
    {
        if ($this->isReference()) {
            return $this->getRef()->selectorCount();
        }

        return parent::selectorCount();
    }

    /**
     * Returns the set of selectors as an array.
     * @param Project $p
     * @return \an|array
     */
    public function getSelectors(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef()->getSelectors($p);
        }

        return parent::getSelectors($p);
    }

    /**
     * Returns an enumerator for accessing the set of selectors.
     */
    public function selectorElements()
    {
        if ($this->isReference()) {
            return $this->getRef()->selectorElements();
        }

        return parent::selectorElements();
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector|the $selector
     * @throws BuildException
     * @internal param the $selector new selector to add
     * @return the selector that was added
     */
    public function appendSelector(FileSelector $selector)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        parent::appendSelector($selector);
    }

    /**
     * Makes sure that there is only one entry, sets an error message if
     * not.
     */
    public function verifySettings()
    {
        if ($this->selectorCount() != 1) {
            $this->setError(
                "One and only one selector is allowed within the "
                . "<selector> tag"
            );
        }
    }

}
