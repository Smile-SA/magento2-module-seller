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

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * Seller Data provider for adminhtml edit form
 *
 * @todo :
 * - filter / disable fields when changing scope
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
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
     * @var mixed
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EavValidationRules
     */
    private $eavValidationRules;

    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    /**
     *
     * @param string                $name               DataProvider name.
     * @param string                $primaryFieldName   Database primary key field.
     * @param string                $requestFieldName   Request identifier field.
     * @param mixed                 $collectionFactory  Item collection factory.
     * @param StoreManagerInterface $storeManager       Store manager.
     * @param EavValidationRules    $eavValidationRules Validation rules
     * @param FieldMapper           $fieldMapper        Field mapper.
     * @param array                 $meta               Default meta.
     * @param array                 $data               Default data.
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        StoreManagerInterface $storeManager,
        EavValidationRules $eavValidationRules,
        FieldMapper $fieldMapper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory    = $collectionFactory;
        $this->storeManager         = $storeManager;
        $this->eavValidationRules   = $eavValidationRules;
        $this->fieldMapper          = $fieldMapper;
        $this->meta                 = $this->prepareMeta($meta);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = parent::getData();

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addAttributeToSelect('*');
        }

        return $this->collection;
    }

    /**
     * Get default scope label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getDefaultScopeLabel()
    {
        return __('[GLOBAL]');
    }

    /**
     * Prepare meta data.
     *
     * @param array $meta The meta data.
     *
     * @return array
     */
    private function prepareMeta($meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta($this->getFieldsMap(), $this->getAttributesMeta()));

        return $meta;
    }

    /**
     * Get attributes meta.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    private function getAttributesMeta()
    {
        $meta   = [];

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

            $meta[$code]['scopeLabel']    = $this->getScopeLabel($attribute);
            $meta[$code]['componentType'] = \Magento\Ui\Component\Form\Field::NAME;
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
        $result    = [];
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
}
