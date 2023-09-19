<?php

declare(strict_types=1);

namespace Smile\Seller\Model;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Smile\Retailer\Api\Data\RetailerInterfaceFactory;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Api\Data\SellerInterfaceFactory;
use Smile\Seller\Model\ResourceModel\Seller as ResourceModel;
use Smile\Seller\Model\Seller as SellerModel;

/**
 * Seller repository implementation.
 */
class SellerRepository
{
    private array $sellerRepositoryById = [];

    public function __construct(
        private EntityManager $entityManager,
        private ResourceModel $resourceModel,
        private SellerInterfaceFactory|RetailerInterfaceFactory $sellerFactory,
        private ?string $sellerAttributeSetName = null
    ) {
    }

    /**
     * Create seller service.
     *
     * @throws CouldNotSaveException
     */
    public function save(SellerInterface $seller): SellerInterface
    {
        /** @var SellerModel $seller */
        $this->applyAttributeSet($seller);

        $this->resourceModel->beforeSave($seller);
        $seller = $this->entityManager->save($seller);
        $this->resourceModel->afterSave($seller);

        unset($this->sellerRepositoryById[$seller->getId()]);

        return $seller;
    }

    /**
     * Get info about seller by seller id.
     *
     * @throws NoSuchEntityException
     */
    public function get(int $sellerId, ?int $storeId = null): SellerInterface
    {
        $cacheKey = $storeId ?? 'all';

        if (!isset($this->sellerRepositoryById[$sellerId][$cacheKey])) {
            /** @var SellerModel $sellerModel */
            $sellerModel = $this->sellerFactory->create();

            if (null !== $storeId) {
                $sellerModel->setData('store_id', $storeId);
            }

            $seller = $this->entityManager->load($sellerModel, (string) $sellerId);
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
     * Retrieve seller by seller code.
     *
     * @throws NoSuchEntityException
     */
    public function getByCode(string $sellerCode, ?int $storeId = null): SellerInterface
    {
        $sellerId = $this->resourceModel->getIdByCode($sellerCode);
        if (!$sellerId) {
            throw new NoSuchEntityException(__('Requested seller doesn\'t exist'));
        }

        return $this->get($sellerId, $storeId);
    }

    /**
     * Delete seller.
     *
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws StateException
     */
    public function delete(SellerInterface $seller): bool
    {
        /** @var SellerModel $seller */
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
     * Delete seller by identifier.
     *
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws StateException
     */
    public function deleteByIdentifier(int $sellerId): bool
    {
        $seller = $this->get($sellerId);

        return $this->delete($seller);
    }

    /**
     * Retrieve Attribute Set Id to use for this entity, if any.
     */
    public function getEntityAttributeSetId(): ?int
    {
        $attributeSetId = null;

        if (null !== $this->sellerAttributeSetName) {
            /** @var SellerModel $sellerModel */
            $sellerModel    = $this->sellerFactory->create();
            /** @var ResourceModel $resourceModel */
            $resourceModel  = $sellerModel->getResource();
            $attributeSetId = $resourceModel->getAttributeSetIdByName($this->sellerAttributeSetName);
        }

        return $attributeSetId;
    }

    /**
     * Apply correct attribute set to the current seller item.
     */
    private function applyAttributeSet(SellerInterface $seller): SellerInterface
    {
        // add a fallback in case retailer attribute_set_id is not correctly returned from Retailer entity
        if (null === $this->sellerAttributeSetName && $seller->getAttributeSetName()) {
            $this->sellerAttributeSetName = $seller->getAttributeSetName();
        }

        $attributeSetId = $this->getEntityAttributeSetId();
        if (null !== $attributeSetId) {
            $seller->setData('attribute_set_id', $attributeSetId);
        }

        return $seller;
    }
}
