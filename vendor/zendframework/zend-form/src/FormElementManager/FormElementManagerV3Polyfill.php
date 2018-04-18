<?php
/**
 * @link      http://github.com/zendframework/zend-form for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\FormElementManager;

use Interop\Container\ContainerInterface;
use Zend\Form\Element;
use Zend\Form\ElementFactory;
use Zend\Form\ElementInterface;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\FormFactoryAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Stdlib\InitializableInterface;

/**
 * zend-servicemanager v3-compatible plugin manager implementation for form elements.
 *
 * Enforces that elements retrieved are instances of ElementInterface.
 */
class FormElementManagerV3Polyfill extends AbstractPluginManager
{
    use FormElementManagerTrait;

    /**
     * Aliases for default set of helpers
     *
     * @var array
     */
    protected $aliases = [
        'button'         => Element\Button::class,
        'Button'         => Element\Button::class,
        'captcha'        => Element\Captcha::class,
        'Captcha'        => Element\Captcha::class,
        'checkbox'       => Element\Checkbox::class,
        'Checkbox'       => Element\Checkbox::class,
        'collection'     => Element\Collection::class,
        'Collection'     => Element\Collection::class,
        'color'          => Element\Color::class,
        'Color'          => Element\Color::class,
        'csrf'           => Element\Csrf::class,
        'Csrf'           => Element\Csrf::class,
        'date'           => Element\Date::class,
        'Date'           => Element\Date::class,
        'dateselect'     => Element\DateSelect::class,
        'dateSelect'     => Element\DateSelect::class,
        'DateSelect'     => Element\DateSelect::class,
        'datetime'       => Element\DateTime::class,
        'dateTime'       => Element\DateTime::class,
        'DateTime'       => Element\DateTime::class,
        'datetimelocal'  => Element\DateTimeLocal::class,
        'dateTimeLocal'  => Element\DateTimeLocal::class,
        'DateTimeLocal'  => Element\DateTimeLocal::class,
        'datetimeselect' => Element\DateTimeSelect::class,
        'dateTimeSelect' => Element\DateTimeSelect::class,
        'DateTimeSelect' => Element\DateTimeSelect::class,
        'element'        => Element::class,
        'Element'        => Element::class,
        'email'          => Element\Email::class,
        'Email'          => Element\Email::class,
        'fieldset'       => Fieldset::class,
        'Fieldset'       => Fieldset::class,
        'file'           => Element\File::class,
        'File'           => Element\File::class,
        'form'           => Form::class,
        'Form'           => Form::class,
        'hidden'         => Element\Hidden::class,
        'Hidden'         => Element\Hidden::class,
        'image'          => Element\Image::class,
        'Image'          => Element\Image::class,
        'month'          => Element\Month::class,
        'Month'          => Element\Month::class,
        'monthselect'    => Element\MonthSelect::class,
        'monthSelect'    => Element\MonthSelect::class,
        'MonthSelect'    => Element\MonthSelect::class,
        'multicheckbox'  => Element\MultiCheckbox::class,
        'multiCheckbox'  => Element\MultiCheckbox::class,
        'MultiCheckbox'  => Element\MultiCheckbox::class,
        'multiCheckBox'  => Element\MultiCheckbox::class,
        'MultiCheckBox'  => Element\MultiCheckbox::class,
        'number'         => Element\Number::class,
        'Number'         => Element\Number::class,
        'password'       => Element\Password::class,
        'Password'       => Element\Password::class,
        'radio'          => Element\Radio::class,
        'Radio'          => Element\Radio::class,
        'range'          => Element\Range::class,
        'Range'          => Element\Range::class,
        'select'         => Element\Select::class,
        'Select'         => Element\Select::class,
        'submit'         => Element\Submit::class,
        'Submit'         => Element\Submit::class,
        'text'           => Element\Text::class,
        'Text'           => Element\Text::class,
        'textarea'       => Element\Textarea::class,
        'Textarea'       => Element\Textarea::class,
        'time'           => Element\Time::class,
        'Time'           => Element\Time::class,
        'url'            => Element\Url::class,
        'Url'            => Element\Url::class,
        'week'           => Element\Week::class,
        'Week'           => Element\Week::class,
    ];

    /**
     * Factories for default set of helpers
     *
     * @var array
     */
    protected $factories = [
        Element\Button::class         => ElementFactory::class,
        Element\Captcha::class        => ElementFactory::class,
        Element\Checkbox::class       => ElementFactory::class,
        Element\Collection::class     => ElementFactory::class,
        Element\Color::class          => ElementFactory::class,
        Element\Csrf::class           => ElementFactory::class,
        Element\Date::class           => ElementFactory::class,
        Element\DateSelect::class     => ElementFactory::class,
        Element\DateTime::class       => ElementFactory::class,
        Element\DateTimeLocal::class  => ElementFactory::class,
        Element\DateTimeSelect::class => ElementFactory::class,
        Element::class                => ElementFactory::class,
        Element\Email::class          => ElementFactory::class,
        Fieldset::class               => ElementFactory::class,
        Element\File::class           => ElementFactory::class,
        Form::class                   => ElementFactory::class,
        Element\Hidden::class         => ElementFactory::class,
        Element\Image::class          => ElementFactory::class,
        Element\Month::class          => ElementFactory::class,
        Element\MonthSelect::class    => ElementFactory::class,
        Element\MultiCheckbox::class  => ElementFactory::class,
        Element\Number::class         => ElementFactory::class,
        Element\Password::class       => ElementFactory::class,
        Element\Radio::class          => ElementFactory::class,
        Element\Range::class          => ElementFactory::class,
        Element\Select::class         => ElementFactory::class,
        Element\Submit::class         => ElementFactory::class,
        Element\Text::class           => ElementFactory::class,
        Element\Textarea::class       => ElementFactory::class,
        Element\Time::class           => ElementFactory::class,
        Element\Url::class            => ElementFactory::class,
        Element\Week::class           => ElementFactory::class,

        // v2 normalized variants

        'zendformelementbutton'         => ElementFactory::class,
        'zendformelementcaptcha'        => ElementFactory::class,
        'zendformelementcheckbox'       => ElementFactory::class,
        'zendformelementcollection'     => ElementFactory::class,
        'zendformelementcolor'          => ElementFactory::class,
        'zendformelementcsrf'           => ElementFactory::class,
        'zendformelementdate'           => ElementFactory::class,
        'zendformelementdateselect'     => ElementFactory::class,
        'zendformelementdatetime'       => ElementFactory::class,
        'zendformelementdatetimelocal'  => ElementFactory::class,
        'zendformelementdatetimeselect' => ElementFactory::class,
        'zendformelement'               => ElementFactory::class,
        'zendformelementemail'          => ElementFactory::class,
        'zendformfieldset'              => ElementFactory::class,
        'zendformelementfile'           => ElementFactory::class,
        'zendformform'                  => ElementFactory::class,
        'zendformelementhidden'         => ElementFactory::class,
        'zendformelementimage'          => ElementFactory::class,
        'zendformelementmonth'          => ElementFactory::class,
        'zendformelementmonthselect'    => ElementFactory::class,
        'zendformelementmulticheckbox'  => ElementFactory::class,
        'zendformelementnumber'         => ElementFactory::class,
        'zendformelementpassword'       => ElementFactory::class,
        'zendformelementradio'          => ElementFactory::class,
        'zendformelementrange'          => ElementFactory::class,
        'zendformelementselect'         => ElementFactory::class,
        'zendformelementsubmit'         => ElementFactory::class,
        'zendformelementtext'           => ElementFactory::class,
        'zendformelementtextarea'       => ElementFactory::class,
        'zendformelementtime'           => ElementFactory::class,
        'zendformelementurl'            => ElementFactory::class,
        'zendformelementweek'           => ElementFactory::class,
    ];

    /**
     * Don't share form elements by default (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Don't share form elements by default (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Interface all plugins managed by this class must implement.
     * @var string
     */
    protected $instanceOf = ElementInterface::class;

    /**
     * Inject the factory to any element that implements FormFactoryAwareInterface
     *
     * @param ContainerInterface $container
     * @param mixed $instance Instance to inspect and optionally inject.
     */
    public function injectFactory(ContainerInterface $container, $instance)
    {
        if (! $instance instanceof FormFactoryAwareInterface) {
            return;
        }

        $factory = $instance->getFormFactory();
        $factory->setFormElementManager($this);

        if ($container && $container->has('InputFilterManager')) {
            $inputFilters = $container->get('InputFilterManager');
            $factory->getInputFilterFactory()->setInputFilterManager($inputFilters);
        }
    }

    /**
     * Call init() on any element that implements InitializableInterface
     *
     * @param ContainerInterface $container
     * @param mixed $instance Instance to inspect and optionally initialize.
     */
    public function callElementInit(ContainerInterface $container, $instance)
    {
        if ($instance instanceof InitializableInterface) {
            $instance->init();
        }
    }

    /**
     * Override setInvokableClass
     *
     * Overrides setInvokableClass to:
     *
     * - add a factory mapping $invokableClass to ElementFactory::class
     * - alias $name to $invokableClass
     *
     * @param string $name
     * @param null|string $class
     * @return void
     */
    public function setInvokableClass($name, $class = null)
    {
        $class = $class ?: $name;

        if (! $this->has($class)) {
            $this->setFactory($class, ElementFactory::class);
        }

        if ($class === $name) {
            return;
        }

        $this->setAlias($name, $class);
    }

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param  mixed $plugin
     * @throws InvalidServiceException
     * @return void
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($plugin) ? get_class($plugin) : gettype($plugin))
            ));
        }
    }

    /**
     * Overrides parent::configure in order to ensure default initializers are in expected positions.
     *
     * Always pushes `injectFactory` to top of initializer stack, and
     * `callElementInit` to the bottom.
     *
     * {@inheritDoc}
     */
    public function configure(array $config)
    {
        $firstInitializer = [$this, 'injectFactory'];
        $lastInitializer  = [$this, 'callElementInit'];

        foreach ([$firstInitializer, $lastInitializer] as $default) {
            if (false === ($index = array_search($default, $this->initializers))) {
                continue;
            }
            unset($this->initializers[$index]);
        }

        parent::configure($config);

        array_unshift($this->initializers, $firstInitializer);
        array_push($this->initializers, $lastInitializer);

        return $this;
    }
}
