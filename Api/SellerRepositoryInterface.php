<?php

namespace Smile\Seller\Api;

/**
 * @api
 */
interface SellerRepositoryInterface
{
    /**
     * Create seller service
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller
     * @return \Smile\Seller\Api\Data\SellerInterface
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Smile\Seller\Api\Data\SellerInterface $seller);

    /**
     * Get info about seller by seller id
     *
     * @param int $sellerId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sellerId, $storeId = null);

    /**
     * Delete seller by identifier
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller seller which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Smile\Seller\Api\Data\SellerInterface $seller);

    /**
     * Delete seller by identifier
     *
     * @param int $sellerId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteByIdentifier($sellerId);
}
