# Aranyasen\HL7\Segment  







## Methods

| Name | Description |
|------|-------------|
|[__construct](#segment__construct)|Create a segment.|
|[getField](#segmentgetfield)|Get the field at index.|
|[getFields](#segmentgetfields)|Get fields from a segment|
|[getName](#segmentgetname)|Get the name of the segment. This is basically the value at index 0|
|[setField](#segmentsetfield)|Set the field specified by index to value.|
|[size](#segmentsize)|Get the number of fields for this segment, not including the name|




### Segment::__construct  

**Description**

```php
public __construct (string $name, array|null $fields)
```

Create a segment. 

A segment may be created with just a name or a name and an array of field  
values. The segment name should be a standard HL7 segment (e.g. MSH / PID etc.) that is three characters long, and  
upper case. If an array is given, all fields will be filled from that array. Note that for composed fields and  
sub-components, the array may hold sub-arrays and sub-sub-arrays. Repeated fields can not be supported the same  
way, since we can't distinguish between composed fields and repeated fields.  
  
Example:  
```php  
$seg = new Segment("PID");  
  
$seg->setField(3, "12345678");  
echo $seg->getField(1);  
``` 

**Parameters**

* `(string) $name`
: Name of the segment  
* `(array|null) $fields`
: Fields for segment  

**Return Values**

`void`

<hr />


### Segment::getField  

**Description**

```php
public getField (int $index)
```

Get the field at index. 

If the field is a composite field, it returns an array  
Example:  
```php  
$field = $seg->getField(9); // Returns a string/null/array depending on what the 9th field is.  
``` 

**Parameters**

* `(int) $index`
: Index of field  

**Return Values**

`null|string|array`

> The value of the field

<hr />


### Segment::getFields  

**Description**

```php
public getFields (int $from, int|null $to)
```

Get fields from a segment 

Get the fields in the specified range, or all if nothing specified. If only the 'from' value is provided, all  
fields from this index till the end of the segment will be returned. 

**Parameters**

* `(int) $from`
: Start range at this index  
* `(int|null) $to`
: Stop range at this index  

**Return Values**

`array`

> List of fields

<hr />


### Segment::getName  

**Description**

```php
public getName (void)
```

Get the name of the segment. This is basically the value at index 0 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`mixed`

> Name of segment

<hr />


### Segment::setField  

**Description**

```php
public setField (int $index, string|array $value)
```

Set the field specified by index to value. 

Indices start at 1, to stay with the HL7 standard. Trying to set the  
value at index 0 has no effect. The value may also be a reference to an array (that may itself contain arrays)  
to support composite fields (and sub-components).  
  
Examples:  
```php  
$segment->setField(18, 'abcd'); // Sets 18th field to abcd  
$segment->setField(8, 'ab^cd'); // Sets 8th field to ab^cd  
$segment->setField(10, ['John', 'Doe']); // Sets 10th field to John^Doe  
$segment->setField(12, ['']); // Sets 12th field to ''  
```  
  
If values are not provided at all, the method will just return. 

**Parameters**

* `(int) $index`
: Index to set  
* `(string|array) $value`
: Value for field  

**Return Values**

`boolean`



<hr />


### Segment::size  

**Description**

```php
public size (void)
```

Get the number of fields for this segment, not including the name 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`

> number of fields

<hr />

