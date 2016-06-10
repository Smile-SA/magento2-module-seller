<?php

namespace Smile\Seller\Model\ResourceModel\Seller\Attribute;

class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * Main select object initialization.
     * Joins catalog/eav_attribute table
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getResource()->getMainTable()])
        ->where(
            'main_table.entity_type_id=?',
            $this->eavConfig->getEntityType(\Smile\Seller\Model\Seller::ENTITY)->getId()
        );
        return $this;
    }
}