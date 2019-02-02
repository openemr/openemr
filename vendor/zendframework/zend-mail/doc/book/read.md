# Reading and Storing Mail

zend-mail can read mail messages from several local or remote mail storage
types. Storage adapters share the same API for counting and fetching messages,
and some of them implement additional interfaces for less common features. For a
feature overview of the implemented storages, see the following table.

Feature               | Mbox     | Maildir  | Pop3     | IMAP
--------------------- | -------- | -------- | -------- | ----
Storage type          | local    | local    | remote   | remote
Fetch message         | Yes      | Yes      | Yes      | Yes
Fetch MIME-part       | emulated | emulated | emulated | emulated
Folders               | Yes      | Yes      | No       | Yes
Create message/folder | No       | todo     | No       | todo
Flags                 | No       | Yes      | No       | Yes
Quota                 | No       | Yes      | No       | No

Storage adapters return instances of `Zend\Mail\Storage\Message`, which has a
different API than [messages used when sending](message/intro.md); the API is
described in the ["Working with messages"](#working-with-messages) section.

## Basic POP3 example

```php
use Zend\Mail\Storage\Pop3;

$mail = Pop3([
    'host'     => 'localhost',
    'user'     => 'test',
    'password' => 'test',
]);

echo $mail->countMessages() . " messages found\n";
foreach ($mail as $message) {
    printf("Mail from '%s': %s\n", $message->from, $message->subject);
}
```

## Using local storage via mbox and maildir

Mbox and Maildir are the two supported formats for local mail storage.

If you want to read from an mbox file, provide the filename to the constructor
of `Zend\Mail\Storage\Mbox`:

```php
use Zend\Mail\Storage\Mbox;

$mail = new Mbox(['filename' => '/home/test/mail/inbox']);
```

Maildir operates similarly, but requires a dirname instead:

```php
use Zend\Mail\Storage\Maildir;
$mail = new Maildir(['dirname' => '/home/test/mail/']);
```

Both constructors throw a `Zend\Mail\Exception` if the storage can't be read.

## Using remote storage protocols

For remote storage, the two most popular protocols are supported: POP3 and IMAP.
Both need at least a host and a user to connect and login. The default password
is an empty string, and the default port for the protocol is used if none is
provided.

```php
use Zend\Mail\Storage\Imap;
use Zend\Mail\Storage\Pop3;

// Connecting with Pop3:
$mail = Pop3([
    'host'     => 'example.com',
    'user'     => 'test',
    'password' => 'test',
]);

// Connecting with Imap:
$mail = new Imap([
    'host'     => 'example.com',
    'user'     => 'test',
    'password' => 'test',
]);

// Example of using POP3 on a non-standard port:
$mail = new Pop3([
    'host'     => 'example.com',
    'port'     => 1120,
    'user'     => 'test',
    'password' => 'test',
]);
```

Both storage adapters support SSL and TLS. If you use SSL, the default port
changes as specified in the relevant RFC.

```php
use Zend\Mail\Storage\Pop3;

// Examples use Pop3; the same configuration works for Imap.

// Use SSL on a non-standard port (default is 995 for Pop3 and 993 for Imap)
$mail = new Pop3([
    'host'     => 'example.com',
    'user'     => 'test',
    'password' => 'test',
    'ssl'      => 'SSL',
]);

// use TLS on the default port:
$mail = new Pop3([
    'host'     => 'example.com',
    'user'     => 'test',
    'password' => 'test',
    'ssl'      => 'TLS',
]);
```

Both constructors throw `Zend\Mail\Exception` or `Zend\Mail\Protocol\Exception`
(extends `Zend\Mail\Exception`) for connection errors, depending on the type of
error encountered.

## Fetching, counting, and removing messages

Once you have opened the mail storage, you may fetch messages. To do so, you
need the message number, which is a counter starting with 1 for the first
message. To fetch the message, you use the method `getMessage()`:

```php
$message = $mail->getMessage($messageNum);
```

Array access is also supported, but this access method does not support any
additional parameters that could be added to `getMessage()`. As long as you
don't mind, and can live with the default values, you may use:

```php
$message = $mail[$messageNum];
```

For iterating over all messages the `Iterator` interface is implemented:

```php
foreach ($mail as $messageNum => $message) {
    // do stuff ...
}
```

To count the messages in the storage, you can use the method
`countMessages()`; alternately, storage adapters implement `Countable`, allowing
you to `count()` the instance.

```php
// method
$maxMessage = $mail->countMessages();

// array access
$maxMessage = count($mail);
```

To remove a mail, use the method `removeMessage()`, or rely on array access and
use `unset()`:

```php
// method
$mail->removeMessage($messageNum);

// array access
unset($mail[$messageNum]);
```

## Working with messages

After you fetch a message, you can:

- fetch headers
- fetch the message content
- fetch individual parts of multipart messages

All headers can be accessed as message instance properties or via the method
`getHeader()`; use the latter for messages with compound names.  Header names
are normalized to lowercase internally, but may be fetched using any case
structure; dash-separated headers may be fetched using camelCase notation. If no
header matching the name is found, an exception is thrown; use the
`headerExists()` method (or `isset($message->headerName)`) to test for header
existence prior to retrieval.

```php
// get the message object
$message = $mail->getMessage(1);

// output subject of message
echo $message->subject . "\n";

// get content-type header
$type = $message->contentType;

// check if CC isset:
if (isset($message->cc)) { // or $message->headerExists('cc');
    $cc = $message->cc;
}
```

If you have multiple headers with the same name &mdash; e.g. the `Received`
headers &mdash; you will want an array of values. Property access always returns
a string, so use `getHeader()` instead for these situations:

```php
// get header as property - the result is always a string,
// with new lines between each value.
$received = $message->received;

// The same via getHeader() method:
$received = $message->getHeader('received', 'string');

// To retrieve an array of values:
$received = $message->getHeader('received', 'array');
foreach ($received as $line) {
    // do stuff
}

// If you don't define a format you'll get the internal representation
// (string for single headers, array for multiple):
$received = $message->getHeader('received');
if (is_string($received)) {
    // only one received header found in message
}
```

The method `getHeaders()` returns all headers as an array with the lower-cased
name as the key and the value as an array for multiple headers or as string for
single headers.

```php
// dump all headers
foreach ($message->getHeaders() as $name => $value) {
    if (is_string($value)) {
        echo "$name: $value\n";
        continue;
    }
    foreach ($value as $entry) {
        echo "$name: $entry\n";
    }
}
```

If you don't have a multipart message, fetch the content via `getContent()`.
Unlike headers, the content is only fetched when needed (aka late-fetch).

```php
// output message content for HTML
echo '<pre>';
echo $message->getContent();
echo '</pre>';
```

Checking for multipart messages is done with the method `isMultipart()`. If you
have a multipart message you, can get retrieve the individual
`Zend\Mail\Storage\Part` instances making up the message via the `getPart()`
method, which accepts the part index as a parameter (indices start with 1).
`Zend\Mail\Storage\Part` is the base class of `Zend\Mail\Storage\Message`, and
thus exposes the same API with regards to headers, content, and retrieving
nested parts.

```php
// get the first non-multipart part
$part = $message;
while ($part->isMultipart()) {
    $part = $message->getPart(1);
}
echo 'Type of this part is ' . strtok($part->contentType, ';') . "\n";
echo "Content:\n";
echo $part->getContent();
```

`Zend\Mail\Storage\Part` also implements `RecursiveIterator`, which allows iterating
through all parts, even when nested. Additionally, it implements the magic
method `__toString()`, which returns the content.

```php
use RecursiveIteratorIterator;
use Zend\Mail\Exception;

// output first text/plain part
$foundPart = null;
foreach (new RecursiveIteratorIterator($mail->getMessage(1)) as $part) {
    try {
        if (strtok($part->contentType, ';') == 'text/plain') {
            $foundPart = $part;
            break;
        }
    } catch (Exception $e) {
        // ignore
    }
}
if (! $foundPart) {
    echo 'no plain text part found';
} else {
    echo "plain text part: \n" . $foundPart;
}
```

## Checking for flags

Maildir and IMAP support storing flags with messages. The `Zend\Mail\Storage`
class defines constants for all known maildir and IMAP system flags, named
`FLAG_<flagname>`. To check for flags, `Zend\Mail\Storage\Message` has
a method called `hasFlag()`. With `getFlags()` you'll get all flags.

```php
use Zend\Mail\Storage;

// Find unread messages:
echo "Unread mails:\n";
foreach ($mail as $message) {
    if ($message->hasFlag(Storage::FLAG_SEEN)) {
        continue;
    }

    // mark recent/new mails
    echo ($message->hasFlag(Storage::FLAG_RECENT))
        ? '! '
        : '  ';

    echo $message->subject . "\n";
}

// Check for known flags
$flags = $message->getFlags();
echo 'Message is flagged as: ';
foreach ($flags as $flag) {
    switch ($flag) {
        case Storage::FLAG_ANSWERED:
            echo 'Answered ';
            break;
        case Storage::FLAG_FLAGGED:
            echo 'Flagged ';
            break;

        // ...
        // check for other flags
        // ...

        default:
            echo $flag . '(unknown flag) ';
    }
}
```

As IMAP allows user or client defined flags, you could get flags that don't have
a constant in `Zend\Mail\Storage`. Instead, they are returned as strings and can
be checked the same way with `hasFlag()`.

```php
// check message for client defined flags $IsSpam, $SpamTested
if (! $message->hasFlag('$SpamTested')) {
    echo 'message has not been tested for spam';
} elseif ($message->hasFlag('$IsSpam')) {
    echo 'this message is spam';
} else {
    echo 'this message is ham';
}
```

## Using folders

All storage adapters except POP3 support folders (also called *mailboxes*). The
interface implemented by all aadapters supporting folders is called
`Zend\Mail\Storage\Folder\FolderInterface`. Each also supports an optional
configuration parameter called `folder`, which is the folder selected after
login.

For the local storage adapters, you need to use the adapter-specific folder
classes, `Zend\Mail\Storage\Folder\Mbox` and `Zend\Mail\Storage\Folder\Maildir`.
Each accepts a single parameter, `dirname`, with the name of the base direcor.
The format for maildir is as defined in
[maildir++](https://en.wikipedia.org/wiki/Maildir#Maildir.2B.2B) (with a dot as
default delimiter); mbox uses a directory hierarchy of mbox files. If you don't
have an mbox file called `INBOX` in your mbox base directory, you need to
specify another folder via the constructor.

`Zend\Mail\Storage\Imap` supports folders by default.

Examples for opening folders with each adapter:

```php
use Zend\Mail\Storage\Folder;
use Zend\Mail\Storage\Imap;

// mbox with folders:
$mail = Folder\Mbox(['dirname' => '/home/test/mail/']);

// mbox with a default folder not called INBOX; also works
// with the maildir and IMAP implementations.
$mail = new Folder\Mbox([
    'dirname' => '/home/test/mail/',
    'folder'  => 'Archive',
]);

// maildir with folders:
$mail = new Folder\Maildir(['dirname' => '/home/test/mail/']);

// maildir with colon as delimiter, as suggested in Maildir++:
$mail = new Folder\Maildir([
    'dirname' => '/home/test/mail/',
    'delim'   => ':',
]);

// IMAP is the same with and without folders:
$mail = new Imap([
    'host'     => 'example.com',
    'user'     => 'test',
    'password' => 'test',
]);
```

With the method `getFolders($root = null)`, you can get the folder hierarchy
starting with the root folder, or the given folder. The method returns an
instance of `Zend\Mail\Storage\Folder`, which implements `RecursiveIterator`,
and all children are also instances of `Folder`. Each of these instances has a
local and a global name returned by the methods `getLocalName()` and
`getGlobalName()`. The global name is the absolute name from the root folder
(including delimiters); the local name is the name in the parent folder.

If you use the iterator, the key of the current element is the local name. The
global name is also returned by the magic method `__toString()`. Some folders
may not be selectable, which means they can't store messages; selecting them
results in an error. This can be checked with the method `isSelectable()`.

The following demonstrates providing a tree view of a folder:

```php
use RecursiveIteratorIterator;

$folders = new RecursiveIteratorIterator(
    $this->mail->getFolders(),
    RecursiveIteratorIterator::SELF_FIRST
);

echo '<select name="folder">';
foreach ($folders as $localName => $folder) {
    $localName = str_pad('', $folders->getDepth(), '-', STR_PAD_LEFT)
        .  $localName;
    echo '<option';

    if (! $folder->isSelectable()) {
        echo ' disabled="disabled"';
    }

    printf(
        ' value="%s">%s</option>',
        htmlspecialchars($folder),
        htmlspecialchars($localName)
    );
}
echo '</select>';
```

The current selected folder is returned by the method `getSelectedFolder()`.
Changing the folder is done with the method `selectFolder()`, which needs the
*global name* as a parameter. If you want to avoid writing delimiters, you can
also use the properties of a `Folder` instance:

```php
// depending on your mail storage and its settings $rootFolder->Archive->2005
// is the same as:
//  /Archive/2005
//  Archive:2005
//  INBOX.Archive.2005
//  ...
$folder = $mail->getFolders()->Archive->2005;
printf("Last folder was %s; new folder is %s\n", $mail->getSelectedFolder(), $folder);
$mail->selectFolder($folder);
```

## Advanced Use

### Using NOOP

If you're using a remote storage and have some long tasks, you might need to
keep the connection alive via noop:

```php
foreach ($mail as $message) {

    // do some calculations ...

    $mail->noop(); // keep alive

    // do something else ...

    $mail->noop(); // keep alive
}
```

### Caching instances

`Zend\Mail\Storage\Mbox`, `Zend\Mail\Storage\Folder\Mbox`,
`Zend\Mail\Storage\Maildir`, and `Zend\Mail\Storage\Folder\Maildir` implement the
magic methods `__sleep()` and `__wakeup()`, which means they are serializable.

Serialization avoids parsing files and directory trees multiple times. The
disadvantage is that your mbox or maildir storage should not change; as such,
its best used with static storage.

You can combine serialization with writable storage in a number of ways:

- Check the current mbox file for modification time changes.
- Reparse the folder structure if a folder has vanished (which still results in
  an error, but you can search for another folder afterwards).
- Create a signal file whenever a change is made; check for that signal file,
  reparse if present, and remove it afterwards.

```php
use Zend\Mail\Storage\Folder\Mbox;

// There's no specific cache handler/class used here,
// change the code to match your cache handler.
$signalFile  = '/home/test/.mail.last_change';
$mboxBasedir = '/home/test/mail/';
$cacheId     = 'example mail cache ' . $mboxBasedir . $signalFile;
$cache       = new Cache();
$hasCache    = ($cache->has($cacheId)
    && filemtime($signalFile) <= $cache->getMTime($cacheId)
);

$mail        = $hasCache
    ? $cache->get($cacheId)
    : new Mbox(['dirname' => $mboxBasedir]);

// do stuff ...

// Cache when done
$cache->set($cacheId, $mail);
```

### Extending Protocol Classes

Remote storage adapters use two classes: `Zend\Mail\Storage\<Name>` and
`Zend\Mail\Protocol\<Name>`. The protocol class translates the protocol commands
and responses from and to PHP, like methods for the commands or variables with
different structures for data. The storage class implements the common
interface for message access.

If you need additional protocol features, you can extend the protocol class and
use it in the constructor of the main class. As an example, assume we need to
knock different ports before we can connect to POP3.

```php
namespace Example\Mail
{
    use Zend\Mail;

    class Exception extends Mail\Exception
    {
    }
}

namespace Example\Mail\Protocol
{
    use Zend\Mail\Protocol;

    class Exception extends Protocol\Exception
    {
    }
}

namespace Example\Mail\Protocol\Pop3
{
    use Zend\Mail\Protocol\Pop3;

    class Knock extends Pop3
    {
        private $host
        
        private $port;

        public function __construct($host, $port = null)
        {
            // no auto connect in this class
            $this->host = $host;
            $this->port = $port;
        }

        public function knock($port)
        {
            $sock = @fsockopen($this->host, $port);
            if ($sock) {
                fclose($sock);
            }
        }

        public function connect($host = null, $port = null, $ssl = false)
        {
            if ($host === null) {
                $host = $this->host;
            }
            if ($port === null) {
                $port = $this->port;
            }
            parent::connect($host, $port);
        }
    }
}

namespace Example\Mail\Pop3
{
    use Example\Mail\Protoco\Pop3\Knock as KnockProtocol;
    use Zend\Mail\Storage\Pop3;

    class Knock extends Pop3
    {
        public function __construct(array $params)
        {
            // ... check $params here! ...
            $protocol = new KnockProtocol($params['host']);

            // do our "special" thing
            foreach ((array) $params['knock_ports'] as $port) {
                $protocol->knock($port);
            }

            // get to correct state
            $protocol->connect($params['host'], $params['port']);
            $protocol->login($params['user'], $params['password']);

            // initialize parent
            parent::__construct($protocol);
        }
    }
}

$mail = new Example\Mail\Pop3\Knock([
    'host'        => 'localhost',
    'user'        => 'test',
    'password'    => 'test',
    'knock_ports' => [1101, 1105, 1111],
]);
```

The above assumes a connection is made; when connected, it logs in and, if
supported, selects a folder as provided to the constructor.  When defining your
own protocol class, make sure that's done or the next method will fail if the
server doesn't allow it in the current state.

### Using Quotas

`Zend\Mail\Storage\Writable\Maildir` has support for Maildir++ quotas. It's
disabled by default, but it's possible to use it manually, if the automatic
checks are not desired (this means `appendMessage()`, `removeMessage()`, and
`copyMessage()` do no checks and do not add entries to the maildirsize file). If
enabled, an exception is thrown if you try to write to the maildir and it's
already over quota.

There are three methods used for quotas: `getQuota()`, `setQuota()`, and
`checkQuota()`:

```php
use Zend\Mail\Storage\Writable\Maildir;

$mail = new Maildir(['dirname' => '/home/test/mail/']);
$mail->setQuota(true); // true to enable, false to disable

printf("Quota check is now %s\n", $mail->getQuota() ? 'enabled' : 'disabled');

// Check quota can be used even if quota checks are disabled:
printf("You are %sover quota\n", $mail->checkQuota() ? '' : 'not ');
```

`checkQuota()` can also return a more detailed response by passing a boolean
`true` argument:

```php
$quota = $mail->checkQuota(true);
printf("You are %sover quota\n", $quota['over_quota'] ? '' : 'not ');
printf(
    "You have %d of %d messages and use %d of %d octets\n",
    $quota['count'],
    $quota['quota']['count'],
    $quota['size'],
    $quota['quota']['size']
);
```

If you want to specify your own quota instead of using the one specified in the
maildirsize file, you can do with `setQuota()`:

```php
// Message count and octet size supported; order does matter.
$quota = $mail->setQuota(['size' => 10000, 'count' => 100]);
```

To add your own quota checks, use single letters as keys, and they will be
preserved (but obviously not checked). It's also possible to extend
`Zend\Mail\Storage\Writable\Maildir` to define your own quota if the maildirsize
file is missing (which can happen in Maildir++):

```php
namespace Example\Mail\Storage;

use Zend\Mail\Storage\Exception;
use Zend\Mail\Storage\Writable\Maildir as BaseMaildir;

class Maildir extends BaseMaildir
{
    /**
     * getQuota is called with $fromStorage = true by quota checks.
     *
     * @param bool $fromStorage
     * @return bool|array
     */
    public function getQuota($fromStorage = false) {
        try {
            return parent::getQuota($fromStorage);
        } catch (Exception $e) {
            if (! $fromStorage) {
                // unknown error:
                throw $e;
            }

            // maildirsize file must be missing
            list($count, $size) = get_quota_from_somewhere_else();
            return ['count' => $count, 'size' => $size];
        }
    }
}
```
