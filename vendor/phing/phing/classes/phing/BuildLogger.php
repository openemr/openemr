<?php
/*
 *  $Id: 695c190fb6a2339063b67d1557a6af553e6ef936 $
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

require_once 'phing/BuildListener.php';

/**
 * Interface for build loggers.
 *
 * Build loggers are build listeners but with some additional functionality:
 *   - They can be configured with a log level (below which they will ignore messages)
 *   - They have error and output streams
 *
 * Classes that implement a listener must implement this interface.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 695c190fb6a2339063b67d1557a6af553e6ef936 $
 * @see       BuildEvent
 * @see       Project::addBuildListener()
 * @package   phing
 */
interface BuildLogger extends BuildListener
{

    /**
     * Sets the min log level that this logger should respect.
     *
     * Messages below this level are ignored.
     *
     * Constants for the message levels are in Project.php. The order of
     * the levels, from least to most verbose, is:
     *   - Project::MSG_ERR
     *   - Project::MSG_WARN
     *   - Project::MSG_INFO
     *   - Project::MSG_VERBOSE
     *   - Project::MSG_DEBUG
     *
     * @param int $level The log level integer (e.g. Project::MSG_VERBOSE, etc.).
     */
    public function setMessageOutputLevel($level);

    /**
     * Sets the standard output stream to use.
     * @param OutputStream $output Configured output stream (e.g. STDOUT) for standard output.
     */
    public function setOutputStream(OutputStream $output);

    /**
     * Sets the output stream to use for errors.
     * @param OutputStream $err Configured output stream (e.g. STDERR) for errors.
     */
    public function setErrorStream(OutputStream $err);

    /**
     * Sets this logger to produce emacs (and other editor) friendly output.
     *
     * @param bool $emacsMode true if output is to be unadorned so that emacs and other editors
     *                             can parse files names, etc.
     */
    public function setEmacsMode($emacsMode);
}
