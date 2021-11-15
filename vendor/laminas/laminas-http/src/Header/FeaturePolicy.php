<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Feature Policy (based on Editor’s Draft, 28 November 2019)
 *
 * @link https://w3c.github.io/webappsec-feature-policy/
 */
class FeaturePolicy implements HeaderInterface
{
    /**
     * Valid directive names
     *
     * @var string[]
     *
     * @see https://github.com/w3c/webappsec-feature-policy/blob/master/features.md
     */
    protected $validDirectiveNames = [
        // Standardized Features
        'accelerometer',
        'ambient-light-sensor',
        'autoplay',
        'battery',
        'camera',
        'display-capture',
        'document-domain',
        'fullscreen',
        'execution-while-not-rendered',
        'execution-while-out-of-viewport',
        'gyroscope',
        'magnetometer',
        'microphone',
        'midi',
        'payment',
        'picture-in-picture',
        'sync-xhr',
        'usb',
        'wake-lock',
        'xr',

        // Proposed Features
        'encrypted-media',
        'geolocation',
        'speaker',

        // Experimental Features
        'document-write',
        'font-display-late-swap',
        'layout-animations',
        'loading-frame-default-eager',
        'loading-image-default-eager',
        'legacy-image-formats',
        'oversized-images',
        'sync-script',
        'unoptimized-lossy-images',
        'unoptimized-lossless-images',
        'unsized-media',
        'vertical-scroll',
        'serial',
    ];

    /**
     * The directives defined for this policy
     *
     * @var array
     */
    protected $directives = [];

    /**
     * Get the list of defined directives
     *
     * @return array
     */
    public function getDirectives()
    {
        return $this->directives;
    }

    /**
     * Sets the directive to consist of the source list
     *
     * @param string $name The directive name.
     * @param string[] $sources The source list.
     * @return $this
     * @throws Exception\InvalidArgumentException If the name is not a valid directive name.
     */
    public function setDirective($name, array $sources)
    {
        if (! in_array($name, $this->validDirectiveNames, true)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid directive name; received "%s"',
                __METHOD__,
                (string) $name
            ));
        }
        if (empty($sources)) {
            $this->directives[$name] = "'none'";
            return $this;
        }

        array_walk($sources, [__NAMESPACE__ . '\HeaderValue', 'assertValid']);

        $this->directives[$name] = implode(' ', $sources);
        return $this;
    }

    /**
     * Create Feature Policy header from a given header line
     *
     * @param string $headerLine The header line to parse.
     * @return static
     * @throws Exception\InvalidArgumentException If the name field in the given header line does not match.
     */
    public static function fromString($headerLine)
    {
        $header = new static();
        $headerName = $header->getFieldName();
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);
        // Ensure the proper header name
        if (strcasecmp($name, $headerName) !== 0) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for %s string: "%s"',
                $headerName,
                $name
            ));
        }
        // As per https://w3c.github.io/webappsec-feature-policy/#algo-parse-policy-directive
        $tokens = explode(';', $value);
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token) {
                list($directiveName, $directiveValue) = array_pad(explode(' ', $token, 2), 2, null);
                if (! isset($header->directives[$directiveName])) {
                    $header->setDirective(
                        $directiveName,
                        $directiveValue === null ? [] : [$directiveValue]
                    );
                }
            }
        }

        return $header;
    }

    /**
     * Get the header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Feature-Policy';
    }

    /**
     * Get the header value
     *
     * @return string
     */
    public function getFieldValue()
    {
        $directives = [];
        foreach ($this->directives as $name => $value) {
            $directives[] = sprintf('%s %s;', $name, $value);
        }
        return implode(' ', $directives);
    }

    /**
     * Return the header as a string
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('%s: %s', $this->getFieldName(), $this->getFieldValue());
    }
}
