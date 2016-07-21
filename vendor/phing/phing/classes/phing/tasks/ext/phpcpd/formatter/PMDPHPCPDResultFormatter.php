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

require_once 'phing/tasks/ext/phpcpd/formatter/PHPCPDResultFormatter.php';

/**
 * Prints PMD-XML output of phpcpd run
 *
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 *
 * @package phing.tasks.ext.phpcpd.formatter
 */
class PMDPHPCPDResultFormatter extends PHPCPDResultFormatter
{
    /**
     * Processes a list of clones.
     *
     * @param PHPCPD_CloneMap|CodeCloneMap $clones
     * @param Project                      $project
     * @param boolean                      $useFile
     * @param PhingFile|null               $outFile
     *
     * @throws BuildException
     */
    public function processClones($clones, Project $project, $useFile = false, $outFile = null)
    {
        if (!$useFile || empty($outFile)) {
            throw new BuildException('Output filename required for this formatter');
        }

        if (get_class($clones) == 'PHPCPD_CloneMap') {
            $logger = new PHPCPD_Log_XML_PMD($outFile);
        } else {
            $logger = new \SebastianBergmann\PHPCPD\Log\PMD($outFile);
        }

        $logger->processClones($clones);
    }
}
