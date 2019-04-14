<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

use NaiveSerializer\CollapseToSingleValue;

final class ItemIdContainerContainer implements CollapseToSingleValue
{
    /**
     * @var ItemIdContainer
     */
    private $itemIdContainer;

    public function __construct(ItemIdContainer $itemIdContainer)
    {
        $this->itemIdContainer = $itemIdContainer;
    }
}