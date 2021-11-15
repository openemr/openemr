<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

/**
 * Deprecated by https://github.com/zendframework/zf2/pull/5636
 *
 * @deprecated
 */
interface FieldsetPrepareAwareInterface
{
    /**
     * Prepare the fieldset element (called while this fieldset is added to another one)
     *
     * @return mixed
     */
    public function prepareFieldset();
}
