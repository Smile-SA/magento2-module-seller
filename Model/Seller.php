<?php

namespace Smile\Seller\Model;

class Seller extends \Magento\Framework\Model\AbstractExtensibleModel implements
    //\Magento\Framework\DataObject\IdentityInterface,
    \Smile\Seller\Api\Data\SellerInterface
{
    const CACHE_TAG = self::ENTITY;

    const KEY_NAME        = 'name';
    const KEY_IS_ACTIVE   = 'is_active';
    const KEY_UPDATED_AT  = 'updated_at';
    const KEY_CREATED_AT  = 'created_at';
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
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->setData($name);

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

    protected function getCustomAttributesCodes()
    {
        return ['name'];
    }

}
