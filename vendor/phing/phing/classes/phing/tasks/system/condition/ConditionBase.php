<?php
/*
 *  $Id: 51ec1dd117e7c11d416654a444491cab3028073d $
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

require_once 'phing/ProjectComponent.php';
include_once 'phing/Project.php';
include_once 'phing/tasks/system/AvailableTask.php';
include_once 'phing/tasks/system/condition/Condition.php';
include_once 'phing/parser/CustomChildCreator.php';

/**
 * Abstract baseclass for the <condition> task as well as several
 * conditions - ensures that the types of conditions inside the task
 * and the "container" conditions are in sync.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 51ec1dd117e7c11d416654a444491cab3028073d $
 * @package   phing.tasks.system.condition
 */
abstract class ConditionBase extends ProjectComponent
    implements IteratorAggregate, CustomChildCreator
{

    public $conditions = array(); // needs to be public for "inner" class access

    /**
     * @return int
     */
    public function countConditions()
    {
        return count($this->conditions);
    }

    /**
     * Required for IteratorAggregate
     */
    public function getIterator()
    {
        return new ConditionEnumeration($this);
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param AvailableTask $a
     * @return void
     */
    public function addAvailable(AvailableTask $a)
    {
        $this->conditions[] = $a;
    }

    /**
     * @return NotCondition
     */
    public function createNot()
    {
        include_once 'phing/tasks/system/condition/NotCondition.php';
        $num = array_push($this->conditions, new NotCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return AndCondition
     */
    public function createAnd()
    {
        include_once 'phing/tasks/system/condition/AndCondition.php';
        $num = array_push($this->conditions, new AndCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OrCondition
     */
    public function createOr()
    {
        include_once 'phing/tasks/system/condition/OrCondition.php';
        $num = array_push($this->conditions, new OrCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return XorCondition
     */
    public function createXor()
    {
        include_once 'phing/tasks/system/condition/XorCondition.php';
        $num = array_push($this->conditions, new XorCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return EqualsCondition
     */
    public function createEquals()
    {
        include_once 'phing/tasks/system/condition/EqualsCondition.php';
        $num = array_push($this->conditions, new EqualsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OsCondition
     */
    public function createOs()
    {
        include_once 'phing/tasks/system/condition/OsCondition.php';
        $num = array_push($this->conditions, new OsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsFalseCondition
     */
    public function createIsFalse()
    {
        include_once 'phing/tasks/system/condition/IsFalseCondition.php';
        $num = array_push($this->conditions, new IsFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsTrueCondition
     */
    public function createIsTrue()
    {
        include_once 'phing/tasks/system/condition/IsTrueCondition.php';
        $num = array_push($this->conditions, new IsTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyFalseCondition
     */
    public function createIsPropertyFalse()
    {
        include_once 'phing/tasks/system/condition/IsPropertyFalseCondition.php';
        $num = array_push($this->conditions, new IsPropertyFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyTrueCondition
     */
    public function createIsPropertyTrue()
    {
        include_once 'phing/tasks/system/condition/IsPropertyTrueCondition.php';
        $num = array_push($this->conditions, new IsPropertyTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ContainsCondition
     */
    public function createContains()
    {
        include_once 'phing/tasks/system/condition/ContainsCondition.php';
        $num = array_push($this->conditions, new ContainsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsSetCondition
     */
    public function createIsSet()
    {
        include_once 'phing/tasks/system/condition/IsSetCondition.php';
        $num = array_push($this->conditions, new IsSetCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ReferenceExistsCondition
     */
    public function createReferenceExists()
    {
        include_once 'phing/tasks/system/condition/ReferenceExistsCondition.php';
        $num = array_push($this->conditions, new ReferenceExistsCondition());

        return $this->conditions[$num - 1];
    }
    
    public function createVersionCompare()
    {
        include_once 'phing/tasks/system/condition/VersionCompareCondition.php';
        $num = array_push($this->conditions, new VersionCompareCondition());

        return $this->conditions[$num - 1];
    }

    public function createHttp()
    {
        include_once 'phing/tasks/system/condition/HttpCondition.php';
        $num = array_push($this->conditions, new HttpCondition());

        return $this->conditions[$num - 1];
    }

    public function createPhingVersion()
    {
        include_once 'phing/tasks/system/condition/PhingVersion.php';
        $num = array_push($this->conditions, new PhingVersion());

        return $this->conditions[$num - 1];
    }

    public function createHasFreeSpace()
    {
        include_once 'phing/tasks/system/condition/HasFreeSpaceCondition.php';
        $num = array_push($this->conditions, new HasFreeSpaceCondition());

        return $this->conditions[$num - 1];
    }

    public function createFilesMatch()
    {
        include_once 'phing/tasks/system/condition/FilesMatch.php';
        $num = array_push($this->conditions, new FilesMatch());

        return $this->conditions[$num - 1];
    }

    public function createSocket()
    {
        include_once 'phing/tasks/system/condition/SocketCondition.php';
        $num = array_push($this->conditions, new SocketCondition());

        return $this->conditions[$num - 1];
    }

    public function createIsFailure()
    {
        include_once 'phing/tasks/system/condition/IsFailure.php';
        $num = array_push($this->conditions, new IsFailure());

        return $this->conditions[$num - 1];
    }

    /**
     * @param  string         $elementName
     * @param  Project        $project
     * @throws BuildException
     * @return Condition
     */
    public function customChildCreator($elementName, Project $project)
    {
        $condition = $project->createCondition($elementName);
        $num = array_push($this->conditions, $condition);

        return $this->conditions[$num - 1];
    }

}

/**
 * "Inner" class for handling enumerations.
 * Uses build-in PHP5 iterator support.
 *
 * @package   phing.tasks.system.condition
 */
class ConditionEnumeration implements Iterator
{

    /** Current element number */
    private $num = 0;

    /** "Outer" ConditionBase class. */
    private $outer;

    /**
     * @param ConditionBase $outer
     */
    public function __construct(ConditionBase $outer)
    {
        $this->outer = $outer;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->outer->countConditions() > $this->num;
    }

    public function current()
    {
        $o = $this->outer->conditions[$this->num];
        if ($o instanceof ProjectComponent) {
            $o->setProject($this->outer->getProject());
        }

        return $o;
    }

    public function next()
    {
        $this->num++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->num;
    }

    public function rewind()
    {
        $this->num = 0;
    }
}
