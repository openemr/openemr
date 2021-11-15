<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\File;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;
use Laminas\Stdlib\ErrorHandler;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class RenameUpload extends AbstractFilter
{
    /**
     * @var array
     */
    protected $options = [
        'target'               => null,
        'use_upload_name'      => false,
        'use_upload_extension' => false,
        'overwrite'            => false,
        'randomize'            => false,
        'stream_factory'       => null,
        'upload_file_factory'  => null,
    ];

    /**
     * Store already filtered values, so we can filter multiple
     * times the same file without being block by move_uploaded_file
     * internal checks
     *
     * @var array
     */
    protected $alreadyFiltered = [];

    /**
     * Constructor
     *
     * @param array|string $targetOrOptions The target file path or an options array
     */
    public function __construct($targetOrOptions = [])
    {
        if (is_array($targetOrOptions)) {
            $this->setOptions($targetOrOptions);
        } else {
            $this->setTarget($targetOrOptions);
        }
    }

    /**
     * @param  StreamFactoryInterface $factory Factory to use to produce a PSR-7
     *     stream with which to seed a PSR-7 UploadedFileInterface.
     * @return self
     */
    public function setStreamFactory(StreamFactoryInterface $factory)
    {
        $this->options['stream_factory'] = $factory;
        return $this;
    }

    /**
     * @return null|StreamFactoryInterface
     */
    public function getStreamFactory()
    {
        return $this->options['stream_factory'];
    }

    /**
     * @param  string $target Target file path or directory
     * @return self
     */
    public function setTarget($target)
    {
        if (! is_string($target)) {
            throw new Exception\InvalidArgumentException(
                'Invalid target, must be a string'
            );
        }
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * @return string Target file path or directory
     */
    public function getTarget()
    {
        return $this->options['target'];
    }

    /**
     * @param  UploadedFileFactoryInterface $factory Factory to use to produce
     *     filtered PSR-7 UploadedFileInterface instances.
     * @return self
     */
    public function setUploadFileFactory(UploadedFileFactoryInterface $factory)
    {
        $this->options['upload_file_factory'] = $factory;
        return $this;
    }

    /**
     * @return null|UploadedFileFactoryInterface
     */
    public function getUploadFileFactory()
    {
        return $this->options['upload_file_factory'];
    }

    /**
     * @param  bool $flag When true, this filter will use the $_FILES['name']
     *                       as the target filename.
     *                       Otherwise, it uses the default 'target' rules.
     * @return self
     */
    public function setUseUploadName($flag = true)
    {
        $this->options['use_upload_name'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseUploadName()
    {
        return $this->options['use_upload_name'];
    }

    /**
     * @param  bool $flag When true, this filter will use the original file
     *                    extension for the target filename
     * @return self
     */
    public function setUseUploadExtension($flag = true)
    {
        $this->options['use_upload_extension'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseUploadExtension()
    {
        return $this->options['use_upload_extension'];
    }

    /**
     * @param  bool $flag Shall existing files be overwritten?
     * @return self
     */
    public function setOverwrite($flag = true)
    {
        $this->options['overwrite'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getOverwrite()
    {
        return $this->options['overwrite'];
    }

    /**
     * @param  bool $flag Shall target files have a random postfix attached?
     * @return self
     */
    public function setRandomize($flag = true)
    {
        $this->options['randomize'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRandomize()
    {
        return $this->options['randomize'];
    }

    /**
     * Defined by Laminas\Filter\Filter
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param  string|array|UploadedFileInterface $value Full path of file to
     *     change; $_FILES data array; or UploadedFileInterface instance.
     * @return string|array|UploadedFileInterface Returns one of the following:
     *     - New filename, for string $value
     *     - Array with tmp_name and name keys for array $value
     *     - UploadedFileInterface for UploadedFileInterface $value
     * @throws Exception\RuntimeException
     */
    public function filter($value)
    {
        // PSR-7 uploaded file
        if ($value instanceof UploadedFileInterface) {
            return $this->filterPsr7UploadedFile($value);
        }

        // File upload via traditional SAPI
        if (is_array($value) && isset($value['tmp_name'])) {
            return $this->filterSapiUploadedFile($value);
        }

        // String filename
        if (is_string($value)) {
            return $this->filterStringFilename($value);
        }

        // Unrecognized; return verbatim
        return $value;
    }

    /**
     * @param  string $sourceFile Source file path
     * @param  string $targetFile Target file path
     * @throws Exception\RuntimeException
     * @return bool
     */
    protected function moveUploadedFile($sourceFile, $targetFile)
    {
        ErrorHandler::start();
        $result = move_uploaded_file($sourceFile, $targetFile);
        $warningException = ErrorHandler::stop();
        if (! $result || null !== $warningException) {
            throw new Exception\RuntimeException(
                sprintf("File '%s' could not be renamed. An error occurred while processing the file.", $sourceFile),
                0,
                $warningException
            );
        }

        return $result;
    }

    /**
     * @param  string $targetFile Target file path
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    protected function checkFileExists($targetFile)
    {
        if (! file_exists($targetFile)) {
            return;
        }

        if (! $this->getOverwrite()) {
            throw new Exception\InvalidArgumentException(
                sprintf("File '%s' could not be renamed. It already exists.", $targetFile)
            );
        }

        unlink($targetFile);
    }

    /**
     * @param $source
     * @param $clientFileName
     *
     * @return string
     */
    protected function getFinalTarget($source, $clientFileName)
    {
        $target = $this->getTarget();
        if ($target === null || $target === '*') {
            $target = $source;
        }

        // Get the target directory
        if (is_dir($target)) {
            $targetDir = $target;
            $last      = $target[strlen($target) - 1];
            if (($last !== '/') && ($last !== '\\')) {
                $targetDir .= DIRECTORY_SEPARATOR;
            }
        } else {
            $info      = pathinfo($target);
            $targetDir = $info['dirname'] . DIRECTORY_SEPARATOR;
        }

        // Get the target filename
        if ($this->getUseUploadName()) {
            $targetFile = basename($clientFileName);
        } elseif (! is_dir($target)) {
            $targetFile = basename($target);
            if ($this->getUseUploadExtension() && ! $this->getRandomize()) {
                $targetInfo = pathinfo($targetFile);
                $sourceinfo = pathinfo($clientFileName);
                if (isset($sourceinfo['extension'])) {
                    $targetFile = $targetInfo['filename'] . '.' . $sourceinfo['extension'];
                }
            }
        } else {
            $targetFile = basename($source);
        }

        if ($this->getRandomize()) {
            $targetFile = $this->applyRandomToFilename($clientFileName, $targetFile);
        }

        return $targetDir . $targetFile;
    }

    /**
     * @param  string $source
     * @param  string $filename
     * @return string
     */
    protected function applyRandomToFilename($source, $filename)
    {
        $info = pathinfo($filename);
        $filename = $info['filename'] . str_replace('.', '_', uniqid('_', true));

        $sourceinfo = pathinfo($source);

        $extension = '';
        if ($this->getUseUploadExtension() === true && isset($sourceinfo['extension'])) {
            $extension .= '.' . $sourceinfo['extension'];
        } elseif (isset($info['extension'])) {
            $extension .= '.' . $info['extension'];
        }

        return $filename . $extension;
    }

    /**
     * @param  string $fileName
     * @return string
     */
    private function filterStringFilename($fileName)
    {
        if (isset($this->alreadyFiltered[$fileName])) {
            return $this->alreadyFiltered[$fileName];
        }

        $targetFile = $this->getFinalTarget($fileName, $fileName);
        if ($fileName === $targetFile || ! file_exists($fileName)) {
            return $fileName;
        }

        $this->checkFileExists($targetFile);
        $this->moveUploadedFile($fileName, $targetFile);
        $this->alreadyFiltered[$fileName] = $targetFile;

        return $this->alreadyFiltered[$fileName];
    }

    /**
     * @param  array<string, mixed> $fileData
     * @return array<string, string>
     */
    private function filterSapiUploadedFile(array $fileData)
    {
        $sourceFile = $fileData['tmp_name'];

        if (isset($this->alreadyFiltered[$sourceFile])) {
            return $this->alreadyFiltered[$sourceFile];
        }

        $clientFilename = $fileData['name'];

        $targetFile = $this->getFinalTarget($sourceFile, $clientFilename);
        if ($sourceFile === $targetFile || ! file_exists($sourceFile)) {
            return $fileData;
        }

        $this->checkFileExists($targetFile);
        $this->moveUploadedFile($sourceFile, $targetFile);

        $this->alreadyFiltered[$sourceFile] = $fileData;
        $this->alreadyFiltered[$sourceFile]['tmp_name'] = $targetFile;

        return $this->alreadyFiltered[$sourceFile];
    }

    /**
     * @param  UploadedFileInterface $uploadedFile
     * @return UploadedFileInterface
     * @throws Exception\RuntimeException if no stream factory is composed in the filter.
     * @throws Exception\RuntimeException if no uploaded file factory is composed in the filter.
     */
    private function filterPsr7UploadedFile(UploadedFileInterface $uploadedFile)
    {
        $sourceFile = $uploadedFile->getStream()->getMetadata('uri');

        if (isset($this->alreadyFiltered[$sourceFile])) {
            return $this->alreadyFiltered[$sourceFile];
        }

        $clientFilename = $uploadedFile->getClientFilename();
        $targetFile     = $this->getFinalTarget($sourceFile, $clientFilename);

        if ($sourceFile === $targetFile || ! file_exists($sourceFile)) {
            return $uploadedFile;
        }

        $this->checkFileExists($targetFile);
        $uploadedFile->moveTo($targetFile);

        $streamFactory = $this->getStreamFactory();
        if (! $streamFactory) {
            throw new Exception\RuntimeException(sprintf(
                'No PSR-17 %s present; cannot filter file. Please pass the stream_file_factory'
                . ' option with a %s instance when creating the filter for use with PSR-7.',
                StreamFactoryInterface::class,
                StreamFactoryInterface::class
            ));
        }

        $stream = $streamFactory->createStreamFromFile($targetFile);

        $uploadedFileFactory = $this->getUploadFileFactory();
        if (! $uploadedFileFactory) {
            throw new Exception\RuntimeException(sprintf(
                'No PSR-17 %s present; cannot filter file. Please pass the upload_file_factory'
                . ' option with a %s instance when creating the filter for use with PSR-7.',
                UploadedFileFactoryInterface::class,
                UploadedFileFactoryInterface::class
            ));
        }

        $this->alreadyFiltered[$sourceFile] = $uploadedFileFactory->createUploadedFile(
            $stream,
            filesize($targetFile),
            UPLOAD_ERR_OK,
            $uploadedFile->getClientFilename(),
            $uploadedFile->getClientMediaType()
        );

        return $this->alreadyFiltered[$sourceFile];
    }
}
