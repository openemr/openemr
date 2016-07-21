<?php
/**
 * $Id: 20db9b04d2f5ea6acc7db0f00d83daa6b8580cec $
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

require_once 'phing/tasks/ext/phpcpd/formatter/PHPCPDResultFormatter.php';

/**
 * Prints plain text output of phpcpd run
 *
 * @package phing.tasks.ext.phpcpd.formatter
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: 20db9b04d2f5ea6acc7db0f00d83daa6b8580cec $
 */
class DefaultPHPCPDResultFormatter extends PHPCPDResultFormatter
{
    /**
     * Processes a list of clones.
     *
     * @param CodeCloneMap   $clones
     * @param Project        $project
     * @param boolean        $useFile
     * @param PhingFile|null $outFile
     */
    public function processClones($clones, Project $project, $useFile = false, $outFile = null)
    {
        if (get_class($clones) == 'SebastianBergmann\PHPCPD\CodeCloneMap') {
            if (class_exists('SebastianBergmann\PHPCPD\Log\Text')) {
                $this->processClonesNew($clones, $useFile, $outFile);

                return;
            }

            $logger = new \SebastianBergmann\PHPCPD\TextUI\ResultPrinter();
        } else {
            $logger = new PHPCPD_TextUI_ResultPrinter();
        }

        // default format goes to logs, no buffering
        ob_start();
        $logger->printResult($clones, $project->getBaseDir(), true);
        $output = ob_get_contents();
        ob_end_clean();

        if (!$useFile || empty($outFile)) {
            echo $output;
        } else {
            file_put_contents($outFile->getPath(), $output);
        }
    }

    /**
     * Wrapper for PHPCPD 2.0
     *
     * @param CodeCloneMap   $clones
     * @param boolean        $useFile
     * @param PhingFile|null $outFile
     */
    private function processClonesNew($clones, $useFile = false, $outFile = null)
    {
        if ($useFile) {
            $resource = fopen($outFile->getPath(), "w");
        } else {
            $resource = fopen("php://output", "w");
        }

        $output = new \Symfony\Component\Console\Output\StreamOutput($resource);
        $logger = new \SebastianBergmann\PHPCPD\Log\Text();
        $logger->printResult($output, $clones);
    }
}
