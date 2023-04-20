<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model;

use Magento\Framework\EntityManager\EntityManager;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Seller\Model\ResourceModel\Seller as ResourceModel;

/**
 * Seller Repository
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SellerRepository
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var array
     */
    private array $sellerRepositoryById = [];

    /**
     * @var array|object
     */
    private array|object $sellerFactory;

    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * @var ?string Attribute set name of the entity
     */
    private ?string $sellerAttributeSetName = null;

    /**
     * SellerRepository constructor.
     *
     * @param EntityManager          $entityManager    The entity manager.
     * @param ResourceModel          $resourceModel    Resource model.
     * @param SellerInterfaceFactory $sellerFactory    The seller factory.
     * @param string|null            $attributeSetName The seller attribute Set Name, if any.
     *
     */
    public function __construct(
        EntityManager $entityManager,
        ResourceModel $resourceModel,
        $sellerFactory,
        ?string $attributeSetName = null
    ) {
        $this->entityManager          = $entityManager;
        $this->resourceModel          = $resourceModel;
        $this->sellerFactory          = $sellerFactory;
        $this->sellerAttributeSetName = $attributeSetName;
    }

    /**
     * Create seller service
     *
     * @param SellerInterface $seller The seller
     *
     * @return SellerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(SellerInterface $seller)
    {
        $this->applyAttributeSet($seller);

        $this->resourceModel->beforeSave($seller);
        $seller = $this->entityManager->save($seller);
        $this->resourceModel->afterSave($seller);

        unset($this->sellerRepositoryById[$seller->getId()]);
    }

    /**
     * Get info about seller by seller id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param int|string  $sellerId The seller Id
     * @param ?int        $storeId  The store Id
     *
     * @return SellerInterface
     */
    public function get(int|string $sellerId, ?int $storeId = null)
    {
        $cacheKey = (null !== $storeId) ? $storeId : 'all';

        if (!isset($this->sellerRepositoryById[$sellerId][$cacheKey])) {
            $sellerModel = $this->sellerFactory->create();

            if (null !== $storeId) {
                $sellerModel->setStoreId((int) $storeId);
            }

            $seller = $this->entityManager->load($sellerModel, $sellerId);
            $this->resourceModel->afterLoad($sellerModel);

            if (!$seller->getId()) {
                $exception = new NoSuchEntityException();
                throw $exception->singleField($seller->getIdFieldName(), $sellerId);
            }

            $this->sellerRepositoryById[$sellerId][$cacheKey] = $seller;
        }

        return $this->sellerRepositoryById[$sellerId][$cacheKey];
    }

    /**
     * Retrieve seller by seller code
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param int  $sellerCode The seller Code
     * @param ?int $storeId    The store Id
     *
     * @return SellerInterface
     */
    public function getByCode(int $sellerCode, ?int $storeId = null)
    {
        $sellerId = $this->resourceModel->getIdByCode($sellerCode);

        if (!$sellerId) {
            throw new NoSuchEntityException(__('Requested seller doesn\'t exist'));
        }

        return $this->get($sellerId, $storeId);
    }

    /**
     * Delete seller
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param SellerInterface $seller seller which will deleted
     *
     * @return bool Will returned True if deleted
     */
    public function delete(SellerInterface $seller): bool
    {
        $sellerId = $seller->getId();

        $this->resourceModel->beforeDelete($seller);
        $deleteResult = $this->entityManager->delete($seller);
        $this->resourceModel->afterDelete($seller);

        if ($deleteResult && isset($this->sellerRepositoryById[$sellerId])) {
            unset($this->sellerRepositoryById[$sellerId]);
        }

        return $deleteResult;
    }

    /**
     * Delete seller by identifier
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param int $sellerId The seller id
     *
     * @return bool Will returned True if deleted
     */
    public function deleteByIdentifier(int $sellerId): bool
    {
        $seller = $this->get($sellerId);

        return $this->delete($seller);
    }

    /**
     * Retrieve Attribute Set Id to use for this entity, if any
     *
     * @return null|int
     */
    public function getEntityAttributeSetId(): null|int
    {
        $attributeSetId = null;

        if (null !== $this->sellerAttributeSetName) {
            $sellerModel    = $this->sellerFactory->create();
            $attributeSetId = $sellerModel->getResource()->getAttributeSetIdByName($this->sellerAttributeSetName);
        }

        return $attributeSetId;
    }

    /**
     * Apply correct attribute set to the current seller item
     *
     * @param SellerInterface $seller The seller
     *
     * @return SellerInterface
     */
    private function applyAttributeSet(SellerInterface $seller)
    {
        $attributeSetId = $this->getEntityAttributeSetId();
        if (null !== $attributeSetId) {
            $seller->setAttributeSetId($attributeSetId);
        }

        return $seller;
    }
}
