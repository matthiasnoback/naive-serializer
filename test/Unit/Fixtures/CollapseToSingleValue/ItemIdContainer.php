<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

use NaiveSerializer\CollapseToSingleValue;

final class ItemIdContainer implements CollapseToSingleValue
{
    /**
     * @var ItemId
     */
    private $itemId;

    public function __construct(ItemId $itemId)
    {
        $this->itemId = $itemId;
    }
}