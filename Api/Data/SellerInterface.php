<?php

declare(strict_types=1);

namespace Smile\Seller\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 * @method mixed getData(...$key)
 * @method mixed setData(...$data)
 */
interface SellerInterface extends CustomAttributesDataInterface
{
    public const ENTITY_TYPE_CODE = 'smile_seller';
    public const ENTITY = 'smile_seller';
    public const MEDIA_PATH  = 'image';

    /**
     * Get Seller Id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set Seller Id
     *
     * @param mixed $entityId The id
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get seller name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set seller name
     *
     * @param string $name The seller name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Check whether seller is active
     *
     * @return bool|null
     */
    public function getIsActive(): ?bool;

    /**
     * Retrieve Seller Code
     *
     * @return string
     */
    public function getSellerCode(): string;

    /**
     * Set Seller Code
     *
     * @param string $sellerCode The seller code
     * @return $this
     */
    public function setSellerCode(string $sellerCode): self;

    /**
     * Set whether category is active
     *
     * @param bool $isActive If seller is active
     * @return $this
     */
    public function setIsActive(bool $isActive): self;

    /**
     * Retrieve Seller creation date
     *
     * @return ?string
     */
    public function getCreatedAt(): ?string;

    /**
     * Set Seller creation date
     *
     * @param string $createdAt The creation date
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * Get Seller update date
     *
     * @return ?string
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set seller update date
     *
     * @param string $updatedAt Update date
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;

    /**
     * Retrieve AttributeSetName
     *
     * @return string
     */
    public function getAttributeSetName(): string;

    /**
     * Get Image name
     *
     * @return ?string
     */
    public function getMediaPath(): ?string;

    /**
     * Set Image name
     *
     * @return $this
     */
    public function setMediaPath(string $path): self;
}
