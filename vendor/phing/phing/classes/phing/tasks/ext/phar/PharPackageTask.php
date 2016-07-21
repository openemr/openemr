<?php
/*
 * $Id: 16adcda5de07d6005fa96cbf7f0f4c713e03a1b3 $
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

require_once 'phing/tasks/system/MatchingTask.php';
require_once 'phing/types/IterableFileSet.php';
require_once 'phing/tasks/ext/phar/PharMetadata.php';

/**
 * Package task for {@link http://www.php.net/manual/en/book.phar.php Phar technology}.
 *
 * @package phing.tasks.ext
 * @author Alexey Shockov <alexey@shockov.com>
 * @since 2.4.0
 */
class PharPackageTask
    extends MatchingTask
{
    /**
     * @var PhingFile
     */
    private $destinationFile;

    /**
     * @var int
     */
    private $compression = Phar::NONE;

    /**
     * Base directory, from where local package paths will be calculated.
     *
     * @var PhingFile
     */
    private $baseDirectory;

    /**
     * @var PhingFile
     */
    private $cliStubFile;

    /**
     * @var PhingFile
     */
    private $webStubFile;

    /**
     * @var string
     */
    private $stubPath;

    /**
     * Private key the Phar will be signed with.
     *
     * @var PhingFile
     */
    private $key;

    /**
     * Password for the private key.
     *
     * @var string
     */
    private $keyPassword = '';

    /**
     * @var int
     */
    private $signatureAlgorithm = Phar::SHA1;

    /**
     * @var array
     */
    private $filesets = array();

    /**
     * @var PharMetadata
     */
    private $metadata = null;

    /**
     * @var string
     */
    private $alias;

    /**
     * @return PharMetadata
     */
    public function createMetadata()
    {
        return ($this->metadata = new PharMetadata());
    }

    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $this->fileset = new IterableFileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Signature algorithm (md5, sha1, sha256, sha512, openssl),
     * used for this package.
     *
     * @param string $algorithm
     */
    public function setSignature($algorithm)
    {
        /*
         * If we don't support passed algprithm, leave old one.
         */
        switch ($algorithm) {
            case 'md5':
                $this->signatureAlgorithm = Phar::MD5;
                break;
            case 'sha1':
                $this->signatureAlgorithm = Phar::SHA1;
                break;
            case 'sha256':
                $this->signatureAlgorithm = Phar::SHA256;
                break;
            case 'sha512':
                $this->signatureAlgorithm = Phar::SHA512;
                break;
            case 'openssl':
                $this->signatureAlgorithm = Phar::OPENSSL;
                break;
            default:
                break;
        }
    }

    /**
     * Compression type (gzip, bzip2, none) to apply to the packed files.
     *
     * @param string $compression
     */
    public function setCompression($compression)
    {
        /**
         * If we don't support passed compression, leave old one.
         */
        switch ($compression) {
            case 'gzip':
                $this->compression = Phar::GZ;
                break;
            case 'bzip2':
                $this->compression = Phar::BZ2;
                break;
            default:
                break;
        }
    }

    /**
     * Destination (output) file.
     *
     * @param PhingFile $destinationFile
     */
    public function setDestFile(PhingFile $destinationFile)
    {
        $this->destinationFile = $destinationFile;
    }

    /**
     * Base directory, which will be deleted from each included file (from path).
     * Paths with deleted basedir part are local paths in package.
     *
     * @param PhingFile $baseDirectory
     */
    public function setBaseDir(PhingFile $baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Relative path within the phar package to run,
     * if accessed on the command line.
     *
     * @param PhingFile $stubFile
     */
    public function setCliStub(PhingFile $stubFile)
    {
        $this->cliStubFile = $stubFile;
    }

    /**
     * Relative path within the phar package to run,
     * if accessed through a web browser.
     *
     * @param PhingFile $stubFile
     */
    public function setWebStub(PhingFile $stubFile)
    {
        $this->webStubFile = $stubFile;
    }

    /**
     * A path to a php file that contains a custom stub.
     *
     * @param string $stubPath
     */
    public function setStub($stubPath)
    {
        $this->stubPath = $stubPath;
    }

    /**
     * An alias to assign to the phar package.
     *
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Sets the private key to use to sign the Phar with.
     *
     * @param PhingFile $key Private key to sign the Phar with.
     */
    public function setKey(PhingFile $key)
    {
        $this->key = $key;
    }

    /**
     * Password for the private key.
     *
     * @param string $keyPassword
     */
    public function setKeyPassword($keyPassword)
    {
        $this->keyPassword = $keyPassword;
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->checkPreconditions();

        try {
            $this->log(
                'Building package: ' . $this->destinationFile->__toString(),
                Project::MSG_INFO
            );

            /**
             * Delete old package, if exists.
             */
            if ($this->destinationFile->exists()) {
                /**
                 * TODO Check operation for errors...
                 */
                $this->destinationFile->delete();
            }

            $phar = $this->buildPhar();
            $phar->startBuffering();

            $baseDirectory = realpath($this->baseDirectory->getPath());

            foreach ($this->filesets as $fileset) {
                $this->log(
                    'Adding specified files in ' . $fileset->getDir($this->project) . ' to package',
                    Project::MSG_VERBOSE
                );

                $phar->buildFromIterator($fileset, $baseDirectory);
            }

            $phar->stopBuffering();

            /**
             * File compression, if needed.
             */
            if (Phar::NONE != $this->compression) {
                $phar->compressFiles($this->compression);
            }

            if ($this->signatureAlgorithm == Phar::OPENSSL) {

                // Load up the contents of the key
                $keyContents = file_get_contents($this->key);

                // Attempt to load the given key as a PKCS#12 Cert Store first.
                if (openssl_pkcs12_read($keyContents, $certs, $this->keyPassword)) {
                    $private = openssl_pkey_get_private($certs['pkey']);
                } else {
                    // Fall back to a regular PEM-encoded private key.
                    // Setup an OpenSSL resource using the private key
                    // and tell the Phar to sign it using that key.
                    $private = openssl_pkey_get_private($keyContents, $this->keyPassword);
                }

                openssl_pkey_export($private, $pkey);
                $phar->setSignatureAlgorithm(Phar::OPENSSL, $pkey);

                // Get the details so we can get the public key and write that out
                // alongside the phar.
                $details = openssl_pkey_get_details($private);
                file_put_contents($this->destinationFile . '.pubkey', $details['key']);

            } else {
                $phar->setSignatureAlgorithm($this->signatureAlgorithm);
            }
        } catch (Exception $e) {
            throw new BuildException(
                'Problem creating package: ' . $e->getMessage(),
                $e,
                $this->getLocation()
            );
        }
    }

    /**
     * @throws BuildException
     */
    private function checkPreconditions()
    {
        if (!extension_loaded('phar')) {
            throw new BuildException(
                "PharPackageTask require either PHP 5.3 or better or the PECL's Phar extension"
            );
        }

        if (is_null($this->destinationFile)) {
            throw new BuildException("destfile attribute must be set!", $this->getLocation());
        }

        if ($this->destinationFile->exists() && $this->destinationFile->isDirectory()) {
            throw new BuildException("destfile is a directory!", $this->getLocation());
        }

        if (!$this->destinationFile->canWrite()) {
            throw new BuildException("Can not write to the specified destfile!", $this->getLocation());
        }
        if (!is_null($this->baseDirectory)) {
            if (!$this->baseDirectory->exists()) {
                throw new BuildException("basedir '" . (string) $this->baseDirectory . "' does not exist!", $this->getLocation(
                    ));
            }
        }
        if ($this->signatureAlgorithm == Phar::OPENSSL) {

            if (!extension_loaded('openssl')) {
                throw new BuildException("PHP OpenSSL extension is required for OpenSSL signing of Phars!", $this->getLocation(
                ));
            }

            if (is_null($this->key)) {
                throw new BuildException("key attribute must be set for OpenSSL signing!", $this->getLocation());
            }

            if (!$this->key->exists()) {
                throw new BuildException("key '" . (string) $this->key . "' does not exist!", $this->getLocation());
            }

            if (!$this->key->canRead()) {
                throw new BuildException("key '" . (string) $this->key . "' cannot be read!", $this->getLocation());
            }
        }
    }

    /**
     * Build and configure Phar object.
     *
     * @return Phar
     */
    private function buildPhar()
    {
        $phar = new Phar($this->destinationFile);

        if (!empty($this->stubPath)) {
            $phar->setStub(file_get_contents($this->stubPath));
        } else {
            if (!empty($this->cliStubFile)) {
                $cliStubFile = $this->cliStubFile->getPathWithoutBase($this->baseDirectory);
            } else {
                $cliStubFile = null;
            }

            if (!empty($this->webStubFile)) {
                $webStubFile = $this->webStubFile->getPathWithoutBase($this->baseDirectory);
            } else {
                $webStubFile = null;
            }

            $phar->setDefaultStub($cliStubFile, $webStubFile);
        }

        if ($this->metadata === null) {
            $this->createMetaData();
        }

        if ($metadata = $this->metadata->toArray()) {
            $phar->setMetadata($metadata);
        }

        if (!empty($this->alias)) {
            $phar->setAlias($this->alias);
        }

        return $phar;
    }
}
