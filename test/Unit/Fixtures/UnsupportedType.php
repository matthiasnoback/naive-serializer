<?php
declare(strict_types=1);

namespace NaiveSerializer\Test\Unit\Fixtures;

use Assert\Assert;

final class UnsupportedType
{
    /**
     * @var resource
     */
    private $unsupportedType;

    public function __construct()
    {
        $resource = fopen(__FILE__, 'r');
        Assert::that($resource)->isResource();

        $this->unsupportedType = $resource;
    }
}
