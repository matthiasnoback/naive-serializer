<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

final class Timestamp
{
    /**
     * @var int
     */
    private $secondsSinceUnixEpoch;

    /**
     * @var TimeZone
     */
    private $timeZone;

    private function __construct(int $secondsSinceUnixEpoch, TimeZone $timeZone)
    {
        $this->secondsSinceUnixEpoch = $secondsSinceUnixEpoch;
        $this->timeZone = $timeZone;
    }

    public static function fromSecondsSinceUnixEpochUtc(int $secondsSinceUnixEpoch): Timestamp
    {
        return new Timestamp(
            $secondsSinceUnixEpoch,
            TimeZone::fromString('UTC')
        );
    }

    public static function fromSecondsSinceUnixEpoch(int $secondsSinceUnixEpoch, TimeZone $timeZone): Timestamp
    {
        return new Timestamp(
            $secondsSinceUnixEpoch,
            $timeZone
        );
    }
}