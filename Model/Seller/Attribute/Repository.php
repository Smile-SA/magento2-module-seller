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
namespace Smile\Seller\Model\Seller\Attribute;

use Magento\Eav\Api\AttributeRepositoryInterface as EavAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\MetadataObjectInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Seller Attributes Repository
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Repository implements AttributeRepositoryInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var EavAttributeRepositoryInterface
     */
    private EavAttributeRepositoryInterface $eavAttributeRepository;

    /**
     * @param EavAttributeRepositoryInterface $eavAttributeRepository EAV Attributes Repository
     * @param SearchCriteriaBuilder           $searchCriteriaBuilder  Search Criteria Builder
     */
    public function __construct(
        EavAttributeRepositoryInterface $eavAttributeRepository,
        SearchCriteriaBuilder           $searchCriteriaBuilder
    ) {
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null): array
    {
        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AttributeSearchResultsInterface
    {
        return $this->eavAttributeRepository->getList(
            SellerInterface::ENTITY,
            $searchCriteria
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $attributeCode): AttributeInterface|SellerAttributeInterface
    {
        return $this->eavAttributeRepository->get(
            SellerInterface::ENTITY,
            $attributeCode
        );
    }
}
