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
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Seller\Api\SellerRepositoryInterface;
use Smile\Seller\Api\Data\SellerInterfaceFactory;

/**
 * Seller Repository
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SellerRepository implements SellerRepositoryInterface
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    private $sellerRepositoryById;

    /**
     * @var array
     */
    private $sellerFactory;

    /**
     * @var null Attribute set name of the entity
     */
    private $sellerAttributeSetName = null;

    /**
     * SellerRepository constructor.
     *
     * @param EntityManager $entityManager    The entity manager
     * @param SellerFactory $sellerFactory    The seller factory
     * @param string|null   $attributeSetName The seller attribute Set Name, if any
     *
     */
    public function __construct(EntityManager $entityManager, SellerFactory $sellerFactory, $attributeSetName = null)
    {
        $this->entityManager          = $entityManager;
        $this->sellerFactory          = $sellerFactory;
        $this->sellerRepositoryById   = [];
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
        $seller = $this->entityManager->save($seller);
        unset($this->sellerRepositoryById[$seller->getId()]);
    }

    /**
     * Get info about seller by seller id
     *
     * @param int $sellerId The seller Id
     * @param int $storeId  The store Id
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param \Smile\Seller\Api\Data\SellerInterface $seller seller which will deleted
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Smile\Seller\Api\Data\SellerInterface $seller)
    {
        $sellerId = $seller->getId();

        $deleteResult = $this->entityManager->delete($seller);

        if ($deleteResult && isset($this->sellerRepositoryById[$sellerId])) {
            unset($this->sellerRepositoryById[$sellerId]);
        }

        return $deleteResult;
    }

    /**
     * Delete seller by identifier
     *
     * @param int $sellerId The seller id
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

    /**
     * Get a retailer by its code
     *
     * @param string $codeRetailer Retailer code
     *
     * @return SellerInterface
     *
     * @throws NoSuchEntityException
     */
    public function getByCode($codeRetailer)
    {
        /** @var Seller\Collection $seller */
        $sellerCollection = $this->sellerCollectionFactory
            ->create()
            ->addFieldToFilter('seller_code', ['eq' => $codeRetailer]);
        /** @var SellerInterface $seller */
        $seller = $sellerCollection->getFirstItem();
        if (!$seller->getId()) {
            throw new NoSuchEntityException(__('Retailer with code "%1" does not exist.', $codeRetailer));
        }
        return $seller;
    }
}
