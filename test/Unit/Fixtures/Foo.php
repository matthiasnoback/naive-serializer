<?php
declare(strict_types=1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class Foo
{
    /**
     * @var string
     */
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}
