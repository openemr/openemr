<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper\File;

use function ini_get;

/**
 * A view helper to render the hidden input with a Session progress id
 * for file uploads progress tracking.
 */
class FormFileSessionProgress extends FormFileUploadProgress
{
    /**
     * @return string
     */
    protected function getName()
    {
        return ini_get('session.upload_progress.name');
    }
}
