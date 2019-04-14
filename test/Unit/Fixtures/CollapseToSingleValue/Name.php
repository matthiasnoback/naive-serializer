<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

use NaiveSerializer\CollapseToSingleValue;

final class Name implements CollapseToSingleValue
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): Name
    {
        return new Name($value);
    }
}