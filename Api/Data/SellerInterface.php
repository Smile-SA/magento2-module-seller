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
     * The seller media_path field
     */
    const MEDIA_PATH  = 'image';

    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Seller Id
     *
     * @param int $entityId The id
     *
     * @return $this
     */
    public function setId($entityId);

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
     * @param string $updatedAt Update date
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Retrieve AttributeSetName
     *
     * @return string
     */
    public function getAttributeSetName();

    /**
     * Get Image name
     *
     * @return string
     */
    public function getMediaPath();

    /**
     * Set Image name
     *
     * @return string
     */

    public function setMediaPath($path);
}
