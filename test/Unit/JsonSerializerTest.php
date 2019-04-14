<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit;

use NaiveSerializer\CollapseToSingleValue;
use NaiveSerializer\Serializer;
use NaiveSerializer\Test\Unit\Fixtures\ArrayCases;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\ChainCheck;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\ItemAdded;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\ItemId;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\ItemIdContainer;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\ItemIdContainerContainer;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\Name;
use NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue\Timestamp;
use NaiveSerializer\Test\Unit\Fixtures\DefaultValue;
use NaiveSerializer\Test\Unit\Fixtures\NoDocblock;
use NaiveSerializer\Test\Unit\Fixtures\NoVarAnnotation;
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
    public function it_serializes_and_deserializes_multiple_types_of_arrays()
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
    public function you_cant_deserialize_built_in_classes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        Serializer::deserialize(\DateTime::class, '{}');
    }

    /**
     * @test
     */
    public function you_cant_serialize_built_in_classes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('not user-defined');
        Serializer::serialize(new \DateTime());
    }

    /**
     * @test
     */
    public function you_need_to_provide_valid_json_data()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('invalid JSON');

        Serializer::deserialize(SupportedCases::class, '[invalid JSON');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_dockblock_for_all_properties()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('docblock');

        Serializer::deserialize(NoDocblock::class, '{"noDocblock":"..."}');
    }

    /**
     * @test
     */
    public function you_need_to_define_a_var_annotations_for_all_properties()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('@var annotation');

        Serializer::deserialize(NoVarAnnotation::class, '{"noVarAnnotation":"..."}');
    }

    /**
     * @test
     */
    public function upon_deserialization_it_skips_properties_that_are_not_defined_in_the_data_and_keeps_default_values()
    {
        $expected = new DefaultValue();

        $actual = Serializer::deserialize(DefaultValue::class, '{}');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_not_serialize_unsupported_types()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        Serializer::serialize(new UnsupportedType());
    }

    /**
     * @test
     */
    public function it_can_not_deserialize_unsupported_types()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported type');

        Serializer::deserialize(UnsupportedType::class, '{"unsupportedType":"..."}');
    }

    /**
     * @test
     */
    public function null_is_allowed_when_deserializing()
    {
        $expected = new NullIsAllowed();
        $expected->nullIsAllowed = null;

        $actual = Serializer::deserialize(NullIsAllowed::class, '{"nullIsAllowed":null}');

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

        $actual = Serializer::deserialize(SimpleClass::class . '[]', '[{"property":"value"}]');

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_cannot_serialize_top_level_collapsable_objects()
    {
        //
        // Actually not sold on this...
        //
        // If return value for serialize could return array|string, we could handle this,
        // but I'm not sure that 1) makes sense and 2) is desirable.
        //

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp('/'.str_replace('\\', '\\\\', CollapseToSingleValue::class).'/');
        $original = Name::fromString('elephpant');

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
"elephpant"
EOD;

        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(Name::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /** @test */
    public function it_serializes_and_deserializes_deep_collapsable_objects()
    {
        $original = new ItemAdded(
            ItemId::fromString('57D44FEF-C6E6-4908-B43D-76AF8295D06A'),
            Name::fromString('elephpant'),
            Timestamp::fromSecondsSinceUnixEpochUtc(100)
        );

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
{
    "itemId": "57D44FEF-C6E6-4908-B43D-76AF8295D06A",
    "name": "elephpant",
    "timestamp": {
        "secondsSinceUnixEpoch": 100,
        "timeZone": "UTC"
    }
}
EOD;

        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(ItemAdded::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }

    /** @test */
    public function it_serializes_and_deserializes_chained_collapsable_objects()
    {
        $itemId = ItemId::fromString('34E2BFF4-3B28-431E-8E1C-A8EC9391B0FB');
        $itemIdContainer = new ItemIdContainer($itemId);
        $itemIdContainerContainer = new ItemIdContainerContainer($itemIdContainer);
        $original = new ChainCheck($itemIdContainer, $itemIdContainerContainer);

        $serialized = Serializer::serialize($original);

        $expectedJson = <<<EOD
{
    "itemIdContainer": "34E2BFF4-3B28-431E-8E1C-A8EC9391B0FB",
    "itemIdContainerContainer": "34E2BFF4-3B28-431E-8E1C-A8EC9391B0FB"
}
EOD;

        $this->assertJsonStringEqualsJsonString($expectedJson, $serialized);

        $deserialized = Serializer::deserialize(ChainCheck::class, $serialized);

        $this->assertEquals($original, $deserialized);
    }
}
