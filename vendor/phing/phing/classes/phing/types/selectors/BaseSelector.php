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

require_once 'phing/types/selectors/FileSelector.php';

/**
 * A convenience base class that you can subclass Selectors from. It
 * provides some helpful common behaviour. Note that there is no need
 * for Selectors to inherit from this class, it is only necessary that
 * they implement FileSelector.
 *
 * {@inheritdoc}
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a>
 *
 * @package phing.types.selectors
 */
abstract class BaseSelector extends DataType implements FileSelector
{
    /** @var string $errmsg */
    private $errmsg = null;

    /**
     * Allows all selectors to indicate a setup error. Note that only
     * the first error message is recorded.
     *
     * @param string $msg The error message any BuildException should throw.
     *
     * @return void
     */
    public function setError($msg)
    {
        if ($this->errmsg === null) {
            $this->errmsg = $msg;
        }
    }

    /**
     * Returns any error messages that have been set.
     *
     * @return string the error condition
     */
    public function getError()
    {
        return $this->errmsg;
    }

    /**
     * <p>Subclasses can override this method to provide checking of their
     * state. So long as they call validate() from isSelected(), this will
     * be called automatically (unless they override validate()).</p>
     * <p>Implementations should check for incorrect settings and call
     * setError() as necessary.</p>
     */
    public function verifySettings()
    {
    }

    /**
     * Subclasses can use this to throw the requisite exception
     * in isSelected() in the case of an error condition.
     *
     * @throws BuildException
     */
    public function validate()
    {
        if ($this->getError() === null) {
            $this->verifySettings();
        }
        if ($this->getError() !== null) {
            throw new BuildException($this->errmsg);
        }
    }
}
