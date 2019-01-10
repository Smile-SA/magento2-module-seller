<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Florent Maissiat <florent.maissiat@smile.eu>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Api;

/**
 * Seller Repository Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Florent Maissiat <florent.maissiat@smile.eu>
 */
interface SellerRepositoryInterface
{
    /**
     * Create seller service
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller The seller
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Smile\Seller\Api\Data\SellerInterface $seller);

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
    public function get($sellerId, $storeId = null);

    /**
     * Retrieve seller by seller code
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @param int $sellerCode The seller Code
     * @param int $storeId    The store Id
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     */
    public function getByCode($sellerCode, $storeId = null);

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
    public function delete(\Smile\Seller\Api\Data\SellerInterface $seller);

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
    public function deleteByIdentifier($sellerId);

    /**
     * Retrieve Attribute Set Id to use for this entity, if any
     *
     * @return null|int
     */
    public function getEntityAttributeSetId();
}
