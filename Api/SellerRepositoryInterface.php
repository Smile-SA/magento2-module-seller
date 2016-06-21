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

namespace Smile\Seller\Api;

/**
 * Seller Repository Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
interface SellerRepositoryInterface
{
    /**
     * Create seller service
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller The seller
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Smile\Seller\Api\Data\SellerInterface $seller);

    /**
     * Get info about seller by seller id
     *
     * @param int $sellerId The seller Id
     * @param int $storeId  The store Id
     *
     * @return \Smile\Seller\Api\Data\SellerInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sellerId, $storeId = null);

    /**
     * Delete seller by identifier
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $seller seller which will deleted
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Smile\Seller\Api\Data\SellerInterface $seller);

    /**
     * Delete seller by identifier
     *
     * @param int $sellerId The seller id
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteByIdentifier($sellerId);
}
