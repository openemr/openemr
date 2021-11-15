<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Content Security Policy Level 3 Header
 *
 * @link http://www.w3.org/TR/CSP/
 */
class ContentSecurityPolicy implements MultipleHeaderInterface
{
    /**
     * Valid directive names
     *
     * @var array
     */
    protected $validDirectiveNames = [
        // As per http://www.w3.org/TR/CSP/#directives
        // Fetch directives
        'child-src',
        'connect-src',
        'default-src',
        'font-src',
        'frame-src',
        'img-src',
        'manifest-src',
        'media-src',
        'object-src',
        'prefetch-src',
        'script-src',
        'script-src-elem',
        'script-src-attr',
        'style-src',
        'style-src-elem',
        'style-src-attr',
        'worker-src',

        // Document directives
        'base-uri',
        'plugin-types',
        'sandbox',

        // Navigation directives
        'form-action',
        'frame-ancestors',
        'navigate-to',

        // Reporting directives
        'report-uri',
        'report-to',

        // Other directives
        'block-all-mixed-content',
        'require-sri-for',
        'trusted-types',
        'upgrade-insecure-requests',
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
     * Reverses http://www.w3.org/TR/CSP/#parsing-1
     *
     * @param string $name The directive name.
     * @param array $sources The source list.
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

        if ($name === 'block-all-mixed-content'
            || $name === 'upgrade-insecure-requests'
        ) {
            if ($sources) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Received value for %s directive; none expected',
                    $name
                ));
            }

            $this->directives[$name] = '';
            return $this;
        }

        if (empty($sources)) {
            if ('report-uri' === $name) {
                if (isset($this->directives[$name])) {
                    unset($this->directives[$name]);
                }
                return $this;
            }

            $this->directives[$name] = "'none'";
            return $this;
        }

        array_walk($sources, [__NAMESPACE__ . '\HeaderValue', 'assertValid']);
        $this->directives[$name] = implode(' ', $sources);

        return $this;
    }

    /**
     * Create Content Security Policy header from a given header line
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
        if (strcasecmp($name, $headerName) != 0) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for %s string: "%s"',
                $headerName,
                $name
            ));
        }
        // As per http://www.w3.org/TR/CSP/#parsing
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
        return 'Content-Security-Policy';
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
        return str_replace(' ;', ';', implode(' ', $directives));
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

    public function toStringMultipleHeaders(array $headers)
    {
        $strings = [$this->toString()];
        foreach ($headers as $header) {
            if (! $header instanceof ContentSecurityPolicy) {
                throw new Exception\RuntimeException(
                    'The ContentSecurityPolicy multiple header implementation can only'
                    . ' accept an array of ContentSecurityPolicy headers'
                );
            }
            $strings[] = $header->toString();
        }

        return implode("\r\n", $strings) . "\r\n";
    }
}
