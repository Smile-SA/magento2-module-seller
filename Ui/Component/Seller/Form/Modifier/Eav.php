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
namespace Smile\Seller\Ui\Component\Seller\Form\Modifier;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Model\Locator\LocatorInterface;
use Smile\Seller\Model\Seller\Attribute\ScopeOverriddenValue;
use Magento\Eav\Api\Data\AttributeInterface;
use Smile\Seller\Ui\Component\Seller\Form\FieldMapper;

/**
 * Scope modifier for Seller Data provider : handles displaying attributes scope, "use default" checkbox etc...
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Eav implements ModifierInterface
{
    /**
     * @var \Smile\Seller\Model\Locator\LocatorInterface
     */
    private $locator;

    /**
     * @var \Smile\Seller\Model\Seller\Attribute\ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $canDisplayUseDefault = [];

    /**
     * @var \Smile\Seller\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Smile\Seller\Ui\Component\Seller\Form\FieldMapper
     */
    private $fieldMapper;

    /**
     * @var \Magento\Ui\DataProvider\EavValidationRules
     */
    private $eavValidationRules;

    /**
     * EAV attribute properties to fetch from meta storage
     *
     * @var array
     */
    private $metaProperties = [
        'formElement' => 'frontend_input',
        'required'    => 'is_required',
        'label'       => 'frontend_label',
        'sortOrder'   => 'sort_order',
        'notice'      => 'note',
        'default'     => 'default_value',
        'size'        => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    private $formElement = [
        'text'    => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * Eav constructor.
     *
     * @param \Smile\Seller\Model\Locator\LocatorInterface              $locator                      Locator
     * @param \Smile\Seller\Model\Seller\Attribute\ScopeOverriddenValue $scopeOverriddenValue         Scope Overriden Value checker
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManagerInterface        Store Manager
     * @param \Smile\Seller\Api\AttributeRepositoryInterface            $attributeRepositoryInterface Attributes Repository
     * @param \Magento\Ui\DataProvider\EavValidationRules               $eavValidationRules           EAV Validation rules
     * @param \Smile\Seller\Ui\Component\Seller\Form\FieldMapper        $fieldMapper                  Field Mapper
     */
    public function __construct(
        LocatorInterface $locator,
        ScopeOverriddenValue $scopeOverriddenValue,
        StoreManagerInterface $storeManagerInterface,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        EavValidationRules $eavValidationRules,
        FieldMapper $fieldMapper
    ) {
        $this->locator = $locator;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->storeManager = $storeManagerInterface;
        $this->attributeRepository = $attributeRepositoryInterface;
        $this->eavValidationRules = $eavValidationRules;
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if ($this->locator->getSeller()) {
            if (isset($data[$this->locator->getSeller()->getId()])) {
                $data[$this->locator->getSeller()->getId()]['store_id'] = $this->locator->getStore()->getId();
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta($this->getFieldsMap(), $this->getAttributesMeta()));

        return $meta;
    }

    /**
     * Get attributes meta.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    private function getAttributesMeta()
    {
        $meta = [];

        foreach ($this->getAttributes()->getItems() as $attribute) {
            $code = $attribute->getAttributeCode();

            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);

                $meta[$code][$metaName] = $value;

                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value]) ? $this->formElement[$value] : $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['componentType'] = \Magento\Ui\Component\Form\Field::NAME;
            $meta[$code] += $this->addUseDefaultValueCheckbox($attribute);
            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
        }

        return $meta;
    }

    /**
     * List of EAV attributes of the current model.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private function getAttributes()
    {
        return $this->fieldMapper->getAttributesCollection();
    }

    /**
     * Field map by fielset code.
     *
     * @return array
     */
    private function getFieldsMap()
    {
        return $this->fieldMapper->getFieldsMap();
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap  The field Map
     * @param array $fieldsMeta The fields meta
     *
     * @return array
     */
    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        $fieldsets = $this->fieldMapper->getFieldsets();

        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (!isset($result[$fieldSet])) {
                    $result[$fieldSet]['arguments']['data']['config'] = [
                        'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                        'label'         => $fieldsets[$fieldSet]['name'],
                        'sortOrder'     => $fieldsets[$fieldSet]['sortOrder'],
                        'collapsible'   => true,
                    ];
                }

                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @param mixed $attribute The attribute.
     *
     * @return string
     */
    private function getScopeLabel($attribute)
    {
        $html = '';
        if (!$attribute || $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return $html;
        }

        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * Add the "Use Default Value" checkbox if needed
     *
     * @param SellerAttributeInterface $attribute Seller Attribute
     *
     * @return array
     */
    private function addUseDefaultValueCheckbox(SellerAttributeInterface $attribute)
    {
        $canDisplayService = $this->canDisplayUseDefault($attribute);
        $meta = [];

        if ($canDisplayService) {
            $meta['service'] = ['template' => 'ui/form/element/helper/service'];
            $meta['disabled'] = !$this->scopeOverriddenValue->containsValue(
                $this->locator->getSeller(),
                $attribute->getAttributeCode(),
                $this->locator->getStore()->getId()
            );
        }

        return $meta;
    }

    /**
     * Whether attribute can have default value
     *
     * @param SellerAttributeInterface $attribute The attribute
     *
     * @return bool
     */
    private function canDisplayUseDefault(SellerAttributeInterface $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();

        $seller = $this->locator->getSeller();

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            (!$attribute->isScopeGlobal())
            && $seller
            && $seller->getId()
            && $seller->getStoreId()
        );
    }
}
