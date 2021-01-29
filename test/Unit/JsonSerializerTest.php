<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit;

use NaiveSerializer\Serializer;
use NaiveSerializer\Test\Unit\Fixtures\ArrayCases;
use NaiveSerializer\Test\Unit\Fixtures\DefaultValue;
use NaiveSerializer\Test\Unit\Fixtures\Foo;
use NaiveSerializer\Test\Unit\Fixtures\IgnoredProperty;
use NaiveSerializer\Test\Unit\Fixtures\NoDocblock;
use NaiveSerializer\Test\Unit\Fixtures\NoVarAnnotation;
use NaiveSerializer\Test\Unit\Fixtures\NullCompoundType;
use NaiveSerializer\Test\Unit\Fixtures\NullIsAllowed;
use NaiveSerializer\Test\Unit\Fixtures\SimpleClass;
use NaiveSerializer\Test\Unit\Fixtures\SupportedCases;
use NaiveSerializer\Test\Unit\Fixtures\UnsupportedType;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_serializes_and_deserializes_a_json_object(): void
    {
        $original = new Fixtures\SupportedCases();
        $original->a = 'a';
        $original->b = 1;
        $originalSub = new Fixtures\SupportedCases();
        $originalSub->a = 'a1';
        $originalSub->b = 2;
        $original->c = [$originalSub];
        $original->d = true;
        $original->e = 1.23;

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
{
    "a": "a",
    "b":1,
    "c": [
        {
            "a": "a1",
            "b": 2,
            "c": [],
            "d": null,
            "e": null
        }
    ],
    "d": true,
    "e": 1.23
}
EOD;

        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(Fixtures\SupportedCases::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /**
     * @test
     */
    public function it_serializes_and_deserializes_multiple_types_of_arrays(): void
    {
        $original = new ArrayCases();
        $original->intList = [1, 2, 3];
        $original->stringList = ['a', 'b', 'c'];
        $original->intToStringMap = [1 => 'b', 2 => 'c'];
        $original->stringToStringMap = ['a' => 'A', 'b' => 'B', 'c' => 'C'];

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
{
    "intList": [1, 2, 3],
    "stringList": ["a", "b", "c"],
    "intToStringMap": {
        "1": "b",
        "2": "c"
    },
    "stringToStringMap": {
        "a": "A",
        "b": "B",
        "c": "C"
    }
}
EOD;
        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(ArrayCases::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /**
     * @test
     */
    public function it_supports_PHP74_native_property_types(): void
    {
        $original = new Fixtures\NativePropertyTypes();
        $original->string = 'a';
        $original->int = 1;
        $originalSub = new Fixtures\NativePropertyTypes();
        $originalSub->string = 'a1';
        $originalSub->int = 2;
        $originalSub->bool = false;
        $originalSub->float = 2.34;
        $original->array = [$originalSub];
        $original->bool = true;
        $original->float = 1.23;

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
{
    "string": "a",
    "int":1,
    "array": [
        {
            "string": "a1",
            "int": 2,
            "array": [],
            "bool": false,
            "float": 2.34,
            "optionalString": null,
            "optionalInt": null,
            "optionalBool": null,
            "optionalFloat": null
        }
    ],
    "bool": true,
    "float": 1.23,
    "optionalString": null,
    "optionalInt": null,
    "optionalBool": null,
    "optionalFloat": null
}
EOD;

        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(Fixtures\NativePropertyTypes::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /**
     * @test
     */
    public function you_cant_deserialize_built_in_classes(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        Serializer::deserialize(\DateTime::class, '{}');
    }

    /**
     * @test
     */
    public function you_cant_serialize_built_in_classes(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        Serializer::serialize(new \DateTime());
    }

    /**
     * @test
     */
    public function you_need_to_provide_valid_json_data(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('invalid JSON');

        Serializer::deserialize(SupportedCases::class, '[invalid JSON');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_dockblock_for_all_properties(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('docblock');

        Serializer::deserialize(NoDocblock::class, '{"noDocblock":"..."}');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_var_annotations_for_all_properties(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('@var annotation');

        Serializer::deserialize(NoVarAnnotation::class, '{"noVarAnnotation":"..."}');
    }

    /**
     * @test
     */
    public function upon_deserialization_it_skips_properties_that_are_not_defined_in_the_data_and_keeps_default_values(): void
    {
        $expected = new DefaultValue();

        $actual = Serializer::deserialize(DefaultValue::class, '{}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_not_serialize_unsupported_types(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        Serializer::serialize(new UnsupportedType());
    }

    /**
     * @test
     */
    public function it_can_not_deserialize_unsupported_types(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        Serializer::deserialize(UnsupportedType::class, '{"unsupportedType":"..."}');
    }

    /**
     * @test
     */
    public function null_is_allowed_when_deserializing(): void
    {
        $expected = new NullIsAllowed();
        $expected->nullIsAllowed = null;

        $actual = Serializer::deserialize(NullIsAllowed::class, '{"nullIsAllowed":null}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_deal_with_possible_null_compound_types_when_deserializing(): void
    {
        $expected = new NullCompoundType();
        $expected->nullOrFoo = new Foo('bar');

        $actual = Serializer::deserialize(NullCompoundType::class, '{"nullOrFoo":{"foo":"bar"}}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_accepts_null_for_possible_null_compound_types_when_deserializing(): void
    {
        $expected = new NullCompoundType();
        $expected->nullOrFoo = null;

        $actual = Serializer::deserialize(NullCompoundType::class, '{"nullOrFoo":null}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function you_can_deserialize_an_array(): void
    {
        $object = new SimpleClass();
        $object->property = 'value';
        $expected = [$object];

        $actual = Serializer::deserialize(SimpleClass::class . '[]', '[{"property":"value"}]');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_ignores_properties_annotated_with_ignore(): void
    {
        $object = new IgnoredProperty();
        $object->foo = 'bar';
        $object->events = [new SimpleClass()];

        self::assertJsonStringEqualsJsonString(
            '{"foo":"bar"}',
            Serializer::serialize($object)
        );

        $object = new IgnoredProperty();
        $object->foo = 'bar';

        self::assertEquals(
            $object,
            Serializer::deserialize(IgnoredProperty::class, '{"foo":"bar"}')
        );
    }
}
