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
     * SellerRepository constructor.
     *
     * @param EntityManager $entityManager    The entity manager
     * @param SellerFactory $sellerFactory    The seller factory
     *
     */
    public function __construct(EntityManager $entityManager, SellerFactory $sellerFactory)
    {
        $this->entityManager        = $entityManager;
        $this->sellerFactory        = $sellerFactory;
        $this->sellerRepositoryById = [];
    }

    /**
     * Create seller service
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Smile\Seller\Api\Data\SellerInterface $seller)
    {
        $seller = $this->entityManager->save($seller);
        if ($seller->getId()) {
            $this->sellerRepositoryById[$seller->getId()] = $seller;
        }
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
        if (!isset($this->sellerRepositoryById[$sellerId])) {
            $sellerModel = $this->sellerFactory->create();

            if (null !== $storeId) {
                $sellerModel->setStoreId($storeId);
            }

            $seller = $this->entityManager->load($sellerModel, $sellerId);

            if (!$seller->getId()) {
                $exception = new NoSuchEntityException();
                throw $exception->singleField($seller->getIdFieldName(), $sellerId);
            }

            $this->sellerRepositoryById[$sellerId] = $seller;
        }

        return $this->sellerRepositoryById[$sellerId];
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
}
