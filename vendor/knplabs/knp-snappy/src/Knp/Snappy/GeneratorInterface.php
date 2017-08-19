<?php

namespace Knp\Snappy;

/**
 * Interface for the media generators
 *
 * @package Snappy
 *
 * @author  Matthieu Bontemps <matthieu.bontemps@knplabs.com>
 * @author  Antoine Hérault <antoine.herault@knplabs.com>*
 */
interface GeneratorInterface
{
    /**
     * Generates the output media file from the specified input HTML file
     *
     * @param  array|string $input   The input HTML filename or URL
     * @param  string $output  The output media filename
     * @param  array  $options An array of options for this generation only
     * @param  bool   $overwrite Overwrite the file if it exists. If not, throw a FileAlreadyExistsException
     */
    public function generate($input, $output, array $options = array(), $overwrite = false);

    /**
     * Generates the output media file from the given HTML
     *
     * @param  array|string $html    The HTML to be converted
     * @param  string $output  The output media filename
     * @param  array  $options An array of options for this generation only
     * @param  bool   $overwrite Overwrite the file if it exists. If not, throw a FileAlreadyExistsException
     */
    public function generateFromHtml($html, $output, array $options = array(), $overwrite = false);

    /**
     * Returns the output of the media generated from the specified input HTML
     * file
     *
     * @param  array|string $input   The input HTML filename or URL
     * @param  array  $options An array of options for this output only
     *
     * @return string
     */
    public function getOutput($input, array $options = array());

    /**
     * Returns the output of the media generated from the given HTML
     *
     * @param  array|string $html    The HTML to be converted
     * @param  array  $options An array of options for this output only
     *
     * @return string
     */
    public function getOutputFromHtml($html, array $options = array());
}
