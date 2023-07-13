<?php

declare(strict_types=1);

namespace Smile\Seller\Api;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;

/**
 * @api
 */
interface AttributeRepositoryInterface extends MetadataServiceInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param SearchCriteriaInterface $searchCriteria Search Criteria
     * @return AttributeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface;

    /**
     * Retrieve specific attribute
     *
     * @param string $attributeCode The attribute code
     * @return AttributeInterface|SellerAttributeInterface
     */
    public function get(string $attributeCode): AttributeInterface|SellerAttributeInterface;
}
