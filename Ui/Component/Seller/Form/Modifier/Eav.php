<?php

declare(strict_types=1);

namespace Smile\Seller\Ui\Component\Seller\Form\Modifier;

use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\Locator\LocatorInterface;
use Smile\Seller\Model\ResourceModel\Seller as ResourceModelSeller;
use Smile\Seller\Model\Seller\Attribute\ScopeOverriddenValue;
use Smile\Seller\Model\SellerMediaUpload;
use Smile\Seller\Ui\Component\Seller\Form\FieldMapper;

/**
 * Scope modifier for Seller Data provider: handles displaying attributes scope, "use default" checkbox, etc.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Eav implements ModifierInterface
{
    private StoreManagerInterface $storeManager;

    // @phpstan-ignore-next-line as to avoid possible backward compatibility issue
    private AttributeRepositoryInterface $attributeRepository;
    private array $canDisplayUseDefault = [];

    /**
     * EAV attribute properties to fetch from meta storage
     */
    private array $metaProperties = [
        'formElement' => 'frontend_input',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * Form element mapping.
     */
    private array $formElement = [
        'text' => 'input',
        'boolean' => 'checkbox',
    ];

    private array $validationRules = [
        'email' => ['validate-email' => true],
        'date' => ['validate-date'  => true],
    ];

    public function __construct(
        private LocatorInterface $locator,
        private ScopeOverriddenValue $scopeOverriddenValue,
        StoreManagerInterface $storeManagerInterface,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        private EavValidationRules $eavValidationRules,
        private FieldMapper $fieldMapper,
        private SellerMediaUpload $media
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->attributeRepository = $attributeRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        if ($this->locator->getSeller()) {
            if (isset($data[$this->locator->getSeller()->getId()])) {
                $data[$this->locator->getSeller()->getId()]['store_id'] = $this->locator->getStore()->getId();
                $data[$this->locator->getSeller()->getId()] = $this->convertValues(
                    $this->locator->getSeller(),
                    $data[$this->locator->getSeller()->getId()]
                );
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        return array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta($this->getFieldsMap(), $this->getAttributesMeta())
        );
    }

    /**
     * Get attributes meta.
     *
     * @throws LocalizedException
     */
    private function getAttributesMeta(): array
    {
        $meta = [];

        /** @var SellerAttributeInterface|AbstractAttribute $attribute */
        foreach ($this->getAttributes()->getItems() as $attribute) {
            $code = $attribute->getAttributeCode();

            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);

                $meta[$code][$metaName] = $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = $this->formElement[$value] ?? $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if ($attribute->getFrontendInput() && isset($this->validationRules[$attribute->getFrontendInput()])) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $rules = array_merge($rules, $this->validationRules[$attribute->getFrontendInput()]);
            }

            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['label'] = __($meta[$code]['label']);
            $meta[$code] += $this->customizeCheckbox($attribute);
            $meta[$code]['componentType'] = Field::NAME;
            $meta[$code] += $this->addUseDefaultValueCheckbox($attribute);
            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
        }

        return $meta;
    }

    /**
     * List of EAV attributes of the current model.
     */
    private function getAttributes(): Collection
    {
        return $this->fieldMapper->getAttributesCollection();
    }

    /**
     * Field map by fieldset code.
     */
    private function getFieldsMap(): array
    {
        return $this->fieldMapper->getFieldsMap();
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata.
     */
    private function prepareFieldsMeta(array $fieldsMap, array $fieldsMeta): array
    {
        $result = [];
        $fieldsets = $this->fieldMapper->getFieldsets();

        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (!isset($result[$fieldSet])) {
                    $result[$fieldSet]['arguments']['data']['config'] = [
                        'componentType' => Fieldset::NAME,
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
     * Retrieve label of attribute scope (global, website, store).
     */
    private function getScopeLabel(mixed $attribute): string
    {
        $html = '';
        if (
            !$attribute || $this->storeManager->isSingleStoreMode()
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
     * Add the "Use Default Value" checkbox if needed.
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
     * Whether attribute can have default value.
     */
    private function canDisplayUseDefault(SellerAttributeInterface $attribute): bool
    {
        $attributeCode = $attribute->getAttributeCode();

        /** @var ResourceModelSeller|SellerInterface|null $seller */
        $seller = $this->locator->getSeller();

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            !$attribute->isScopeGlobal()
            && $seller
            && $seller->getId()
            && $seller->getStoreId()
        );
    }

    /**
     * Customize checkboxes.
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
     * Converts category image data to acceptable for rendering format.
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
                $stat = $this->media->getStat($fileName);
                $mime = $this->media->getMimeType($fileName);

                $data[$attributeCode][0]['name'] = $fileName;
                $data[$attributeCode][0]['url']  = $this->getBaseImageUrl() . $fileName;
                $data[$attributeCode][0]['size'] = isset($stat['size']) ?: 0;
                $data[$attributeCode][0]['type'] = $mime;
            }
        }

        return $data;
    }

    /**
     * Get base image url.
     */
    public function getBaseImageUrl(): string
    {
        /** @var Store $currentStore */
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'seller/';
    }
}
