<?php

declare(strict_types=1);

namespace Smile\Seller\Model;

use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Smile\Seller\Api\AttributeRepositoryInterface;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\ResourceModel\Seller as SellerResource;

/**
 * Seller Model.
 */
class Seller extends AbstractExtensibleModel implements SellerInterface, IdentityInterface
{
    public const CACHE_TAG = SellerInterface::ENTITY;
    public const KEY_NAME = 'name';
    public const KEY_IS_ACTIVE = 'is_active';
    public const KEY_UPDATED_AT = 'updated_at';
    public const KEY_CREATED_AT = 'created_at';
    public const KEY_SELLER_CODE = 'seller_code';

    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $_eventPrefix = SellerInterface::ENTITY;
    protected $_eventObject = 'seller';
    protected $_cacheTag = self::CACHE_TAG;
    // phpcs:enable

    /**
     * @var string[]
     */
    protected array $interfaceAttributes = [
        'id',
        self::KEY_NAME,
        self::KEY_IS_ACTIVE,
        self::KEY_UPDATED_AT,
        self::KEY_CREATED_AT,
        self::KEY_SELLER_CODE,
        self::MEDIA_PATH,
    ];

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        private AttributeRepositoryInterface $metadataService,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
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
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SellerResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->_getData(self::KEY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getSellerCode(): string
    {
        return $this->_getData(self::KEY_SELLER_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return (bool) $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): self
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function setSellerCode(string $sellerCode): self
    {
        return $this->setData(self::KEY_SELLER_CODE, $sellerCode);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive): self
    {
        return $this->setData(self::KEY_IS_ACTIVE, (bool) $isActive);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * Get default attribute source model
     */
    public function getDefaultAttributeSourceModel(): string
    {
        return Table::class;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeSetName(): string
    {
        return 'Default';
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        $identities = [self::CACHE_TAG . '_' . $this->getId()];
        if ($this->_appState->getAreaCode() == Area::AREA_FRONTEND) {
            $identities[] = self::CACHE_TAG;
        }

        return array_unique($identities);
    }

    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes(): array
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
            $this->customAttributesCodes = array_diff($this->customAttributesCodes, $this->interfaceAttributes);
        }

        return $this->customAttributesCodes;
    }

    /**
     * @inheritdoc
     */
    public function getMediaPath(): ?string
    {
        return $this->getData(self::MEDIA_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setMediaPath(string $path): self
    {
        return $this->setData(self::MEDIA_PATH, $path);
    }
}
