<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

final class ItemAdded
{
    /**
     * @var ItemId
     */
    private $itemId;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * ItemAdded constructor.
     *
     * @param ItemId $itemId
     * @param Name $name
     * @param Timestamp $timestamp
     */
    public function __construct(ItemId $itemId, Name $name, Timestamp $timestamp)
    {
        $this->itemId = $itemId;
        $this->name = $name;
        $this->timestamp = $timestamp;
    }
}