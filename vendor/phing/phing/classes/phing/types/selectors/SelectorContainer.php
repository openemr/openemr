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

/**
 * This is the interface for selectors that can contain other selectors.
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a>
 *
 * @package phing.types.selectors
 */
interface SelectorContainer
{
    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool whether any selectors are in this container
     */
    public function hasSelectors();

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int the number of selectors in this container
     */
    public function selectorCount();

    /**
     * Returns a *copy* of the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return array an array of selectors in this container
     */
    public function getSelectors(Project $p);

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return an enumerator that goes through each of the selectors
     */
    public function selectorElements();

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector the new selector to add
     *
     * @return FileSelector the selector that was added
     */
    public function appendSelector(FileSelector $selector);

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     */
    public function createSelector();

    /**
     * add an "And" selector entry on the selector list
     */
    public function createAnd();

    /**
     * add an "Or" selector entry on the selector list
     */
    public function createOr();

    /**
     * add a "Not" selector entry on the selector list
     */
    public function createNot();

    /**
     * add a "None" selector entry on the selector list
     */
    public function createNone();

    /**
     * add a majority selector entry on the selector list
     */
    public function createMajority();

    /**
     * add a selector date entry on the selector list
     */
    public function createDate();

    /**
     * add a selector size entry on the selector list
     */
    public function createSize();

    /**
     * add a selector filename entry on the selector list
     */
    public function createFilename();

    /**
     * add an extended selector entry on the selector list
     */
    public function createCustom();

    /**
     * add a contains selector entry on the selector list
     */
    public function createContains();

    /**
     * add a present selector entry on the selector list
     */
    public function createPresent();

    /**
     * add a depth selector entry on the selector list
     */
    public function createDepth();

    /**
     * add a depends selector entry on the selector list
     */
    public function createDepend();

}
