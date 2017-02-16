<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\Locator;

use Magento\Store\Api\Data\StoreInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Offer Locator Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface LocatorInterface
{
    /**
     * @return SellerInterface
     */
    public function getSeller();

    /**
     * @return StoreInterface
     */
    public function getStore();
}
