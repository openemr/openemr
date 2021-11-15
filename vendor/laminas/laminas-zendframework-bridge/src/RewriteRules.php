<?php

/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-zendframework-bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

class RewriteRules
{
    /**
     * @return array
     */
    public static function namespaceRewrite()
    {
        return [
            // Expressive
            'Zend\\ProblemDetails\\' => 'Mezzio\\ProblemDetails\\',
            'Zend\\Expressive\\'     => 'Mezzio\\',

            // Laminas
            'Zend\\'                    => 'Laminas\\',
            'ZF\\ComposerAutoloading\\' => 'Laminas\\ComposerAutoloading\\',
            'ZF\\DevelopmentMode\\'     => 'Laminas\\DevelopmentMode\\',

            // Apigility
            'ZF\\Apigility\\' => 'Laminas\\ApiTools\\',
            'ZF\\'            => 'Laminas\\ApiTools\\',

            // ZendXml, API wrappers, zend-http OAuth support, zend-diagnostics, ZendDeveloperTools
            'ZendXml\\'                => 'Laminas\\Xml\\',
            'ZendOAuth\\'              => 'Laminas\\OAuth\\',
            'ZendDiagnostics\\'        => 'Laminas\\Diagnostics\\',
            'ZendService\\ReCaptcha\\' => 'Laminas\\ReCaptcha\\',
            'ZendService\\Twitter\\'   => 'Laminas\\Twitter\\',
            'ZendDeveloperTools\\'     => 'Laminas\\DeveloperTools\\',
        ];
    }

    /**
     * @return array
     */
    public static function namespaceReverse()
    {
        return [
            // ZendXml, ZendOAuth, ZendDiagnostics, ZendDeveloperTools
            'Laminas\\Xml\\'            => 'ZendXml\\',
            'Laminas\\OAuth\\'          => 'ZendOAuth\\',
            'Laminas\\Diagnostics\\'    => 'ZendDiagnostics\\',
            'Laminas\\DeveloperTools\\' => 'ZendDeveloperTools\\',

            // Zend Service
            'Laminas\\ReCaptcha\\' => 'ZendService\\ReCaptcha\\',
            'Laminas\\Twitter\\'   => 'ZendService\\Twitter\\',

            // Zend
            'Laminas\\' => 'Zend\\',

            // Expressive
            'Mezzio\\ProblemDetails\\' => 'Zend\\ProblemDetails\\',
            'Mezzio\\'                 => 'Zend\\Expressive\\',

            // Laminas to ZfCampus
            'Laminas\\ComposerAutoloading\\' => 'ZF\\ComposerAutoloading\\',
            'Laminas\\DevelopmentMode\\'     => 'ZF\\DevelopmentMode\\',

            // Apigility
            'Laminas\\ApiTools\\Admin\\'         => 'ZF\\Apigility\\Admin\\',
            'Laminas\\ApiTools\\Doctrine\\'      => 'ZF\\Apigility\\Doctrine\\',
            'Laminas\\ApiTools\\Documentation\\' => 'ZF\\Apigility\\Documentation\\',
            'Laminas\\ApiTools\\Example\\'       => 'ZF\\Apigility\\Example\\',
            'Laminas\\ApiTools\\Provider\\'      => 'ZF\\Apigility\\Provider\\',
            'Laminas\\ApiTools\\Welcome\\'       => 'ZF\\Apiglity\\Welcome\\',
            'Laminas\\ApiTools\\'                => 'ZF\\',
        ];
    }
}
