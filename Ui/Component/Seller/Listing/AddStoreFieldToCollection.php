<?php

declare(strict_types=1);

namespace Smile\Seller\Ui\Component\Seller\Listing;

use Magento\Framework\Data\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
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
        // @phpstan-ignore-next-line
        if (isset($condition['eq']) && $condition['eq']) {
            /** @var Store|StoreInterface $store */
            $store = $this->storeManager->getStore($condition['eq']);
            /** @var \Smile\Seller\Model\ResourceModel\Seller\Collection $collection */
            // @phpstan-ignore-next-line as generated object
            $collection->setStore($store);
        }
    }
}
