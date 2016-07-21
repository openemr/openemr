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

include_once 'phing/system/io/BufferedWriter.php';
include_once 'phing/system/io/OutputStreamWriter.php';

class PrintStream
{
    /**
     * @var bool
     */
    private $autoFlush = false;

    /**
     * @var BufferedWriter
     */
    private $textOut;

    /**
     * @var OutputStream
     */
    protected $out;

    /**
     * @param OutputStream $out
     * @param bool $autoFlush
     */
    public function __construct(OutputStream $out, $autoFlush = false)
    {
        $this->out = $out;
        $this->autoFlush = $autoFlush;

        $this->textOut = new BufferedWriter(new OutputStreamWriter($out));
    }

    /**
     * @param mixed $value
     */
    public function println($value)
    {
        $this->prints($value);
        $this->newLine();
    }

    /**
     * @param mixed $value
     */
    public function prints($value)
    {
        if (is_bool($value)) {
            $value = $value === true ? 'true' : 'false';
        }

        $this->write((string) $value);
    }

    /**
     *
     */
    private function newLine()
    {
        $this->textOut->newLine();

        if ($this->autoFlush) {
            $this->textOut->flush();
        }
    }

    /**
     * @param string $buf
     * @param int $off
     * @param int $len
     */
    private function write($buf, $off = null, $len = null)
    {
        $this->textOut->write($buf, $off, $len);

        if ($this->autoFlush || $buff = '\n' && $this->autoFlush) {
            $this->textOut->flush();
        }
    }
}
