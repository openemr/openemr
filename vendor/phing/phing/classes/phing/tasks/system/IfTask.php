<?php

/*
 *  $Id: 92f075809a5c7900b630f1dbb6fe4b544d66279d $
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

require_once 'phing/tasks/system/condition/ConditionBase.php';
require_once 'phing/tasks/system/SequentialTask.php';

/**
 * Perform some tasks based on whether a given condition holds true or
 * not.
 *
 * <p>This task is heavily based on the Condition framework that can
 * be found in Ant 1.4 and later, therefore it cannot be used in
 * conjunction with versions of Ant prior to 1.4.</p>
 *
 * <p>This task doesn't have any attributes, the condition to test is
 * specified by a nested element - see the documentation of your
 * <code><condition&gt;</code> task (see
 * <a href="http://jakarta.apache.org/ant/manual/CoreTasks/condition.html">the
 * online documentation</a> for example) for a complete list of nested
 * elements.</p>
 *
 * <p>Just like the <code><condition&gt;</code> task, only a single
 * condition can be specified - you combine them using
 * <code><and&gt;</code> or <code><or&gt;</code> conditions.</p>
 *
 * <p>In addition to the condition, you can specify three different
 * child elements, <code><elseif&gt;</code>, <code><then&gt;</code> and
 * <code><else&gt;</code>.  All three subelements are optional.
 *
 * Both <code><then&gt;</code> and <code><else&gt;</code> must not be
 * used more than once inside the if task.  Both are
 * containers for Ant tasks, just like Ant's
 * <code><parallel&gt;</code> and <code><sequential&gt;</code>
 * tasks - in fact they are implemented using the same class as Ant's
 * <code><sequential&gt;</code> task.</p>
 *
 *  The <code><elseif&gt;</code> behaves exactly like an <code><if&gt;</code>
 * except that it cannot contain the <code><else&gt;</code> element
 * inside of it.  You may specify as may of these as you like, and the
 * order they are specified is the order they are evaluated in.  If the
 * condition on the <code><if&gt;</code> is false, then the first
 * <code><elseif&gt;</code> who's conditional evaluates to true
 * will be executed.  The <code><else&gt;</code> will be executed
 * only if the <code><if&gt;</code> and all <code><elseif&gt;</code>
 * conditions are false.
 *
 * <p>Use the following task to define the <code><if&gt;</code>
 * task before you use it the first time:</p>
 *
 * <pre><code>
 *   &lt;taskdef name=&quot;if&quot; classname=&quot;net.sf.antcontrib.logic.IfTask&quot; /&gt;
 * </code></pre>
 *
 * <h3>Crude Example</h3>
 *
 * <code>
 * <if>
 *  <equals arg1="${foo}" arg2="bar" />
 *  <then>
 *    <echo message="The value of property foo is bar" />
 *  </then>
 *  <else>
 *    <echo message="The value of property foo is not bar" />
 *  </else>
 * </if>
 * </code>
 *
 * <code>
 * <if>
 *  <equals arg1="${foo}" arg2="bar" /&gt;
 *  <then>
 *   <echo message="The value of property foo is 'bar'" />
 *  </then>
 *
 *  <elseif>
 *   <equals arg1="${foo}" arg2="foo" />
 *   <then>
 *    <echo message="The value of property foo is 'foo'" />
 *   </then>
 *  </elseif>
 *
 *  <else>
 *   <echo message="The value of property foo is not 'foo' or 'bar'" />
 *  </else>
 * </if>
 * </code>
 *
 * @author <a href="mailto:stefan.bodewig@freenet.de">Stefan Bodewig</a>
 * @package phing.tasks.system
 */
class IfTask extends ConditionBase
{

    private $thenTasks = null;
    private $elseIfTasks = array();
    private $elseTasks = null;

    /***
     * A nested Else if task
     * @param ElseIfTask $ei
     */
    public function addElseIf(ElseIfTask $ei)
    {
        $this->elseIfTasks[] = $ei;
    }

    /**
     * A nested <then> element - a container of tasks that will
     * be run if the condition holds true.
     *
     * <p>Not required.</p>
     * @param SequentialTask $t
     * @throws BuildException
     */
    public function addThen(SequentialTask $t)
    {
        if ($this->thenTasks != null) {
            throw new BuildException("You must not nest more than one <then> into <if>");
        }
        $this->thenTasks = $t;
    }

    /**
     * A nested <else> element - a container of tasks that will
     * be run if the condition doesn't hold true.
     *
     * <p>Not required.</p>
     * @param SequentialTask $e
     * @throws BuildException
     */
    public function addElse(SequentialTask $e)
    {
        if ($this->elseTasks != null) {
            throw new BuildException("You must not nest more than one <else> into <if>");
        }
        $this->elseTasks = $e;
    }

    public function main()
    {

        if ($this->countConditions() > 1) {
            throw new BuildException("You must not nest more than one condition into <if>");
        }
        if ($this->countConditions() < 1) {
            throw new BuildException("You must nest a condition into <if>");
        }
        $conditions = $this->getConditions();
        $c = $conditions[0];

        if ($c->evaluate()) {
            if ($this->thenTasks != null) {
                $this->thenTasks->main();
            }
        } else {
            $done = false;
            $sz = count($this->elseIfTasks);
            for ($i = 0; $i < $sz && !$done; $i++) {
                $ei = $this->elseIfTasks[$i];
                if ($ei->evaluate()) {
                    $done = true;
                    $ei->main();
                }
            }

            if (!$done && $this->elseTasks != null) {
                $this->elseTasks->main();
            }
        }
    }
}

/**
 * "Inner" class for IfTask.
 * This class has same basic structure as the IfTask, although of course it doesn't support <else> tags.
 *
 * @package phing.tasks.system
 */
class ElseIfTask extends ConditionBase
{

    private $thenTasks = null;

    /**
     * @param SequentialTask $t
     * @throws BuildException
     */
    public function addThen(SequentialTask $t)
    {
        if ($this->thenTasks != null) {
            throw new BuildException("You must not nest more than one <then> into <elseif>");
        }
        $this->thenTasks = $t;
    }

    /**
     * @throws BuildException
     * @return boolean
     */
    public function evaluate()
    {

        if ($this->countConditions() > 1) {
            throw new BuildException("You must not nest more than one condition into <elseif>");
        }
        if ($this->countConditions() < 1) {
            throw new BuildException("You must nest a condition into <elseif>");
        }

        $conditions = $this->getConditions();
        $c = $conditions[0];

        return $c->evaluate();
    }

    /**
     *
     */
    public function main()
    {
        if ($this->thenTasks != null) {
            $this->thenTasks->main();
        }
    }
}
