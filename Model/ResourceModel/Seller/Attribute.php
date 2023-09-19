<?php

declare(strict_types=1);

namespace Smile\Seller\Model\ResourceModel\Seller;

use Magento\Eav\Model\Entity\Attribute as BaseAttribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Model\Seller;

/**
 * Seller Attributes.
 */
class Attribute extends BaseAttribute implements SellerAttributeInterface, ScopedAttributeInterface
{
    /**
     * Attributes shared between all entities
     *
     * @var string[]
     */
    private array $globalAttributes = [
        Seller::KEY_SELLER_CODE,
    ];

    /**
     * Retrieve attribute is global scope flag
     */
    public function isScopeGlobal(): bool
    {
        return in_array($this->getAttributeCode(), $this->globalAttributes);
    }

    /**
     * Retrieve attribute is website scope website
     */
    public function isScopeWebsite(): bool
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     */
    public function isScopeStore(): bool
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * @inheritdoc
     */
    public function __sleep()
    {
        $this->unsetData('entity_type');

        return parent::__sleep();
    }
}
