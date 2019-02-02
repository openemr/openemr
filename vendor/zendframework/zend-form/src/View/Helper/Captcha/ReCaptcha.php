<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\Captcha;

use Zend\Captcha\ReCaptcha as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;

class ReCaptcha extends FormInput
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (! $element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render ReCaptcha form elements
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $attributes = $element->getAttributes();
        $captcha = $element->getCaptcha();

        if ($captcha === null || ! $captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute implementing Zend\Captcha\AdapterInterface; '
                . 'none found',
                __METHOD__
            ));
        }

        $name = $element->getName();

        $markup = $captcha->getService()->getHtml($name);
        $hidden = $this->renderHiddenInput($name);

        return $hidden . $markup;
    }

    /**
     * Render hidden input element if the element's name is not 'g-recaptcha-response'
     * so that required validation works
     *
     * Note that only the first parameter is needed, the other three parameters
     * are deprecated.
     *
     * @param  string $name
     * @param  string $challengeId @deprecated
     * @param  string $responseName @deprecated
     * @param  string $responseId @deprecated
     * @return string
     */
    protected function renderHiddenInput($name, $challengeId = '', $responseName = '', $responseId = '')
    {
        if ($name === 'g-recaptcha-response') {
            return '';
        }

        $pattern        = '<input type="hidden" %s%s';
        $closingBracket = $this->getInlineClosingBracket();

        $attributes = $this->createAttributesString([
            'name'  => $name,
            'value' => 'g-recaptcha-response',
        ]);
        $challenge = sprintf($pattern, $attributes, $closingBracket);
        return $challenge;
    }

    /**
     * No longer used with v2 of Recaptcha API
     *
     * @deprecated
     *
     * @param  string $challengeId
     * @param  string $responseId
     * @return string
     */
    protected function renderJsEvents($challengeId, $responseId)
    {
        return '';
    }
}
