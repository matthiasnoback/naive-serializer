<?php
declare(strict_types = 1);

namespace NaiveSerializer;

final class Serializer
{
    /**
     * @see JsonSerializer::deserialize()
     *
     * @param string $type
     * @param string $jsonEncodedData
     * @return mixed
     */
    public static function deserialize(string $type, string $jsonEncodedData)
    {
        return (new JsonSerializer())->deserialize($type, $jsonEncodedData);
    }

    /**
     * @see JsonSerializer::serialize()
     *
     * @param mixed $rawData
     */
    public static function serialize($rawData): string
    {
        return (new JsonSerializer())->serialize($rawData);
    }
}
