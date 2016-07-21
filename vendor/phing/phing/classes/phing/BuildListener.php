<?php
/*
 *  $Id: e0f8f926fe954fa2128283a0707dda812fcf1708 $
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
 * Interface for build listeners.
 *
 * Classes that implement a listener must extend this class and (faux)implement
 * all methods that are decleard as dummies below.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: e0f8f926fe954fa2128283a0707dda812fcf1708 $
 * @see       BuildEvent
 * @see       Project::addBuildListener()
 * @package   phing
 */
interface BuildListener
{

    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent $event The BuildEvent
     */
    public function buildStarted(BuildEvent $event);

    /**
     * Fired after the last target has finished.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event);

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event);

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent#getException()
     */
    public function targetFinished(BuildEvent $event);

    /**
     * Fired when a task is started.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event);

    /**
     * Fired when a task has finished.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event);

    /**
     * Fired whenever a message is logged.
     *
     * @param BuildEvent $event The BuildEvent
     * @see BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event);
}
