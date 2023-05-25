<?php

namespace Smile\Seller\Ui\Component\Seller\Listing;

use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Filter Strategy for store_id field.
 */
class AddStoreFieldToCollection implements AddFilterToCollectionInterface
{
    public function __construct(protected StoreManagerInterface $storeManager)
    {
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq']) && $condition['eq']) {
            /** @var \Smile\Seller\Model\ResourceModel\Seller\Collection $collection  */
            $collection->setStore($this->storeManager->getStore($condition['eq']));
        }
    }
}
