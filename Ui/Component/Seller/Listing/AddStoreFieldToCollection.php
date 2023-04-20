<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Ui\Component\Seller\Listing;

use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Filter Strategy for store_id field.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AddStoreFieldToCollection implements AddFilterToCollectionInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager Store Manager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null): void
    {
        if (isset($condition['eq']) && $condition['eq']) {
            /** @var \Smile\Seller\Model\ResourceModel\Seller\Collection $collection  */
            $collection->setStore($this->storeManager->getStore($condition['eq']));
        }
    }
}
