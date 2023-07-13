<?php

declare(strict_types=1);

namespace Smile\Seller\Model\Seller\Attribute;

use Magento\Eav\Api\AttributeRepositoryInterface as EavAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Seller Attributes Repository.
 */
class Repository implements AttributeRepositoryInterface
{
    public function __construct(
        private EavAttributeRepositoryInterface $eavAttributeRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface
    {
        return $this->eavAttributeRepository->getList(
            SellerInterface::ENTITY,
            $searchCriteria
        );
    }

    /**
     * @inheritdoc
     */
    public function get(string $attributeCode): AttributeInterface|SellerAttributeInterface
    {
        return $this->eavAttributeRepository->get(
            SellerInterface::ENTITY,
            $attributeCode
        );
    }
}
