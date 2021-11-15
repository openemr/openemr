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
class ContentSecurityPolicyReportOnly extends ContentSecurityPolicy
{
    /**
     * Get the header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Content-Security-Policy-Report-Only';
    }
}
