<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures;

final class SupportedCases
{
    /**
     * @var string
     */
    public $a;

    /**
     * @var int
     */
    public $b;

    /**
     * @var SupportedCases[]
     */
    public $c = [];

    /**
     * @var bool
     */
    public $d;

    /**
     * @var float
     */
    public $e;
}
