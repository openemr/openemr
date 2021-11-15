# Aranyasen\HL7\Messages\ACK  





## Extend:

Aranyasen\HL7\Message

## Methods

| Name | Description |
|------|-------------|
|[setAckCode](#acksetackcode)|Set the acknowledgement code for the acknowledgement.|
|[setErrorMessage](#ackseterrormessage)|Set the error message for the acknowledgement.|

## Inherited methods

| Name | Description |
|------|-------------|
|__construct|Constructor for Message. Consider using the HL7 factory to obtain a message instead.|
|addSegment|Append a segment to the end of the message|
|getSegmentAsString|Get the segment identified by index as string, using the messages separators.|
|getSegmentByIndex|Return the segment specified by $index.|
|getSegmentFieldAsString|Get the field identified by $fieldIndex from segment $segmentIndex.|
|getSegments|Return an array containing all segments in the right order.|
|getSegmentsByName|Return an array of all segments with the given name|
|insertSegment|Insert a segment.|
|removeSegmentByIndex|Remove the segment indexed by $index.|
|segmentToString|Convert Segment object to string|
|setSegment|Set the segment on index.|
|toString|Return a string representation of this message.|



### ACK::setAckCode  

**Description**

```php
public setAckCode (string $code, string $msg)
```

Set the acknowledgement code for the acknowledgement. 

Code should be one of: A, E, R. Codes can be prepended with C or A, denoting enhanced or normal acknowledge mode.  
This denotes: accept, general error and reject respectively. The ACK module will determine the right answer mode  
(normal or enhanced) based upon the request, if not provided. The message provided in $msg will be set in MSA 3. 

**Parameters**

* `(string) $code`
: Code to use in acknowledgement  
* `(string) $msg`
: Acknowledgement message  

**Return Values**

`boolean`



<hr />


### ACK::setErrorMessage  

**Description**

```php
public setErrorMessage (string $msg)
```

Set the error message for the acknowledgement. 

This will also set the error code to either AE or CE, depending on the mode of the incoming message. 

**Parameters**

* `(string) $msg`
: Error message  

**Return Values**

`void`

<hr />

