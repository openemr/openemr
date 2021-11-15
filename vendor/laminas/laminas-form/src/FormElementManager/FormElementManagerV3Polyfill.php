<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\FormElementManager;

use Laminas\Form\FormElementManager;

/**
 * laminas-servicemanager v3-compatible plugin manager implementation for form elements.
 *
 * Enforces that elements retrieved are instances of ElementInterface.
 *
 * @deprecated Use \Laminas\Form\FormElementManager instead
 */
class FormElementManagerV3Polyfill extends FormElementManager
{
}
