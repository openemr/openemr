<?php
/*
 *  $Id: 66a06bb5f7df99f501c5fe7d427c39d7ea661051 $
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

require_once 'phing/Task.php';

/**
 * Use introspection to "adapt" an arbitrary ( not extending Task, but with
 * similar patterns).
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 66a06bb5f7df99f501c5fe7d427c39d7ea661051 $
 * @package   phing
 */
class TaskAdapter extends Task
{

    /** target object */
    private $proxy;

    /**
     * Main entry point.
     * @throws BuildException
     * @throws Exception
     * @return void
     */
    public function main()
    {

        if (method_exists($this->proxy, "setProject")) {
            try { // try to set project
                $this->proxy->setProject($this->project);
            } catch (Exception $ex) {
                $this->log("Error setting project in " . get_class($this->proxy) . Project::MSG_ERR);
                throw new BuildException($ex);
            }
        } else {
            throw new Exception("Error setting project in class " . get_class($this->proxy));
        }

        if (method_exists($this->proxy, "main")) {
            try { //try to call main
                $this->proxy->main($this->project);
            } catch (BuildException $be) {
                throw $be;
            } catch (Exception $ex) {
                $this->log("Error in " . get_class($this->proxy), Project::MSG_ERR);
                throw new BuildException("Error in " . get_class($this->proxy), $ex);
            }
        } else {
            throw new BuildException("Your task-like class '" . get_class(
                    $this->proxy
                ) . "' does not have a main() method");
        }
    }

    /**
     * Set the target object.
     * @param  object $o
     * @return void
     */
    public function setProxy($o)
    {
        $this->proxy = $o;
    }

    /**
     * Gets the target object.
     * @return object
     */
    public function getProxy()
    {
        return $this->proxy;
    }

}
