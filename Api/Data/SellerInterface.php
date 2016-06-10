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
namespace Smile\Seller\Api\Data;

/**
 * Seller Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
interface SellerInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * Entity type code
     */
    const ENTITY = 'smile_seller';

    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Seller Id
     *
     * @param int $id The id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get seller name
     *
     * @return string
     */
    public function getName();

    /**
     * Set seller name
     *
     * @param string $name The seller name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Check whether seller is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Retrieve Seller Code
     *
     * @return string
     */
    public function getSellerCode();

    /**
     * Set Seller Code
     *
     * @param string $sellerCode The seller code
     *
     * @return $this
     */
    public function setSellerCode($sellerCode);

    /**
     * Set whether category is active
     *
     * @param bool $isActive If seller is active
     *
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Retrieve Seller creation date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Seller creation date
     *
     * @param string $createdAt The creation date
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Seller update date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set seller update date
     *
     * @param string $updatedAt
     *
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
     *
     * @return $this
     */
    public function setExtensionAttributes(\Smile\Seller\Api\Data\SellerExtensionInterface $extensionAttributes);
}
