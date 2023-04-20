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
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\AttributeRepositoryInterface;
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
class Seller extends AbstractExtensibleModel implements SellerInterface, IdentityInterface
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
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $metadataService;

    /**
     * Attributes are that part of interface
     *
     * @var array
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

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Seller constructor.
     *
     * @param Context                      $context                Application Context
     * @param Registry                     $registry               Application Registry
     * @param ExtensionAttributesFactory   $extensionFactory       Extension Attributes Factory
     * @param AttributeValueFactory        $customAttributeFactory Custom Attributes Factory
     * @param StoreManagerInterface        $storeManager           Store Manager
     * @param AttributeRepositoryInterface $metadataService        Metadata Service
     * @param ?AbstractResource            $resource               Resource Model
     * @param ?AbstractDb                  $resourceCollection     Resource Collection
     * @param array                        $data                   Model Data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        AttributeRepositoryInterface $metadataService,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->metadataService = $metadataService;

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
    public function getName(): string
    {
        return $this->_getData(self::KEY_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getSellerCode(): string
    {
        return $this->_getData(self::KEY_SELLER_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive(): bool
    {
        return (bool) $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): self
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function setSellerCode(string $sellerCode): self
    {
        return $this->setData(self::KEY_SELLER_CODE, $sellerCode);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive(bool $isActive): self
    {
        return $this->setData(self::KEY_IS_ACTIVE, (bool) $isActive);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
    * {@inheritDoc}
    */
    public function setUpdatedAt(string $updatedAt): self
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel(): string
    {
        return 'Magento\Eav\Model\Entity\Attribute\Source\Table';
    }

    /**
     * Retrieve AttributeSetName
     *
     * @return string
     */
    public function getAttributeSetName(): string
    {
        return 'Default';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities(): array
    {
        $identities = [self::CACHE_TAG . '_' . $this->getId()];
        if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_FRONTEND) {
            $identities[] = self::CACHE_TAG;
        }

        return array_unique($identities);
    }

    /**
     * Retrieve custom attributes codes list
     *
     * @return array
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
     * Internal Constructor
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init('Smile\Seller\Model\ResourceModel\Seller');
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaPath(): ?string
    {
        return $this->getData(self::MEDIA_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setMediaPath(string $path): self
    {
        return $this->setData(self::MEDIA_PATH, $path);
    }
}
