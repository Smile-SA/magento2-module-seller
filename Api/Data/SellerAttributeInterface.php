<?php

namespace Smile\Seller\Api\Data;

/**
 * @api
 */
interface SellerAttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    const ENTITY_TYPE_CODE = 'smile_seller';

    public function getScope();
}
