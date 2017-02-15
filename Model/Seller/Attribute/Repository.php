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

use Smile\Seller\Api\AttributeRepositoryInterface;
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
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository EAV Attributes Repository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder  $searchCriteriaBuilder  Search Criteria Builder
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->eavAttributeRepository->getList(
            SellerInterface::ENTITY,
            $searchCriteria
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributeCode)
    {
        return $this->eavAttributeRepository->get(
            SellerInterface::ENTITY,
            $attributeCode
        );
    }
}
