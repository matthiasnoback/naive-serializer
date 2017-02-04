<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class UnsupportedType
{
    /**
     * @var resource
     */
    private $unsupportedType;

    public function __construct()
    {
        $this->unsupportedType = fopen(__FILE__, 'r');
    }
}
