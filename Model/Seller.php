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
class Seller extends \Magento\Framework\Model\AbstractExtensibleModel implements SellerInterface
{
    /**
     * Default cache tag
     */
    const CACHE_TAG = self::ENTITY;

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
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = self::ENTITY;

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
