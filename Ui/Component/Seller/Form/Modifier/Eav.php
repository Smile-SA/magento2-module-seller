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

use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\Locator\LocatorInterface;
use Smile\Seller\Model\Seller\Attribute\ScopeOverriddenValue;
use Magento\Eav\Api\Data\AttributeInterface;
use Smile\Seller\Ui\Component\Seller\Form\FieldMapper;
use Smile\Seller\Model\SellerMediaUpload;

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
     * @var LocatorInterface
     */
    private LocatorInterface $locator;

    /**
     * @var ScopeOverriddenValue
     */
    private ScopeOverriddenValue $scopeOverriddenValue;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var array
     */
    private array $canDisplayUseDefault = [];

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var FieldMapper
     */
    private FieldMapper $fieldMapper;

    /**
     * @var EavValidationRules
     */
    private EavValidationRules $eavValidationRules;

    /**
     * @var SellerMediaUpload
     */
    private SellerMediaUpload $media;

    /**
     * EAV attribute properties to fetch from meta storage
     *
     * @var array
     */
    private array $metaProperties = [
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
    private array $formElement = [
        'text'    => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var array
     */
    private array $validationRules = [
        'email' => ['validate-email' => true],
        'date'  => ['validate-date'  => true],
    ];

    /**
     * Eav constructor.
     *
     * @param LocatorInterface             $locator                      Locator
     * @param ScopeOverriddenValue         $scopeOverriddenValue         Scope Overriden Value checker
     * @param StoreManagerInterface        $storeManagerInterface        Store Manager
     * @param AttributeRepositoryInterface $attributeRepositoryInterface Attributes Repository
     * @param EavValidationRules           $eavValidationRules           EAV Validation rules
     * @param FieldMapper                  $fieldMapper                  Field Mapper
     * @param SellerMediaUpload            $media                        Seller Media Manager
     */
    public function __construct(
        LocatorInterface $locator,
        ScopeOverriddenValue $scopeOverriddenValue,
        StoreManagerInterface $storeManagerInterface,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        EavValidationRules $eavValidationRules,
        FieldMapper $fieldMapper,
        SellerMediaUpload $media
    ) {
        $this->locator = $locator;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->storeManager = $storeManagerInterface;
        $this->attributeRepository = $attributeRepositoryInterface;
        $this->eavValidationRules = $eavValidationRules;
        $this->fieldMapper = $fieldMapper;
        $this->media = $media;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data): array
    {
        if ($this->locator->getSeller()) {
            if (isset($data[$this->locator->getSeller()->getId()])) {
                $data[$this->locator->getSeller()->getId()]['store_id'] = $this->locator->getStore()->getId();
                $data[$this->locator->getSeller()->getId()] = $this->convertValues($this->locator->getSeller(), $data[$this->locator->getSeller()->getId()]);
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta): array
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
    private function getAttributesMeta(): array
    {
        $meta = [];

        /** @var SellerAttributeInterface $attribute */
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
            if ($attribute->getFrontendInput() && isset($this->validationRules[$attribute->getFrontendInput()])) {
                $rules = array_merge($rules, $this->validationRules[$attribute->getFrontendInput()]);
            }

            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['label'] = __($meta[$code]['label']);
            $meta[$code] += $this->customizeCheckbox($attribute);
            $meta[$code]['componentType'] = \Magento\Ui\Component\Form\Field::NAME;
            $meta[$code] += $this->addUseDefaultValueCheckbox($attribute);
            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
        }

        return $meta;
    }

    /**
     * List of EAV attributes of the current model.
     *
     * @return Collection
     */
    private function getAttributes(): Collection
    {
        return $this->fieldMapper->getAttributesCollection();
    }

    /**
     * Field map by fielset code.
     *
     * @return array
     */
    private function getFieldsMap(): array
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
    private function prepareFieldsMeta(array $fieldsMap, array $fieldsMeta): array
    {
        $result = [];
        $fieldsets = $this->fieldMapper->getFieldsets();

        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (!isset($result[$fieldSet])) {
                    $result[$fieldSet]['arguments']['data']['config'] = [
                        'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                        'label'         => __($fieldsets[$fieldSet]['name']),
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
    private function getScopeLabel(mixed $attribute): string
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
    private function addUseDefaultValueCheckbox(SellerAttributeInterface $attribute): array
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
    private function canDisplayUseDefault(SellerAttributeInterface $attribute): bool
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

    /**
     * Customize checkboxes
     *
     * @param SellerAttributeInterface $attribute The attribute
     *
     * @return array
     */
    private function customizeCheckbox(SellerAttributeInterface $attribute): array
    {
        $meta = [];

        if ($attribute->getFrontendInput() === 'boolean') {
            $meta['prefer'] = 'toggle';
            $meta['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }

        return $meta;
    }

    /**
     * Converts category image data to acceptable for rendering format
     *
     * @param SellerInterface $seller The seller
     * @param array                                  $data   Seller Data
     *
     * @return array
     */
    private function convertValues(SellerInterface $seller, array $data): array
    {
        foreach ($this->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!isset($data[$attributeCode])) {
                continue;
            }

            if ($attribute->getBackend() instanceof ImageBackendModel || $attribute->getFrontendInput() === 'image') {
                unset($data[$attributeCode]);
                $fileName = $seller->getData($attributeCode);
                $stat     = $this->media->getStat($fileName);
                $mime     = $this->media->getMimeType($fileName);

                $data[$attributeCode][0]['name'] = $fileName;
                $data[$attributeCode][0]['url']  = $this->getBaseImageUrl() . $fileName;
                $data[$attributeCode][0]['size'] = isset($stat) ? $stat['size'] : 0;
                $data[$attributeCode][0]['type'] = $mime;
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getBaseImageUrl(): string
    {
        $currentStore = $this->storeManager->getStore();
        $mediaUrl     = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'seller/';
    }
}
