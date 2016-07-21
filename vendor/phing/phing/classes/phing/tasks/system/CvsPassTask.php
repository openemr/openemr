<?php
/*
 *  $Id: d3d7f451134334520e03ba410d4e3ddd5beb1bfe $
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
include_once 'phing/system/io/BufferedReader.php';
include_once 'phing/system/io/BufferedWriter.php';
include_once 'phing/util/StringHelper.php';

/**
 * Adds an new entry to a CVS password file.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Jeff Martin <jeff@custommonkey.org> (Ant)
 * @version $Id: d3d7f451134334520e03ba410d4e3ddd5beb1bfe $
 * @package phing.tasks.system
 */
class CvsPassTask extends Task
{

    /** CVS Root */
    private $cvsRoot;
    /** Password file to add password to */
    private $passFile;
    /** Password to add to file */
    private $password;

    /** Array contain char conversion data */
    private static $shifts = array(
        0,
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20,
        21,
        22,
        23,
        24,
        25,
        26,
        27,
        28,
        29,
        30,
        31,
        114,
        120,
        53,
        79,
        96,
        109,
        72,
        108,
        70,
        64,
        76,
        67,
        116,
        74,
        68,
        87,
        111,
        52,
        75,
        119,
        49,
        34,
        82,
        81,
        95,
        65,
        112,
        86,
        118,
        110,
        122,
        105,
        41,
        57,
        83,
        43,
        46,
        102,
        40,
        89,
        38,
        103,
        45,
        50,
        42,
        123,
        91,
        35,
        125,
        55,
        54,
        66,
        124,
        126,
        59,
        47,
        92,
        71,
        115,
        78,
        88,
        107,
        106,
        56,
        36,
        121,
        117,
        104,
        101,
        100,
        69,
        73,
        99,
        63,
        94,
        93,
        39,
        37,
        61,
        48,
        58,
        113,
        32,
        90,
        44,
        98,
        60,
        51,
        33,
        97,
        62,
        77,
        84,
        80,
        85,
        223,
        225,
        216,
        187,
        166,
        229,
        189,
        222,
        188,
        141,
        249,
        148,
        200,
        184,
        136,
        248,
        190,
        199,
        170,
        181,
        204,
        138,
        232,
        218,
        183,
        255,
        234,
        220,
        247,
        213,
        203,
        226,
        193,
        174,
        172,
        228,
        252,
        217,
        201,
        131,
        230,
        197,
        211,
        145,
        238,
        161,
        179,
        160,
        212,
        207,
        221,
        254,
        173,
        202,
        146,
        224,
        151,
        140,
        196,
        205,
        130,
        135,
        133,
        143,
        246,
        192,
        159,
        244,
        239,
        185,
        168,
        215,
        144,
        139,
        165,
        180,
        157,
        147,
        186,
        214,
        176,
        227,
        231,
        219,
        169,
        175,
        156,
        206,
        198,
        129,
        164,
        150,
        210,
        154,
        177,
        134,
        127,
        182,
        128,
        158,
        208,
        162,
        132,
        167,
        209,
        149,
        241,
        153,
        251,
        237,
        236,
        171,
        195,
        243,
        233,
        253,
        240,
        194,
        250,
        191,
        155,
        142,
        137,
        245,
        235,
        163,
        242,
        178,
        152
    );

    /**
     * Create a CVS task using the default cvspass file location.
     */
    public function __construct()
    {
        $this->passFile = new PhingFile(
            Phing::getProperty(
                "cygwin.user.home",
                Phing::getProperty("user.home")
            )
            . DIRECTORY_SEPARATOR . ".cvspass");
    }

    /**
     * Does the work.
     *
     * @throws BuildException if someting goes wrong with the build
     */
    final public function main()
    {
        if ($this->cvsRoot === null) {
            throw new BuildException("cvsroot is required");
        }
        if ($this->password === null) {
            throw new BuildException("password is required");
        }

        $this->log("cvsRoot: " . $this->cvsRoot, Project::MSG_DEBUG);
        $this->log("password: " . $this->password, Project::MSG_DEBUG);
        $this->log("passFile: " . $this->passFile->__toString(), Project::MSG_DEBUG);

        $reader = null;
        $writer = null;

        try {
            $buf = "";

            if ($this->passFile->exists()) {
                $reader = new BufferedReader(new FileReader($this->passFile));

                $line = null;
                while (($line = $reader->readLine()) !== null) {
                    if (!StringHelper::startsWith($this->cvsRoot, $line)) {
                        $buf .= $line . PHP_EOL;
                    }
                }
            }

            $pwdfile = $buf . $this->cvsRoot . " A" . $this->mangle($this->password);

            $this->log("Writing -> " . $pwdfile, Project::MSG_DEBUG);

            $writer = new BufferedWriter(new FileWriter($this->passFile));
            $writer->write($pwdfile);
            $writer->newLine();

            $writer->close();
            if ($reader) {
                $reader->close();
            }

        } catch (IOException $e) {
            if ($reader) {
                try {
                    $reader->close();
                } catch (Exception $e) {
                }
            }

            if ($writer) {
                try {
                    $writer->close();
                } catch (Exception $e) {
                }
            }

            throw new BuildException($e);
        }
    }

    /**
     * "Encode" the password.
     * @param $password
     * @return string
     */
    final private function mangle($password)
    {
        $buf = "";
        for ($i = 0, $plen = strlen($password); $i < $plen; $i++) {
            $buf .= chr(self::$shifts[ord($password{$i})]);
        }

        return $buf;
    }

    /**
     * The CVS repository to add an entry for.
     * @param string $cvsRoot
     */
    public function setCvsroot($cvsRoot)
    {
        $this->cvsRoot = $cvsRoot;
    }

    /**
     * Password file to add the entry to.
     * @param PhingFile $passFile
     */
    public function setPassfile(PhingFile $passFile)
    {
        $this->passFile = $passFile;
    }

    /**
     * Password to be added to the password file.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

}
