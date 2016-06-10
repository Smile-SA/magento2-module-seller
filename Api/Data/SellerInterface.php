<?php

namespace Smile\Seller\Api\Data;

/**
 * @api
 */
interface SellerInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ENTITY = 'smile_seller';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Check whether category is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * @return string
     */
    public function getSellerCode();

    /**
     *
     * @param string $sellerCode
     * @return $this
     */
    public function setSellerCode($sellerCode);

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);


    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Smile\Seller\Api\Data\SellerExtensionInterface|null
     */
    public function getExtensionAttributes();


    /**
     * Set an extension attributes object.
     *
     * @param \Smile\Seller\Api\Data\SellerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Smile\Seller\Api\Data\SellerExtensionInterface $extensionAttributes);
}
