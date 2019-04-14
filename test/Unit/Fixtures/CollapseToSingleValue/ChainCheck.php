<?php
declare(strict_types = 1);

namespace NaiveSerializer\Test\Unit\Fixtures\CollapseToSingleValue;

final class ChainCheck
{
    /**
     * @var ItemIdContainer
     */
    private $itemIdContainer;

    /**
     * @var ItemIdContainerContainer
     */
    private $itemIdContainerContainer;

    public function __construct(ItemIdContainer $itemIdContainer, ItemIdContainerContainer $itemIdContainerContainer)
    {
        $this->itemIdContainer = $itemIdContainer;
        $this->itemIdContainerContainer = $itemIdContainerContainer;
    }
}