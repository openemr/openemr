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

include_once 'phing/BuildListener.php';

/**
 * Instances of classes that implement this interface can register
 * to be also notified when things happened during a subbuild.
 *
 * <p>A subbuild is a separate project instance created by the
 * <code>&lt;phing&gt;</code> task family.  These project instances will
 * never fire the buildStarted and buildFinished events, but they will
 * fire subBuildStarted/ and subBuildFinished.  The main project
 * instance - the one created by running Phing in the first place - will
 * never invoke one of the methods of this interface.</p>
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing
 */
interface SubBuildListener extends BuildListener
{
    /**
     * Signals that a subbuild has started. This event
     * is fired before any targets have started.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Must not be <code>null</code>.
     */
    public function subBuildStarted(BuildEvent $event);

    /**
     * Signals that the last target has finished. This event
     * will still be fired if an error occurred during the build.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Must not be <code>null</code>.
     *
     * @see BuildEvent::getException()
     */
    public function subBuildFinished(BuildEvent $event);
}
