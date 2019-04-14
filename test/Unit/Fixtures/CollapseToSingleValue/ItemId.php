<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

use NaiveSerializer\CollapseToSingleValue;

final class ItemId implements CollapseToSingleValue
{
    /**
     * @var string
     */
    private $uuid;

    private function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public static function fromString(string $uuid): ItemId
    {
        return new ItemId($uuid);
    }
}