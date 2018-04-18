# The ViewEvent

zend-view incorporates and utilizes a custom [zend-eventmanager
Event](https://zendframework.github.com/zend-eventmanager) implementation,
`Zend\View\ViewEvent`. This event is created during `Zend\View\View::getEvent()`
and is passed directly to all the events the `View` class triggers.

The `ViewEvent` adds accessors and mutators for the following:

- `Model` object, typically representing the layout view model.
- `Renderer` object.
- `Request` object.
- `Response` object.
- `Result` (typically a string representing the rendered content).

The methods it defines are:

- `setModel(Model $model)`
- `getModel()`
- `setRequest($request)`
- `getRequest()`
- `setResponse($response)`
- `getResponse()`
- `setRenderer($renderer)`
- `getRenderer()`
- `setResult($result)`
- `getResult()`

## Order of events

The following events are triggered, in the following order:

Name            | Constant                         | Description
--------------- | -------------------------------- | -----------
`renderer`      | `ViewEvent::EVENT_RENDERER`      | Render the view, with the help of renderers.
`renderer.post` | `ViewEvent::EVENT_RENDERER_POST` | Triggers after the view is rendered.
`response`      | `ViewEvent::EVENT_RESPONSE`      | Populate the response from the view.

Each is described in the following sections.

## ViewEvent::EVENT\_RENDERER

### Listeners

The following classes are listening to this event (they are sorted from higher priority to lower
priority):

#### For PhpStrategy

This listener is added when the strategy used for rendering is `PhpStrategy`:

Class                            | Priority | Method called    | Description
-------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\PhpStrategy` | 1        | `selectRenderer` | Return a `PhpRenderer`

#### For JsonStrategy

This listener is added when the strategy used for rendering is `JsonStrategy`:

Class                             | Priority | Method called    | Description
--------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\JsonStrategy` | 1        | `selectRenderer` | Return a `JsonRenderer`

#### For FeedStrategy

This listener is added when the strategy used for rendering is `FeedStrategy`:

Class                             | Priority | Method called    | Description
--------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\FeedStrategy` | 1        | `selectRenderer` | Return a `FeedRenderer`

### Triggerers

This event is triggered by the following classes:

Class            | In method | Description
---------------- | --------- | -----------
`Zend\View\View` | `render`  | It has a short circuit callback that stops propagation once one result return an instance of a Renderer.

## ViewEvent::EVENT\_RENDERER\_POST

### Listeners

There are currently no built-in listeners for this event.

### Triggerers

This event is triggered by the following classes:

Class            | In method | Description
---------------- | --------- | -----------
`Zend\View\View` | `render`  | This event is triggered after `ViewEvent::EVENT_RENDERER` and before `ViewEvent::EVENT_RESPONSE`.

## ViewEvent::EVENT\_RESPONSE

### Listeners

The following classes are listening to this event (they are sorted from higher priority to lower
priority):

#### For PhpStrategy

This listener is added when the strategy used for rendering is `PhpStrategy`:

Class                            | Priority | Method called    | Description
-------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\PhpStrategy` | 1        | `injectResponse` | Populate the `Response` object from the rendered view.

#### For JsonStrategy

This listener is added when the strategy used for rendering is `JsonStrategy`:

Class                             | Priority | Method called    | Description
--------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\JsonStrategy` | 1        | `injectResponse` | Populate the `Response` object with the serialized JSON content.

#### For FeedStrategy

This listener is added when the strategy used for rendering is `FeedStrategy`:

Class                             | Priority | Method called    | Description
--------------------------------- | -------- | ---------------- | -----------
`Zend\View\Strategy\FeedStrategy` | 1        | `injectResponse` | Populate the `Response` object with the rendered feed.

### Triggerers

This event is triggered by the following classes:

Class            | In method | Description
---------------- | --------- | -----------
`Zend\View\View` | `render`  | This event is triggered after `ViewEvent::EVENT_RENDERER` and `ViewEvent::EVENT_RENDERER_POST`.
