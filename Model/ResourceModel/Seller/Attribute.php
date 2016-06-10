<?php

namespace Smile\Seller\Model\ResourceModel\Seller;

use Smile\Seller\Model\Seller;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class Attribute extends \Magento\Eav\Model\Entity\Attribute implements SellerAttributeInterface, ScopedAttributeInterface
{
    private $globalAttributes = [
        Seller::KEY_SELLER_CODE
    ];

    public function getScope()
    {
        return in_array($this->getAttributeCode(), $this->globalAttributes);
    }
}