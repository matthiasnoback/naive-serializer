<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit;

use NaiveSerializer\JsonSerializer;
use NaiveSerializer\Test\Unit\Fixtures\ArrayCases;
use NaiveSerializer\Test\Unit\Fixtures\DefaultValue;
use NaiveSerializer\Test\Unit\Fixtures\NoDocblock;
use NaiveSerializer\Test\Unit\Fixtures\NoVarAnnotation;
use NaiveSerializer\Test\Unit\Fixtures\NullIsAllowed;
use NaiveSerializer\Test\Unit\Fixtures\SimpleClass;
use NaiveSerializer\Test\Unit\Fixtures\SupportedCases;
use NaiveSerializer\Test\Unit\Fixtures\UnsupportedType;

class JsonSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_serializes_and_deserializes_a_json_object()
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

        $serialized = JsonSerializer::serialize($original);

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

        $deserialized = JsonSerializer::deserialize(Fixtures\SupportedCases::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /**
     * @test
     */
    public function it_serializes_and_deserializes_multiple_types_of_arrays()
    {
        $original = new ArrayCases();
        $original->intList = [1, 2, 3];
        $original->stringList = ['a', 'b', 'c'];
        $original->intToStringMap = [1 => 'b', 2 => 'c'];
        $original->stringToStringMap = ['a' => 'A', 'b' => 'B', 'c' => 'C'];

        $serialized = JsonSerializer::serialize($original);

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

        $deserialized = JsonSerializer::deserialize(ArrayCases::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /**
     * @test
     */
    public function you_cant_deserialize_built_in_classes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        JsonSerializer::deserialize(\DateTime::class, '{}');
    }

    /**
     * @test
     */
    public function you_cant_serialize_built_in_classes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        JsonSerializer::serialize(new \DateTime());
    }

    /**
     * @test
     */
    public function you_need_to_provide_valid_json_data()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('invalid JSON');

        JsonSerializer::deserialize(SupportedCases::class, '[invalid JSON');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_dockblock_for_all_properties()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('docblock');

        JsonSerializer::deserialize(NoDocblock::class, '{"noDocblock":"..."}');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_var_annotations_for_all_properties()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('@var annotation');

        JsonSerializer::deserialize(NoVarAnnotation::class, '{"noVarAnnotation":"..."}');
    }

    /**
     * @test
     */
    public function upon_deserialization_it_skips_properties_that_are_not_defined_in_the_data_and_keeps_default_values()
    {
        $expected = new DefaultValue();

        $actual = JsonSerializer::deserialize(DefaultValue::class, '{}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_not_serialize_unsupported_types()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        JsonSerializer::serialize(new UnsupportedType());
    }

    /**
     * @test
     */
    public function it_can_not_deserialize_unsupported_types()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        JsonSerializer::deserialize(UnsupportedType::class, '{"unsupportedType":"..."}');
    }

    /**
     * @test
     */
    public function null_is_allowed_when_deserializing()
    {
        $expected = new NullIsAllowed();
        $expected->nullIsAllowed = null;

        $actual = JsonSerializer::deserialize(NullIsAllowed::class, '{"nullIsAllowed":null}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function you_can_deserialize_an_array()
    {
        $object = new SimpleClass();
        $object->property = 'value';
        $expected = [$object];

        $actual = JsonSerializer::deserialize(SimpleClass::class . '[]', '[{"property":"value"}]');

        $this->assertEquals($expected, $actual);
    }
}
