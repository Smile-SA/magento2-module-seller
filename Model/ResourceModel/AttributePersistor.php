<?php

namespace Smile\Seller\Model\ResourceModel;

use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AttributePersistor extends \Magento\Eav\Model\ResourceModel\AttributePersistor
{
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($attribute instanceof SellerAttributeInterface) {
            $useDefault = $useDefault || $attribute->getScope() == ScopedAttributeInterface::SCOPE_GLOBAL;
        }

        return parent::getScopeValue($scope, $attribute, $useDefault);
    }
}
