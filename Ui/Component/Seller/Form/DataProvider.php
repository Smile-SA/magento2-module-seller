<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @seller    Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Ui\Component\Seller\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Api\SellerRepositoryInterface;
use Smile\Seller\Model\Seller;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Smile\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Seller\Model\SellerFactory;

/**
 * Seller Data provider for adminhtml edit form
 *
 * @seller   Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var string
     */
    protected $requestScopeFieldName = 'store';

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
     * @param StoreManagerInterface     $storeManager            The Store Manager
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
        StoreManagerInterface $storeManager,
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
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->sellerRepositrory = $sellerRepository;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepare meta data
     *
     * @param array $meta The meta data
     *
     * @return array
     */
    public function prepareMeta($meta)
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
            $this->loadedData[$seller->getId()] = $sellerData;
        }

        return $this->loadedData;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();

        /* @var \Smile\Seller\Model\ResourceModel\Seller\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = $value;

                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }

                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
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
    public function getCurrentSeller()
    {
        $seller = $this->registry->registry('current_seller');
        if ($seller) {

            return $seller;
        }

        $requestId = $this->request->getParam($this->requestFieldName);
        $requestScope = $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID);

        if ($requestId) {
            $seller = $this->sellerRepository->get($requestId, $requestScope);
        }

        return $seller;
    }

    /**
     * Retrieve label of attribute scope
     * GLOBAL | WEBSITE | STORE
     *
     * @param SellerAttributeInterface $attribute
     *
     * @return string
     */
    public function getScopeLabel(SellerAttributeInterface $attribute)
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
     * Filter fields
     *
     * @param array $sellerData
     *
     * @return array
     */
    protected function filterFields($sellerData)
    {
        return array_diff_key($sellerData, array_flip($this->ignoreFields));
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'seller_code',
                    'name',
                    'is_active',
                ],
        ];
    }
}
