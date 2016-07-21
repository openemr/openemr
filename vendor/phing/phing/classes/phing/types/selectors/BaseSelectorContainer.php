<?php

/*
 * $Id: f3f01f53eeee0ccfc7066a5335b69e697244375d $
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

require_once 'phing/types/selectors/SelectorContainer.php';
require_once 'phing/types/selectors/BaseSelector.php';

/**
 * This is the base class for selectors that can contain other selectors.
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a> (Ant)
 * @package phing.types.selectors
 */
abstract class BaseSelectorContainer extends BaseSelector implements SelectorContainer
{

    private $selectorsList = array();

    /**
     * Indicates whether there are any selectors here.
     */
    public function hasSelectors()
    {
        return !(empty($this->selectorsList));
    }

    /**
     * Gives the count of the number of selectors in this container
     */
    public function selectorCount()
    {
        return count($this->selectorsList);
    }

    /**
     * Returns a copy of the selectors as an array.
     * @param Project $p
     * @return array
     */
    public function getSelectors(Project $p)
    {
        $result = array();
        for ($i = 0, $size = count($this->selectorsList); $i < $size; $i++) {
            $result[] = clone $this->selectorsList[$i];
        }

        return $result;
    }

    /**
     * Returns an array for accessing the set of selectors (not a copy).
     */
    public function selectorElements()
    {
        return $this->selectorsList;
    }

    /**
     * Convert the Selectors within this container to a string. This will
     * just be a helper class for the subclasses that put their own name
     * around the contents listed here.
     *
     * @return string comma separated list of Selectors contained in this one
     */
    public function toString()
    {
        $buf = "";
        $arr = $this->selectorElements();
        for ($i = 0, $size = count($arr); $i < $size; $i++) {
            $buf .= $arr[$i]->toString() . (isset($arr[$i + 1]) ? ', ' : '');
        }

        return $buf;
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector|the $selector
     * @internal param the $selector new selector to add
     * @return the selector that was added
     */
    public function appendSelector(FileSelector $selector)
    {
        $this->selectorsList[] = $selector;
    }

    /**
     * <p>This implementation validates the container by calling
     * verifySettings() and then validates each contained selector
     * provided that the selector implements the validate interface.
     * </p>
     * <p>Ordinarily, this will validate all the elements of a selector
     * container even if the isSelected() method of some elements is
     * never called. This has two effects:</p>
     * <ul>
     * <li>Validation will often occur twice.
     * <li>Since it is not required that selectors derive from
     * BaseSelector, there could be selectors in the container whose
     * error conditions are not detected if their isSelected() call
     * is never made.
     * </ul>
     */
    public function validate()
    {
        $this->verifySettings();
        $errmsg = $this->getError();
        if ($errmsg !== null) {
            throw new BuildException($errmsg);
        }
        foreach ($this->selectorsList as $o) {
            if ($o instanceof BaseSelector) {
                $o->validate();
            }
        }
    }

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     */
    public function createSelector()
    {
        $o = new SelectSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an "And" selector entry on the selector list
     */
    public function createAnd()
    {
        $o = new AndSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an "Or" selector entry on the selector list
     */
    public function createOr()
    {
        $o = new OrSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a "Not" selector entry on the selector list
     */
    public function createNot()
    {
        $o = new NotSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a "None" selector entry on the selector list
     */
    public function createNone()
    {
        $o = new NoneSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a majority selector entry on the selector list
     */
    public function createMajority()
    {
        $o = new MajoritySelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector date entry on the selector list
     */
    public function createDate()
    {
        $o = new DateSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector size entry on the selector list
     */
    public function createSize()
    {
        $o = new SizeSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a selector filename entry on the selector list
     */
    public function createFilename()
    {
        $o = new FilenameSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add an extended selector entry on the selector list
     */
    public function createCustom()
    {
        $o = new ExtendSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a contains selector entry on the selector list
     */
    public function createContains()
    {
        $o = new ContainsSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a contains selector entry on the selector list
     */
    public function createContainsRegexp()
    {
        $o = new ContainsRegexpSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a present selector entry on the selector list
     */
    public function createPresent()
    {
        $o = new PresentSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a depth selector entry on the selector list
     */
    public function createDepth()
    {
        $o = new DepthSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a depends selector entry on the selector list
     */
    public function createDepend()
    {
        $o = new DependSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a type selector entry on the selector list
     */
    public function createType()
    {
        $o = new TypeSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a readable selector entry on the selector list
     */
    public function createReadable()
    {
        $o = new ReadableSelector();
        $this->appendSelector($o);

        return $o;
    }

    /**
     * add a writable selector entry on the selector list
     */
    public function createWritable()
    {
        $o = new WritableSelector();
        $this->appendSelector($o);

        return $o;
    }
}
