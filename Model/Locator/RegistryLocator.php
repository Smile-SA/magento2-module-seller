<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Registry Locator for seller
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SellerInterface
     */
    private $seller;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
     */
    public function getSeller()
    {
        if (null !== $this->seller) {
            return $this->seller;
        }

        if ($product = $this->registry->registry('current_seller')) {
            return $this->seller = $product;
        }

        throw new NotFoundException(__('Seller was not registered'));
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getStore()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($store = $this->registry->registry('current_store')) {
            return $this->store = $store;
        }

        throw new NotFoundException(__('Store was not registered'));
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getSeller()->getWebsiteIds();
    }
}
