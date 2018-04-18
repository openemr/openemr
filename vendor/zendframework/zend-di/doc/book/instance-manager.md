# Instance Manager

The `InstanceManager` is responsible for any runtime information associated with
the zend-di DiC.  This means that the information that goes into the instance
manager is specific to both how the particular consuming application's needs,
and even more specifically to the environment in which the application is
running.

## Parameters

Parameters are simply entry points for either dependencies or instance
configuration values. A class consists of a set of parameters, each uniquely
named. When writing your classes, you should attempt to not use the same
parameter name twice in the same class when you expect that that parameters is
used for either instance configuration or an object dependency. This leads to an
ambiguous parameter, and is a situation best avoided.

Our movie finder example can be further used to explain these concepts:

```php
namespace MyLibrary
{
    class DbAdapter
    {
        protected $username = null;
        protected $password = null;

        public function __construct($username, $password)
        {
            $this->username = $username;
            $this->password = $password;
        }
    }
}

namespace MyMovieApp
{
    class MovieFinder
    {
        protected $dbAdapter = null;

        public function __construct(\MyLibrary\DbAdapter $dbAdapter)
        {
            $this->dbAdapter = $dbAdapter;
        }
    }

    class MovieLister
    {
        protected $movieFinder = null;

        public function __construct(MovieFinder $movieFinder)
        {
            $this->movieFinder = $movieFinder;
        }
    }
}
```

In the above example, the class `DbAdapter` has 2 parameters: `username` and
`password`; `MovieFinder` has one parameter: `dbAdapter`; and `MovieLister` has
one parameter: `movieFinder`. Any of these can be utilized for injection of
either dependencies or scalar values during instance configuration or during
call time.

When looking at the above code, since the `dbAdapter` parameter and the
`movieFinder` parameter are both type-hinted with concrete types, the DiC can
assume that it can fulfill these object tendencies by itself. On the other hand,
username and password do not have type-hints and are, more than likely, scalar
in nature. Since the DiC cannot reasonably know this information, it must be
provided to the instance manager in the form of parameters. Not doing so will
force `$di->get('MyMovieApp\\MovieLister')` to throw an exception.

The following ways of using parameters are available:

```php
// setting instance configuration into the instance manager
$di->instanceManager()->setParameters('MyLibrary\DbAdapter', [
    'username' => 'myusername',
    'password' => 'mypassword',
]);

// forcing a particular dependency to be used by the instance manager
$di->instanceManager()->setParameters('MyMovieApp\MovieFinder', [
    'dbAdapter' => new MyLibrary\DbAdapter('myusername', 'mypassword')
]);

// passing instance parameters at call time
$movieLister = $di->get('MyMovieApp\MovieLister', [
    'username' => $config->username,
    'password' => $config->password,
]);

// passing a specific instance at call time
$movieLister = $di->get('MyMovieApp\MovieLister', [
    'dbAdapter' => new MyLibrary\DbAdapter('myusername', 'mypassword')
]);
```

## Preferences

In many cases, you might be using interfaces as type hints as opposed to
concrete types. Lets assume the movie example was modified in the following way:

```php
namespace MyMovieApp
{
    interface MovieFinderInterface
    {
        // methods required for this type
    }

    class GenericMovieFinder implements MovieFinderInterface
    {
        protected $dbAdapter = null;

        public function __construct(\MyLibrary\DbAdapter $dbAdapter)
        {
            $this->dbAdapter = $dbAdapter;
        }
    }

    class MovieLister
    {
        protected $movieFinder = null;

        public function __construct(MovieFinderInterface $movieFinder)
        {
            $this->movieFinder = $movieFinder;
        }
    }
}
```

What you'll notice above is that the `MovieLister` type now expects that the
dependency injected implements the `MovieFinderInterface`. This allows multiple
implementations of this base interface to be used as a dependency, if that is
what the consumer decides they want to do. As you can imagine, zend-di, by
itself would not be able to determine what kind of concrete object to use
fulfill this dependency, so this type of 'preference' needs to be made known to
the instance manager.

To give this information to the instance manager, see the following code
example:

```php
$di->instanceManager()->addTypePreference(
    'MyMovieApp\MovieFinderInterface',
    'MyMovieApp\GenericMovieFinder'
);

// assuming all instance config for username, password is setup
$di->get('MyMovieApp\MovieLister');
```

## Aliases

In some situations, you'll find that you need to alias an instance. There are
two main reasons to do this. First, it creates a simpler, alternative name to
use when using the DiC, as opposed to using the full class name. Second, you
might find that you need to have the same object type in two separate contexts.
This means that when you alias a particular class, you can then attach a
specific instance configuration to that alias, as opposed to attaching that
configuration to the class name.

To demonstrate both of these points, we'll look at a use case where we'll have
two separate database adapters. One will be for read-only operations, the other
will be for read-write operations.

> ### Alias parameters
>
> Aliases can also have parameters registered at alias time.

```php
// assume the MovieLister example of code from the quick start.

$im = $di->instanceManager();

// add alias for short naming
$im->addAlias('movielister', 'MyMovieApp\MovieLister');

// add aliases for specific instances
$im->addAlias('dbadapter-readonly', 'MyLibrary\DbAdapter', [
    'username' => $config->db->readAdapter->username,
    'password' => $config->db->readAdapter->password,
]);
$im->addAlias('dbadapter-readwrite', 'MyLibrary\DbAdapter', [
    'username' => $config->db->readWriteAdapter->username,
    'password' => $config->db->readWriteAdapter->password,
]);

// set a default type to use, pointing to an alias
$im->addTypePreference('MyLibrary\DbAdapter', 'dbadapter-readonly');

$movieListerRead = $di->get('MyMovieApp\MovieLister');
$movieListerReadWrite = $di->get('MyMovieApp\MovieLister', [
    'dbAdapter' => 'dbadapter-readwrite',
]);
```
