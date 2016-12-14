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
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    private $sellerRepositoryById = [];

    /**
     * @var array
     */
    private $sellerFactory;

    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var null Attribute set name of the entity
     */
    private $sellerAttributeSetName = null;

    /**
     * SellerRepository constructor.
     *
     * @param EntityManager          $entityManager    The entity manager.
     * @param ResourceModel          $resourceModel    Resource model.
     * @param SellerInterfaceFactory $sellerFactory    The seller factory.
     * @param string|null            $attributeSetName The seller attribute Set Name, if any.
     *
     */
    public function __construct(EntityManager $entityManager, ResourceModel $resourceModel, $sellerFactory, $attributeSetName = null)
    {
        $this->entityManager          = $entityManager;
        $this->resourceModel          = $resourceModel;
        $this->sellerFactory          = $sellerFactory;
        $this->sellerAttributeSetName = $attributeSetName;
    }

    /**
     * Create seller service
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller The seller
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Smile\Seller\Api\Data\SellerInterface $seller)
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
     * @param int $sellerId The seller Id
     * @param int $storeId  The store Id
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     */
    public function get($sellerId, $storeId = null)
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
     * Delete seller
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller seller which will deleted
     *
     * @return bool Will returned True if deleted
     */
    public function delete(\Smile\Seller\Api\Data\SellerInterface $seller)
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
    public function deleteByIdentifier($sellerId)
    {
        $seller = $this->get($sellerId);

        return $this->delete($seller);
    }

    /**
     * Retrieve Attribute Set Id to use for this entity, if any
     *
     * @return null|int
     */
    public function getEntityAttributeSetId()
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
     * @param \Smile\Seller\Api\Data\SellerInterface $seller The seller
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     */
    private function applyAttributeSet(\Smile\Seller\Api\Data\SellerInterface $seller)
    {
        $attributeSetId = $this->getEntityAttributeSetId();
        if (null !== $attributeSetId) {
            $seller->setAttributeSetId($attributeSetId);
        }

        return $seller;
    }
}
