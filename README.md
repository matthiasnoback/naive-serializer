# Naive serializer

[![Build Status](https://travis-ci.org/matthiasnoback/naive-serializer.svg?branch=master)](https://travis-ci.org/matthiasnoback/naive-serializer)

The [JsonSerializer](src/JsonSerializer.php) that comes with this library is a very simple serializer/deserializer which recursively converts an object graph to and from JSON, without any configuration or custom code. Its design goals are:

- Users shouldn't be forced to add custom configuration to their existing classes.
- Users shouldn't need to write any supporting code.
- The solution should take care of as few edge cases as possible.
- The solution should be as small as possible, without becoming useless (<=100 LOC).
- The solution should warn the user about its limitations using descriptive exceptions.

In order to make this work, this library restricts you to using only values of type:

- `null`
- scalar (int, float, bool)
- user-defined objects (so no built-in PHP classes like `\DateTimeImmutable`)
- arrays where keys are irrelevant
- and any combination of the above

Furthermore, you need to define the types you used in standard `@var` docblock annotations (as you probably already do), e.g.

~~~language-php
/**
 * @var string
 *
 * @var int
 * 
 * @var bool
 *
 * You can use a relative class name:
 *
 * @var ClassName
 * 
 * Or a full class name:
 *
 * @var Fully\Qualified\Class\Name
 */
~~~

Of course, every property should have just one `@var` annotation.

You can define lists of the above types by simply adding `[]` to the `@var` annotation, e.g.
 
~~~language-php
@var Fully\Qualified\Class\Name
~~~

To work around the limitation that you can't use PHP's built-in classes, simply convert the data internally to something else. For example, to use a `\DateTimeImmutable` timestamp:

~~~language-php
/**
 * @var string
 */
private $timestamp;

public function __construct(\DateTimeImmutable $timestamp)
{
    $this->timestamp = $timestamp->format(\DateTime::ISO8601);
}

public function getTimestamp() : \DateTimeImmutable
{
    return \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $this->timestamp);
}
~~~

To use the serializer:

~~~language-php
// create an object
$object  = ...;

$serializedData = JsonSerializer::serialize($object);

// $serializedData will be a pretty-printed JSON string
~~~

To deserialize the data:

~~~language-php
$restoredObject = JsonSerializer::deserialize(
    Fully\Qualified\Class\Name::class, 
    $serializedData
);

// $restoredObject will be of type Fully\Qualified\Class\Name
~~~

# Thanks

This library stands on the shoulders of the `phpdocumentor/reflection-docblock` library, which does all the work related to property type resolving.
