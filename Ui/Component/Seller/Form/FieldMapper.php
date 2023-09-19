<?php

declare(strict_types=1);

namespace Smile\Seller\Ui\Component\Seller\Form;

use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\ResourceModel\Seller\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Data provider field mapper.
 */
class FieldMapper
{
    private Collection $attributesCollection;
    private array $fieldsMap = [];
    private array $fieldsets = [];

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        private AttributeGroupRepositoryInterface $attributeGroupRepository,
        string $attributeSetId
    ) {
        $this->initFieldsMap($attributeCollectionFactory, $attributeSetId);
    }

    /**
     * Attribute collection for the current mapper.
     */
    public function getAttributesCollection(): Collection
    {
        return $this->attributesCollection;
    }

    /**
     * Mapping of the attribute by fieldsets.
     */
    public function getFieldsMap(): array
    {
        return $this->fieldsMap;
    }

    /**
     * Fieldset properties.
     */
    public function getFieldsets(): array
    {
        return $this->fieldsets;
    }

    /**
     * Init attribute collection, fieldsets and mapping.
     */
    private function initFieldsMap(
        AttributeCollectionFactory $attributeCollectionFactory,
        string $attributeSetId
    ): FieldMapper {

        $this->fieldsMap = [];
        $this->attributesCollection = $attributeCollectionFactory->create();
        $this->attributesCollection->setAttributeSetFilterBySetName($attributeSetId, SellerInterface::ENTITY);
        $this->attributesCollection->addSetInfo();

        foreach ($this->attributesCollection as $attribute) {
            $attributeGroupId = $attribute->getAttributeGroupId();
            /** @var Group $attributeGroup */
            $attributeGroup = $this->attributeGroupRepository->get($attributeGroupId);
            $fieldsetCode = str_replace('-', '_', $attributeGroup->getAttributeGroupCode());
            $this->fieldsets[$fieldsetCode] = [
                'name' => $attributeGroup->getAttributeGroupName(),
                'sortOrder' => $attributeGroup->getSortOrder(),
            ];
            $this->fieldsMap[$fieldsetCode][] = $attribute->getAttributeCode();
        }

        return $this;
    }
}
