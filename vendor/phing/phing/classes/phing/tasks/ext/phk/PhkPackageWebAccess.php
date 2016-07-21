<?php
/**
 * $Id: 37ba9ef129372cad5ee8e00607f98042ec1c59fe $
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

require_once 'phing/tasks/ext/phk/PhkPackageWebAccessPath.php';

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @package phing.tasks.ext.phk
 */
class PhkPackageWebAccess
{
    /**
     * @var array
     */
    private $paths = array();

    /**
     * @return PhkPackageWebAccessPath
     */
    public function createPath()
    {
        return ($this->paths[] = new PhkPackageWebAccessPath());
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        /*
         * Get real paths...
         */
        $paths = array();

        foreach ($this->paths as $path) {
            $paths[] = $path->getPath();
        }

        return $paths;
    }
}
