<?php

declare(strict_types=1);

namespace Smile\Seller\Api\Data;

use Magento\Eav\Api\Data\AttributeInterface;

/**
 * @api
 */
interface SellerAttributeInterface extends AttributeInterface
{
    public const ENTITY_TYPE_CODE = 'smile_seller';

    /**
     * Check if attribute has a global scope
     *
     * @return bool
     */
    public function isScopeGlobal(): bool;

    /**
     * Check if attribute has a website scope
     *
     * @return bool
     */
    public function isScopeWebsite(): bool;

    /**
     * Retrieve attribute has a store scope
     *
     * @return bool
     */
    public function isScopeStore(): bool;
}
