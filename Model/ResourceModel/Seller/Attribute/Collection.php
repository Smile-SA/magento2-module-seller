<?php

declare(strict_types=1);

namespace Smile\Seller\Model\ResourceModel\Seller\Attribute;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\ResourceModel\Seller\Attribute as SellerAttribute;

/**
 * Seller Attributes Collection.
 */
class Collection extends Attribute\Collection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            SellerAttribute::class,
            Attribute::class
        );
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect(): self
    {
        $this->getSelect()->from(['main_table' => $this->getResource()->getMainTable()])
            ->where(
                'main_table.entity_type_id=?',
                $this->eavConfig->getEntityType(SellerInterface::ENTITY)->getId()
            );

        return $this;
    }
}
