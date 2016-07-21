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

require_once 'phing/util/DataStore.php';

use PHPMD\AbstractRenderer;
use PHPMD\Report;

/**
 * This class will remove files with violations from cache
 *
 * @category  PHP
 * @package   PHPMD
 * @author    Rui Filipe Da Cunha Alves <ruifil@ruifil.com>
 */
class PHPMDRendererRemoveFromCache extends AbstractRenderer
{
    /**
     * Cache data storage
     * @var DataStore
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param DataStore $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * This method will be called when the engine has finished the source
     * analysis phase. To remove file with violations from cache.
     *
     * @param Report $report
     * @return void
     */
    public function renderReport(Report $report)
    {
        foreach ($report->getRuleViolations() as $violation) {
            $fileName = $violation->getFileName();
            $this->cache->remove($fileName, null);
        }
    }
}
