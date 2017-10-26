<?php

namespace Knp\Snappy;

/**
 * Use this class to create a snapshot / thumbnail from a HTML page
 *
 * @package Snappy
 *
 * @author  Matthieu Bontemps <matthieu.bontemps@knplabs.com>
 * @author  Antoine Hérault <antoine.herault@knplabs.com>
 */
class Image extends AbstractGenerator
{
    /**
     * {@inheritDoc}
     */
    public function __construct($binary = null, array $options = array(), array $env = null)
    {
        $this->setDefaultExtension('jpg');

        parent::__construct($binary, $options, $env);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addOptions(array(
            'allow'                        => null,    // Allow the file or files from the specified folder to be loaded (repeatable)
            'checkbox-checked-svg'         => null,    // Use this SVG file when rendering checked checkboxes
            'checked-svg'                  => null,    // Use this SVG file when rendering unchecked checkboxes
            'cookie'                       => array(), // Set an additional cookie (repeatable)
            'cookie-jar'                   => null,    // Read and write cookies from and to the supplied cookie jar file
            'crop-h'                       => null,    // Set height for cropping
            'crop-w'                       => null,    // Set width for cropping
            'crop-x'                       => null,    // Set x coordinate for cropping (default 0)
            'crop-y'                       => null,    // Set y coordinate for cropping (default 0)
            'custom-header'                => array(), // Set an additional HTTP header (repeatable)
            'custom-header-propagation'    => null,    // Add HTTP headers specified by --custom-header for each resource request.
            'no-custom-header-propagation' => null,    // Do not add HTTP headers specified by --custom-header for each resource request.
            'debug-javascript'             => null,    // Show javascript debugging output
            'no-debug-javascript'          => null,    // Do not show javascript debugging output (default)
            'encoding'                     => null,    // Set the default text encoding, for input
            'format'                       => $this->getDefaultExtension(),   // Output format
            'height'                       => null,    // Set screen height (default is calculated from page content) (default 0)
            'images'                       => null,    // Do load or print images (default)
            'no-images'                    => null,    // Do not load or print images
            'disable-javascript'           => null,    // Do not allow web pages to run javascript
            'enable-javascript'            => null,    // Do allow web pages to run javascript (default)
            'javascript-delay'             => null,    // Wait some milliseconds for javascript finish (default 200)
            'load-error-handling'          => null,    // Specify how to handle pages that fail to load: abort, ignore or skip (default abort)
            'load-media-error-handling'    => null,    // Specify how to handle media files that fail to load: abort, ignore or skip (default ignore)
            'disable-local-file-access'    => null,    // Do not allowed conversion of a local file to read in other local files, unless explicitly allowed with allow
            'enable-local-file-access'     => null,    // Allowed conversion of a local file to read in other local files. (default)
            'minimum-font-size'            => null,    // Minimum font size
            'password'                     => null,    // HTTP Authentication password
            'disable-plugins'              => null,    // Disable installed plugins (default)
            'enable-plugins'               => null,    // Enable installed plugins (plugins will likely not work)
            'post'                         => array(), // Add an additional post field
            'post-file'                    => array(), // Post an additional file
            'proxy'                        => null,    // Use a proxy
            'quality'                      => null,    // Output image quality (between 0 and 100) (default 94)
            'radiobutton-checked-svg'      => null,    // Use this SVG file when rendering checked radio-buttons
            'radiobutton-svg'              => null,    // Use this SVG file when rendering unchecked radio-buttons
            'run-script'                   => null,    // Run this additional javascript after the page is done loading (repeatable)
            'disable-smart-width'          => null,    // Use the specified width even if it is not large enough for the content
            'enable-smart-width'           => null,    // Extend --width to fit unbreakable content (default)
            'stop-slow-scripts'            => null,    // Stop slow running javascript
            'no-stop-slow-scripts'         => null,    // Do not stop slow running javascript (default)
            'transparent'                  => null,    // Make the background transparent in pngs *
            'use-xserver'                  => null,    // Use the X server (some plugins and other stuff might not work without X11)
            'user-style-sheet'             => null,    // Specify a user style sheet, to load with every page
            'username'                     => null,    // HTTP Authentication username
            'width'                        => null,    // Set screen width (default is 1024)
            'window-status'                => null,    // Wait until window.status is equal to this string before rendering page
            'zoom'                         => null,    // Use this zoom factor (default 1)
            'quiet'                        => null,    // Be less verbose
        ));
    }
}
