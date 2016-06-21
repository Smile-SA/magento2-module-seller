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

namespace Smile\Seller\Model\ResourceModel\Seller;

use Smile\Seller\Model\Seller;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Seller Attributes
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute implements SellerAttributeInterface, ScopedAttributeInterface
{
    /**
     * Attributes shared between all entities
     * @var array
     */
    private $globalAttributes = [
        Seller::KEY_SELLER_CODE,
    ];

    /**
     * Return attribute scope
     *
     * @return bool
     */
    public function getScope()
    {
        return in_array($this->getAttributeCode(), $this->globalAttributes);
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }
}
