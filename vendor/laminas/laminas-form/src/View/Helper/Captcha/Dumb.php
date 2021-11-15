<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper\Captcha;

use Laminas\Captcha\Dumb as CaptchaAdapter;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

use function sprintf;
use function strrev;

class Dumb extends AbstractWord
{
    /**
     * Render the captcha
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || ! $captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute of type Laminas\Captcha\Dumb; none found',
                __METHOD__
            ));
        }

        $captcha->generate();

        $label = sprintf(
            '%s <b>%s</b>',
            $captcha->getLabel(),
            strrev($captcha->getWord())
        );

        $position     = $this->getCaptchaPosition();
        $separator    = $this->getSeparator();
        $captchaInput = $this->renderCaptchaInputs($element);

        $pattern = '%s%s%s';
        if ($position === self::CAPTCHA_PREPEND) {
            return sprintf($pattern, $captchaInput, $separator, $label);
        }

        return sprintf($pattern, $label, $separator, $captchaInput);
    }
}
