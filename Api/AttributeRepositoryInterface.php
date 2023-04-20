<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Api;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;

/**
 * Seller Attributes Repository Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface AttributeRepositoryInterface extends \Magento\Framework\Api\MetadataServiceInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param SearchCriteriaInterface $searchCriteria Search Criteria
     *
     * @return AttributeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface;

    /**
     * Retrieve specific attribute
     *
     * @param string $attributeCode The attribute code
     *
     * @return AttributeInterface|SellerAttributeInterface
     */
    public function get(string $attributeCode): AttributeInterface|SellerAttributeInterface;
}
