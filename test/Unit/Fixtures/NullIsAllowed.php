<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class NullIsAllowed
{
    /**
     * @var string|null
     */
    public ?string $nullIsAllowed = 'default';
}
