# XML to JSON conversion

`Zend\Json` provides a convenience method for transforming *XML* formatted data into *JSON* format.
This feature was inspired from an [IBM developerWorks
article](http://www.ibm.com/developerworks/xml/library/x-xml2jsonphp/).

`Zend\Json` includes a static function called `Zend\Json\Json::fromXml()`. This function will
generate *JSON* from a given *XML* input. This function takes any arbitrary *XML* string as an input
parameter. It also takes an optional boolean input parameter to instruct the conversion logic to
ignore or not ignore the *XML* attributes during the conversion process. If this optional input
parameter is not given, then the default behavior is to ignore the *XML* attributes. This function
call is made as shown below:

```php
// fromXml function simply takes a String containing XML contents
// as input.
$jsonContents = Zend\Json\Json::fromXml($xmlStringContents, true);
```

`Zend\Json\Json::fromXml()` function does the conversion of the *XML* formatted string input
parameter and returns the equivalent *JSON* formatted string output. In case of any *XML* input
format error or conversion logic error, this function will throw an exception. The conversion logic
also uses recursive techniques to traverse the *XML* tree. It supports recursion upto 25 levels
deep. Beyond that depth, it will throw a `Zend\Json\Exception`. There are several *XML* files with
varying degree of complexity provided in the tests directory of Zend Framework. They can be used to
test the functionality of the xml2json feature.

## Example

The following is a simple example that shows both the *XML* input string passed to and the *JSON*
output string returned as a result from the `Zend\Json\Json::fromXml()` function. This example used
the optional function parameter as not to ignore the *XML* attributes during the conversion. Hence,
you can notice that the resulting *JSON* string includes a representation of the *XML* attributes
present in the *XML* input string.

*XML* input string passed to `Zend\Json\Json::fromXml()` function:

```php
<?xml version="1.0" encoding="UTF-8"?>
<books>
    <book id="1">
        <title>Code Generation in Action</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>Manning</publisher>
    </book>

    <book id="2">
        <title>PHP Hacks</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>O'Reilly</publisher>
    </book>

    <book id="3">
        <title>Podcasting Hacks</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>O'Reilly</publisher>
    </book>
</books>
```

*JSON* output string returned from `Zend\Json\Json::fromXml()` function:

```php
{
   "books" : {
      "book" : [ {
         "@attributes" : {
            "id" : "1"
         },
         "title" : "Code Generation in Action",
         "author" : {
            "first" : "Jack", "last" : "Herrington"
         },
         "publisher" : "Manning"
      }, {
         "@attributes" : {
            "id" : "2"
         },
         "title" : "PHP Hacks", "author" : {
            "first" : "Jack", "last" : "Herrington"
         },
         "publisher" : "O'Reilly"
      }, {
         "@attributes" : {
            "id" : "3"
         },
         "title" : "Podcasting Hacks", "author" : {
            "first" : "Jack", "last" : "Herrington"
         },
         "publisher" : "O'Reilly"
      }
   ]}
}
```

More details about this xml2json feature can be found in the original proposal itself. Take a look
at the [Zend\_xml2json
proposal](http://framework.zend.com/wiki/display/ZFPROP/Zend_xml2json+-+Senthil+Nathan).
