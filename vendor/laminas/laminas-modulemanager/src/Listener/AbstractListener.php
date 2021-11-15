<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\VarExporter;
use Laminas\ModuleManager\Listener\Exception\ConfigCannotBeCachedException;
use Webimpress\SafeWriter\FileWriter;

/**
 * Abstract listener
 */
abstract class AbstractListener
{
    /**
     * @var ListenerOptions
     */
    protected $options;

    /**
     * __construct
     *
     * @param  ListenerOptions $options
     */
    public function __construct(ListenerOptions $options = null)
    {
        $options = $options ?: new ListenerOptions;
        $this->setOptions($options);
    }

    /**
     * Get options.
     *
     * @return ListenerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options.
     *
     * @param ListenerOptions $options the value to be set
     * @return AbstractListener
     */
    public function setOptions(ListenerOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Write a simple array of scalars to a file
     *
     * @param  string $filePath
     * @param  array $array
     * @return AbstractListener
     */
    protected function writeArrayToFile($filePath, $array)
    {
        try {
            $content = "<?php\n" . VarExporter::export(
                $array,
                VarExporter::ADD_RETURN | VarExporter::CLOSURE_SNAPSHOT_USES
            );
        } catch (ExportException $e) {
            throw ConfigCannotBeCachedException::fromExporterException($e);
        }

        FileWriter::writeFile($filePath, $content);

        return $this;
    }
}
