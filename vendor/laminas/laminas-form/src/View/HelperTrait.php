<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View;

use IntlDateFormatter;
use Laminas\Form\ElementInterface;
use Laminas\Form\FormInterface;
use Laminas\Form\View\Helper\Captcha\Dumb;
use Laminas\Form\View\Helper\Captcha\Figlet;
use Laminas\Form\View\Helper\Captcha\Image;
use Laminas\Form\View\Helper\Captcha\ReCaptcha;
use Laminas\Form\View\Helper\Form;
use Laminas\Form\View\Helper\FormButton;
use Laminas\Form\View\Helper\FormCaptcha;
use Laminas\Form\View\Helper\FormCheckbox;
use Laminas\Form\View\Helper\FormCollection;
use Laminas\Form\View\Helper\FormColor;
use Laminas\Form\View\Helper\FormDate;
use Laminas\Form\View\Helper\FormDateSelect;
use Laminas\Form\View\Helper\FormDateTime;
use Laminas\Form\View\Helper\FormDateTimeLocal;
use Laminas\Form\View\Helper\FormDateTimeSelect;
use Laminas\Form\View\Helper\FormElement;
use Laminas\Form\View\Helper\FormElementErrors;
use Laminas\Form\View\Helper\FormEmail;
use Laminas\Form\View\Helper\FormFile;
use Laminas\Form\View\Helper\FormHidden;
use Laminas\Form\View\Helper\FormImage;
use Laminas\Form\View\Helper\FormInput;
use Laminas\Form\View\Helper\FormLabel;
use Laminas\Form\View\Helper\FormMonth;
use Laminas\Form\View\Helper\FormMonthSelect;
use Laminas\Form\View\Helper\FormMultiCheckbox;
use Laminas\Form\View\Helper\FormNumber;
use Laminas\Form\View\Helper\FormPassword;
use Laminas\Form\View\Helper\FormRadio;
use Laminas\Form\View\Helper\FormRange;
use Laminas\Form\View\Helper\FormReset;
use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\View\Helper\FormSearch;
use Laminas\Form\View\Helper\FormSelect;
use Laminas\Form\View\Helper\FormSubmit;
use Laminas\Form\View\Helper\FormTel;
use Laminas\Form\View\Helper\FormText;
use Laminas\Form\View\Helper\FormTextarea;
use Laminas\Form\View\Helper\FormTime;
use Laminas\Form\View\Helper\FormUrl;
use Laminas\Form\View\Helper\FormWeek;

// @codingStandardsIgnoreStart

/**
 * Helper trait for auto-completion of code in modern IDEs.
 *
 * The trait provides convenience methods for view helpers,
 * defined by the laminas-form component. It is designed to be used
 * for type-hinting $this variable inside laminas-view templates via doc blocks.
 *
 * The base class is PhpRenderer, followed by the helper trait from
 * the laminas-form component. However, multiple helper traits from different
 * Laminas components can be chained afterwards.
 *
 * @example @var \Laminas\View\Renderer\PhpRenderer|\Laminas\Form\View\HelperTrait $this
 *
 * @method string|Form form(FormInterface|null $form = null)
 * @method string|FormButton formButton(ElementInterface|null $element = null, string|null $buttonContent = null)
 * @method string|FormCaptcha formCaptcha(ElementInterface|null $element = null)
 * @method string|Dumb formCaptchaDumb(ElementInterface|null $element = null)
 * @method string|Figlet formCaptchaFiglet(ElementInterface|null $element = null)
 * @method string|Image formCaptchaImage(ElementInterface|null $element = null)
 * @method string|ReCaptcha formCaptchaRecaptcha(ElementInterface|null $element = null)
 * @method string|FormCheckbox formCheckbox(ElementInterface|null $element = null)
 * @method string|FormCollection formCollection(ElementInterface|null $element = null, bool $wrap = true)
 * @method string|FormColor formColor(ElementInterface|null $element = null)
 * @method string|FormDate formDate(ElementInterface|null $element = null)
 * @method string|FormDateTime formDateTime(ElementInterface|null $element = null)
 * @method string|FormDateTimeLocal formDateTimeLocal(ElementInterface|null $element = null)
 * @method string|FormDateTimeSelect formDateTimeSelect(ElementInterface|null $element = null, int $dateType = IntlDateFormatter::LONG, int|null|string $timeType = IntlDateFormatter::LONG, string|null $locale = null)
 * @method string|FormDateSelect formDateSelect(ElementInterface $element = null, $dateType = IntlDateFormatter::LONG, $locale = null)
 * @method string|FormElement formElement(ElementInterface|null $element = null)
 * @method string|FormElementErrors formElementErrors(ElementInterface|null $element = null, array $attributes = [])
 * @method string|FormEmail formEmail(ElementInterface|null $element = null)
 * @method string|FormFile formFile(ElementInterface|null $element = null)
 * @method string formFileApcProgress(ElementInterface|null $element = null)
 * @method string formFileSessionProgress(ElementInterface|null $element = null)
 * @method string formFileUploadProgress(ElementInterface|null $element = null)
 * @method string|FormHidden formHidden(ElementInterface|null $element = null)
 * @method string|FormImage formImage(ElementInterface|null $element = null)
 * @method string|FormInput formInput(ElementInterface|null $element = null)
 * @method string|FormLabel formLabel(ElementInterface|null $element = null, string|null $labelContent = null, string|null $position = null)
 * @method string|FormMonth formMonth(ElementInterface|null $element = null)
 * @method string|FormMonthSelect formMonthSelect(ElementInterface|null $element = null, int $dateType = IntlDateFormatter::LONG, string|null $locale = null)
 * @method string|FormMultiCheckbox formMultiCheckbox(ElementInterface|null $element = null, string|null $labelPosition = null)
 * @method string|FormNumber formNumber(ElementInterface|null $element = null)
 * @method string|FormPassword formPassword(ElementInterface|null $element = null)
 * @method string|FormRadio formRadio(ElementInterface|null $element = null, string|null $labelPosition = null)
 * @method string|FormRange formRange(ElementInterface|null $element = null)
 * @method string|FormReset formReset(ElementInterface|null $element = null)
 * @method string|FormRow formRow(ElementInterface|null $element = null, string|null $labelPosition = null, bool|null $renderErrors = null, string|null $partial = null)
 * @method string|FormSearch formSearch(ElementInterface|null $element = null)
 * @method string|FormSelect formSelect(ElementInterface|null $element = null)
 * @method string|FormSubmit formSubmit(ElementInterface|null $element = null)
 * @method string|FormTel formTel(ElementInterface|null $element = null)
 * @method string|FormText formText(ElementInterface|null $element = null)
 * @method string|FormTextarea formTextarea(ElementInterface|null $element = null)
 * @method string|FormTime formTime(ElementInterface|null $element = null)
 * @method string|FormUrl formUrl(ElementInterface|null $element = null)
 * @method string|FormWeek formWeek(ElementInterface|null $element = null)
 */
trait HelperTrait
{
}
// @codingStandardsIgnoreEnd
