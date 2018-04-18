# Configuration

`Zend\Paginator` has several configuration methods that can be called:

Method signature                                             | Description
------------------------------------------------------------ | -----------
`setCurrentPageNumber(int $page) : void`                     | Sets the current page number (default 1).
`setItemCountPerPage(int $count) : void`                     | Sets the maximum number of items to display on a page (default 10).
`setPageRange(int $range) : void`                            | Sets the number of items to display in the pagination control (default 10). Note: Most of the time this number will be adhered to exactly, but scrolling styles do have the option of only using it as a guideline or starting value (e.g., Elastic).
`setView(Zend\View\Renderer\RendererInterface $view) : void` | Sets the view instance, for rendering convenience.
