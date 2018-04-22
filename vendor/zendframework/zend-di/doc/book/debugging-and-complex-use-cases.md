# Debugging & Complex Use Cases

## Debugging a DiC

It is possible to dump the information contained within both the `Definition`
and `InstanceManager` for a `Zend\Di\Di` instance.

The easiest way is to do the following:

```php
Zend\Di\Display\Console::export($di);
```

If you are using a `RuntimeDefinition` where upon you expect a particular
definition to be resolve at the first-call, you can see that information to the
console display to force it to read that class:

```php
Zend\Di\Display\Console::export($di, ['A\ClassIWantTo\GetTheDefinitionFor']);
```

## Complex Use Cases

### Interface Injection

```php
namespace Foo\Bar
{
    class Baz implements BamAwareInterface
    {
        public $bam;

        public function setBam(Bam $bam)
        {
            $this->bam = $bam;
        }
    }

    class Bam
    {
    }

    interface BamAwareInterface
    {
        public function setBam(Bam $bam);
    }
}

namespace {
    include 'zf2bootstrap.php';
    $di = new Zend\Di\Di;
    $baz = $di->get('Foo\Bar\Baz');
}
```

### Setter Injection with Class Definition

```php
namespace Foo\Bar
{
    class Baz
    {
        public $bam;

        public function setBam(Bam $bam)
        {
            $this->bam = $bam;
        }
    }

    class Bam {
    }
}

namespace {
    $di = new Zend\Di\Di;
    $di->configure(new Zend\Di\Config([
        'definition' => [
            'class' => [
                'Foo\Bar\Baz' => [
                    'setBam' => ['required' => true],
                ],
            ],
        ],
    ]));
    $baz = $di->get('Foo\Bar\Baz');
}
```

### Multiple Injections To A Single Injection Point

```php
namespace Application
{
    class Page
    {
        public $blocks;

        public function addBlock(Block $block)
        {
            $this->blocks[] = $block;
        }
    }

    interface Block
    {
    }
}

namespace MyModule {
    class BlockOne implements \Application\Block {}
    class BlockTwo implements \Application\Block {}
}

namespace {
    include 'zf2bootstrap.php';
    $di = new Zend\Di\Di;
    $di->configure(new Zend\Di\Config([
        'definition' => [
            'class' => [
                'Application\Page' => [
                    'addBlock' => [
                        'block' => [
                            'type' => 'Application\Block',
                            'required' => true
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            'Application\Page' => [
                'injections' => [
                    'MyModule\BlockOne',
                    'MyModule\BlockTwo',
                ],
            ],
        ],
    ]));
    $page = $di->get('Application\Page');
}
```
