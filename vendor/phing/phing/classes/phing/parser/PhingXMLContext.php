<?php
/*
 * $Id: 077aa8989e2f94958a9e053a52a3eff7e58938b0 $
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
 * Track the current state of the Xml parse operation.
 *
 * @author    Bryan Davis <bpd@keynetics.com>
 * @version   $Id: 077aa8989e2f94958a9e053a52a3eff7e58938b0 $
 * @package   phing.parser
 */
class PhingXMLContext
{

    /**
     * Target that will hold all tasks/types placed outside of targets
     *
     * @var Target
     */
    private $implicitTarget;

    /**
     * Current target
     *
     * @var Target
     */
    private $currentTarget = null;

    /**
     * List of current targets
     *
     * @var Target[]
     */
    private $currentTargets = null;

    /**
     * Constructor
     * @param Project $project the project to which this antxml context belongs to
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->implicitTarget = new Target();
        $this->implicitTarget->setName("");
        $this->implicitTarget->setHidden(true);
    }

    /** The project to configure. */
    private $project;

    private $configurators = array();

    /**
     * @param $cfg
     */
    public function startConfigure($cfg)
    {
        $this->configurators[] = $cfg;
    }

    public function endConfigure()
    {
        array_pop($this->configurators);
    }

    /**
     * @return null
     */
    public function getConfigurator()
    {
        $l = count($this->configurators);
        if (0 == $l) {
            return null;
        } else {
            return $this->configurators[$l - 1];
        }
    }

    /** Impoerted files */
    private $importStack = array();

    /**
     * @param $file
     */
    public function addImport($file)
    {
        $this->importStack[] = $file;
    }

    /**
     * @return array
     */
    public function getImportStack()
    {
        return $this->importStack;
    }

    /**
     * find out the project to which this context belongs
     * @return project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return Target
     */
    public function getImplicitTarget()
    {
        return $this->implicitTarget;
    }

    /**
     * @param Target $target
     */
    public function setImplicitTarget(Target $target)
    {
        $this->implicitTarget = $target;
    }

    /**
     * @return Target
     */
    public function getCurrentTarget()
    {
        return $this->currentTarget;
    }

    /**
     * @param Target $target
     */
    public function setCurrentTarget(Target $target)
    {
        $this->currentTarget = $target;
    }

    /**
     * @return Target[]
     */
    public function &getCurrentTargets()
    {
        return $this->currentTargets;
    }

    /**
     * @param Target[] $currentTargets
     */
    public function setCurrentTargets(array $currentTargets)
    {
        $this->currentTargets = $currentTargets;
    }

} //end PhingXMLContext
