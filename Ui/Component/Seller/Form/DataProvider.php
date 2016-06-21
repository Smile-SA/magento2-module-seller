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

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\EavValidationRules;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Api\SellerRepositoryInterface;
use Smile\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Smile\Seller\Model\Seller;

/**
 * Seller Data provider for adminhtml edit form
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * EAV attribute properties to fetch from meta storage
     *
     * @var array
     */
    protected $metaProperties = [
        'dataType'  => 'frontend_input',
        'visible'   => 'is_visible',
        'required'  => 'is_required',
        'label'     => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice'    => 'note',
        'default'   => 'default_value',
        'size'      => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text'    => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * List of fields that should not be added into the form
     *
     * @var array
     */
    protected $ignoreFields = [];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * DataProvider constructor
     *
     * @param string                    $name                    Component Name
     * @param string                    $primaryFieldName        Primary Field Name
     * @param string                    $requestFieldName        Request Field Name
     * @param EavValidationRules        $eavValidationRules      EAV Validation Rules
     * @param SellerCollectionFactory   $sellerCollectionFactory Seller Collection Factory
     * @param Registry                  $registry                The Registry
     * @param Config                    $eavConfig               EAV Configuration
     * @param RequestInterface          $request                 The Request
     * @param SellerRepositoryInterface $sellerRepository        The Seller Repository
     * @param array                     $meta                    Component Metadata
     * @param array                     $data                    Component Data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        SellerCollectionFactory $sellerCollectionFactory,
        Registry $registry,
        Config $eavConfig,
        RequestInterface $request,
        SellerRepositoryInterface $sellerRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->eavValidationRules = $eavValidationRules;

        $this->collection = $sellerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');

        $this->eavConfig = $eavConfig;
        $this->registry = $registry;
        $this->request = $request;
        $this->sellerRepository = $sellerRepository;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Get Component data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $seller = $this->getCurrentSeller();

        if ($seller) {
            $sellerData = $seller->getData();
            $sellerData = $this->filterFields($sellerData);
            if (!empty($sellerData)) {
                $this->loadedData[$seller->getId()] = $sellerData;
            }
        }

        return $this->loadedData;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta The meta data
     *
     * @return array
     */
    private function prepareMeta($meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta(
                $this->getFieldsMap(),
                $this->getAttributesMeta($this->eavConfig->getEntityType(SellerInterface::ENTITY))
            )
        );

        return $meta;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType The entity type
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();

        /* @var \Smile\Seller\Model\ResourceModel\Seller\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // Use getDataUsingMethod, since some getters are defined and apply additional processing of returning value.
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

            $meta[$code]['componentType'] = Field::NAME;
        }

        $result = [];
        foreach ($meta as $key => $item) {
            $result[$key] = $item;
            $result[$key]['sortOrder'] = 0;
        }

        return $result;
    }

    /**
     * Get current seller
     *
     * @return Seller
     * @throws NoSuchEntityException
     */
    private function getCurrentSeller()
    {
        $seller = $this->registry->registry('current_seller');
        if ($seller) {
            return $seller;
        }

        $requestId = $this->request->getParam($this->requestFieldName);

        if ($requestId) {
            $seller = $this->sellerRepository->get($requestId);
        }

        if (!$seller || !$seller->getId()) {
            $seller = $this->collection->getNewEmptyItem();
        }

        return $seller;
    }

    /**
     * Filter fields
     *
     * @param array $sellerData The seller data
     *
     * @return array
     */
    private function filterFields($sellerData)
    {
        return array_diff_key($sellerData, array_flip($this->ignoreFields));
    }

    /**
     * @return array
     */
    private function getFieldsMap()
    {
        return [
            'general' => [
                'seller_code',
                'name',
                'is_active',
            ],
        ];
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
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }

        return $result;
    }
}
