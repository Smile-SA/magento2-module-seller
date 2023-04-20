<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Registry Locator for offers
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ?SellerInterface
     */
    private ?SellerInterface $seller = null;

    /**
     * @var ?StoreInterface
     */
    private ?StoreInterface $store = null;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Registry              $registry     The application registry
     * @param StoreManagerInterface $storeManager The Store Manager
     */
    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
     */
    public function getSeller(): SellerInterface|null
    {
        if (null !== $this->seller) {
            return $this->seller;
        }

        if ($this->registry->registry('current_seller')) {
            return $this->seller = $this->registry->registry('current_seller');
        }

        return null;
    }
    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getStore(): StoreInterface|null
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($this->getSeller() && $this->getSeller()->getStoreId()) {
            $this->store = $this->storeManager->getStore($this->getSeller()->getStoreId());

            return $this->store;
        }

        if ($this->registry->registry('current_store') !== null) {
            $this->store = $this->registry->registry('current_store');

            return $this->store;
        }

        return $this->storeManager->getStore(Store::DEFAULT_STORE_ID);
    }
}
