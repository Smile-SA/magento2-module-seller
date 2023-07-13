<?php

declare(strict_types=1);

namespace Smile\Seller\Model\Locator;

use Magento\Store\Api\Data\StoreInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Offer Locator Interface.
 */
interface LocatorInterface
{
    /**
     * Get the current seller.
     */
    public function getSeller(): ?SellerInterface;

    /**
     * Get the current store.
     */
    public function getStore(): ?StoreInterface;
}
