<?php

declare(strict_types=1);

namespace Smile\Seller\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\Entity\ScopeInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;

/**
 * Seller Attributes Persistor.
 */
class AttributePersistor extends \Magento\Eav\Model\ResourceModel\AttributePersistor
{
    /**
     * @inheritdoc
     */
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($attribute instanceof SellerAttributeInterface) {
            $useDefault = $useDefault || $attribute->isScopeGlobal();
        }

        return parent::getScopeValue($scope, $attribute, $useDefault);
    }
}
