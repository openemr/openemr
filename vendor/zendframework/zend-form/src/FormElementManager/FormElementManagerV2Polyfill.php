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
use Zend\Form\Exception;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\FormFactoryAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * zend-servicemanager v2-compatible plugin manager implementation for form elements.
 *
 * Enforces that elements retrieved are instances of ElementInterface.
 */
class FormElementManagerV2Polyfill extends AbstractPluginManager
{
    use FormElementManagerTrait;

    /**
     * Aliases for default set of helpers
     *
     * @var array
     */
    protected $aliases = [
        'button'         => Element\Button::class,
        'captcha'        => Element\Captcha::class,
        'checkbox'       => Element\Checkbox::class,
        'collection'     => Element\Collection::class,
        'color'          => Element\Color::class,
        'csrf'           => Element\Csrf::class,
        'date'           => Element\Date::class,
        'dateselect'     => Element\DateSelect::class,
        'datetime'       => Element\DateTime::class,
        'datetimelocal'  => Element\DateTimeLocal::class,
        'datetimeselect' => Element\DateTimeSelect::class,
        'element'        => Element::class,
        'email'          => Element\Email::class,
        'fieldset'       => Fieldset::class,
        'file'           => Element\File::class,
        'form'           => Form::class,
        'hidden'         => Element\Hidden::class,
        'image'          => Element\Image::class,
        'month'          => Element\Month::class,
        'monthselect'    => Element\MonthSelect::class,
        'multicheckbox'  => Element\MultiCheckbox::class,
        'number'         => Element\Number::class,
        'password'       => Element\Password::class,
        'radio'          => Element\Radio::class,
        'range'          => Element\Range::class,
        'select'         => Element\Select::class,
        'submit'         => Element\Submit::class,
        'text'           => Element\Text::class,
        'textarea'       => Element\Textarea::class,
        'time'           => Element\Time::class,
        'url'            => Element\Url::class,
        'week'           => Element\Week::class,
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
     * Constructor
     *
     * Overrides parent constructor in order to add the initializer methods injectFactory()
     * and callElementInit().
     *
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configInstanceOrParentLocator = null, array $v3config = [])
    {
        // Provide default initializers, ensuring correct order
        array_unshift($this->initializers, [$this, 'injectFactory']);
        array_push($this->initializers, [$this, 'callElementInit']);

        parent::__construct($configInstanceOrParentLocator, $v3config);
    }

    /**
     * Inject the factory to any element that implements FormFactoryAwareInterface
     *
     * @param mixed $instance Instance to inspect and potentially inject.
     * @param ContainerInterface $container Container passed to initializer.
     */
    public function injectFactory($instance, ContainerInterface $container)
    {
        // Need to retrieve the parent container
        $container = $container->getServiceLocator() ?: $container;

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
     * @param mixed $instance Instance to inspect and optionally initialize.
     * @param ContainerInterface $container
     */
    public function callElementInit($instance, ContainerInterface $container)
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
     * @param string $invokableClass
     * @param null|bool $shared Ignored.
     * @return self
     */
    public function setInvokableClass($name, $invokableClass, $shared = null)
    {
        if (! $this->has($invokableClass)) {
            $this->setFactory($invokableClass, ElementFactory::class);
        }

        if ($invokableClass !== $name) {
            $this->setAlias($name, $invokableClass);
        }

        return $this;
    }

    /**
     * Validate the plugin is of the expected type.
     *
     * @param mixed $plugin
     * @throws Exception\InvalidElementException
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new Exception\InvalidElementException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($plugin) ? get_class($plugin) : gettype($plugin))
            ));
        }
    }

    /**
     * Overrides parent::addInitializer in order to ensure default initializers are in expected positions.
     *
     * Always pushes `injectFactory` to top of initializer stack, and
     * `callElementInit` to the bottom.
     *
     * {@inheritDoc}
     */
    public function addInitializer($initializer, $topOfStack = true)
    {
        $firstInitializer = [$this, 'injectFactory'];
        $lastInitializer  = [$this, 'callElementInit'];

        foreach ([$firstInitializer, $lastInitializer] as $default) {
            if (false === ($index = array_search($default, $this->initializers))) {
                continue;
            }
            unset($this->initializers[$index]);
        }

        parent::addInitializer($initializer, $topOfStack);

        array_unshift($this->initializers, $firstInitializer);
        array_push($this->initializers, $lastInitializer);

        return $this;
    }
}
