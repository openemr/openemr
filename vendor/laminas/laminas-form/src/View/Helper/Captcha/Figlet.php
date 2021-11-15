<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper\Captcha;

use Laminas\Captcha\Figlet as CaptchaAdapter;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

use function sprintf;

class Figlet extends AbstractWord
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
                '%s requires that the element has a "captcha" attribute of type Laminas\Captcha\Figlet; none found',
                __METHOD__
            ));
        }

        $captcha->generate();

        $figlet = sprintf(
            '<pre>%s</pre>',
            $captcha->getFiglet()->render($captcha->getWord())
        );

        $position     = $this->getCaptchaPosition();
        $separator    = $this->getSeparator();
        $captchaInput = $this->renderCaptchaInputs($element);

        $pattern = '%s%s%s';
        if ($position == self::CAPTCHA_PREPEND) {
            return sprintf($pattern, $captchaInput, $separator, $figlet);
        }

        return sprintf($pattern, $figlet, $separator, $captchaInput);
    }
}
