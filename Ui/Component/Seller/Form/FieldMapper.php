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
    private $attributeGroupRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $attributesCollection;

    /**
     * @var string[]
     */
    private $fieldsMap = [];

    /**
     *
     * @var array
     */
    private $fieldsets = [];

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
        $attributeSetId
    ) {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->initFieldsMap($attributeCollectionFactory, $attributeSetId);
    }

    /**
     * Attribute collection for the current mapper.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributesCollection()
    {
        return $this->attributesCollection;
    }

    /**
     * Mapping of the attribute by fieldsets.
     *
     * @return string[]
     */
    public function getFieldsMap()
    {
        return $this->fieldsMap;
    }

    /**
     * Fielset properties.
     *
     * @return array
     */
    public function getFieldsets()
    {
        return $this->fieldsets;
    }

    /**
     * Init attribute collection, fielsets and mapping.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory Seller attribute collection factory.
     * @param string                     $attributeSetId             Mapper attribute set identifier.
     *
     * @return \Smile\Seller\Ui\Component\Seller\Form\FieldMapper
     */
    private function initFieldsMap(AttributeCollectionFactory $attributeCollectionFactory, $attributeSetId)
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
