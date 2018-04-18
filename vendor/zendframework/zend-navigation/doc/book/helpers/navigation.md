# Navigation Proxy

The `navigation()` helper is a proxy helper that relays calls to other
navigational helpers. It can be considered an entry point to all
navigation-related view tasks.

The `Navigation` helper finds other helpers that implement
`Zend\View\Helper\Navigation\HelperInterface`, which means custom view helpers
can also be proxied.  This would, however, require that the custom helper path
is added to the view.

When proxying to other helpers, the `Navigation` helper can inject its
container, ACL and optionally role, and a translator. This means that you won't
have to explicitly set all three in all navigational helpers, nor resort to
injecting by means of static methods.

## Methods

Method signature                                                               | Description
------------------------------------------------------------------------------ | -----------
`findHelper(string $helper, bool $strict = true) : Navigation\HelperInterface` | Finds the given helper, verifies that it is a navigational helper, and injects the current container, ACL and role instances,  and translator, if present. If `$strict` is `true`, the method will raise an exception when unable to find a valid helper.
`getInjectContainer() : bool`                                                  | Retrieve the flag indicating whether or not to inject the current container into proxied helpers; default is `true`.
`setInjectContainer(bool $flag) : self`                                        | Set the flag indicating whether or not to inject the current container into proxied helpers.
`getInjectAcl() : bool`                                                        | Retrieve the flag indicating whether or not to inject ACL and role instances into proxied helpers; default is `true`.
`setInjectAcl(bool $flag) : self`                                              | Set the flag indicating whether or not to inject ACL and role instances into proxied helpers.
`getInjectTranslator() : bool`                                                 | Retrieve the flag indicating whether or not to inject the current translator instance into proxied helpers; default is `true`.
`setInjectTranslator(bool $flag) : self`                                       | Set the flag indicating whether or not to inject the current translator instance into proxied helpers.
`getDefaultProxy() : string`                                                   | Retrieve the default proxy helper to delegate to when rendering; defaults to `menu`.
`setDefaultProxy(string $helper) : self`                                       | Set the default proxy helper to delegate to when rendering.
`render(AbstractContainer = null)`                                             | Proxies to the render method of the default proxy.
