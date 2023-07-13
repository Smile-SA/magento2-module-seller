<?php

declare(strict_types=1);

namespace Smile\Seller\Model\Locator;

use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Registry Locator for offers.
 */
class RegistryLocator implements LocatorInterface
{
    private ?SellerInterface $seller = null;
    private ?StoreInterface $store = null;

    public function __construct(
        private Registry $registry,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getSeller(): ?SellerInterface
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
     * @inheritdoc
     */
    public function getStore(): ?StoreInterface
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($this->getSeller() && $this->getSeller()->getData('store_id')) {
            $this->store = $this->storeManager->getStore($this->getSeller()->getData('store_id'));

            return $this->store;
        }

        if ($this->registry->registry('current_store') !== null) {
            $this->store = $this->registry->registry('current_store');

            return $this->store;
        }

        return $this->storeManager->getStore(Store::DEFAULT_STORE_ID);
    }
}
