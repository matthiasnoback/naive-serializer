<?php
declare(strict_types=1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class NativePropertyTypes
{
    public string $string;

    public int $int;

    /**
     * @var NativePropertyTypes[]
     */
    public array $array = [];

    public bool $bool;

    public float $float;

    public ?string $optionalString = null;

    public ?int $optionalInt = null;

    public ?bool $optionalBool = null;

    public ?float $optionalFloat = null;
}
