<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Model\ResourceModel\Seller\Attribute;

use Smile\Seller\Api\Data\SellerInterface;

/**
 * Seller Attributes Collection
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * Main select object initialization.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getResource()->getMainTable()])
            ->where(
                'main_table.entity_type_id=?',
                $this->eavConfig->getEntityType(SellerInterface::ENTITY)->getId()
            );

        return $this;
    }
}
