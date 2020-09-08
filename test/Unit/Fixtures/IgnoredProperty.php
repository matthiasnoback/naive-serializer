<?php
declare(strict_types=1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class IgnoredProperty
{
    /**
     * @var array<object>
     * @ignore
     */
    public $events = [];

    /**
     * @var string
     */
    public $foo;
}
