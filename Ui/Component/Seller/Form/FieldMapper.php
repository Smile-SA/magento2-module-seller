<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Ui\Component\Seller\Form;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Smile\Seller\Api\Data\SellerInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Smile\Seller\Model\ResourceModel\Seller\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Dataprovider field mapper.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class FieldMapper
{
    /**
     * @var AttributeGroupRepositoryInterface
     */
    private AttributeGroupRepositoryInterface $attributeGroupRepository;

    /**
     * @var Collection
     */
    private Collection $attributesCollection;

    /**
     * @var string[]
     */
    private array $fieldsMap = [];

    /**
     *
     * @var array
     */
    private array $fieldsets = [];

    /**
     * Constructor.
     *
     * @param AttributeCollectionFactory        $attributeCollectionFactory Seller attribute collection factory.
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository   Attribute group repository.
     * @param string                            $attributeSetId             Mapper attribute set identifier.
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        string $attributeSetId
    ) {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->initFieldsMap($attributeCollectionFactory, $attributeSetId);
    }

    /**
     * Attribute collection for the current mapper.
     *
     * @return Collection
     */
    public function getAttributesCollection(): Collection
    {
        return $this->attributesCollection;
    }

    /**
     * Mapping of the attribute by fieldsets.
     *
     * @return string[]
     */
    public function getFieldsMap(): array
    {
        return $this->fieldsMap;
    }

    /**
     * Fielset properties.
     *
     * @return array
     */
    public function getFieldsets(): array
    {
        return $this->fieldsets;
    }

    /**
     * Init attribute collection, fielsets and mapping.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory Seller attribute collection factory.
     * @param string                     $attributeSetId             Mapper attribute set identifier.
     *
     * @return FieldMapper
     */
    private function initFieldsMap(AttributeCollectionFactory $attributeCollectionFactory, string $attributeSetId): FieldMapper
    {
        $this->fieldsMap            = [];
        $this->attributesCollection = $attributeCollectionFactory->create();
        $this->attributesCollection->setAttributeSetFilterBySetName($attributeSetId, SellerInterface::ENTITY);
        $this->attributesCollection->addSetInfo();

        foreach ($this->attributesCollection as $attribute) {
            $attributeGroupId  = $attribute->getAttributeGroupId();
            $attributeGroup    = $this->attributeGroupRepository->get($attributeGroupId);
            $fieldsetCode      = str_replace('-', '_', $attributeGroup->getAttributeGroupCode());
            $this->fieldsets[$fieldsetCode] = ['name' => $attributeGroup->getAttributeGroupName(), 'sortOrder' => $attributeGroup->getSortOrder()];
            $this->fieldsMap[$fieldsetCode][] = $attribute->getAttributeCode();
        }

        return $this;
    }
}
