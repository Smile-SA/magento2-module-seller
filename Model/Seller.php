<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Seller Model
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName) The properties are inherited
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Seller extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * Default cache tag
     */
    const CACHE_TAG = SellerInterface::ENTITY;

    /**
     * "Name" attribute code
     */
    const KEY_NAME        = 'name';

    /**
     * "Is active" attribute code
     */
    const KEY_IS_ACTIVE   = 'is_active';

    /**
     * "Update At" attribute code
     */
    const KEY_UPDATED_AT  = 'updated_at';

    /**
     * "Created At" attribute code
     */
    const KEY_CREATED_AT  = 'created_at';

    /**
     * "Seller code" attribute code
     */
    const KEY_SELLER_CODE = 'seller_code';

    /**
     * The image path used to store seller image attributes.
     */
    const IMAGE_PATH = 'seller';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = SellerInterface::ENTITY;

    /**
     * Parameter name in event.
     *
     * @var string
     */
    protected $_eventObject = 'seller';

    /**
     * Model cache tag for clear cache in after save and after delete.
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Attributes are that part of interface
     *
     * @var array
     */
    protected $interfaceAttributes = [
        'id',
        self::KEY_NAME,
        self::KEY_IS_ACTIVE,
        self::KEY_UPDATED_AT,
        self::KEY_CREATED_AT,
        self::KEY_SELLER_CODE,
    ];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Seller constructor.
     *
     * @param \Magento\Framework\Model\Context                        $context                Application Context
     * @param \Magento\Framework\Registry                             $registry               Application Registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory       Extension Attributes Factory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory Custom Attributes Factory
     * @param \Magento\Store\Model\StoreManagerInterface              $storeManager           Store Manager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource               Resource Model
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection     Resource Collection
     * @param array                                                   $data                   Model Data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->_getData(self::KEY_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getSellerCode()
    {
        return $this->_getData(self::KEY_SELLER_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive()
    {
        return (bool) $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create('Smile\Seller\Api\Data\SellerInterface');
        }

        return $extensionAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->setData(self::KEY_NAME, $name);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSellerCode($sellerCode)
    {
        $this->setData(self::KEY_SELLER_CODE, $sellerCode);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, (bool) $isActive);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
    * {@inheritDoc}
    */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function setExtensionAttributes(\Smile\Seller\Api\Data\SellerExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Retrieve Image URL for an attribute having image backend.
     *
     * @param string $attributeCode The attributeCode
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageAttributeUrl($attributeCode)
    {
        $url = false;
        $image = $this->getData($attributeCode);
        if ($image) {
            if (!is_string($image)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }

            $url  = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $url .= self::IMAGE_PATH . '/' . $image;
        }

        return $url;
    }

    /**
     * Retrieve custom attributes codes list
     *
     * @return array
     */
    protected function getCustomAttributesCodes()
    {
        $attributesCodes = parent::getCustomAttributesCodes();
        $attributesCodes[] = 'name';

        return $attributesCodes;
    }

    /**
     * Internal Constructor
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init('Smile\Seller\Model\ResourceModel\Seller');
    }
}
